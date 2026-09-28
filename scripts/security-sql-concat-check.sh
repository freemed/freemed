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
set -u

cd "$(dirname "${BASH_SOURCE[0]}")/.." || { echo "security-sql-concat-check: cannot cd to repo root"; exit 2; }
ALLOW="scripts/security-sql-concat-allowlist.txt"
if [ ! -f "$ALLOW" ]; then
	echo "security-sql-concat-check: missing allowlist $ALLOW" >&2
	exit 2
fi

hits=$(grep -rn --include=*.php \
        -E "(['\"] *\. *\\\$[A-Za-z_][A-Za-z0-9_]*|\\\$[A-Za-z_][A-Za-z0-9_]* *\. *['\"])" . \
      | grep -v -E '/(net/php/pear|gwtphp|log4php|php-gettext|acl/adodb|smarty)/' \
      | grep -iE 'select |insert |update |delete |where | and | set |concat' \
      | grep -vE 'addslashes|escape\(|->quote\(|intval|\(int\)|\(float\)|htmlentities|prepare\(')

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
