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
# After the 2.6b sweep the allowlist must hold ONLY the vendored library paths
# (which the grep below already excludes) and the single documented raw
# expression case, core/SupportModule.class.php::$additional_fields. An
# application-file entry is a review red flag, not a fix.
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
