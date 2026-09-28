<?php
// $Id$
// FreeMED — allowlist validator + quoter for SQL schema/column identifiers.
//
// Identifiers (table names, column names, ORDER BY expressions) cannot be bound
// parameters, so they are validated against a strict grammar and emitted
// backtick-quoted; anything else is refused. Refusing rather than stripping is
// deliberate: stripping changes query meaning silently.
//
// Usage (Task 2.6b sweep):
//	$q = "SELECT * FROM ".SqlIdent::name($this->table_name);
//	$q = "SELECT * FROM `patient` ORDER BY ".SqlIdent::columns($this->order_field);
// When a call site gets `false` back it must fall back to a known-safe value
// (or refuse the clause) rather than splice the raw value.
//
// See tests/security/sql_ident.test.php.

class SqlIdent {

	// Method: name
	//
	//	A single schema/column token, optionally table-qualified.
	//
	// Parameters:
	//
	//	$token - candidate identifier, e.g. 'patient', 'ptst', 'p.ptlname'
	//
	// Returns:
	//
	//	Backtick-quoted identifier ('`p`.`ptlname`'), or boolean false if the
	//	token is not exactly one (optionally qualified) name.
	//
	public static function name ( $token ) {
		if (!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/', (string) $token)) { return false; }
		return '`'.str_replace('.', '`.`', $token).'`';
	} // end method name

	// Method: columns
	//
	//	A column list or ORDER BY expression: comma-separated names with an
	//	optional per-column ASC/DESC. Covers every expression-shaped literal in
	//	the tree (tests/security/evidence/identifier-inventory.txt), e.g.
	//	'pdate,problem', 'insconame, inscostate', 'logdate DESC'.
	//
	// Returns:
	//
	//	Backtick-quoted, comma-joined expression ('`a`, `b` DESC'), or false.
	//
	public static function columns ( $expr ) {
		if (trim((string) $expr) == '') { return false; }
		$out = array();
		foreach (explode(',', (string) $expr) AS $part) {
			$part = trim($part); $dir = '';
			if (preg_match('/^(.*?)\s+(ASC|DESC)$/i', $part, $m)) {
				$part = trim($m[1]); $dir = ' '.strtoupper($m[2]);
			}
			$n = self::name($part);
			if ($n === false) { return false; }
			$out[] = $n.$dir;
		}
		return $out ? join(', ', $out) : false;
	} // end method columns

	// Method: expression
	//
	//	The ONE allowlisted raw-SQL case: `$additional_fields` in
	//	<org.freemedsoftware.core.SupportModule>, which holds code-authored SQL
	//	expressions with a result alias by design (not input data).
	//
	//	Shape-checked only: no ';', no '--' / '/*' comment, no backtick, no NUL,
	//	and it must end in ' AS <alias>'. The permitted character set cannot
	//	terminate the statement it is spliced into.
	//
	// Returns:
	//
	//	The (trimmed) expression, or boolean false.
	//
	public static function expression ( $expr ) {
		if (!is_string($expr)) { return false; }
		$expr = trim($expr);
		if ($expr == '') { return false; }
		if (preg_match('/[;`\x00]/', $expr)) { return false; }
		if (strpos($expr, '--') !== false) { return false; }
		if (strpos($expr, '/*') !== false) { return false; }
		if (!preg_match('/^[A-Za-z0-9_\s(),.*\'"]+?\s+AS\s+`?[A-Za-z0-9_]+`?$/i', $expr)) { return false; }
		return $expr;
	} // end method expression

	// Method: valid
	//
	//	Boolean form of <name>, for call sites that only need to know.
	//
	public static function valid ( $token ) {
		return self::name($token) !== false;
	} // end method valid

	// Method: validColumns
	//
	//	Boolean form of <columns>, for call sites that only need to know.
	//
	public static function validColumns ( $expr ) {
		return self::columns($expr) !== false;
	} // end method validColumns

} // end class SqlIdent

?>
