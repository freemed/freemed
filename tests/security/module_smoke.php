<?php
// tests/security/module_smoke.php — Task 2.6c: the functional gate for the 2.6b sweep.
//
// The sweep rewrites query construction in ~60 files; a static gate cannot tell
// you it broke a module. This harness enumerates the registered modules (the
// `modules` table), instantiates each one the same way the relays do, calls its
// listing method and then GetRecord() on the first returned id, and writes JSON
//
//	{ "<module_class>": "OK" | "EXC: message" | "SKIP: reason", ... }
//
// to stdout. The gate is the *diff* between two runs: modules whose tables are
// missing from the reduced Phase 0.3 seed fail before the sweep too, so the
// baseline is recorded (tests/security/evidence/module-smoke-before.json) and
// the post-sweep failure set must be identical or strictly smaller. Any new EXC
// is a sweep regression.
//
// Dispatch is asserted, not assumed (the vacuity trap): a relay method answers
// HTTP 200 with an EMPTY body when `modules` has no row for its module_class
// (ModuleIndex::GetModuleProperty returns null and the relay's
// @call_user_func_array swallows it). "OK" therefore requires ALL of
//	1. the registry row's module_class resolves to a real file (resolve_module),
//	2. CreateObject('org.freemedsoftware.module.<class>') returns an object whose
//	   class is the one asked for (not null, not a different class),
//	3. the listing method returns an array (not false / not a PEAR_Error),
//	4. GetRecord(<first id>) returns a non-empty row.
// A negative control row, __control_missing_module__ (a class with no registry
// row that does not exist on disk), is always included: it MUST come back EXC.
// If it comes back OK the harness itself is vacuous and exits 3.
//
// Run against the SERVED copy, not the checkout — the checkout has no
// lib/settings.php (it is deliberately outside the tree):
//
//	cd /home/jbuchbinder/.hermes/cache/scratch/freemed-verify && ./sync-code.sh
//	docker run --rm --network freemed-verify_freemed-net \
//	  -v /home/jbuchbinder/.hermes/cache/scratch/freemed-verify/app:/app -w /app \
//	  php:8.3-cli php tests/security/module_smoke.php
//
// First run in a fresh stack: `php tests/security/module_smoke.php --register`
// populates the `modules` registry from lib/org/freemedsoftware/module/*.php
// (INSERT ... ON DUPLICATE KEY UPDATE, idempotent) so that resolve_module() has
// a row per module. Without a registry row the dispatch is vacuous by
// construction; --register writes to the database, so it is never implicit.
//
// Exit codes: 0 = ran (EXC rows are data for the diff, not a harness failure),
//             3 = could not produce signal (no config, no registry, control OK).

$root = dirname(dirname(dirname(__FILE__)));
chdir($root); // resolve_module() returns repo-root-relative paths; is_file() needs the right cwd
$argv_list = isset($argv) ? $argv : array();
$do_register = in_array('--register', $argv_list, true);

// Anything below may print (deprecations, module debug, session notices): stdout
// is the artifact, so it is buffered and discarded before the JSON is emitted.
ob_start();

$results = array();
$notes = array();
$emitted = false;
$error_tally = array();
$error_shown = 0;

function smoke_emit () {
	global $results, $emitted;
	if ($emitted) { return; }
	$emitted = true;
	ksort($results);
	while (ob_get_level()) { ob_end_clean(); }
	echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} // end function smoke_emit

// If a module dies mid-run the artifact must still be emitted (never truncated
// into a "nothing leaked" vacuous negative).
register_shutdown_function('smoke_emit');

function smoke_note ( $module, $status ) {
	global $results;
	$key = $module;
	$n = 2;
	while (array_key_exists($key, $results)) { $key = $module . '#' . $n; $n++; }
	$results[$key] = $status;
} // end function smoke_note

function smoke_detail ( $e ) {
	return get_class($e) . ': ' . str_replace(array("\n", "\r"), ' ', $e->getMessage());
} // end function smoke_detail

// ---------------------------------------------------------------------------
// bootstrap (the application's own, exactly as a request would do it)
// ---------------------------------------------------------------------------

if (!is_file($root . '/lib/settings.php')) {
	smoke_note('__harness__', 'EXC: no lib/settings.php at ' . $root . ' — run against the served copy, not the checkout');
	smoke_emit();
	exit(3);
}

// Errors are observations here, not crashes: trigger_error(..., E_USER_ERROR) —
// which is how the base classes report a mis-declared identifier — is converted
// to an exception so the harness can record which module owns it. Warnings and
// notices are swallowed from stdout but tallied for the stderr summary.
set_error_handler(function ($no, $str, $file, $line) {
	global $error_tally, $error_shown;
	if ($no & (E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR)) {
		throw new ErrorException($str, 0, $no, $file, $line);
	}
	$key = $str . ' @ ' . basename($file) . ':' . $line;
	$error_tally[$key] = isset($error_tally[$key]) ? $error_tally[$key] + 1 : 1;
	if ($error_shown < 5 and strpos($str, 'Deprecated') === false) { $error_shown++; fwrite(STDERR, "php: {$key}\n"); }
	return true;
});

ini_set('display_errors', '0');
set_time_limit(0);

include_once($root . '/lib/freemed.php');

if (!is_object($GLOBALS['sql'])) {
	smoke_note('__harness__', 'EXC: no SQL connection after bootstrap');
	smoke_emit();
	exit(3);
}

// ---------------------------------------------------------------------------
// --register: give every module class on disk a registry row
// ---------------------------------------------------------------------------

if ($do_register) {
	$dir = $root . '/lib/org/freemedsoftware/module';
	$added = 0;
	foreach (glob($dir . '/*.php') AS $file) {
		$class = basename($file, '.php');
		if (substr($class, -6) == '.class') { $class = substr($class, 0, -6); }
		$path = 'lib/org/freemedsoftware/module/' . basename($file);
		$uid  = substr(md5($class), 0, 8) . '-' . substr(md5($class), 8, 4) . '-'
			. substr(md5($class), 12, 4) . '-' . substr(md5($class), 16, 4) . '-'
			. substr(md5($class), 20, 12);
		$q = 'INSERT INTO modules (module_uid, module_name, module_class, module_table,'
			. ' module_version, module_category, module_path, module_stamp, module_hidden)'
			. ' VALUES ('
			. $GLOBALS['sql']->quote($uid) . ','
			. $GLOBALS['sql']->quote($class) . ','
			. $GLOBALS['sql']->quote($class) . ",'',"
			. $GLOBALS['sql']->quote('0.0') . ','
			. $GLOBALS['sql']->quote('smoke') . ','
			. $GLOBALS['sql']->quote($path) . ','
			. (int) @filemtime($file) . ',0)'
			. ' ON DUPLICATE KEY UPDATE module_path = VALUES(module_path), module_stamp = VALUES(module_stamp)';
		$GLOBALS['sql']->query($q);
		$added++;
	}
	fwrite(STDERR, "registered {$added} module classes from {$dir}\n");
}

// ---------------------------------------------------------------------------
// the registry: the set the relays can dispatch to
// ---------------------------------------------------------------------------

try {
	$registry = $GLOBALS['sql']->queryAll("SELECT module_class, module_path, module_table FROM modules ORDER BY module_class");
} catch (Throwable $e) {
	$registry = array();
}

if (!is_array($registry) or !count($registry)) {
	smoke_note('__harness__', 'EXC: the modules registry is empty — every relay dispatch would be vacuous (run with --register)');
	smoke_emit();
	exit(3);
}

// ---------------------------------------------------------------------------
// dispatch one module
// ---------------------------------------------------------------------------

// The patient id GetList()/GetRecords() are exercised with: the lowest real id,
// or 1 when the reduced seed has no patients (the query builder still runs, and
// a broken builder returns DB_Error, not an empty array).
function smoke_patient () {
	$id = $GLOBALS['sql']->queryOne("SELECT MIN(id) FROM patient");
	return ($id === NULL or $id === false or $id === '') ? 1 : $id;
} // end function smoke_patient

function smoke_module ( $row, $patient ) {
	$class = $row['module_class'];

	// 1. the registry row must resolve to a real file
	$path = resolve_module($class);
	if (!$path or !is_file($path)) {
		return 'EXC: registry row does not resolve to a file (module_path=' . var_export($row['module_path'], true) . ')';
	}

	// 2. instantiate exactly as the relays do
	$obj = CreateObject('org.freemedsoftware.module.' . $class);
	if (!is_object($obj)) {
		return 'EXC: CreateObject() returned ' . gettype($obj) . ' — dispatch did not happen';
	}
	if (strtolower(get_class($obj)) !== strtolower($class)) {
		return 'EXC: dispatch returned ' . get_class($obj) . ' instead of ' . $class;
	}

	// 3. every callable listing method that exists, so the query builders the
	//    sweep rewrites are actually executed (GetAll, then GetRecords, then
	//    the EMR GetList)
	$called = array();
	$ids = array();
	$rows_total = 0;
	$candidates = array(
		array('GetAll', array()),
		array('GetRecords', array(100)),
		array('GetList', array($patient, 10)),
	);
	foreach ($candidates AS $cand) {
		list($m, $args) = $cand;
		if (!method_exists($obj, $m)) { continue; }
		$rm = new ReflectionMethod($obj, $m);
		if ($rm->getNumberOfRequiredParameters() > count($args)) { continue; }
		$list = call_user_func_array(array($obj, $m), $args);
		$called[] = $m;
		if (!is_array($list)) {
			return 'EXC: ' . $m . '() returned ' . (is_object($list) ? get_class($list) : gettype($list)) . ' (no records)';
		}
		$rows_total += count($list);
		foreach ($list AS $r) {
			if (is_array($r) and isset($r['id']) and $r['id'] !== NULL and $r['id'] !== '') { $ids[] = $r['id']; }
		}
	}
	if (!count($called)) {
		return 'SKIP: no callable parameterless GetAll/GetRecords/GetList';
	}

	// 4. GetRecord() on the first row that carries an id
	if (!count($ids)) {
		return 'SKIP: ' . join('+', $called) . '() returned ' . $rows_total . ' rows, none with an id';
	}
	if (!method_exists($obj, 'GetRecord')) { return 'SKIP: no GetRecord'; }
	$rec = $obj->GetRecord($ids[0]);
	if (!is_array($rec) or !count($rec)) {
		return 'EXC: GetRecord(' . $ids[0] . ') returned ' . (is_object($rec) ? get_class($rec) : gettype($rec));
	}
	return 'OK';
} // end function smoke_module

$patient = smoke_patient();
foreach ($registry AS $row) {
	if (!is_array($row) or !isset($row['module_class'])) { continue; }
	try {
		smoke_note($row['module_class'], smoke_module($row, $patient));
	} catch (Throwable $e) {
		smoke_note($row['module_class'], 'EXC: ' . smoke_detail($e));
	}
}

// ---------------------------------------------------------------------------
// negative control: an unregistered, non-existent class must NOT come back OK
// ---------------------------------------------------------------------------

$control = array('module_class' => 'NoSuchModuleControl', 'module_path' => '', 'module_table' => '');
try {
	$control_status = smoke_module($control, $patient);
} catch (Throwable $e) {
	$control_status = 'EXC: ' . smoke_detail($e);
}
smoke_note('__control_missing_module__', $control_status);

// ---------------------------------------------------------------------------
// summary (stderr — stdout is the JSON artifact)
// ---------------------------------------------------------------------------

$counts = array('OK' => 0, 'EXC' => 0, 'SKIP' => 0, 'OTHER' => 0);
foreach ($results AS $status) {
	if (strpos($status, 'OK') === 0) { $counts['OK']++; }
	elseif (strpos($status, 'EXC') === 0) { $counts['EXC']++; }
	elseif (strpos($status, 'SKIP') === 0) { $counts['SKIP']++; }
	else { $counts['OTHER']++; }
}
fwrite(STDERR, sprintf("module smoke: OK=%d EXC=%d SKIP=%d other=%d (registry rows=%d)\n",
	$counts['OK'], $counts['EXC'], $counts['SKIP'], $counts['OTHER'], count($registry)));
if (count($error_tally)) {
	arsort($error_tally);
	fwrite(STDERR, "php diagnostics (top 5):\n");
	$i = 0;
	foreach ($error_tally AS $msg => $n) { fwrite(STDERR, "  {$n}x {$msg}\n"); if (++$i >= 5) { break; } }
}

smoke_emit();

if (substr($control_status, 0, 3) === 'OK') {
	fwrite(STDERR, "HARNESS SELF-CHECK FAILED: the negative control came back OK — dispatch is not being asserted\n");
	exit(3);
}
exit(0);
