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
# separator, a dot-segment, a percent-escape, a NUL, a backslash or a newline
# must be REJECTED before it can reach the filesystem, not answered as a lookup
# miss.
#
# This script is the executable form of that requirement. It is a gate, not a
# demo: a hostile row answered with HTTP 200 "Help index ... not present." is
# reported as UNVALIDATED and FAILS, because that is exactly the pre-fix
# behaviour the gate exists to catch (measured on both servers before the fix
# landed - see tests/security/evidence/path-sink-audit.md).
#
# THE NEWLINE ROW (fix round 1). PCRE's `$` matches immediately before a final
# newline, so `preg_match('/^[A-Za-z0-9._-]+$/', "abc\n")` returns true on PHP
# 8.3 and Apache hands PHP exactly that token: `GET /chtml.php/abc%0A` was
# answered 200 "Help index abc\n not present." by the reviewed (unanchored)
# validation. The token class is now anchored with \z, and the row is in the
# hostile set. Its verdict accepts BOTH honest outcomes, because the two servers
# of the verify stack differ: Apache delivers the raw 0x0A byte to PHP, so the
# row passes only when PHP rejects it (400); nginx strips it upstream, so what
# PHP validates is the clean name and the row passes as NORMALISED UPSTREAM - as
# long as no raw newline byte comes back inside the echoed token. Either way the
# assertion is the same and is measured, not assumed: no raw newline reaches the
# code under test.
#
# Usage:
#   BASE=http://localhost:38081 DOCROOT=/var/www/html ./repro-chtml-path.sh
#   ./repro-chtml-path.sh --self-test
#
# Environment:
#   BASE      base URL of the deployment under test (default http://localhost:38081)
#   DOCROOT   document root of that deployment, on disk. REQUIRED for a PASS:
#             it is used ONLY to install and remove a throwaway legitimate
#             archive so the "a valid page still serves" row can be measured.
#             Without it that row is UNMEASURED, and a run whose positive
#             control was never measured cannot pass (a stub answering 400 for
#             every request - including the legitimate page - must not be able
#             to satisfy this gate). Nothing else is written.
#   TIMEOUT   per-request curl timeout in seconds (default 15)
#
# Exit codes:
#   0  every hostile row reached PHP and was rejected (HTTP 400 "Invalid CHTML
#      page name.") or was refused upstream by the web server before PHP ran;
#      no response carried file contents; no newline row came back with a raw
#      newline in the token; AND the legitimate page was measured to serve
#   1  FAILED - a hostile row was answered as a lookup miss (unvalidated), a
#      response carried file contents, or the legitimate page stopped serving
#   2  inconclusive - either NO hostile row reached PHP at all, or the
#      legitimate-page control was not measured (no usable DOCROOT), so nothing
#      about the code under test was measured (a vacuous pass is never
#      reported as clean)
#
#   --self-test exits 0 when every assertion held, 1 when one failed, 2 when the
#   fixture could not be started.
#
# Only `set -u` is used: every row must be attempted even when an earlier curl
# fails.
set -u

SELF_SRC="${BASH_SOURCE[0]:-}"
# Resolve the script to an absolute, symlink-free path. `--self-test` re-invokes
# this file in a child bash, so a bare `$BASH_SOURCE` breaks whenever the script
# was found through PATH (BASH_SOURCE is then a bare name) or through a symlinked
# shim: the child's working directory may not hold it. An absolute real path
# always works.
resolve_self() {
  local p="$1"
  if command -v realpath >/dev/null 2>&1; then
    realpath -m -- "$p" 2>/dev/null && return 0
  fi
  if readlink -f -- "$p" >/dev/null 2>&1; then
    readlink -f -- "$p" && return 0
  fi
  printf '%s' "$p"
}
SELF="$SELF_SRC"
case "$SELF_SRC" in
  */*) ;;
  *) SELF="$(command -v -- "$SELF_SRC" 2>/dev/null || printf '%s' "$SELF_SRC")" ;;
esac
case "$SELF" in
  /*) ;;
  *) SELF="${PWD%/}/$SELF" ;;
esac
SELF="$(resolve_self "$SELF")"

BASE="${BASE:-http://localhost:38081}"
DOCROOT="${DOCROOT:-}"
TIMEOUT="${TIMEOUT:-15}"
TMPDIR="${TMPDIR:-/tmp}"

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
#   abc%0A                        TRAILING NEWLINE (fix round 1). PCRE `$`
#                                 matches before a final newline, so the
#                                 unanchored token class accepted "abc\n" and
#                                 Apache delivered that raw byte to PHP; the
#                                 anchor is now \z. Classified by its own rule -
#                                 see THE NEWLINE ROW above.
#   zzreview%0A                   the same class, on the name the reviewer used
#   ../../../../etc/passwd        raw dot-segments, for the record
HOSTILE_TOKENS=(
  '..%2f..%2f..%2fetc%2fpasswd'
  '..%2fzzchtmlprobe'
  '%2e%2e%2fzzchtmlprobe'
  '%252e%252e%2fzzchtmlprobe'
  '..%5Czzchtmlprobe'
  'zzchtmlprobe%2500'
  'abc%0A'
  'zzreview%0A'
  '../../../../etc/passwd'
)

# The newline rows get the dedicated verdict described in the header instead of
# the generic "rejected / refused upstream / unvalidated" one.
is_newline_row() {
  case "$1" in *%0A) return 0 ;; *) return 1 ;; esac
}
# The token the server delivers to PHP when it strips the newline upstream.
newline_row_clean_name() { printf '%s' "${1%\%0A}"; }

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

# ---------------------------------------------------------------------------
# run_gate: the measurement itself.
# ---------------------------------------------------------------------------
run_gate() {
if ! TMPBODY="$(mktemp "${TMPDIR%/}/freemed-chtml-body.XXXXXX")"; then
  printf 'repro: mktemp failed in %s\n' "${TMPDIR%/}" >&2
  exit 2
fi

FAIL=0
ROWS=0
UPSTREAM=0
REJECTED=0
UNVALIDATED=0
LEAKED=0
NORMALISED=0
FIXTURE_INSTALLED=0

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
printf 'fixture: %s\n' "$([ -n "$DOCROOT" ] && printf '%s/doc/%s.chtml (installed for this run, removed on exit)' "$DOCROOT" "$FIXTURE_NAME" || printf 'none (DOCROOT not set: the "valid page still serves" row is UNMEASURED, so this run cannot pass)')"

# ---------------------------------------------------------------------------
# 0. Legitimate page control. Without this row a rejected hostile token could
#    simply mean "this endpoint refuses everything here" - which is exactly the
#    stub that must NOT be able to satisfy this gate.
# ---------------------------------------------------------------------------
printf '\n== control: a legitimate CHTML page ==\n'
LEGIT_OK=0
CONTROL_MEASURED=0
if [ -n "$DOCROOT" ]; then
  if [ -w "$DOCROOT" ] && [ -d "$DOCROOT/doc" ]; then
    fixtmp="$(mktemp -d "${TMPDIR%/}/freemed-chtml-fixture.XXXXXX")" || exit 2
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
  CONTROL_MEASURED=1
  if [ "$F_CODE" = "200" ] && grep -qF -- "$FIXTURE_MARKER" "$TMPBODY"; then
    printf '    verdict: VALID PAGE STILL SERVES (the archive content came back)\n'
    LEGIT_OK=1
  else
    printf '    >>> FAILED: the legitimate page did not serve (HTTP %s). The endpoint is\n' "$F_CODE"
    printf '    broken for valid input - this is a regression, not a hardening.\n'
    FAIL=1
  fi
else
  printf '    verdict: UNMEASURED (no writable DOCROOT given); this row proves nothing, and\n'
  printf '    without it a gate that answers 400 for EVERY request would look clean.\n'
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
  elif is_newline_row "$tok"; then
    # Dedicated rule: the assertion is "no raw 0x0A byte reaches the code under
    # test inside the page token". PHP rejecting the token satisfies it; the web
    # server stripping the newline before PHP does too. A response that echoes
    # the token WITH the newline in it is the reviewed bypass and fails.
    clean="$(newline_row_clean_name "$tok")"
    body="$(cat "$TMPBODY")"
    if [ "$body" = "$(printf 'Help index %s\n not present.' "$clean")" ]; then
      printf '     verdict: >>> UNVALIDATED - a RAW NEWLINE reached PHP inside the page token and\n'
      printf '     was answered as a lookup miss. This is the reviewed bypass: PCRE $ matches\n'
      printf '     before a final newline, so the unanchored token class accepted it.\n'
      UNVALIDATED=$((UNVALIDATED + 1))
      FAIL=1
    elif [ "$body" = "Help index ${clean} not present." ]; then
      printf '     verdict: NORMALISED UPSTREAM - the web server stripped the newline before PHP,\n'
      printf '     so the token PHP validated was the clean name "%s" and no raw newline\n' "$clean"
      printf '     byte reached the code under test (measured: Apache delivers the raw byte to\n'
      printf '     PHP, nginx does not - hence two honest pass shapes for this row)\n'
      NORMALISED=$((NORMALISED + 1))
    else
      printf '     verdict: >>> UNVALIDATED - unexpected answer for a newline token. The raw byte\n'
      printf '     must either be rejected by PHP (400) or be removed upstream; neither happened.\n'
      UNVALIDATED=$((UNVALIDATED + 1))
      FAIL=1
    fi
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
printf '\n%s hostile row(s): %s rejected by PHP, %s reached PHP unvalidated, %s leaked (contents), %s refused upstream, %s newline row(s) normalised upstream\n' \
  "${#HOSTILE_TOKENS[@]}" "$REJECTED" "$UNVALIDATED" "$LEAKED" "$UPSTREAM" "$NORMALISED"
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
if [ "$CONTROL_MEASURED" -ne 1 ]; then
  printf '>>> RESULT: INCONCLUSIVE - the legitimate-page control was NOT measured (DOCROOT=%s),\n' "${DOCROOT:-<unset>}"
  printf '    so "a valid page still serves" was never established and this run says nothing\n'
  printf '    about whether valid input still works. An implementation that answers 400 for\n'
  printf '    EVERY request - including the legitimate page - would otherwise look clean.\n'
  printf '    Re-run with DOCROOT=<document root of the deployment>. Not a pass.\n'
  exit 2
fi
printf '>>> RESULT: PASS - every hostile token%s was rejected before reaching the\n' \
  "$([ "$UPSTREAM" -gt 0 ] && printf ' that reached PHP' || printf '')"
printf '    filesystem, no response carried contents%s.\n' \
  "$([ "$REACHED_PHP" -gt 0 ] && printf ', and %s row(s) were refused upstream' "$UPSTREAM")"
printf '    The legitimate page was measured to still serve, and the %s newline row(s) came\n' "$NORMALISED"
printf '    back with no raw newline in the token (see each row for which layer handled it).\n'
printf '    What was NOT measured: the archive contents themselves (a valid token whose\n'
printf '    doc/*.chtml is missing is answered "Help index ... not present." - that string\n'
printf '    is not a leak and is not counted as one), and any deployment whose web server\n'
printf '    hands PHP different PATH_INFO than the two measured here.\n'
printf '    Sensitivity of this gate is proven by:\n'
printf '      %s --self-test\n' "$SELF"
exit 0
}

# ---------------------------------------------------------------------------
# --self-test: prove the gate fails the implementations it must fail.
#
# Same convention as the sibling tests/security/repro-help-traversal.sh: a tiny
# local HTTP fixture (python3, no docker, no Phase 0.3 stack) that preserves the
# raw path, with four behaviours behind it, and this script re-invoked against
# each in a child bash:
#
#   /unanchored/chtml.php   the REVIEWED implementation - preg_match with `$`
#   /vulnerable/chtml.php   pre-Task-1.3 chtml.php - no validation at all
#   /hardened/chtml.php     the fix - the token class anchored with \z
#   /always400/chtml.php    400 "Invalid CHTML page name." for EVERY request,
#                           the legitimate page included (the stub the reviewed
#                           gate accepted as a PASS when DOCROOT was unset)
#
# The legitimate page is served out of the tar.gz this script installs in the
# fixture docroot, so the positive control is a real measurement against all
# four, not an assumption.
# ---------------------------------------------------------------------------
selftest() {
  if ! command -v python3 >/dev/null 2>&1; then
    printf 'self-test: python3 is required to run the fixture\n' >&2
    exit 2
  fi

  if ! FIXDIR="$(mktemp -d "${TMPDIR%/}/freemed-chtml-fixture-tree.XXXXXX")"; then
    printf 'self-test: mktemp -d failed in %s\n' "${TMPDIR%/}" >&2
    exit 2
  fi
  if ! PORTFILE="$(mktemp "${TMPDIR%/}/freemed-chtml-fixture-port.XXXXXX")"; then
    printf 'self-test: mktemp failed for the port file\n' >&2
    exit 2
  fi
  rm -f "$PORTFILE"

  # Fixture docroot: the gate installs ${FIXTURE_NAME}.chtml into doc/ itself,
  # exactly as it does against a real deployment.
  SELFTEST_DOCROOT="$FIXDIR/docroot"
  mkdir -p "$SELFTEST_DOCROOT/doc" || exit 2

  cat >"$FIXDIR/fixture.py" <<'PYEOF'
#!/usr/bin/env python3
"""Path-info-preserving HTTP fixture for repro-chtml-path.sh --self-test.

Four behaviours behind one server. The request path is NOT normalised: percent
escapes are decoded exactly once, like Apache, and every byte that survives that
is handed to the mode unchanged - so what the gate measures is what the mode
does with the token, not what a normalising client did to the URL first.
Two upstream shapes are reproduced because they are what the verify stack
measures: an encoded slash is refused with 400 (AllowEncodedSlashes Off), and a
decoded `..` path segment is refused with 400 before the handler runs (Apache and
nginx both do this; it is why the raw traversal row never reaches PHP there).

  /vulnerable/chtml.php/<token>   pre-Task-1.3 chtml.php: no validation at all
  /unanchored/chtml.php/<token>   the reviewed implementation:
                                  preg_match('/^[A-Za-z0-9._-]+$/')  <- PCRE `$`
                                  matches before a final newline
  /hardened/chtml.php/<token>     the fix:
                                  preg_match('/^[A-Za-z0-9._-]+\z/')
  /always400/chtml.php/<token>    400 "Invalid CHTML page name." for EVERY
                                  request, including the legitimate page

The legitimate page comes out of <docroot>/doc/<token>.chtml (the tar.gz the
gate installs), so a stub cannot fake it.
"""
import http.server
import os
import re
import sys
import tarfile
import urllib.parse

FIXDIR, PORTFILE, DOCROOT = sys.argv[1], sys.argv[2], sys.argv[3]

MODES = ("vulnerable", "unanchored", "hardened", "always400")
# Python's `$` matches before a final newline, like PCRE without /D: this is the
# reviewed predicate, deliberately reproduced.
RE_UNANCHORED = re.compile(r"^[A-Za-z0-9._-]+$")
# Python's \Z is an absolute end-of-string anchor: the fix.
RE_HARDENED = re.compile(r"^[A-Za-z0-9._-]+\Z")
RE_ENC_SLASH = re.compile(r"%2f|%2F", re.IGNORECASE)


def archive_member(token, resource):
    """Mimic CHTMLReader::GetResource() for the one member the gate asks for."""
    path = os.path.join(DOCROOT, "doc", token + ".chtml")
    if not os.path.isfile(path):
        return None
    if resource.strip("/") not in ("", "index.html"):
        return None
    try:
        with tarfile.open(path, "r:gz") as tf:
            for name in tf.getnames():
                if name.lstrip("./") == "index.html":
                    fh = tf.extractfile(name)
                    return fh.read() if fh else None
    except (tarfile.TarError, OSError):
        return None
    return None


class Handler(http.server.BaseHTTPRequestHandler):
    protocol_version = "HTTP/1.0"

    def log_message(self, *a):  # keep the server log quiet
        pass

    def reply(self, code, body, ctype="text/plain"):
        raw = body.encode("utf-8", "replace") if isinstance(body, str) else body
        self.send_response(code)
        self.send_header("Content-Type", ctype)
        self.send_header("Content-Length", str(len(raw)))
        self.end_headers()
        self.wfile.write(raw)

    def do_GET(self):
        raw = self.path  # handed over untouched: no normalisation of the token
        if RE_ENC_SLASH.search(raw):
            self.reply(400, "<title>400 Bad Request</title>\nfixture: encoded slash refused\n")
            return
        decoded = urllib.parse.unquote(raw)
        # A decoded `..` path segment is refused before any handler runs, which is
        # what both measured servers of the verify stack do. Every other row
        # keeps the bytes it was sent.
        if ".." in decoded.split("/"):
            self.reply(400, "<title>400 Bad Request</title>\nfixture: dot segment refused upstream\n")
            return

        mode = None
        for m in MODES:
            prefix = "/" + m + "/chtml.php"
            if decoded.startswith(prefix):
                mode = m
                pinfo = decoded[len(prefix):]
                break
        if mode is None:
            self.reply(404, "<title>404 Not Found</title>\nfixture: unknown path\n")
            return

        # /chtml.php/<token>/<resource...> : $parts[0] is the empty string before
        # the leading slash, so the token is parts[1].
        parts = pinfo.split("/")
        token = parts[1] if len(parts) > 1 else ""
        resource = "/".join(parts[2:])

        if mode == "always400":
            self.reply(400, "Invalid CHTML page name.")
            return
        if mode == "unanchored" and not RE_UNANCHORED.match(token):
            self.reply(400, "Invalid CHTML page name.")
            return
        if mode == "hardened" and not RE_HARDENED.match(token):
            self.reply(400, "Invalid CHTML page name.")
            return

        member = archive_member(token, resource)
        if member is not None:
            self.reply(200, member, "text/html")
            return
        # chtml.php's lookup-miss answer, with the token echoed as delivered.
        self.reply(200, "Help index %s not present." % token)


httpd = http.server.ThreadingHTTPServer(("127.0.0.1", 0), Handler)
with open(PORTFILE, "w") as fh:
    fh.write(str(httpd.server_address[1]))
httpd.serve_forever()
PYEOF

  python3 "$FIXDIR/fixture.py" "$FIXDIR" "$PORTFILE" "$SELFTEST_DOCROOT" \
    >"$FIXDIR/server.log" 2>&1 &
  FIXPID=$!

  local n=0
  while [ ! -s "$PORTFILE" ] && [ "$n" -lt 100 ]; do
    if ! kill -0 "$FIXPID" 2>/dev/null; then break; fi
    sleep 0.1
    n=$((n + 1))
  done
  if [ ! -s "$PORTFILE" ]; then
    printf 'self-test: fixture did not start; server log follows\n' >&2
    cat "$FIXDIR/server.log" >&2 2>/dev/null
    exit 2
  fi
  local PORT
  PORT="$(cat "$PORTFILE")"
  n=0
  while [ "$n" -lt 50 ]; do
    if [ "$(curl -s -o /dev/null -w '%{http_code}' --max-time 5 \
            "http://127.0.0.1:$PORT/hardened/chtml.php/zzchtmlprobe/index.html")" = "200" ]; then break; fi
    sleep 0.1
    n=$((n + 1))
  done

  printf 'self-test: fixture on 127.0.0.1:%s (vulnerable + unanchored + hardened + always-400\n' "$PORT"
  printf '           chtml.php, path-info preserving, percent-escapes decoded once)\n'
  printf 'self-test: no docker, no Phase 0.3 stack; the fixture tree lives in %s and is removed on exit\n\n' "$FIXDIR"

  SELFTEST_FAIL=0
  local RC=0 CASE_OUT=""
  check_eq() { # desc expected actual
    if [ "$2" = "$3" ]; then
      printf '    PASS  %s (expected %s, got %s)\n' "$1" "$2" "$3"
    else
      printf '    FAIL  %s (expected %s, got %s)\n' "$1" "$2" "$3"
      SELFTEST_FAIL=$((SELFTEST_FAIL + 1))
    fi
  }
  check_nonzero() { # desc actual
    if [ "$2" != "0" ]; then
      printf '    PASS  %s (exit %s, non-zero)\n' "$1" "$2"
    else
      printf '    FAIL  %s (expected a non-zero exit, got 0)\n' "$1"
      SELFTEST_FAIL=$((SELFTEST_FAIL + 1))
    fi
  }
  check_has() { # desc needle file
    if grep -qF -- "$2" "$3"; then
      printf '    PASS  %s\n' "$1"
    else
      printf '    FAIL  %s (not found in output: %s)\n' "$1" "$2"
      SELFTEST_FAIL=$((SELFTEST_FAIL + 1))
    fi
  }
  check_absent() { # desc needle file
    if grep -qF -- "$2" "$3"; then
      printf '    FAIL  %s (should NOT appear in output: %s)\n' "$1" "$2"
      SELFTEST_FAIL=$((SELFTEST_FAIL + 1))
    else
      printf '    PASS  %s\n' "$1"
    fi
  }
  run_case() { # label base docroot
    printf -- '--- %s\n' "$1"
    printf '    target: %s   (DOCROOT=%s)\n' "$2" "$([ -n "$3" ] && printf '%s' "$3" || printf '<unset>')"
    if ! CASE_OUT="$(mktemp "${TMPDIR%/}/freemed-chtml-selftest-out.XXXXXX")"; then
      printf 'self-test: mktemp failed\n' >&2
      exit 2
    fi
    SELFTEST_TEMPS="$SELFTEST_TEMPS $CASE_OUT"
    BASE="$2" DOCROOT="$3" TIMEOUT="$TIMEOUT" TMPDIR="$TMPDIR" \
      bash "$SELF" >"$CASE_OUT" 2>&1
    RC=$?
    sed 's/^/    | /' "$CASE_OUT"
    printf '\n'
  }

  printf '== case A: the REVIEWED implementation (token class unanchored: `$`), DOCROOT given\n'
  printf '   expected: exit 1, the newline row flagged UNVALIDATED\n'
  run_case "A unanchored fixture" "http://127.0.0.1:$PORT/unanchored" "$SELFTEST_DOCROOT"
  check_eq "A exit code is 1 (FAILED)" 1 "$RC"
  check_has "A flags the newline row as the unanchored-regex bypass (raw newline reached PHP)" "a RAW NEWLINE reached PHP inside the page token" "$CASE_OUT"
  check_has "A names the concrete token" "token: abc%0A" "$CASE_OUT"
  check_has "A reports FAILED" ">>> RESULT: FAILED" "$CASE_OUT"
  check_absent "A does not report PASS" ">>> RESULT: PASS" "$CASE_OUT"

  printf '== case B: a stub answering 400 for EVERY request, DOCROOT given\n'
  printf '   expected: exit 1 - the legitimate page must not be faked away\n'
  run_case "B always-400 fixture" "http://127.0.0.1:$PORT/always400" "$SELFTEST_DOCROOT"
  check_eq "B exit code is 1 (FAILED)" 1 "$RC"
  check_has "B fails the positive control" "the legitimate page did not serve" "$CASE_OUT"
  check_has "B reports the control as FAILED" "legitimate page control: FAILED" "$CASE_OUT"
  check_absent "B does not report PASS" ">>> RESULT: PASS" "$CASE_OUT"

  printf '== case B2: the same always-400 stub, DOCROOT UNSET (the reviewed hole)\n'
  printf '   expected: non-zero and never PASS - an unmeasured positive control cannot pass,\n'
  printf '   so ./repro-chtml-path.sh no longer says PASS against a stub that 400s everything\n'
  run_case "B2 always-400 fixture, no DOCROOT" "http://127.0.0.1:$PORT/always400" ""
  check_nonzero "B2 exit code is non-zero" "$RC"
  check_eq "B2 exit code is 2 (inconclusive, not a pass)" 2 "$RC"
  check_has "B2 says the control was not measured" "legitimate-page control was NOT measured" "$CASE_OUT"
  check_absent "B2 does not report PASS" ">>> RESULT: PASS" "$CASE_OUT"

  printf '== case C: the FIX (token class anchored with \\z), DOCROOT given\n'
  printf '   expected: exit 0, newline row rejected by PHP, legitimate page served\n'
  run_case "C hardened fixture" "http://127.0.0.1:$PORT/hardened" "$SELFTEST_DOCROOT"
  check_eq "C exit code is 0 (PASS)" 0 "$RC"
  check_has "C rejects the newline row (PHP, 400)" "token: abc%0A" "$CASE_OUT"
  check_has "C measures the positive control" "VALID PAGE STILL SERVES" "$CASE_OUT"
  check_has "C reports PASS" ">>> RESULT: PASS" "$CASE_OUT"
  check_absent "C never reports UNVALIDATED" "UNVALIDATED" "$CASE_OUT"
  check_has "C still flags the NUL row as rejected by PHP" "token: zzchtmlprobe%2500" "$CASE_OUT"

  printf '== case D: pre-Task-1.3 chtml.php (no validation at all), DOCROOT given\n'
  printf '   expected: exit 1 - the gate catches the original pre-fix shape\n'
  run_case "D vulnerable fixture" "http://127.0.0.1:$PORT/vulnerable" "$SELFTEST_DOCROOT"
  check_eq "D exit code is 1 (FAILED)" 1 "$RC"
  check_has "D flags the fixture traversal row as unvalidated" "token: ..%5Czzchtmlprobe" "$CASE_OUT"
  check_has "D reports FAILED" ">>> RESULT: FAILED" "$CASE_OUT"
  check_absent "D does not report PASS" ">>> RESULT: PASS" "$CASE_OUT"

  printf '== case E: the FIX but DOCROOT unset\n'
  printf '   expected: exit 2 - correct code, but without a measured positive control this\n'
  printf '   run is inconclusive rather than a pass\n'
  run_case "E hardened fixture, no DOCROOT" "http://127.0.0.1:$PORT/hardened" ""
  check_eq "E exit code is 2 (inconclusive)" 2 "$RC"
  check_has "E says the control was not measured" "legitimate-page control was NOT measured" "$CASE_OUT"
  check_absent "E does not report PASS" ">>> RESULT: PASS" "$CASE_OUT"

  if [ "$SELFTEST_FAIL" -eq 0 ]; then
    printf '>>> SELF-TEST PASSED - this gate fails the reviewed (unanchored) validation, fails a\n'
    printf '    stub that answers 400 for every request, and passes only the \\z-anchored code\n'
    printf '    with a measured legitimate-page control\n'
    return 0
  fi
  printf '>>> SELF-TEST FAILED - %s assertion(s) did not hold; do not trust this script as a baseline\n' "$SELFTEST_FAIL"
  return 1
}

FIXDIR=""
PORTFILE=""
FIXPID=""
SELFTEST_DOCROOT=""
SELFTEST_TEMPS=""
selftest_cleanup() {
  [ -n "$SELFTEST_TEMPS" ] && rm -f $SELFTEST_TEMPS
  [ -n "$PORTFILE" ] && rm -f "$PORTFILE"
  [ -n "$FIXPID" ] && { kill "$FIXPID" 2>/dev/null; wait "$FIXPID" 2>/dev/null; }
  [ -n "$FIXDIR" ] && rm -rf "$FIXDIR"
  return 0
}

case "${1:-}" in
  --self-test|--selftest)
    # The self-test re-invokes this file in a child bash, so it needs the script
    # to exist on disk under a usable path. Fail with an explanation instead of a
    # confusing child-process error.
    if [ ! -f "$SELF" ]; then
      printf 'self-test: cannot re-invoke this script: %s is not a readable file.\n' "${SELF:-<no path>}" >&2
      printf '            The gate re-runs itself with a different BASE, which needs a real\n' >&2
      printf '            script path (a piped invocation such as `cat ... | bash` has none).\n' >&2
      exit 2
    fi
    trap selftest_cleanup EXIT
    trap on_signal INT TERM HUP
    selftest
    exit $?
    ;;
  ""|--run)
    run_gate
    exit $?
    ;;
  -h|--help)
    printf 'usage: BASE=<url> DOCROOT=<path> %s [--self-test]\n' "$SELF"
    printf '       DOCROOT is required for exit 0: without a measured legitimate-page\n'
    printf '       control the run is inconclusive (exit 2), never a pass.\n'
    exit 0
    ;;
  *)
    printf 'usage: BASE=<url> DOCROOT=<path> %s [--self-test]\n' "$SELF" >&2
    exit 2
    ;;
esac
