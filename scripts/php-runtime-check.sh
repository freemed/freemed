#!/bin/sh
# php-runtime-check.sh - assert that the PHP interpreter serving this tree is
# at or above the PHP2SHELL fix floor.
#
# WHY: CVE-2026-109618 and CVE-2026-109619 are unauthenticated RCE in PHP's
# request parser, fixed in 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12. No file in this
# repository can mitigate them (the request is parsed before any entrypoint
# runs), so the only control that matters is which interpreter the SAPI is
# running. doc/PHP2SHELL_AUDIT is the inventory; this script is the check.
#
# USAGE
#   ./scripts/php-runtime-check.sh                 # check the `php` on PATH
#   PHP_BIN=/path/to/php ./scripts/php-runtime-check.sh
#   ./scripts/php-runtime-check.sh --version 8.3.35  # check a version string
#   ./scripts/php-runtime-check.sh 8.4.27          # raise the floor explicitly
#   ./scripts/php-runtime-check.sh --self-test     # prove the comparison bites
#
# EXIT CODES (fail closed, per the probe discipline in
# legacy-php-security-remediation):
#   0  interpreter at or above the floor
#   1  interpreter BELOW the floor - vulnerable
#   2  INCONCLUSIVE: no interpreter, unparseable version, or a bad floor
# Exit 2 is never a pass. A script that cannot measure must not report "fine".
#
# NOTE ON WHERE THE FLOOR IS MEASURED: this asks the CLI SAPI, because that is
# what runs in the container and in the test harness. A site whose web SAPI is
# a different build (distro mod_php, php-fpm) must run the equivalent check in
# the process that actually serves requests - `php -i` there, or a probe page.
# A distro backport may fix these CVEs without moving PHP_VERSION; check the
# distro advisory for those installs (doc/PHP2SHELL_AUDIT section 5, row 3).

set -u

DEFAULT_MIN="8.3.36"

die_usage() {
	echo "usage: $0 [--version VERSION] [--self-test] [MIN_VERSION]" >&2
	exit 2
}

self_test() {
	# The comparison is the whole control. Prove it accepts the floor, refuses
	# the release below it, and stays INCONCLUSIVE (2) - not "pass" - on junk.
	rc=0
	for row in "8.3.36:0" "8.3.35:1" "8.2.34:1" "8.4.27:0" "9.0.0:0" "8.3.36-dev:0" "x.y.z:2"; do
		want=${row##*:}
		ver=${row%:*}
		sh "$0" --version "$ver" >/dev/null 2>&1
		got=$?
		if [ "$got" -eq "$want" ]; then
			printf 'self-test %-12s expect %s got %s  OK\n' "$ver" "$want" "$got"
		else
			printf 'self-test %-12s expect %s got %s  FAIL\n' "$ver" "$want" "$got"
			rc=1
		fi
	done
	if [ "$rc" -eq 0 ]; then
		echo "self-test PASSED (comparison accepts the floor, refuses 8.3.35, and fails closed on junk)"
	else
		echo "self-test FAILED"
	fi
	exit "$rc"
}

MIN="$DEFAULT_MIN"
MODE="binary"
VERSION=""

while [ $# -gt 0 ]; do
	case "$1" in
		--self-test) self_test ;;
		--version)
			[ $# -ge 2 ] || die_usage
			MODE="string"
			VERSION="$2"
			shift 2
			;;
		--version=*)
			MODE="string"
			VERSION="${1#--version=}"
			shift
			;;
		-h|--help) die_usage ;;
		-*) die_usage ;;
		*) MIN="$1"; shift ;;
	esac
done

# Reject a malformed floor rather than comparing against it.
case "$MIN" in
	''|*[!0-9.]*) echo "php-runtime-check: INCONCLUSIVE - floor '$MIN' is not a version" >&2; exit 2 ;;
esac

if [ "$MODE" = "string" ]; then
	MEASURED="$VERSION"
	SOURCE="--version argument"
else
	PHP_BIN="${PHP_BIN:-php}"
	if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
		echo "php-runtime-check: INCONCLUSIVE - no '$PHP_BIN' on PATH (set PHP_BIN, or run this inside the container: docker run --rm -v \"\$PWD\":/app -w /app php:8.3-cli sh scripts/php-runtime-check.sh)" >&2
		exit 2
	fi
	MEASURED=$("$PHP_BIN" -r 'echo PHP_VERSION;' 2>/dev/null) || MEASURED=""
	SOURCE="$PHP_BIN"
	if [ -z "$MEASURED" ]; then
		echo "php-runtime-check: INCONCLUSIVE - '$PHP_BIN' did not report PHP_VERSION" >&2
		exit 2
	fi
fi

# Normalise a suffix such as "8.3.36-dev"/"8.4.27RC1" to its numeric prefix for
# comparison; the numeric part is what the fix floor is stated in.
NUMERIC=$(printf '%s' "$MEASURED" | sed 's/^\([0-9][0-9]*\.[0-9][0-9]*\.[0-9][0-9]*\).*/\1/')
case "$NUMERIC" in
	*[!0-9.]*|*..*|'')
		echo "php-runtime-check: INCONCLUSIVE - unparseable version '$MEASURED'" >&2
		exit 2
		;;
esac

lowest=$(printf '%s\n%s\n' "$MIN" "$NUMERIC" | sort -V | head -1)

if [ "$lowest" = "$MIN" ]; then
	echo "php-runtime-check: OK - PHP $MEASURED (from $SOURCE) is at or above the PHP2SHELL fix floor $MIN"
	exit 0
fi

cat >&2 <<EOF
php-runtime-check: VULNERABLE - PHP $MEASURED (from $SOURCE) is below the PHP2SHELL
fix floor $MIN. CVE-2026-109618 and CVE-2026-109619 are unauthenticated RCE in
the request parser: no application code can block them, and every request to
this interpreter is in scope, authenticated or not.

Fix: run 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 or later (docker/Dockerfile pins the
container's base by digest at 8.3.36). Stopgap only, and both must be in
configuration loaded BEFORE the request is parsed, followed by a worker
restart: session.upload_progress.enabled=Off, plus fatal_error_backtraces=0 on
8.5. Inventory, exposure and per-layout verdicts: doc/PHP2SHELL_AUDIT.
EOF
exit 1
