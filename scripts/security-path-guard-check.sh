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
# Task 4.1 fix round 1 then closed the two defects the review of that work found
# in this file (both Minor): check 1's own greps were still unanchored, so a
# helper merely NAMED like the resolver satisfied "help.php routes through the
# resolver" (MINOR-4 - both now use RESOLVER_CALL); and the new resolution-call
# rule reported the benign server-derived shape
# realpath($_SERVER['DOCUMENT_ROOT'] . basename($_GET['f'])), which is the
# false-positive class that gets a gate suppressed (MINOR-5, controller ruling
# R33 - the trigger now distinguishes request-carrying keys from server-derived
# ones, and --self-test pins both directions).
# WHAT THIS GATE TREATS AS CONFINEMENT and the limits that remain are documented
# immediately above path_guard_scan(); the --self-test below carries one fixture
# per fix, in two groups (check 1 and check 2).
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

# The resolver name, anchored on a word boundary exactly like the awk rule above,
# so a helper merely NAMED like the resolver (my_help_resolve_path( ... )) does
# not satisfy check 1. Both the positive grep and the mirrored `grep -v` use it,
# and --self-test carries a fixture for each direction (MINOR-4, fix round 1).
RESOLVER_CALL='(^|[^A-Za-z0-9_])help_resolve_path[[:space:]]*[(]'
# Check 1's own sink subset, unchanged since Task 2.7.
CHECK1_SINKS='readfile|file_get_contents|fopen|include|require|highlight_file|show_source'
CHECK1_INPUT='\$_SERVER\[.PATH_INFO.\]|\$_GET|\$_POST|\$_REQUEST'

# ---------------------------------------------------------------------------
# Check 1, shared by the tree run and --self-test.
#   $1 = the file to inspect (help.php in the tree run; a fixture under test).
# ---------------------------------------------------------------------------
check1_no_resolver() {   # prints the file when it does not call the resolver
	grep -qE "$RESOLVER_CALL" "$1" || printf '%s\n' "$1"
}

check1_raw_sink() {   # prints any sink line carrying raw request input
	grep -nE "$CHECK1_SINKS" "$1" \
	| grep -E "$CHECK1_INPUT" \
	| grep -vE "$RESOLVER_CALL" || true
}

check1_report() {   # prints a report when either condition fails
	local f="$1"
	[ -n "$(check1_no_resolver "$f")" ] \
		&& printf 'does not call help_resolve_path(): %s\n' "$f"
	[ -n "$(check1_raw_sink "$f")" ] && check1_raw_sink "$f"
	return 0
}

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
#     same class of bug as raw input into the sink. Which superglobals count as
#     "raw request input" is the R33 narrowing (fix round 1): $_GET, $_POST,
#     $_REQUEST, $_FILES, $_COOKIE always do, and so does $_SERVER with a
#     request-carrying key — PATH_INFO, QUERY_STRING, REQUEST_URI, HTTP_* and
#     anything else NOT on the server-derived list in the awk rule below.
#     $_SERVER with a key that is pure server configuration or script identity
#     (DOCUMENT_ROOT, PHP_SELF, SCRIPT_FILENAME, SCRIPT_NAME, SERVER_*, REMOTE_*,
#     REQUEST_METHOD, GATEWAY_INTERFACE, HTTPS, REDIRECT_STATUS, HTTP_HOST) does
#     NOT, because a request cannot set those: that exclusion is what stops the
#     rule reporting the benign
#	readfile(realpath($_SERVER['DOCUMENT_ROOT'] . basename($_GET['f'])));
#     shape, in which the input is basename-confined and the triggering
#     superglobal is the server's own document root. A gate that reports benign
#     code is a gate operators suppress, so the narrowing was preferred to
#     documenting the false positive — and it is checked in --self-test from both
#     sides (a server-derived key must pass, a request-carrying key must be
#     reported). An UNQUOTED index ($_SERVER[$k]) is not provably server-derived
#     and is reported; a CONCATENATED index ($_SERVER['LITERAL' . $input]) is
#     classified by its leading quoted literal instead, which is weaker - see
#     the concatenated-index bullet in KNOWN LIMITS below.
#     basename() is deliberately NOT part of that rule: it discards the
#     directory part whatever the input is, so it cannot walk anywhere.
#
# KNOWN LIMITS — stated so this is not read as a proof:
#   * same-line only (a path built on one line and used on another is invisible);
#   * one level of nested parentheses inside help_resolve_path(...);
#   * the arity half of the resolver rule is "is there a comma at argument depth
#     1", so a ONE-argument call whose argument contains a comma (e.g.
#     help_resolve_path(implode(',', $x))) is still counted as callable. That
#     direction is fail-open and it weakens only the arity half of the rule, not
#     the confinement half: nothing else on such a line is excused;
#   * the resolution-call rule tests the FIRST thing in the argument, so it does
#     not see a raw superglobal later in a concatenation:
#	readfile(realpath('/x/' . $_GET['f']));
#	readfile(realpath($_SERVER['DOCUMENT_ROOT'] . $_GET['f']));
#     both pass. That is the same bug shape (resolution of an attacker-chosen
#     path) and it is NOT covered. The reason it is not covered is the second
#     shape: flagging every superglobal inside realpath() would report every
#     `realpath(prefix . basename($_GET['f']))`, which is the confinement idiom
#     the rule is trying to encourage. First-argument-only keeps the rule
#     specific; the concatenation forms are a reviewer's question, not something
#     this gate answers;
#   * of the R33 exclusion list, HTTP_HOST is the one key whose value a client
#     does set (a Host header). It is excluded because the ruling names it with
#     the server-derived group, and because the shape it would report —
#     realpath($_SERVER['HTTP_HOST']) — is not a path this tree builds. Every
#     other HTTP_* key is deliberately NOT excluded: those carry request data and
#     are reported. Keyed by the ruling's "judge which of those are genuinely
#     input": of PATH_INFO / QUERY_STRING / REQUEST_URI, all three are
#     client-written, so all three are kept in the trigger set; PHP_SELF is
#     excluded even though its PATH_INFO suffix is client-influenced, because
#     excluding it is what the ruling asks for and because the alternative
#     re-opens the benign-shape false positive above.
#   * a CONCATENATED $_SERVER index is classified by its LEADING quoted literal,
#     which the awk key extraction takes and then stops at. So a server-derived
#     prefix hides a request-carrying tail, and this shape is NOT reported
#     (measured against this file's own path_guard_scan, not inferred):
#	readfile(realpath($_SERVER['DOCUMENT_ROOT' . $_GET['x']]));
#     `DOCUMENT_ROOT` is the leading literal, so the key extraction puts it on
#     the R33 server-derived list and the realpath( on the same line is then
#     treated as resolution and stops the rule. The UNQUOTED form
#     ($_SERVER[$k]) and a literal request-carrying key (PATH_INFO /
#     QUERY_STRING / REQUEST_URI / HTTP_*) ARE reported; the
#     concatenated-with-a-server-derived-prefix form is the residual, and it is
#     stated here rather than claimed closed (M-1);
#   * the resolver name is matched with a `[^A-Za-z0-9_]` boundary, and `>` and
#     `:` both satisfy that class, so a METHOD or STATIC call whose name merely
#     ENDS in help_resolve_path is treated as the resolver and the line is not
#     reported (measured against path_guard_scan):
#	readfile($obj->help_resolve_path($_SERVER['PATH_INFO'], dirname(__FILE__)));
#	readfile(Foo::help_resolve_path($_SERVER['PATH_INFO'], dirname(__FILE__)));
#     both pass. Contrived today: the helper is a global function with exactly one
#     caller (help.php:39), and the boundary is left as-is rather than narrowed,
#     because the narrowing would also have to exclude the `>` of `=>` and no
#     shape in this tree would measure that direction (M-2; the RESOLVER_CALL
#     boundary and the awk strip at the top of this block share the class).
# ---------------------------------------------------------------------------
path_guard_scan() {   # $@ = files to scan
	[ "$#" -gt 0 ] || return 0
	grep -nE "$SINKS" "$@" 2>/dev/null \
	| grep -E "$INPUT" \
	| awk '{ line = $0;
	         gsub(/(^|[^A-Za-z0-9_])help_resolve_path[[:space:]]*[(]([^,()]|[(][^()]*[)])*,[^()]*([(][^()]*[)][^()]*)*[)]/, "", line);
	         if (realpath_head_is_request_input(line)) { print; next }
	         if (line ~ /realpath[[:space:]]*[(]|basename[[:space:]]*[(]/) next;
	         if (line ~ /\$_(GET|POST|REQUEST|FILES|COOKIE|SERVER)/) print }
	       # Does any realpath(...) on the line take a REQUEST-CARRYING superglobal
	       # as the FIRST thing in its argument? $_GET/$_POST/$_REQUEST/$_FILES/
	       # $_COOKIE always do. $_SERVER does unless the key is one of the
	       # server-derived keys listed below - configuration and script identity,
	       # which no request can set. That exclusion is the R33 narrowing: it is
	       # what lets the benign realpath($_SERVER['DOCUMENT_ROOT'] .
	       # basename($_GET['f'])) shape through while PATH_INFO, QUERY_STRING and
	       # REQUEST_URI stay in. An UNQUOTED index ($_SERVER[$k]) is not provably
	       # server-derived, so it is reported (fail closed); a CONCATENATED index
	       # is classified by its LEADING quoted literal instead, so a
	       # server-derived prefix hides a request-carrying tail - that is NOT
	       # fail-closed and is stated in KNOWN LIMITS above path_guard_scan().
	       function realpath_head_is_request_input(text,   rest, sg, after, key) {
	         rest = text
	         while (match(rest, /realpath[[:space:]]*[(][[:space:]]*/)) {
	           rest = substr(rest, RSTART + RLENGTH)
	           if (rest !~ /^\$_/) continue
	           if (!match(rest, /^\$_[A-Za-z]+/)) return 1
	           sg = substr(rest, 1, RLENGTH)
	           if (sg != "$_SERVER") return 1
	           after = substr(rest, length(sg) + 1)
	           if (after !~ /^[[:space:]]*\[/) continue
	           after = substr(after, index(after, "[") + 1)
	           if (after ~ /^[[:space:]]*[0-9$]/) return 1
	           key = after
	           sub(/^[^A-Za-z0-9_]*/, "", key)
	           sub(/[^A-Za-z0-9_].*$/, "", key)
	           if (key !~ /^(DOCUMENT_ROOT|PHP_SELF|SCRIPT_FILENAME|SCRIPT_NAME|SCRIPT_URI|DOCUMENT_URI|SERVER_NAME|SERVER_ADDR|SERVER_PORT|SERVER_PROTOCOL|SERVER_SOFTWARE|SERVER_SIGNATURE|REQUEST_METHOD|GATEWAY_INTERFACE|REMOTE_ADDR|REMOTE_HOST|REMOTE_PORT|HTTPS|REDIRECT_STATUS|HTTP_HOST)$/) return 1
	         }
	         return 0
	       }' || true
}

# ---------------------------------------------------------------------------
# --self-test: the classification contract above, as fixtures. Each row is
# name|source|expected, where FAIL means "the scan must report this line" and
# PASS means "the scan must not".
#
# Two fixture groups, because the gate has two checks: `rows` are classified by
# path_guard_scan (check 2), `rows1` by check1_report (check 1, against help.php
# in the tree run).
#
# Every fix in this file has a row that fails if the fix is undone, so the
# contract cannot be quietly reverted:
#   * word-boundary anchor  -> misnamed_one_arg, misnamed_two_arg (a helper that
#                              is merely NAMED like the resolver must still be
#                              reported) and, in rows1, help1_misnamed;
#   * nested parentheses /  -> resolver_two_args and resolver_nested must PASS
#     callable form            while resolver_one_arg must FAIL (a one-argument
#                              call is an ArgumentCountError on PHP 8 and
#                              confines nothing);
#   * raw superglobal into  -> realpath_raw, server_pathinfo, server_querystring,
#     a resolution call        server_requesturi, server_varindex,
#                              server_httpheader must FAIL;
#   * the R33 narrowing     -> server_docroot and server_scriptfile must PASS
#     (MINOR-5)                (server-derived keys are not request input), so
#                              reverting the narrowing to "any $_SERVER" turns
#                              them red as false positives;
#   * check 1's TWO HALVES  -> in rows1, help1_nosink (`<?php echo 42;`) must
#     (C-3, final fix wave)    FAIL, which pins check1_no_resolver: before this
#                              row, all three check-1 fixtures carried a raw sink
#                              and were classified by check1_raw_sink, so
#                              neutralising check1_no_resolver (the "help.php
#                              must route through the resolver" half) left the
#                              self-test GREEN. help1_raw must FAIL, which pins
#                              check1_raw_sink, and help1_resolved must PASS, so
#                              neither half can be removed silently.
# The expected row COUNTS are asserted too, so deleting a row fails the self-test
# rather than shrinking it silently.
# ---------------------------------------------------------------------------
selftest() {
	local rows row rows1 row1 name src expect out verdict bad n expected
	local expected1 bad1 n1
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
		"server_docroot|<?php readfile(realpath(\$_SERVER['DOCUMENT_ROOT'] . basename(\$_GET['f'])));|PASS"
		"server_scriptfile|<?php readfile(realpath(\$_SERVER['SCRIPT_FILENAME'] . '/' . \$x));|PASS"
		"server_pathinfo|<?php readfile(realpath(\$_SERVER['PATH_INFO']));|FAIL"
		"server_querystring|<?php readfile(realpath(\$_SERVER['QUERY_STRING']));|FAIL"
		"server_requesturi|<?php readfile(realpath(\$_SERVER['REQUEST_URI']));|FAIL"
		"server_varindex|<?php readfile(realpath(\$_SERVER[\$k]));|FAIL"
		"server_httpheader|<?php readfile(realpath(\$_SERVER['HTTP_X_FILE']));|FAIL"
	)
	expected=17
	rows1=(
		"help1_resolved|<?php readfile(help_resolve_path(\$_SERVER['PATH_INFO'], dirname(__FILE__)));|PASS"
		"help1_misnamed|<?php readfile(my_help_resolve_path(\$_SERVER['PATH_INFO']));|FAIL"
		"help1_raw|<?php readfile(\$_SERVER['PATH_INFO']);|FAIL"
		"help1_nosink|<?php echo 42;|FAIL"
	)
	expected1=4
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
	bad1=0
	n1=0
	for row1 in "${rows1[@]}"; do
		name="${row1%%|*}"
		expect="${row1##*|}"
		src="${row1#*|}"; src="${src%|*}"
		printf '%s\n' "$src" > "$st_tmp/$name.php"
		out="$(check1_report "$st_tmp/$name.php")"
		if [ "$expect" = FAIL ]; then
			[ -n "$out" ] && verdict="flagged" || verdict="MISSED (must be flagged)"
		else
			[ -z "$out" ] && verdict="not flagged" || verdict="FALSE POSITIVE"
		fi
		n1=$((n1 + 1))
		if [ "$verdict" = "flagged" ] || [ "$verdict" = "not flagged" ]; then
			printf '  %-16s expect %-4s  %-12s  %s\n' "$name" "$expect" "$verdict" "$src"
		else
			printf '  %-16s expect %-4s  %-12s  %s\n' "$name" "$expect" "*** $verdict ***" "$src"
			bad1=$((bad1 + 1))
		fi
	done
	if [ "$n" -ne "$expected" ]; then
		echo "security-path-guard-check: self-test FAILED (ran $n scan fixtures, expected $expected - a row was removed)"
		return 1
	fi
	if [ "$n1" -ne "$expected1" ]; then
		echo "security-path-guard-check: self-test FAILED (ran $n1 check-1 fixtures, expected $expected1 - a row was removed)"
		return 1
	fi
	if [ "$bad" -ne 0 ] || [ "$bad1" -ne 0 ]; then
		echo "security-path-guard-check: self-test FAILED ($bad of $n scan and $bad1 of $n1 check-1 fixtures misclassified)"
		return 1
	fi
	echo "security-path-guard-check: self-test PASSED ($n of $n scan and $n1 of $n1 check-1 fixtures: arity, word boundary, nesting, the resolution-call rule, and both halves of check 1)"
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
# Both greps are anchored on a word boundary (RESOLVER_CALL), so a helper merely
# named like the resolver does not satisfy this check: MINOR-4 fix round 1.
missing="$(check1_no_resolver help.php)"
if [ -n "$missing" ]; then
	echo "PATH GUARD FAIL: help.php does not call help_resolve_path()"
	fail=1
fi
# and it must not reach a filesystem sink with the raw PATH_INFO
raw="$(check1_raw_sink help.php)"
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
