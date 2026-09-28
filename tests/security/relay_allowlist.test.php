<?php
// tests/security/relay_allowlist.test.php — run: php tests/security/relay_allowlist.test.php
//
// Task 2.8: Relay_Allowlist is the deny-by-default call-set check the relay
// consults before dispatch, so that the NEXT quoting omission is not remotely
// reachable through relay.php. This suite pins the matcher, the two enforcement
// states, the too-broad guard, and the correspondence between the shipped data
// file and the committed enumeration.
//
// Hermetic: no database, no network, no server, no fixtures. The relay's own
// dispatch cannot be exercised from here (handle_request() calls CallMethod for
// the LoggedIn guard, which needs the framework and a database), so the wiring
// is asserted at source level in the last section and end-to-end over HTTP in
// tests/security/evidence/relay-allowlist.txt.
//
//   docker run --rm -v "$PWD":/app -w /app php:8.3-cli php tests/security/relay_allowlist.test.php
//
// How a row can FAIL:
//   * The class does not exist (the pre-change tree): the include check below
//     exits 1 before any row runs, so this file fails against the tree without
//     the change instead of trivially passing.
//   * The matcher rows assert exact booleans, so a matcher that matched too much
//     (regex dots, a `*` honoured mid-pattern, a too-broad pattern honoured) and
//     one that matched too little (case-sensitive comparison, no `*` support)
//     both fail.
//   * The data-file rows re-read the SHIPPED file and the SHIPPED enumeration, so
//     a pattern added to the list without evidence — or a measured live call that
//     was dropped from the list — fails here.

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
ra_row('`*` suffix matches the bare prefix',  Relay_Allowlist::allowed('org.freemedsoftware.module.EncounterNotesTemplate.', $C), true);
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
// this control accepts. It is a real narrowing (101 enumerated api methods, 201
// module methods) and it is the shape the brief names as an example, so it is
// not rejected — but it is also not used in the shipped seed.
ra_row('is_too_broad(module-level wildcard, the brief example)', Relay_Allowlist::is_too_broad('org.freemedsoftware.module.*'), false);
ra_row('is_too_broad(api-level wildcard)',     Relay_Allowlist::is_too_broad('org.freemedsoftware.api.*'), false);
ra_row('is_too_broad(Class-level wildcard, api)', Relay_Allowlist::is_too_broad('org.freemedsoftware.api.UserInterface.*'), false);

// and the observable consequence: a rejected bare `*` does not open the door.
$BROAD = array('enforce' => true, 'patterns' => array('*', 'org.freemedsoftware.*', 'org.freemedsoftware.api.UserInterface.GetUsers'));
ra_row('config with a bare `*`: the real pattern still works',
	Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $BROAD), true);
ra_row('config with a bare `*`: an unlisted method is still refused',
	Relay_Allowlist::allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $BROAD), false);
ra_row('config with a bare `*`: both broad patterns were recorded as rejected',
	Relay_Allowlist::rejected_patterns() === array('*', 'org.freemedsoftware.*'), true);
ra_row('a config with no broad patterns records no rejects',
	(Relay_Allowlist::allowed('org.freemedsoftware.api.UserInterface.GetUsers', $C)
		&& Relay_Allowlist::rejected_patterns() === array()), true);

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
ra_row('shipped pattern count (a floor, so a truncated file is caught)', count($patterns) >= 300, true);

$too_broad_shipped = array();
foreach ($patterns as $p) { if (Relay_Allowlist::is_too_broad($p)) { $too_broad_shipped[] = $p; } }
ra_row('no shipped pattern is too broad', $too_broad_shipped, array());
if ($too_broad_shipped) { echo "     too broad: " . implode(', ', $too_broad_shipped) . "\n"; }

// Named spot checks — the calls the rest of this mitigation actually made, plus
// the login flow, which the allowlist must not break (relay.php skips its login
// guard for public.*, but the allowlist check still runs for those methods).
$must_be_listed = array(
	'org.freemedsoftware.api.UserInterface.GetUsers',              // 2.2-2.5 live probe
	'org.freemedsoftware.api.ModuleSearch.picklist',               // 2.2-2.5 live probe
	'org.freemedsoftware.module.EncounterNotesTemplate.getTemplates', // 2.2-2.5 live probe
	'org.freemedsoftware.api.Remitt.RenderStatementXML',           // 2.2-2.5 live probe
	'org.freemedsoftware.module.Allergies.GetList',                // 2.6f live probe (EMRModule::GetList)
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

	// direction 1: nothing is in the list without evidence.
	$unevidenced = array();
	foreach ($patterns as $p) {
		if (!isset($enumerated[strtolower($p)])) { $unevidenced[] = $p; }
	}
	ra_row('every shipped pattern appears in relay-callset.txt', $unevidenced, array());
	if ($unevidenced) { echo "     unevidenced: " . implode(', ', $unevidenced) . "\n"; }

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
ra_row('Relay::handle_request consults Relay_Allowlist::allowed()',
	(strpos($relay_src, 'Relay_Allowlist::allowed') !== false), true);
ra_row('and it returns INVALID_CALL on refusal',
	(strpos($relay_src, "return 'INVALID_CALL';") !== false), true);
// Placement matters: the check must come AFTER the LoggedIn guard (so a
// never-logged-in caller is still told INVALID_SESSION) and BEFORE the
// call_user_func_array dispatch (or it would be pointless).
$pos_guard = strpos($relay_src, 'Login.LoggedIn');
$pos_check = strpos($relay_src, 'Relay_Allowlist::allowed');
$pos_dispatch = strpos($relay_src, "call_user_func_array ( 'CallMethod'");
ra_row('guard < check < dispatch (order in handle_request)',
	($pos_guard !== false and $pos_check !== false and $pos_dispatch !== false
		and $pos_guard < $pos_check and $pos_check < $pos_dispatch), true);

// ---------------------------------------------------------------------------
echo "\n";
if ($fail) { echo "{$fail} FAILED of {$rows_done} rows\n"; exit(1); }
echo "all passed ({$rows_done} rows)\n";
exit(0);
