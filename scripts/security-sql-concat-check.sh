#!/usr/bin/env bash
# scripts/security-sql-concat-check.sh
#
# Task 2.7 — the regression gate for Task 2.6 (CWE-89). A reviewer can miss a
# concatenation; this check cannot. It looks for a PHP variable concatenated
# directly into a SQL string on the same line with no quoting/escaping/cast in
# sight, and exits non-zero unless the hit is one of the documented exceptions
# listed in scripts/security-sql-concat-allowlist.txt.
#
# It is deliberately heuristic (same-line, keyword-filtered): the value is the
# gate, not the sophistication. Anything it flags is either a real bug or a line
# that should be rewritten with $GLOBALS['sql']->quote() / SqlIdent::name().
#
# After the 2.6b sweep (batches A, B, C) no entry in the allowlist covers
# FreeMED's own code: the only entries left are the vendored libraries the grep
# below does not exclude (lib/agata7/**), the security tests, and the
# third-party phpGACL ACL library, which the sweep does not rewrite. An entry
# for api/**, core/**, module/** or a root script would be a review red flag,
# not a fix.
#
# The documented raw-expression case is core/SupportModule.class.php's
# $additional_fields: code-authored SQL with a result alias by design, checked
# at construction by SqlIdent::expression() and spliced into a sprintf-assembled
# statement. Earlier revisions of this header named the line it sat on
# (SupportModule.class.php:484) as the one case the allowlist MUST hold; that is
# no longer true - the rewrite of the surrounding statement stopped tripping
# this heuristic while the raw splice (validated as above) remains, so the entry
# was removed as stale and the posture is unchanged.
#
# WHAT THIS GATE CANNOT SEE — the interpolation blind spot (deferred item 9;
# the same limit is stated durably in doc/SECURITY_FOLLOWUP section 6.7 b).
# The pattern below requires a quote character ADJACENT to the dot and the
# variable, so a plain interpolation is invisible to it:
#
#	$q = "SELECT id FROM patient WHERE id = $id";   <-- NOT reported (measured)
#
# and so is a value spliced after a validated identifier helper (the Ledger.php
# shape the followup records). That is by design: the plan mandated a same-line,
# dot-adjacency, keyword-filtered heuristic, not a parser. Read a clean run as
# "the shape that caused Task 2.6's findings is absent", never as "no
# concatenation exists in this tree".
#
#   ./scripts/security-sql-concat-check.sh              scan the tree
#   ./scripts/security-sql-concat-check.sh --self-test  classify the fixtures
#                                                       in the documented way
#
# `make security-check` runs the TREE SCAN of both static gates AND their
# --self-test modes (Makefile:70-74), so this file's own sensitivity is proved
# by it; run `--self-test` explicitly while editing this script (the same is
# true of scripts/security-path-guard-check.sh).
set -u

cd "$(dirname "${BASH_SOURCE[0]}")/.." || { echo "security-sql-concat-check: cannot cd to repo root"; exit 2; }
ALLOW="scripts/security-sql-concat-allowlist.txt"
# file-scope (not local) so the EXIT trap of --self-test can still see it under
# set -u once selftest() has returned
st_tmp=''

# ---------------------------------------------------------------------------
# The scan chain, in one place so the tree run and --self-test classify through
# the SAME pipeline (a self-test that re-implemented it would pin nothing).
#   $1 = the root to scan (the repo root, `.`, for a normal run).
# ---------------------------------------------------------------------------
sql_concat_hits() {
	grep -rn --include=*.php \
	        -E "(['\"] *\. *\\\$[A-Za-z_][A-Za-z0-9_]*|\\\$[A-Za-z_][A-Za-z0-9_]* *\. *['\"])" "$1" \
	      | grep -v -E '/(net/php/pear|gwtphp|log4php|php-gettext|acl/adodb|smarty)/' \
	      | grep -iE 'select |insert |update |delete |where | and | set |concat' \
	      | grep -vE 'addslashes|escape\(|->quote\(|intval|\(int\)|\(float\)|htmlentities|prepare\('
}

# ---------------------------------------------------------------------------
# --self-test: the classification contract above, as fixtures. Each row is
# name|source|expected, where FAIL means "the scan must report this line" and
# PASS means "the scan must not". Fixtures are written under mktemp -d (never
# inside the tree), so a self-test run cannot change what the tree scan reports.
#
# Every filter in the chain has a row that fails if the filter is loosened:
#   * the quote-LEFT arm of the pattern -> raw_dot_var must FAIL
#     (`['"] *\. *\$var`)                  (`...usertype='" . $usertype`);
#   * the quote-RIGHT arm of the pattern -> var_dot_raw must FAIL
#     (`\$var *\. *['"]`)                    (`$condition . " AND usertype=…"`,
#                                            the Task 2.6 idiom, natural order);
#   * the quoting/cast exclusion         -> quoted_value (->quote()) and cast_int
#     (addslashes|escape(|->quote(|          (intval()) must PASS: a gate that
#     intval|(int)|(float)|htmlentities)     reported code that already quotes or
#                                            casts would be suppressed, so this
#                                            direction is pinned too;
#   * the SQL keyword filter             -> no_keyword ('Hello ' . $name) must
#                                            PASS: it matches the pattern and
#                                            must still be ignored because the
#                                            line builds no SQL.
# The expected row COUNT is asserted as well, so a row cannot be deleted to
# silence a failure.
# ---------------------------------------------------------------------------
selftest() {
	local rows row name src expect out verdict bad n expected
	st_tmp="$(mktemp -d)"
	trap '[ -n "$st_tmp" ] && rm -rf "$st_tmp"' EXIT
	rows=(
		"raw_dot_var|<?php \$q = \"SELECT id FROM patient WHERE usertype='\" . \$usertype;|FAIL"
		"var_dot_raw|<?php \$condition = \$condition . \" AND usertype='x'\";|FAIL"
		"quoted_value|<?php \$q = \"SELECT id FROM patient WHERE id = \" . \$GLOBALS['sql']->quote(\$id);|PASS"
		"cast_int|<?php \$q = \"SELECT id FROM patient WHERE id = \" . intval(\$id);|PASS"
		"no_keyword|<?php \$greeting = 'Hello ' . \$name;|PASS"
	)
	expected=5
	echo "security-sql-concat-check --self-test"
	bad=0
	n=0
	for row in "${rows[@]}"; do
		name="${row%%|*}"
		expect="${row##*|}"
		src="${row#*|}"; src="${src%|*}"
		printf '%s\n' "$src" > "$st_tmp/$name.php"
		out="$(sql_concat_hits "$st_tmp")"
		if [ "$expect" = FAIL ]; then
			printf '%s\n' "$out" | grep -qF -- "$st_tmp/$name.php" && verdict="flagged" || verdict="MISSED (must be flagged)"
		else
			printf '%s\n' "$out" | grep -qF -- "$st_tmp/$name.php" && verdict="FALSE POSITIVE" || verdict="not flagged"
		fi
		n=$((n + 1))
		if [ "$verdict" = "flagged" ] || [ "$verdict" = "not flagged" ]; then
			printf '  %-14s expect %-4s  %-12s  %s\n' "$name" "$expect" "$verdict" "$src"
		else
			printf '  %-14s expect %-4s  %s  %s\n' "$name" "$expect" "*** $verdict ***" "$src"
			bad=$((bad + 1))
		fi
	done
	if [ "$n" -ne "$expected" ]; then
		echo "security-sql-concat-check: self-test FAILED (ran $n fixtures, expected $expected - a row was removed)"
		return 1
	fi
	if [ "$bad" -ne 0 ]; then
		echo "security-sql-concat-check: self-test FAILED ($bad of $n fixtures misclassified)"
		return 1
	fi
	echo "security-sql-concat-check: self-test PASSED ($n of $n fixtures: both arms of the pattern, the quoting/cast exclusion and the SQL keyword filter)"
	return 0
}

if [ "${1:-}" = "--self-test" ]; then
	selftest
	exit $?
fi

# The allowlist is only needed by the tree scan; --self-test is above this line
# on purpose, so it can be run (and reviewed) anywhere.
if [ ! -f "$ALLOW" ]; then
	echo "security-sql-concat-check: missing allowlist $ALLOW" >&2
	exit 2
fi

hits="$(sql_concat_hits .)"

if [ -n "$hits" ]; then
  # Comments and blank lines in the allowlist must never become patterns: an
  # empty pattern matches EVERY line under grep -F, which would make this gate
  # vacuously clean.
  patterns=$(grep -vE '^[[:space:]]*(#|$)' "$ALLOW" || true)
  if [ -z "$patterns" ]; then
    new="$hits"
  else
    new=$(grep -vFf <(printf '%s\n' "$patterns") <<<"$hits" || true)
  fi
  if [ -n "$new" ]; then
    echo "SQL concatenation without quoting:"
    echo "$new"
    echo ""
    echo "Fix with \$GLOBALS['sql']->quote()/\$GLOBALS['sql']->escape() for values,"
    echo "SqlIdent::name()/SqlIdent::columns() for identifiers, or add a line to"
    echo "$ALLOW if it is the documented raw-expression exception."
    exit 1
  fi
fi
echo "security-sql-concat-check: clean"
