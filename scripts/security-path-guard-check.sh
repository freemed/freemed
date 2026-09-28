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
#
# Task 4.1 hardened check 2 against the three weaknesses the reviews found in it
# (all Minor, none regressing a shape that existed in the tree): the resolver
# match is now anchored on a word boundary, it spans one level of nested
# parentheses, it requires the callable two-argument form (the one-argument form
# the old pattern blessed is an ArgumentCountError on PHP 8), and a resolution
# call applied directly to raw request input is a failure rather than a pass.
# WHAT THIS GATE TREATS AS CONFINEMENT and the limits that remain are documented
# immediately above path_guard_scan(); the --self-test below carries one fixture
# per fix.
#
#   ./scripts/security-path-guard-check.sh              scan the tree
#   ./scripts/security-path-guard-check.sh --self-test  classify the fixtures
#                                                       in the documented way
set -u

cd "$(dirname "${BASH_SOURCE[0]}")/.." || { echo "security-path-guard-check: cannot cd to repo root"; exit 2; }
fail=0
# file-scope (not local) so the EXIT trap of --self-test can still see it under
# set -u once selftest() has returned
st_tmp=''

SINKS='(readfile|file_get_contents|file_put_contents|fopen|include|include_once|require|require_once|unlink|file_exists|is_file|is_readable|highlight_file|show_source|opendir|scandir|rename|copy|chdir|realpath)[[:space:]]*\('
INPUT='\$_GET|\$_POST|\$_REQUEST|\$_FILES|\$_COOKIE|\$_SERVER'

# ---------------------------------------------------------------------------
# Check 2's scan, shared by the tree run and --self-test.
#
# WHAT THIS GATE TREATS AS CONFINEMENT, and why (rewritten by Task 4.1):
#
#   * help_resolve_path(...) — the resolver check 1 mandates, and ONLY in its
#     callable form. A call with at least two arguments (path, base_dir) is
#     removed from the line before the input test, so
#	readfile(help_resolve_path($_SERVER['PATH_INFO'], dirname(__FILE__)));
#	readfile(help_resolve_path(sprintf('%s', $_SERVER['PATH_INFO']), dirname(__FILE__)));
#     pass, the first one including the nested dirname(...). A call with one or
#     zero arguments does NOT count, because lib/help-path.php requires both
#     parameters: such a line confines nothing at run time (it is an
#     ArgumentCountError on PHP 8), so it is reported like any other unconfined
#	readfile(help_resolve_path($_SERVER['PATH_INFO']));     <-- reported
#     sink. Before Task 4.1 the pattern's `[^()]*` could not span the nested
#     dirname(...), so the gate FAILED the real two-argument call while BLESSING
#     the one-argument form — the exact inversion of the fix list, which
#     mandates the resolver and cannot be satisfied by a call that cannot run.
#     The match is anchored on a word boundary ((^|[^A-Za-z0-9_])), so a helper
#     merely NAMED like the resolver (my_help_resolve_path(), or any other name
#     ending in those letters) is not confinement and is still reported; the
#     unanchored substring match it replaces counted it as confinement.
#   * realpath()/basename() elsewhere on the line — with one exception. A
#     resolution call applied DIRECTLY to a raw request superglobal,
#	readfile(realpath($_GET['f']));
#     is RESOLUTION, not confinement: realpath() canonicalises whatever path the
#     client wrote, which is a walk. It is therefore reported, because it is the
#     same class of bug as raw input into the sink. basename() is deliberately
#     NOT part of that rule: it discards the directory part whatever the input
#     is, so it cannot walk anywhere.
#
# KNOWN LIMITS — stated so this is not read as a proof:
#   * same-line only (a path built on one line and used on another is invisible);
#   * one level of nested parentheses inside help_resolve_path(...);
#   * the arity half of the resolver rule is "is there a comma at argument depth
#     1", so a ONE-argument call whose argument contains a comma (e.g.
#     help_resolve_path(implode(',', $x))) is still counted as callable. That
#     direction is fail-open and it weakens only the arity half of the rule, not
#     the confinement half: nothing else on such a line is excused;
#   * the direct-application rule does not catch realpath() applied to a
#     CONCATENATION containing raw input, e.g. readfile(realpath('/x/'.$_GET['f'])),
#     which is the same bug shape (resolution of an attacker-chosen path) but is
#     not the shape the reviews measured, and flagging every line that mentions a
#     superglobal inside realpath() would false-positive on the legitimate
#     realpath(prefix.basename($_GET['f'])).
# ---------------------------------------------------------------------------
path_guard_scan() {   # $@ = files to scan
	[ "$#" -gt 0 ] || return 0
	grep -nE "$SINKS" "$@" 2>/dev/null \
	| grep -E "$INPUT" \
	| awk '{ line = $0;
	         gsub(/(^|[^A-Za-z0-9_])help_resolve_path[[:space:]]*[(]([^,()]|[(][^()]*[)])*,[^()]*([(][^()]*[)][^()]*)*[)]/, "", line);
	         if (line ~ /realpath[[:space:]]*[(][[:space:]]*\$_(GET|POST|REQUEST|FILES|COOKIE|SERVER)/) { print; next }
	         if (line ~ /realpath[[:space:]]*[(]|basename[[:space:]]*[(]/) next;
	         if (line ~ /\$_(GET|POST|REQUEST|FILES|COOKIE|SERVER)/) print }' || true
}

# ---------------------------------------------------------------------------
# --self-test: the classification contract above, as fixtures. Each row is
# name|source|expected, where FAIL means "the scan must report this line" and
# PASS means "the scan must not".
#
# Every one of the Task 4.1 fixes has a row that fails if the fix is undone, so
# the contract cannot be quietly reverted:
#   * word-boundary anchor  -> misnamed_one_arg, misnamed_two_arg (a helper that
#                              is merely NAMED like the resolver must still be
#                              reported);
#   * nested parentheses /  -> resolver_two_args and resolver_nested must PASS
#     callable form            while resolver_one_arg must FAIL (a one-argument
#                              call is an ArgumentCountError on PHP 8 and
#                              confines nothing);
#   * raw superglobal into  -> realpath_raw must FAIL.
#     a resolution call
# The expected row COUNT is asserted too, so deleting a row fails the self-test
# rather than shrinking it silently.
# ---------------------------------------------------------------------------
selftest() {
	local rows row name src expect out verdict bad n expected
	st_tmp="$(mktemp -d)"
	trap '[ -n "$st_tmp" ] && rm -rf "$st_tmp"' EXIT
	rows=(
		"raw_input|<?php readfile(\$_GET['f']);|FAIL"
		"raw_pathinfo|<?php readfile(\$_SERVER['PATH_INFO']);|FAIL"
		"realpath_raw|<?php readfile(realpath(\$_GET['f']));|FAIL"
		"misnamed_one_arg|<?php readfile(my_help_resolve_path(\$_SERVER['PATH_INFO']));|FAIL"
		"misnamed_two_arg|<?php readfile(my_help_resolve_path(\$_SERVER['PATH_INFO'], dirname(__FILE__)));|FAIL"
		"helper_and_raw|<?php readfile(\$_GET['f'] . help_resolve_path(\$_SERVER['PATH_INFO']));|FAIL"
		"resolver_one_arg|<?php readfile(help_resolve_path(\$_SERVER['PATH_INFO']));|FAIL"
		"resolver_two_args|<?php readfile(help_resolve_path(\$_SERVER['PATH_INFO'], dirname(__FILE__)));|PASS"
		"resolver_nested|<?php readfile(help_resolve_path(sprintf('%s', \$_SERVER['PATH_INFO']), dirname(__FILE__)));|PASS"
		"basename_raw|<?php readfile(basename(\$_GET['f']));|PASS"
	)
	expected=10
	echo "security-path-guard-check --self-test"
	bad=0
	n=0
	for row in "${rows[@]}"; do
		name="${row%%|*}"
		expect="${row##*|}"
		src="${row#*|}"; src="${src%|*}"
		printf '%s\n' "$src" > "$st_tmp/$name.php"
		out="$(path_guard_scan "$st_tmp/$name.php")"
		if [ "$expect" = FAIL ]; then
			[ -n "$out" ] && verdict="flagged" || verdict="MISSED (must be flagged)"
		else
			[ -z "$out" ] && verdict="not flagged" || verdict="FALSE POSITIVE"
		fi
		n=$((n + 1))
		if [ "$verdict" = "flagged" ] || [ "$verdict" = "not flagged" ]; then
			printf '  %-16s expect %-4s  %-12s  %s\n' "$name" "$expect" "$verdict" "$src"
		else
			printf '  %-16s expect %-4s  %-12s  %s\n' "$name" "$expect" "*** $verdict ***" "$src"
			bad=$((bad + 1))
		fi
	done
	if [ "$n" -ne "$expected" ]; then
		echo "security-path-guard-check: self-test FAILED (ran $n fixtures, expected $expected - a row was removed)"
		return 1
	fi
	if [ "$bad" -ne 0 ]; then
		echo "security-path-guard-check: self-test FAILED ($bad of $n fixtures misclassified)"
		return 1
	fi
	echo "security-path-guard-check: self-test PASSED ($n of $n fixtures: arity, word boundary, nesting and the resolution-call rule)"
	return 0
}

if [ "${1:-}" = "--self-test" ]; then
	selftest
	exit $?
fi

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
top=()
while IFS= read -r f; do top+=("$f"); done < <(find . -maxdepth 1 -name '*.php' | sort)
# An empty scan must never read as "clean": if there is nothing to scan, the
# gate has not measured anything.
if [ "${#top[@]}" -eq 0 ]; then
	echo "security-path-guard-check: no top-level .php files found to scan" >&2
	exit 2
fi

hits=$(path_guard_scan "${top[@]}")
if [ -n "$hits" ]; then
	echo "PATH GUARD FAIL: request input reaches a filesystem sink without confinement,"
	echo "or through a help_resolve_path() call that cannot run (it requires BOTH"
	echo "arguments, path and base_dir - see the note above path_guard_scan()):"
	echo "$hits"
	fail=1
fi

if [ "$fail" -ne 0 ]; then
	echo "security-path-guard-check: FAILED"
	exit 1
fi
echo "security-path-guard-check: clean"
