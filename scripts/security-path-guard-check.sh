#!/usr/bin/env bash
# scripts/security-path-guard-check.sh
#
# Task 2.7 — the regression gate for the help-path traversal class (Task 1.2).
# Two checks, both deliberately small and explicable:
#
#   1. help.php must resolve $_SERVER['PATH_INFO'] through help_resolve_path()
#      (lib/help-path.php), which validates the ui/locale/page tokens and
#      realpath()-confines the result to the help directory.
#   2. No top-level request script may feed request input ($_GET, $_POST,
#      $_REQUEST, $_FILES, $_COOKIE, $_SERVER) into a filesystem sink on the same
#      line without realpath()/basename() confinement.
#
# Same-line and keyword-based, like the SQL gate: it cannot see a path built on
# line 3 and used on line 9, but it fails closed on the shape that caused the
# finding and is cheap enough to run on every change.
set -u

cd "$(dirname "${BASH_SOURCE[0]}")/.." || { echo "security-path-guard-check: cannot cd to repo root"; exit 2; }
fail=0

# --- 1. help.php must route through the resolver ---------------------------
if [ ! -f help.php ]; then
	echo "security-path-guard-check: help.php not found" >&2
	exit 2
fi
if ! grep -qE 'help_resolve_path *\(' help.php; then
	echo "PATH GUARD FAIL: help.php does not call help_resolve_path()"
	fail=1
fi
# and it must not reach a filesystem sink with the raw PATH_INFO
raw=$(grep -nE "readfile|file_get_contents|fopen|include|require|highlight_file|show_source" help.php \
      | grep -E '\$_SERVER\[.PATH_INFO.\]|\$_GET|\$_POST|\$_REQUEST' \
      | grep -vE 'help_resolve_path *\(' || true)
if [ -n "$raw" ]; then
	echo "PATH GUARD FAIL: help.php passes raw request input to a filesystem sink:"
	echo "$raw"
	fail=1
fi

# --- 2. top-level request scripts: input into a path sink ------------------
sinks='(readfile|file_get_contents|file_put_contents|fopen|include|include_once|require|require_once|unlink|file_exists|is_file|is_readable|highlight_file|show_source|opendir|scandir|rename|copy|chdir|realpath)[[:space:]]*\('
top=()
while IFS= read -r f; do top+=("$f"); done < <(find . -maxdepth 1 -name '*.php' | sort)

# Confinement is tested, not assumed from line shape: help_resolve_path()
# (lib/help-path.php — the resolver check 1 mandates) and realpath()/basename()
# are removed from the line first, and whatever request input is left over
# still counts as raw. So a legitimate one-line refactor
#	readfile(help_resolve_path($_SERVER['PATH_INFO']));
# passes (the only input on the line went through the resolver), while the
# pre-fix readfile($_SERVER['PATH_INFO']); still fails, and so does a line that
# mixes a resolver call with unconfined input, e.g.
#	readfile($_GET['f'] . help_resolve_path($_SERVER['PATH_INFO']));
hits=$(grep -nE "$sinks" "${top[@]}" 2>/dev/null \
      | grep -E '\$_GET|\$_POST|\$_REQUEST|\$_FILES|\$_COOKIE|\$_SERVER' \
      | awk '{ confined = $0;
               gsub(/help_resolve_path[[:space:]]*[(][^()]*[)]/, "", confined);
               if ($0 ~ /realpath[[:space:]]*[(]|basename[[:space:]]*[(]/) next;
               if (confined ~ /\$_(GET|POST|REQUEST|FILES|COOKIE|SERVER)/) print }' || true)
if [ -n "$hits" ]; then
	echo "PATH GUARD FAIL: request input reaches a filesystem sink without confinement:"
	echo "$hits"
	fail=1
fi

if [ "$fail" -ne 0 ]; then
	echo "security-path-guard-check: FAILED"
	exit 1
fi
echo "security-path-guard-check: clean"
