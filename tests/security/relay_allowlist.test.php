<?php
// tests/security/relay_allowlist.test.php — run: php tests/security/relay_allowlist.test.php
//
// Task 2.8: Relay_Allowlist is the deny-by-default call-set check the relay
// consults before dispatch, so that the NEXT quoting omission is not remotely
// reachable through relay.php. This suite pins the matcher, the two enforcement
// states, the too-broad guard, the load-time fail-open branches (including the
// corrupt data file), the shared decision point that a relay re-dispatcher must
// use for its inner calls, and the correspondence between the shipped data file
// and the committed enumeration.
//
// Hermetic: no database, no network, no server, no fixtures (the load() rows
// write their own throwaway data files under the system temp directory and
// remove them on exit). The relay's own dispatch cannot be exercised from here
// (handle_request() calls CallMethod for the LoggedIn guard, which needs the
// framework and a database), so the wiring is asserted at source level in the
// last sections and end-to-end over HTTP in
// tests/security/evidence/relay-allowlist.txt.
//
//   docker run --rm -v "$PWD":/app -w /app php:8.3-cli php tests/security/relay_allowlist.test.php
//
// How a row can FAIL:
//   * The class does not exist (the pre-change tree): the include check below
//     exits 1 before any row runs, so this file fails against the tree without
//     the change instead of trivially passing.
//   * The matcher rows assert exact booleans, so a matcher that matched too much
//     (regex dots, a `*` honoured mid-pattern, a too-broad pattern honoured, a
//     `*` matched against an empty tail) and one that matched too little
//     (case-sensitive comparison, no `*` support) both fail.
//   * The load() rows assert the fail-open outcome AND the warning text for a
//     missing file, a non-array file and a file that cannot be parsed, so
//     removing any of those branches fails here.
//   * The data-file rows re-read the SHIPPED file and the SHIPPED enumeration in
//     BOTH directions and compare their sizes, so a pattern added to the list
//     without evidence - or a measured live call dropped from the list, or a
//     wildcard slipped in - fails here.
//   * The wiring rows read Relay.class.php and UserInterface.class.php, so the
//     check being unwired (or Multicall's inner gate being removed) fails here.

$class_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/core/Relay_Allowlist.class.php';
if (file_exists($class_file)) { require_once $class_file; }
if (!class_exists('Relay_Allowlist')) {
	fwrite(STDERR, "Relay_Allowlist not defined: {$class_file} does not define class Relay_Allowlist\n");
	echo "FAIL: Relay_Allowlist not defined\n";
	exit(1);
}

$fail = 0;
$rows_done = 0;

function ra_row ( $label, $got, $expect ) {
	global $fail, $rows_done;
	$rows_done++;
	$ok = ($got === $expect);
	printf("%-72s => %-42s %s\n", $label, var_export($got, true), $ok ? 'OK' : 'FAIL (want ' . var_export($expect, true) . ')');
	if (!$ok) { $fail++; }
} // end function ra_row

function ra_section ( $title ) {
	printf("\n-- %s --\n", $title);
} // end function ra_section

function ra_slice ( $src, $start, $end ) {
	$a = strpos($src, $start);
	if ($a === false) { return ''; }
	$b = strpos($src, $end, $a);
	return ($b === false) ? substr($src, $a) : substr($src, $a, $b - $a);
} // end function ra_slice

function ra_any_warning ( $needle ) {
	foreach (Relay_Allowlist::warnings() as $w) { if (strpos($w, $needle) !== false) { return true; } }
	return false;
} // end function ra_any_warning

// Throwaway data files for the load() rows. Hermetic: nothing outside the system
// temp directory is touched, and it is removed when this process ends.
$ra_tmp = sys_get_temp_dir() . '/relay-allowlist-test-' . getmypid();
@mkdir($ra_tmp, 0700, true);
register_shutdown_function(function () use ($ra_tmp) {
	foreach (glob($ra_tmp . '/*') as $f) { @unlink($f); }
	@rmdir($ra_tmp);
});

// ===========================================================================
ra_section('matcher: exact, `*` suffix, non-match, unlisted');
// ===========================================================================
// A synthetic list, deliberately tiny, so each row tests one property.
$P = array(
	'org.freemedsoftware.api.UserInterface.GetUsers',
	'org.freemedsoftware.module.Allergies.picklist',
	'org.freemedsoftware.module.EncounterNotesTemplate.*',
	'org.freemedsoftware.public.Login.Validate',
);
$C = array('enforce' => false, 'patterns' => $P);

ra_row('exact match',                         Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $C), true);
ra_row('exact match, a different listed entry', Relay_Allowlist::allowed('org.freemedsoftware.module.Allergies.picklist', $C), true);
ra_row('`*` suffix matches a member',         Relay_Allowlist::allowed('org.freemedsoftware.module.EncounterNotesTemplate.getTemplates', $C), true);
ra_row('`*` suffix matches another member',   Relay_Allowlist::allowed('org.freemedsoftware.module.EncounterNotesTemplate.getTemplateInfo', $C), true);
// M4, fix round 1: the tail must be non-empty. `X.*` means "any method under X";
// the bare prefix `X.` names no method and cannot dispatch, so matching it only
// gave an operator an edge in the matcher they could not predict.
ra_row('`*` suffix does NOT match the bare prefix (M4)', Relay_Allowlist::allowed('org.freemedsoftware.module.EncounterNotesTemplate.', $C), false);
ra_row('non-match (different class)',         Relay_Allowlist::allowed('org.freemedsoftware.api.ModuleSearch.picklist', $C), false);
ra_row('non-match (different method, listed class)', Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetRecords', $C), false);
ra_row('unlisted method',                     Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $C), false);
ra_row('unlisted public.* method',            Relay_Allowlist::allowed('org.freemedsoftware.public.Login.Logout', $C), false);
ra_row('listed public.* method',              Relay_Allowlist::allowed('org.freemedsoftware.public.Login.Validate', $C), true);
ra_row('totally foreign namespace',           Relay_Allowlist::allowed('net.php.pear.Services_JSON.decode', $C), false);

// ===========================================================================
ra_section('matcher: `*` is a SUFFIX wildcard only, and dots are literal');
// ===========================================================================
ra_row('`*` is not a glob for a longer class name',
	Relay_Allowlist::allowed('org.freemedsoftware.module.EncounterNotesTemplateX.getTemplates', $C), false);
// A `*` anywhere but the end is not a supported wildcard, so the pattern is
// malformed rather than literal: it is rejected at load (with a LOG_WARNING)
// instead of silently never matching, which is what an operator writing it
// actually needs to know.
ra_row('is_too_broad(mid-pattern `*`) — malformed, not literal',
	Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.Get*.Users'), true);
ra_row('a mid-pattern `*` matches nothing',
	Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.Get*.Users', array('enforce' => false, 'patterns' => array('org.freemedsoftware.api.UserInterface.Get*.Users'))), false);
ra_row('  ...and it is reported as rejected',
	Relay_Allowlist::rejected_patterns() === array('org.freemedsoftware.api.UserInterface.Get*.Users'), true);
ra_row('dots are literal, not regex "any char"',
	Relay_Allowlist::allowed('orgXfreemedsoftwareXapiXUserInterfaceXGetUsers', $C), false);
ra_row('a bare `*` prefix pattern matches nothing',
	Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', array('enforce' => false, 'patterns' => array('*'))), false);

// ===========================================================================
ra_section('matcher: case, per PHP method dispatch');
// ===========================================================================
// PHP dispatches method names case-insensitively, so refusing a differently
// cased method name would be a pure outage with no security value. The
// enumeration contains exactly this pair (the client writes `User.GetName`, the
// class declares `getName`).
ra_row('listed `getName`, called as `GetName`',
	Relay_Allowlist::allowed('org.freemedsoftware.core.User.GetName', array('enforce' => false, 'patterns' => array('org.freemedsoftware.core.User.getName'))), true);
ra_row('listed `picklist`, called as `Picklist`',
	Relay_Allowlist::allowed('org.freemedsoftware.module.Allergies.Picklist', $C), true);
ra_row('case-insensitivity does not reach a different method',
	Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsersX', $C), false);

// ===========================================================================
ra_section('matcher: input that is not a usable method string');
// ===========================================================================
ra_row('allowed(NULL)',        Relay_Allowlist::allowed(NULL, $C), false);
ra_row('allowed("")',          Relay_Allowlist::allowed('', $C), false);
ra_row('allowed(array) — a JSON body can put an array in `method`',
	Relay_Allowlist::allowed(array('org.freemedsoftware.api.UserInterface.GetUsers'), $C), false);

// ===========================================================================
ra_section('the two enforcement states (both, explicitly)');
// ===========================================================================
$LOG_ONLY  = array('enforce' => false, 'patterns' => $P);
$ENFORCING = array('enforce' => true,  'patterns' => $P);

ra_row('enforce=false: enforce() is false',   Relay_Allowlist::enforce($LOG_ONLY), false);
ra_row('enforce=false: an unlisted method is still not allowed (the relay logs and RUNS it)',
	Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $LOG_ONLY), false);
ra_row('enforce=false: a listed method is allowed',
	Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $LOG_ONLY), true);

ra_row('enforce=true: enforce() is true',     Relay_Allowlist::enforce($ENFORCING), true);
ra_row('enforce=true: the SAME unlisted method is still not allowed (the relay REFUSES it)',
	Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $ENFORCING), false);
ra_row('enforce=true: a listed method is still allowed',
	Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $ENFORCING), true);

// The two states must differ ONLY in what the relay does with a miss; the
// matcher's verdict is the same. That is what makes the log-only stage a
// measurement of the enforced stage rather than a different configuration.
ra_row('the miss verdict is identical in both states',
	Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $LOG_ONLY)
		=== Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $ENFORCING), true);

// ===========================================================================
ra_section('refuse() — the shared decision point (outer relay AND inner re-dispatch)');
// ===========================================================================
// Fix round 1, C1: the relay's check runs once, on the OUTER method string. The
// one runtime rule a re-dispatcher consults for an inner method must therefore
// be the same one, in the same code, or the two paths can diverge (the log-only
// stage would stop measuring the inner call set, and enforcement would not close
// it). refuse() is that single point: TRUE means "refuse this call and answer
// INVALID_CALL", FALSE means "proceed" (listed, or a miss in the log-only
// stage, which has just been logged).
ra_row('refuse(listed, log-only) === false (proceed)',
	Relay_Allowlist::refuse('org.freemedsoftware.api.UserInterface.GetUsers', $LOG_ONLY), false);
ra_row('refuse(unlisted, log-only) === false (logged, still runs)',
	Relay_Allowlist::refuse('org.freemedsoftware.module.Vitals.GetRecentRecord', $LOG_ONLY), false);
ra_row('refuse(listed, enforcing) === false (proceed)',
	Relay_Allowlist::refuse('org.freemedsoftware.api.UserInterface.GetUsers', $ENFORCING), false);
ra_row('refuse(unlisted, enforcing) === true (REFUSED)',
	Relay_Allowlist::refuse('org.freemedsoftware.module.Vitals.GetRecentRecord', $ENFORCING), true);
ra_row('refuse(NULL, enforcing) === true (a non-string can never match)',
	Relay_Allowlist::refuse(NULL, $ENFORCING), true);
ra_row('refuse(array, enforcing) === true',
	Relay_Allowlist::refuse(array('org.freemedsoftware.module.Vitals.GetList'), $ENFORCING), true);
// The shared point is exactly the relay rule, for every input and both stages:
// a re-dispatcher that calls refuse() cannot be stricter or looser than the
// relay itself.
$ra_shared = true;
foreach (array('org.freemedsoftware.api.UserInterface.GetUsers', 'org.freemedsoftware.module.Vitals.GetList',
               'org.freemedsoftware.public.Login.Validate', NULL, '', array('x')) as $ra_m) {
	foreach (array($LOG_ONLY, $ENFORCING) as $ra_c) {
		$want = ( ! Relay_Allowlist::allowed($ra_m, $ra_c) and Relay_Allowlist::enforce($ra_c) );
		if (Relay_Allowlist::refuse($ra_m, $ra_c) !== $want) { $ra_shared = false; }
	}
}
ra_row('refuse() is exactly (!allowed() and enforce()), in both stages', $ra_shared, true);

// ===========================================================================
ra_section('over-broad patterns are rejected, not honoured');
// ===========================================================================
ra_row('is_too_broad("*")',                    Relay_Allowlist::is_too_broad('*'), true);
ra_row('is_too_broad("org.freemedsoftware.*")', Relay_Allowlist::is_too_broad('org.freemedsoftware.*'), true);
ra_row('is_too_broad("org.freemedsoftware")',  Relay_Allowlist::is_too_broad('org.freemedsoftware'), true);
ra_row('is_too_broad("org.freemedsoftware.api") — a bare namespace', Relay_Allowlist::is_too_broad('org.freemedsoftware.api'), true);
ra_row('is_too_broad(NULL)',                   Relay_Allowlist::is_too_broad(NULL), true);
ra_row('is_too_broad(enumerated exact string)', Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.GetUsers'), false);
ra_row('is_too_broad(Class-level wildcard)',   Relay_Allowlist::is_too_broad('org.freemedsoftware.module.EncounterNotesTemplate.*'), false);
// The boundary, deliberately: a namespace-level wildcard is the WIDEST thing
// this control accepts. It is a real narrowing (101 enumerated api methods, 207
// module methods) and it is the shape the brief names as an example, so it is
// not rejected -- but it is NOT accepted in silence (see the next section) and it
// is not used in the shipped seed.
ra_row('is_too_broad(module-level wildcard, the brief example)', Relay_Allowlist::is_too_broad('org.freemedsoftware.module.*'), false);
ra_row('is_too_broad(api-level wildcard)',     Relay_Allowlist::is_too_broad('org.freemedsoftware.api.*'), false);
ra_row('is_too_broad(Class-level wildcard, api)', Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.*'), false);
// M5, fix round 1: a deeper spelling names no relay method, so it would match
// nothing. Fail-closed and reported rather than silently inert.
ra_row('is_too_broad(one segment too deep) — M5',
	Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.GetUsers.foo'), true);
ra_row('is_too_broad(two segments too deep) — M5',
	Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.GetUsers.foo.bar'), true);
// ...and the documented leading example still is a call set.
ra_row('is_too_broad(a method-name PREFIX wildcard) is false',
	Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.GetUser*'), false);

// and the observable consequence: a rejected bare `*` does not open the door.
$BROAD = array('enforce' => true, 'patterns' => array('*', 'org.freemedsoftware.*', 'org.freemedsoftware.api.UserInterface.GetUsers'));
$ra_broad_allowed = Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $BROAD);
$ra_broad_refused = Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $BROAD);
ra_row('config with a bare `*`: the real pattern still works', $ra_broad_allowed, true);
ra_row('config with a bare `*`: an unlisted method is still refused', $ra_broad_refused, false);
ra_row('config with a bare `*`: both broad patterns were recorded as rejected',
	Relay_Allowlist::rejected_patterns() === array('*', 'org.freemedsoftware.*'), true);
// M6, fix round 1: the rejected-pattern path now has a message attached, not just
// a return value, and the test reads it.
$ra_reject_warns = 0;
foreach (Relay_Allowlist::warnings() as $w) { if (strpos($w, 'REJECTED') !== false) { $ra_reject_warns++; } }
ra_row('a rejected pattern is REPORTED (LOG_WARNING text), not just dropped', $ra_reject_warns, 2);
ra_row('a config with no broad patterns records no rejects',
	(Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $C)
		&& Relay_Allowlist::rejected_patterns() === array()), true);

// ===========================================================================
ra_section('I1 — a namespace-level wildcard is accepted AND REPORTED');
// ===========================================================================
// The wildcard the guard must not reject silently. It grants every method of
// every class under the namespace, so accepting it without a log line is
// indistinguishable from the control being off.
ra_row('is_namespace_wildcard(org.freemedsoftware.api.*)', Relay_Allowlist::is_namespace_wildcard('org.freemedsoftware.api.*'), true);
ra_row('is_namespace_wildcard(org.freemedsoftware.module.*)', Relay_Allowlist::is_namespace_wildcard('org.freemedsoftware.module.*'), true);
ra_row('is_namespace_wildcard(Class-level *. is not namespace-level)', Relay_Allowlist::is_namespace_wildcard('org.freemedsoftware.api.UserInterface.*'), false);
ra_row('is_namespace_wildcard(an exact method) is false', Relay_Allowlist::is_namespace_wildcard('org.freemedsoftware.api.UserInterface.GetUsers'), false);

$NW = array('enforce' => false, 'patterns' => array('org.freemedsoftware.api.*'));
$ra_nw_allowed = Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $NW);
$ra_nw_covered = Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUserType', $NW);
$ra_nw_warn    = ra_any_warning('NAMESPACE-LEVEL WILDCARD');
$ra_nw_named   = false;
foreach (Relay_Allowlist::warnings() as $w) {
	if (strpos($w, "'org.freemedsoftware.api.*'") !== false and preg_match('/at least \d+ method declarations/', $w)) { $ra_nw_named = true; }
}
ra_row('namespace wildcard: it IS honoured (not rejected)', $ra_nw_allowed, true);
ra_row('namespace wildcard: it covers another method of the namespace', $ra_nw_covered, true);
ra_row('namespace wildcard: accepted WITH a prominent warning (I1b)', $ra_nw_warn, true);
ra_row('namespace wildcard: the warning names the pattern and a method count', $ra_nw_named, true);

$CW = array('enforce' => false, 'patterns' => array('org.freemedsoftware.api.UserInterface.*'));
$ra_cw_allowed = Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUserType', $CW);
$ra_cw_warn    = ra_any_warning('NAMESPACE-LEVEL WILDCARD');
ra_row('class-level wildcard: honoured', $ra_cw_allowed, true);
ra_row('class-level wildcard: NOT reported as a namespace wildcard', $ra_cw_warn, false);

// ===========================================================================
ra_section('load() — the documented fail-open branches (M6 / I2 / R31)');
// ===========================================================================
// A missing/unreadable file fails open (documented), a file that does not return
// an array fails open, and so must a file that CANNOT BE PARSED: the operator
// notes instruct hand-editing `patterns`, and `@include` does not suppress the
// ParseError a typo raises, so without the catch the relay would die instead of
// degrading.
$ra_missing = $ra_tmp . '/definitely-absent.php';
$ra_r = Relay_Allowlist::load($ra_missing);
$ra_w = Relay_Allowlist::warnings();
ra_row('load(missing file): enforce is false (fail-open)', $ra_r['enforce'], false);
ra_row('load(missing file): no patterns', $ra_r['patterns'], array());
ra_row('load(missing file): logs it', (count(array_filter($ra_w, function ($x) { return strpos($x, 'is missing') !== false; })) === 1), true);

file_put_contents($ra_tmp . '/notarray.php', "<?php\nreturn 'not an array';\n");
$ra_r = Relay_Allowlist::load($ra_tmp . '/notarray.php');
$ra_w = Relay_Allowlist::warnings();
ra_row('load(non-array file): enforce is false (fail-open)', $ra_r['enforce'], false);
ra_row('load(non-array file): no patterns', $ra_r['patterns'], array());
ra_row('load(non-array file): logs it', (count(array_filter($ra_w, function ($x) { return strpos($x, 'did not return an array') !== false; })) === 1), true);

// A trailing comma inside the array - the exact hand-editing typo the operator
// notes invite.
file_put_contents($ra_tmp . '/corrupt.php', "<?php\nreturn array('enforce' => true, 'patterns' => array('org.freemedsoftware.api.UserInterface.GetUsers',);\n");
$ra_r = Relay_Allowlist::load($ra_tmp . '/corrupt.php');
$ra_w = Relay_Allowlist::warnings();
ra_row('load(corrupt file): enforce is false (fail-open, R31)', $ra_r['enforce'], false);
ra_row('load(corrupt file): no patterns', $ra_r['patterns'], array());
ra_row('load(corrupt file): reported LOUDLY as CORRUPT (R31)', (count(array_filter($ra_w, function ($x) { return strpos($x, 'CORRUPT') !== false; })) === 1), true);

file_put_contents($ra_tmp . '/good.php', "<?php\nreturn array('enforce' => true, 'patterns' => array('org.freemedsoftware.api.UserInterface.GetUsers'));\n");
$ra_r = Relay_Allowlist::load($ra_tmp . '/good.php');
ra_row('load(valid file): enforce is read', $ra_r['enforce'], true);
ra_row('load(valid file): patterns are read', $ra_r['patterns'], array('org.freemedsoftware.api.UserInterface.GetUsers'));
ra_row('load(valid file): no warning was emitted', Relay_Allowlist::warnings(), array());

// ===========================================================================
ra_section('the SHIPPED data file');
// ===========================================================================
$shipped_file = dirname(__FILE__) . '/../../data/config/relay-allowlist.php';
$shipped = is_file($shipped_file) ? include $shipped_file : NULL;
ra_row('data/config/relay-allowlist.php exists and returns an array', is_array($shipped), true);
if (!is_array($shipped)) {
	echo "\n";
	echo "{$fail} FAILED (data file unusable — the remaining rows cannot run)\n";
	exit(1);
}
$patterns = isset($shipped['patterns']) && is_array($shipped['patterns']) ? $shipped['patterns'] : array();

// R26 / the brief: the SHIPPED default must be log-only. This row is the one
// that fails if someone ships enforce=true on a call set no real client has ever
// exercised.
ra_row('shipped enforce is false (log-only — ruling R26)', (bool) (isset($shipped['enforce']) ? $shipped['enforce'] : true), false);
ra_row('shipped pattern list is non-empty', count($patterns) > 0, true);
// The count is pinned EXACTLY by the drift guard below (M2); this is only the
// "the file was not truncated before the guard ran" floor.
ra_row('shipped pattern count is not a truncated file (>= 300)', count($patterns) >= 300, true);

$too_broad_shipped = array();
foreach ($patterns as $p) { if (Relay_Allowlist::is_too_broad($p)) { $too_broad_shipped[] = $p; } }
ra_row('no shipped pattern is too broad', $too_broad_shipped, array());
if ($too_broad_shipped) { echo "     too broad: " . implode(', ', $too_broad_shipped) . "\n"; }

// Every shipped pattern must be the shape the matcher documents: exactly four
// dots (org.freemedsoftware.<ns>.<Class>.<Method>), which is also what makes the
// namespace-level wildcard the only 3-dot shape that is accepted.
$bad_shape = array();
foreach ($patterns as $p) { if (substr_count($p, '.') !== 4) { $bad_shape[] = $p; } }
ra_row('every shipped pattern is org.freemedsoftware.<ns>.<Class>.<Method>', $bad_shape, array());
if ($bad_shape) { echo "     wrong shape: " . implode(', ', $bad_shape) . "\n"; }

// The shipped file loads through the real load() path, not only through a bare
// include.
$ra_loaded = Relay_Allowlist::load($shipped_file);
ra_row('the shipped file itself loads with enforce unchanged',
	(bool) $ra_loaded['enforce'], (bool) $shipped['enforce']);
ra_row('the shipped file itself loads with every pattern kept',
	count($ra_loaded['patterns']) === count($patterns), true);

// Named spot checks — the calls the rest of this mitigation actually made, plus
// the login flow, which the allowlist must not break (relay.php skips its login
// guard for public.*, but the allowlist check still runs for those methods).
$must_be_listed = array(
	'org.freemedsoftware.api.UserInterface.GetUsers',              // 2.2-2.5 live probe
	'org.freemedsoftware.api.ModuleSearch.picklist',               // 2.2-2.5 live probe
	'org.freemedsoftware.module.EncounterNotesTemplate.getTemplates', // 2.2-2.5 live probe
	'org.freemedsoftware.api.Remitt.RenderStatementXML',           // 2.2-2.5 live probe
	'org.freemedsoftware.module.Allergies.GetList',                // 2.6f live probe (EMRModule::GetList)
	'org.freemedsoftware.api.UserInterface.Multicall',            // a real client calls it: it must stay listed AND gated
	'org.freemedsoftware.public.Login.LoggedIn',                   // session assertion
	'org.freemedsoftware.public.Login.Validate',                   // the login POST itself
	'org.freemedsoftware.public.Login.Logout',
	'org.freemedsoftware.public.Login.GetLanguages',
	'org.freemedsoftware.public.Login.GetLocations',
	'org.freemedsoftware.public.Installation.CheckDbCredentials',  // the installer flow
	'org.freemedsoftware.public.Installation.CreateDatabase',
	'org.freemedsoftware.public.Installation.CreateSettings',
	'org.freemedsoftware.public.Installation.SetHealthyStatus',
);
$missing = array();
foreach ($must_be_listed as $m) {
	$hit = false;
	foreach ($patterns as $p) { if (strcasecmp($p, $m) === 0) { $hit = true; break; } }
	if (!$hit) { $missing[] = $m; }
}
ra_row('every login/installer/measured call is listed', $missing, array());
if ($missing) { echo "     missing: " . implode(', ', $missing) . "\n"; }

// ===========================================================================
ra_section('data file <-> committed enumeration (the drift guard)');
// ===========================================================================
$enum_file = dirname(__FILE__) . '/evidence/relay-callset.txt';
if (!is_file($enum_file)) {
	ra_row('tests/security/evidence/relay-callset.txt exists', false, true);
} else {
	$enumerated = array();          // lowercased method => source
	$live_probe = array();
	foreach (file($enum_file) as $line) {
		if ($line === '' or $line[0] === '#' or strpos($line, "\t") === false) { continue; }
		$f = explode("\t", rtrim($line, "\n"));
		$enumerated[strtolower($f[0])] = $f[1];
		if (isset($f[1]) and $f[1] === 'live-probe') { $live_probe[strtolower($f[0])] = $f[0]; }
	}
	ra_row('the enumeration is non-empty', count($enumerated) > 0, true);

	$shipped_lc = array();
	foreach ($patterns as $p) { $shipped_lc[strtolower($p)] = $p; }

	// direction 1: nothing is in the list without evidence.
	$unevidenced = array();
	foreach ($patterns as $p) {
		if (!isset($enumerated[strtolower($p)])) { $unevidenced[] = $p; }
	}
	ra_row('every shipped pattern appears in relay-callset.txt', $unevidenced, array());
	if ($unevidenced) { echo "     unevidenced: " . implode(', ', $unevidenced) . "\n"; }

	// direction 2 (M2, fix round 1): nothing ENUMERATED is absent from the list
	// either. Direction 1 alone let an evidenced pattern be deleted to "fix" a
	// refusal while the suite still passed.
	$unlisted = array();
	foreach ($enumerated as $k => $src) {
		if (!isset($shipped_lc[$k])) { $unlisted[] = $k; }
	}
	ra_row('every enumerated method is in the shipped list (M2, direction 2)', $unlisted, array());
	if ($unlisted) { echo "     enumerated but unlisted: " . implode(', ', $unlisted) . "\n"; }

	// ...and the SIZES must be equal, so a deletion cannot be hidden by a
	// rename or a case change inside the same count. They are equal today
	// (323/323, measured); the two numbers are printed by the rows below.
	ra_row('shipped count === enumerated count (M2: equality, not a floor)',
		count($shipped_lc) === count($enumerated), true);

	// I1c: the guard reports EVERY wildcard in use. A wildcard cannot be matched
	// against the enumeration as an exact string, so it is listed explicitly here
	// rather than silently skipped by direction 1.
	$wildcards = array();
	foreach ($patterns as $p) { if (substr($p, -1) === '*') { $wildcards[] = $p; } }
	if ($wildcards) { echo "     wildcards in use: " . implode(', ', $wildcards) . "\n"; }
	ra_row('no wildcard is in use in the shipped list (I1c: the guard names each one)', $wildcards, array());

	// direction 2: nothing MEASURED live came off the list.
	$dropped = array();
	foreach ($live_probe as $k => $display) {
		if (!isset($enumerated[$k])) { continue; }
		$hit = false;
		foreach ($patterns as $p) { if (strcasecmp($p, $display) === 0) { $hit = true; break; } }
		if (!$hit) { $dropped[] = $display; }
	}
	ra_row('every live-probe method is in the list (no measured call was dropped)', $dropped, array());
	if ($dropped) { echo "     dropped: " . implode(', ', $dropped) . "\n"; }

	ra_row('the enumeration carries a provenance source per row',
		(count(array_filter($enumerated, function ($s) { return $s !== ''; })) === count($enumerated)), true);
} // end enumeration cross-check

// ===========================================================================
ra_section('relay wiring (source level — the HTTP proof is in evidence/)');
// ===========================================================================
$relay_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/core/Relay.class.php';
$relay_src = is_file($relay_file) ? file_get_contents($relay_file) : '';
ra_row('Relay.class.php loads Relay_Allowlist',
	(strpos($relay_src, "LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist')") !== false), true);
// Fix round 1: the relay consults the SAME decision point a re-dispatcher must
// use (refuse()), and does not build its own copy of the rule.
ra_row('Relay::handle_request consults the allowlist through refuse() only',
	(strpos($relay_src, 'Relay_Allowlist::refuse') !== false
		and strpos($relay_src, 'Relay_Allowlist::allowed') === false), true);
ra_row('and it returns INVALID_CALL on refusal',
	(strpos($relay_src, "return 'INVALID_CALL';") !== false), true);
// M1: a missing class file must degrade, not fatal the first relay request.
ra_row('Relay.class.php degrades when Relay_Allowlist is missing (M1)',
	(strpos($relay_src, "class_exists ( 'Relay_Allowlist' )") !== false), true);
// Placement matters: the check must come AFTER the LoggedIn guard (so a
// never-logged-in caller is still told INVALID_SESSION) and BEFORE the
// call_user_func_array dispatch (or it would be pointless).
$pos_guard = strpos($relay_src, 'Login.LoggedIn');
$pos_check = strpos($relay_src, 'Relay_Allowlist::refuse');
$pos_dispatch = strpos($relay_src, "call_user_func_array ( 'CallMethod'");
ra_row('guard < check < dispatch (order in handle_request)',
	($pos_guard !== false and $pos_check !== false and $pos_dispatch !== false
		and $pos_guard < $pos_check and $pos_check < $pos_dispatch), true);

// ===========================================================================
ra_section('the C1 re-dispatcher gate (api/UserInterface.class.php:Multicall)');
// ===========================================================================
$ui_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/api/UserInterface.class.php';
$ui_src = is_file($ui_file) ? file_get_contents($ui_file) : '';
ra_row('UserInterface.class.php loads Relay_Allowlist',
	(strpos($ui_src, "LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist')") !== false), true);
$multicall = ra_slice($ui_src, 'public function Multicall', '// end method Multicall');
ra_row('Multicall exists in the source', ($multicall !== ''), true);
ra_row('Multicall gates the INNER method with the shared refuse()',
	(strpos($multicall, 'Relay_Allowlist::refuse') !== false), true);
ra_row('a refused inner call puts INVALID_CALL in ITS OWN slot',
	(strpos($multicall, "'INVALID_CALL'") !== false), true);
ra_row('the refusal is PER CALL (continue; the batch is not aborted)',
	(strpos($multicall, 'continue;') !== false), true);
ra_row('the dead 25/24 `core` guard is gone',
	(strpos($multicall, "substr(\$v['method'], 0, 25)") === false), true);

// ---------------------------------------------------------------------------
echo "\n";
if ($fail) { echo "{$fail} FAILED of {$rows_done} rows\n"; exit(1); }
echo "all passed ({$rows_done} rows)\n";
exit(0);
