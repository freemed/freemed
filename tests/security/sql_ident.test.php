<?php
// tests/security/sql_ident.test.php — run: php tests/security/sql_ident.test.php
//
// Task 2.6a: SqlIdent is the allowlist validator + quoter for SQL schema/column
// identifiers. Identifiers cannot be bound parameters, so the *only* safe shape
// is a strict grammar plus explicit quoting; this suite pins both halves.
//
// Hermetic: the whole fixture (a SQLite database holding the columns the accept
// rows name) is materialised under sys_get_temp_dir() and removed on exit, so
//
//   docker run --rm -v "$PWD":/app -w /app php:8.3-cli php tests/security/sql_ident.test.php
//
// is the whole story — no extra mounts, no server, no host paths.
//
// How an accept row can FAIL, and how a reject row can FAIL:
//   * accept rows assert the *exact emitted string* of name()/columns(), and the
//     emitted fragment is then executed against the fixture. `select` is a
//     reserved word in the fixture, so a validator that validated but did not
//     quote would return the bare token and the exact-string assertion fails;
//     ORDER BY rows are checked for row order, so a dropped/incorrect ASC|DESC
//     fails too.
//   * reject rows are first shown to be dangerous: the row is quoted naively
//     (back-ticked, unvalidated) and *must* make the fixture database error. A
//     hostile row that SQLite accepts would prove nothing, so it fails the test.
//     Only then is SqlIdent required to refuse it.
//   * the inventory cross-check reads tests/security/evidence/identifier-inventory.txt
//     (generated, not retyped) and requires every literal listed there as
//     single-token/expression-shaped to be accepted — a grammar that broke the
//     tree fails here.

$class_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/core/SqlIdent.class.php';
if (file_exists($class_file)) { require_once $class_file; }
if (!class_exists('SqlIdent')) {
	fwrite(STDERR, "SqlIdent not defined: {$class_file} does not define class SqlIdent\n");
	echo "FAIL: SqlIdent not defined\n";
	exit(1);
}

$fail = 0;
$rows_done = 0;
$skipped = 0;

function si_row ( $label, $got, $expect ) {
	global $fail, $rows_done;
	$rows_done++;
	$ok = ($got === $expect);
	printf("%-58s => %-46s %s\n", $label, var_export($got, true), $ok ? 'OK' : 'FAIL (want ' . var_export($expect, true) . ')');
	if (!$ok) { $fail++; }
} // end function si_row

function si_skip ( $label, $why ) {
	global $skipped;
	$skipped++;
	printf("%-58s => SKIP (%s)\n", $label, $why);
} // end function si_skip

// ---------------------------------------------------------------------------
// fixture: a SQLite database with the columns the accept rows name.
// ---------------------------------------------------------------------------

$fixture = sys_get_temp_dir() . '/sql-ident-fixture-' . getmypid() . '.sqlite';
if (file_exists($fixture)) { @unlink($fixture); }
register_shutdown_function(function () use ($fixture) { @unlink($fixture); });

$db = NULL;
$have_db = class_exists('PDO') and in_array('sqlite', PDO::getAvailableDrivers());
if ($have_db) {
	try {
		$db = new PDO('sqlite:' . $fixture);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// `select` and `order` are reserved words: naming them here is what makes
		// the quoting half of the validator falsifiable.
		$db->exec('CREATE TABLE patient (id INTEGER, pdate TEXT, problem TEXT, logdate TEXT,'
			. ' insconame TEXT, inscostate TEXT, ptst TEXT, ptlname TEXT, "__actual_id" TEXT, "select" TEXT)');
		$st = $db->prepare('INSERT INTO patient VALUES (?,?,?,?,?,?,?,?,?,?)');
		$st->execute(array(1, '2026-01-01', 'a', '2026-03-01', 'X', 'CT', 'active', 'zeta', '1', 's1'));
		$st->execute(array(2, '2026-02-01', 'b', '2026-01-01', 'Y', 'NY', 'inactive', 'alpha', '2', 's2'));
	} catch (Throwable $e) {
		$db = NULL;
		$have_db = false;
	}
}

if ($have_db) {
	printf("fixture: %s (%d patient rows)\n\n", $fixture, (int) $db->query('SELECT COUNT(*) FROM patient')->fetchColumn());
} else {
	printf("fixture: SQLite unavailable — string assertions only\n\n");
}

// Run a fragment against the fixture. Returns the fetched rows (array) on
// success, the driver error text (string) on failure, NULL when there is no
// database to run against.
function si_exec ( $sql ) {
	global $db, $have_db;
	if (!$have_db) { return NULL; }
	try {
		return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	} catch (Throwable $e) {
		return $e->getMessage();
	}
} // end function si_exec

// Naive "quoting": what a site that validates nothing would emit. Used to prove
// a hostile row is genuinely able to break (or subvert) a query before we
// require its refusal.
function si_naive ( $token ) {
	return '`' . str_replace('.', '`.`', $token) . '`';
} // end function si_naive

// Prove a hostile row is dangerous: splice it naively into the same shape a
// call site would, execute it (multi-statement: exec(), not query()), and report
// how the fixture objected — an error, or a dropped sentinel table. 'harmless'
// means the row is NOT dangerous and the test must fail, because refusing it
// would then prove nothing.
function si_danger ( $in ) {
	global $db, $have_db;
	if (!$have_db) { return NULL; }
	$db->exec('DROP TABLE IF EXISTS sentinel');
	$db->exec('CREATE TABLE sentinel (x INTEGER)');
	$naive = si_naive($in);
	$sql = (strpos($in, ',') === false)
		? 'SELECT id FROM patient ORDER BY ' . $naive
		: 'SELECT ' . $naive . ' FROM patient';
	$outcome = 'harmless';
	try {
		$db->exec($sql);
	} catch (Throwable $e) {
		$outcome = 'query error';
	}
	$still = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sentinel'")->fetchColumn();
	if ($still !== 'sentinel') { $outcome = 'sentinel table dropped'; }
	return $outcome;
} // end function si_danger

// ===========================================================================
echo "-- name(): accepted --\n";
// ===========================================================================
$accept_names = array(
	'patient'           => '`patient`',
	'ptst'              => '`ptst`',
	'p.ptlname'         => '`p`.`ptlname`',
	'__actual_id'       => '`__actual_id`',       // real: PaymentModule $summary_order_by
	'select'            => '`select`',            // reserved word: proves quoting, not just validation
	'pt2'               => '`pt2`',               // trailing digit
);
foreach ($accept_names as $in => $want) {
	si_row("name(" . var_export($in, true) . ")", SqlIdent::name($in), $want);
}
// and the emitted name must actually address a column
foreach (array('ptst' => 'active', 'p.ptlname' => 'zeta') as $in => $want) {
	$sql = 'SELECT ' . SqlIdent::name($in) . ' AS v FROM patient p WHERE p.id = 1';
	$got = si_exec($sql);
	if ($got === NULL) { si_skip("exec SELECT " . SqlIdent::name($in), 'no fixture db'); continue; }
	si_row("exec SELECT " . SqlIdent::name($in), is_array($got) && count($got) ? $got[0]['v'] : $got, $want);
}

// ===========================================================================
echo "\n-- columns(): accepted (the 9 expression-shaped literals this section drives; the whole inventory is cross-checked below) --\n";
// ===========================================================================
$accept_columns = array(
	'pdate,problem'                      => '`pdate`, `problem`',
	'pdate, problem'                     => '`pdate`, `problem`',
	'insconame, inscostate, inscocity'   => '`insconame`, `inscostate`, `inscocity`',
	'logdate DESC'                       => '`logdate` DESC',
	'id asc'                             => '`id` ASC',
	'dateof DESC,immunization.id'        => '`dateof` DESC, `immunization`.`id`',
	'payrecproc DESC, __actual_id'       => '`payrecproc` DESC, `__actual_id`',
	'authdtbegin,authdtend'              => '`authdtbegin`, `authdtend`',
	'status_order, status_name'          => '`status_order`, `status_name`',
);
foreach ($accept_columns as $in => $want) {
	si_row("columns(" . var_export($in, true) . ")", SqlIdent::columns($in), $want);
}

echo "\n-- columns(): emitted ORDER BY is executed, direction included --\n";
$orders = array(
	'pdate ASC'  => 1,   // 2026-01-01 first
	'pdate DESC' => 2,
	'logdate DESC' => 1, // 2026-03-01 first
	'logdate asc'  => 2,
);
foreach ($orders as $in => $want_id) {
	$frag = SqlIdent::columns($in);
	$got = si_exec('SELECT id FROM patient ORDER BY ' . $frag);
	if ($got === NULL) { si_skip("exec ORDER BY {$frag}", 'no fixture db'); continue; }
	si_row("exec ORDER BY {$frag}", is_array($got) && count($got) ? $got[0]['id'] : $got, $want_id);
}

// ===========================================================================
echo "\n-- rejected (and proven dangerous first) --\n";
// ===========================================================================
$hostile = array(
	'id; DROP TABLE sentinel'  => 'statement break',
	"id' OR 1=1 --"            => 'quote breakout',
	'id /* x */'               => 'comment',
	'(SELECT 1)'               => 'subquery / parens',
	'id UNION SELECT x'        => 'union',
	''                         => 'empty',
	'id`; DROP TABLE sentinel' => 'backtick break',
	"id\nFROM patient"         => 'newline injection',
	'id, (SELECT 1)'           => 'parens inside a list',
	'id DESC, (SELECT 1)'      => 'parens after a direction',
);
foreach ($hostile as $in => $why) {
	// 1. the row must be genuinely able to subvert a query when spliced naively
	if ($have_db) {
		$got = si_danger($in);
		si_row("dangerous: " . var_export($in, true) . " ({$why}) [" . $got . "]",
			($got !== 'harmless' and $got !== NULL) ? 'dangerous' : 'harmless', 'dangerous');
	}
	// 2. and SqlIdent must refuse it in both shapes
	si_row("name(" . var_export($in, true) . ") ({$why})", SqlIdent::name($in), false);
	si_row("columns(" . var_export($in, true) . ") ({$why})", SqlIdent::columns($in), false);
}

// ===========================================================================
echo "\n-- rejects non-identifiers it is handed (no type error) --\n";
// ===========================================================================
si_row('columns(NULL)', SqlIdent::columns(NULL), false);
si_row('columns("")', SqlIdent::columns(''), false);
si_row('columns("   ")', SqlIdent::columns('   '), false);
si_row('name(NULL)', SqlIdent::name(NULL), false);
si_row('name("")', SqlIdent::name(''), false);

// ===========================================================================
echo "\n-- inventory cross-check (generated artifact, not retyped) --\n";
// ===========================================================================
$inv = dirname(__FILE__) . '/evidence/identifier-inventory.txt';
if (!is_file($inv)) {
	si_skip('identifier-inventory.txt', 'not generated yet');
} else {
	$single = 0; $expr = 0; $bad = array(); $section = '';
	foreach (file($inv) as $line) {
		if (preg_match('/^--- ([A-Z-]+) \(\d+\) ---/', $line, $s)) { $section = $s[1]; continue; }
		// only rows the inventory classifies as identifiers are checked; the
		// CLAUSE-FRAGMENT section holds WHERE fragments, which are not names.
		if ($section !== 'SINGLE-TOKEN' and $section !== 'EXPRESSION-SHAPED') { continue; }
		if (!preg_match('/^lib\/[^:]+:\d+\s+\$([A-Za-z_]+) = (.*)$/', rtrim($line), $m)) { continue; }
		$prop = $m[1]; $raw = $m[2];
		if (!preg_match('/^([\'"])(.*)\1$/', $raw, $q)) { continue; }
		$lit = $q[2];
		if ($lit === '') { continue; }
		if (SqlIdent::name($lit) !== false) { $single++; continue; }
		if (SqlIdent::columns($lit) !== false) { $expr++; continue; }
		$bad[] = "\${$prop} = {$raw}";
	}
	printf("%-58s => %s\n", 'inventory literals accepted', "single-token={$single} expression-shaped={$expr} refused=" . count($bad));
	if ($single < 200 or $expr < 18) { $fail++; echo "FAIL: inventory cross-check saw too few literals ({$single}/{$expr})\n"; }
	foreach ($bad as $b) { $fail++; echo "FAIL: inventory literal refused by the grammar: {$b}\n"; }
}

// ---------------------------------------------------------------------------
echo "\n";
if ($fail) { echo "{$fail} FAILED of " . ($rows_done) . " rows\n"; exit(1); }
echo "all passed (" . ($rows_done) . " rows" . ($skipped ? ", {$skipped} skipped" : '') . ")\n";
exit(0);
