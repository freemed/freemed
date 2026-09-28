#!/usr/bin/env bash
#
# repro-chtml-path.sh - hostile repro / regression gate for the CWE-22 sibling
# sink in chtml.php (Task 1.3).
#
# chtml.php derives the CHTML help archive it opens from the first PATH_INFO
# segment:
#
#     $parts = explode ( '/', $_SERVER['PATH_INFO'] );
#     $file  = $parts[1];
#     ... dirname(__FILE__)."/doc/${file}.chtml"
#
# Two things made that survivable rather than exploitable, and both are
# CONFINEment rather than VALIDATION:
#
#   * the forced ".chtml" suffix (a traversal would have to end in <x>.chtml)
#   * the file_exists() gate plus the fact that the archive is opened as a
#     tarball by CHTMLReader (GetResource() -> tar Oxzf with escapeshellarg on
#     both the archive and the resource, so $path is not a filesystem path at
#     all)
#
# What was missing is the same input validation help.php got in Task 1.2: the
# page token is a simple name (`dojo_en_US` is what ui/dojo/view/
# org.freemedsoftware.ui.chtmlbrowser.tpl emits), so anything containing a
# separator, a dot-segment, a percent-escape, a NUL or a backslash must be
# REJECTED before it can reach the filesystem, not answered as a lookup miss.
#
# This script is the executable form of that requirement. It is a gate, not a
# demo: a hostile row answered with HTTP 200 "Help index ... not present." is
# reported as UNVALIDATED and FAILS, because that is exactly the pre-fix
# behaviour the gate exists to catch (measured on both servers before the fix
# landed - see tests/security/evidence/path-sink-audit.md).
#
# Usage:
#   BASE=http://localhost:38081 ./repro-chtml-path.sh
#   BASE=http://localhost:38081 DOCROOT=/var/www/html ./repro-chtml-path.sh
#
# Environment:
#   BASE      base URL of the deployment under test (default http://localhost:38081)
#   DOCROOT   optional. On-disk document root of that deployment, used ONLY to
#             install and remove a throwaway legitimate archive so the
#             "a valid page still serves" row can be measured. Without it that
#             row is reported UNMEASURED (never assumed) and the exit code is
#             unaffected. Nothing else is written.
#   TIMEOUT   per-request curl timeout in seconds (default 15)
#
# Exit codes:
#   0  every hostile row reached PHP and was rejected (HTTP 400 "Invalid CHTML
#      page name.") or was refused upstream by the web server before PHP ran;
#      no response carried file contents; and, when DOCROOT was given, the
#      legitimate page still served
#   1  FAILED - a hostile row was answered as a lookup miss (unvalidated), a
#      response carried file contents, or the legitimate page stopped serving
#   2  inconclusive - NO hostile row reached PHP at all, so nothing about the
#      code under test was measured (a vacuous pass is never reported as clean)
#
# Only `set -u` is used: every row must be attempted even when an earlier curl
# fails.
set -u

BASE="${BASE:-http://localhost:38081}"
DOCROOT="${DOCROOT:-}"
TIMEOUT="${TIMEOUT:-15}"

# The archive that would be opened if a hostile token were accepted. Its marker
# must never appear in a hostile row's response.
FIXTURE_NAME='zzchtmlprobe'
FIXTURE_MARKER='CHTML-PROBE-FIXTURE-OK'

# Markers that mean "file contents came back". "root:x:0:0" is /etc/passwd,
# "<?php" is any PHP source, and the fixture marker is the archive itself.
LEAK_RE='root:[^:]*:0:0:|<\?php|'"${FIXTURE_MARKER}"
# What chtml.php answers when it rejects a token (post-fix).
REJECT_RE='Invalid CHTML page name\.'
# What a web server answers when it refuses the request before PHP ever runs.
UPSTREAM_RE='<title>400 Bad Request</title>|<title>404 Not Found</title>'

# Hostile tokens. Each one is a URL path segment after /chtml.php/.
#   ..%2f..%2f..%2fetc%2fpasswd   encoded-slash traversal (raw ../ is normalised
#                                 away by both servers before PHP, so the
#                                 encoded form is also tested on a server that
#                                 decodes it)
#   ..%2fzzchtmlprobe             encoded-slash traversal that WOULD land on the
#                                 fixture archive if the suffix rule were loose
#   %2e%2e%2fzzchtmlprobe         encoded dots
#   %252e%252e%2fzzchtmlprobe     double-encoded: reaches PHP as the literal
#                                 text "%2e%2e%2f..." - the shape both servers
#                                 DO hand to PHP
#   ..%5Czzchtmlprobe             encoded backslash (a Windows-style separator)
#   zzchtmlprobe%2500             double-encoded NUL: does NOT reach PHP as a
#                                 NUL byte, it reaches it as the literal "%00",
#                                 which the token class must still reject (a raw
#                                 NUL is refused upstream by both servers)
#   ../../../../etc/passwd        raw dot-segments, for the record
HOSTILE_TOKENS=(
  '..%2f..%2f..%2fetc%2fpasswd'
  '..%2fzzchtmlprobe'
  '%2e%2e%2fzzchtmlprobe'
  '%252e%252e%2fzzchtmlprobe'
  '..%5Czzchtmlprobe'
  'zzchtmlprobe%2500'
  '../../../../etc/passwd'
)

TMPBODY=""
FIXTURE_INSTALLED=0
cleanup() {
  [ -n "$TMPBODY" ] && rm -f "$TMPBODY"
  if [ "$FIXTURE_INSTALLED" = "1" ] && [ -n "$DOCROOT" ]; then
    rm -f "${DOCROOT%/}/doc/${FIXTURE_NAME}.chtml"
  fi
  return 0
}
on_signal() { printf '\nrepro: interrupted\n' >&2; cleanup; exit 130; }
trap cleanup EXIT
trap on_signal INT TERM HUP

if ! TMPBODY="$(mktemp "${TMPDIR:-/tmp}/freemed-chtml-body.XXXXXX")"; then
  printf 'repro: mktemp failed in %s\n' "${TMPDIR:-/tmp}" >&2
  exit 2
fi

FAIL=0
INCONCLUSIVE=0
ROWS=0
UPSTREAM=0
REJECTED=0
UNVALIDATED=0
LEAKED=0

# fetch <url> ; sets F_CODE and leaves the body in $TMPBODY
F_CODE=""
fetch() {
  : >"$TMPBODY"
  F_CODE="$(curl -s --path-as-is --max-time "$TIMEOUT" -o "$TMPBODY" \
    -w '%{http_code}' "$1" 2>/dev/null)"
  F_CODE="${F_CODE:-000}"
}

first_bytes() { head -c 200 "$TMPBODY" | tr '\n\r' '  '; }

printf 'repro: chtml.php PATH_INFO page-token validation (CWE-22 sibling sink)\n'
printf 'base=%s  timeout=%ss  client: --path-as-is (curl would otherwise collapse dot\n' "$BASE" "$TIMEOUT"
printf '       segments client-side and the traversal rows would measure nothing)\n'
printf 'fixture: %s\n' "$([ -n "$DOCROOT" ] && printf '%s/doc/%s.chtml (installed for this run, removed on exit)' "$DOCROOT" "$FIXTURE_NAME" || printf 'none (DOCROOT not set: the "valid page still serves" row is UNMEASURED)')"

# ---------------------------------------------------------------------------
# 0. Legitimate page control. Without this row a rejected hostile token could
#    simply mean "this endpoint refuses everything here".
# ---------------------------------------------------------------------------
printf '\n== control: a legitimate CHTML page ==\n'
LEGIT_OK=0
if [ -n "$DOCROOT" ]; then
  if [ -w "$DOCROOT" ] && [ -d "$DOCROOT/doc" ]; then
    fixtmp="$(mktemp -d "${TMPDIR:-/tmp}/freemed-chtml-fixture.XXXXXX")" || exit 2
    printf '%s\n' "$FIXTURE_MARKER" > "$fixtmp/index.html"
    if ( cd "$fixtmp" && tar czf "${DOCROOT%/}/doc/${FIXTURE_NAME}.chtml" index.html ); then
      FIXTURE_INSTALLED=1
    else
      printf '    fixture: could not be written to %s - row UNMEASURED\n' "$DOCROOT"
    fi
    rm -rf "$fixtmp"
  else
    printf '    fixture: DOCROOT=%s is not writable or has no doc/ subdirectory - row UNMEASURED\n' "$DOCROOT"
  fi
fi

LEGIT_URL="$BASE/chtml.php/${FIXTURE_NAME}/index.html"
printf '    GET %s\n' "$LEGIT_URL"
fetch "$LEGIT_URL"
printf '    HTTP %s  body: %s\n' "$F_CODE" "$(first_bytes)"
ROWS=$((ROWS + 1))
if [ "$FIXTURE_INSTALLED" = "1" ]; then
  if [ "$F_CODE" = "200" ] && grep -qF -- "$FIXTURE_MARKER" "$TMPBODY"; then
    printf '    verdict: VALID PAGE STILL SERVES (the archive content came back)\n'
    LEGIT_OK=1
  else
    printf '    >>> FAILED: the legitimate page did not serve (HTTP %s). The endpoint is\n' "$F_CODE"
    printf '    broken for valid input - this is a regression, not a hardening.\n'
    FAIL=1
  fi
else
  printf '    verdict: UNMEASURED (no writable DOCROOT given); this row proves nothing\n'
fi

# The arbitrary-name row: it must be answered as a lookup miss (200), never with
# file contents, and never silently require a session (no auth gate is added by
# this fix - see the audit's recommendation line).
printf '    GET %s/chtml.php/dojo_en_US/topic.html  (arbitrary name, no cookie)\n' "$BASE"
fetch "$BASE/chtml.php/dojo_en_US/topic.html"
printf '    HTTP %s  body: %s\n' "$F_CODE" "$(first_bytes)"
if grep -qE "$LEAK_RE" "$TMPBODY"; then
  printf '    >>> FAILED: this response carried file contents\n'
  FAIL=1
else
  printf '    verdict: answered without a session and without contents (expected)\n'
fi

# ---------------------------------------------------------------------------
# 1. Hostile rows.
# ---------------------------------------------------------------------------
printf '\n== hostile tokens (no cookie, --path-as-is) ==\n'
for tok in "${HOSTILE_TOKENS[@]}"; do
  url="$BASE/chtml.php/$tok"
  printf -- '---- token: %s\n' "$tok"
  fetch "$url"
  nbytes="$(wc -c <"$TMPBODY" | tr -d ' ')"
  printf '     HTTP %s  bytes=%s  body: %s\n' "$F_CODE" "$nbytes" "$(first_bytes)"
  if grep -qE "$LEAK_RE" "$TMPBODY"; then
    printf '     verdict: >>> LEAKED - file contents came back for a hostile token\n'
    LEAKED=$((LEAKED + 1))
    FAIL=1
  elif grep -qE "$REJECT_RE" "$TMPBODY"; then
    printf '     verdict: REJECTED BY PHP (%s)\n' "$(head -c 40 "$TMPBODY" | tr -d '\n')"
    REJECTED=$((REJECTED + 1))
  elif grep -qE "$UPSTREAM_RE" "$TMPBODY"; then
    printf '     verdict: refused upstream by the web server (PHP never ran)\n'
    UPSTREAM=$((UPSTREAM + 1))
  else
    printf '     verdict: >>> UNVALIDATED - the request reached PHP and was answered as a\n'
    printf '     lookup miss instead of being rejected. This is the pre-fix behaviour the\n'
    printf '     gate exists to catch.\n'
    UNVALIDATED=$((UNVALIDATED + 1))
    FAIL=1
  fi
done

# ---------------------------------------------------------------------------
# 2. Verdict.
# ---------------------------------------------------------------------------
REACHED_PHP=$((REJECTED + UNVALIDATED + LEAKED))
if [ "$LEGIT_OK" = "1" ]; then
  LEGIT_STATE=served
elif [ -n "$DOCROOT" ] && [ "$FIXTURE_INSTALLED" = "1" ]; then
  LEGIT_STATE=FAILED
else
  LEGIT_STATE=unmeasured
fi
printf '\n%s hostile row(s): %s rejected by PHP, %s reached PHP unvalidated, %s leaked, %s refused upstream\n' \
  "${#HOSTILE_TOKENS[@]}" "$REJECTED" "$UNVALIDATED" "$LEAKED" "$UPSTREAM"
printf 'reached PHP at all: %s row(s)\n' "$REACHED_PHP"
printf 'legitimate page control: %s\n' "$LEGIT_STATE"

if [ "$LEAKED" -gt 0 ]; then
  printf '>>> RESULT: FAILED - file contents were returned for a hostile page token\n'
  exit 1
fi
if [ "$FAIL" -ne 0 ]; then
  printf '>>> RESULT: FAILED - %s hostile row(s) unvalidated, legitimate-page control %s\n' \
    "$UNVALIDATED" "$LEGIT_STATE"
  exit 1
fi
if [ "$REACHED_PHP" -eq 0 ]; then
  printf '>>> RESULT: INCONCLUSIVE - no hostile row reached PHP on this deployment, so the\n'
  printf '    token validation was never exercised. Not a pass.\n'
  exit 2
fi
printf '>>> RESULT: PASS - every hostile token%s was rejected before reaching the\n' \
  "$([ "$UPSTREAM" -gt 0 ] && printf ' that reached PHP' || printf '')"
printf '    filesystem, no response carried contents%s.\n' \
  "$([ "$REACHED_PHP" -gt 0 ] && printf ', and %s row(s) were refused upstream' "$UPSTREAM")"
printf '    What was NOT measured: the archive contents themselves (a valid token whose\n'
printf '    doc/*.chtml is missing is answered "Help index ... not present." - that string\n'
printf '    is not a leak and is not counted as one), and any deployment whose web server\n'
printf '    hands PHP different PATH_INFO than the two measured here.\n'
exit 0
