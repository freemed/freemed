<?php
// tests/security/relay_allowlist.test.php — run: php tests/security/relay_allowlist.test.php
//
// Task 2.8: Relay_Allowlist is the deny-by-default call-set check the relay
// consults before dispatch, so that the NEXT quoting omission is not remotely
// reachable through relay.php. This suite pins the matcher, the two enforcement
// states, the too-broad guard, the load-time fail-open branches (including the
// corrupt data file), the shared decision point that a relay re-dispatcher must
// use for its inner calls on BOTH axes (the caller-supplied method string of
// Multicall, the caller-chosen class of the ModuleInterface wrappers), the
// never-allow namespace rule that the inner scope reinstates (R36), the
// FormTemplate re-dispatcher's gates (N2), the measured class-axis exposure in
// the committed enumeration (N3/R32), and the correspondence between the
// shipped data file and that enumeration.
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
//   * The wiring rows read Relay.class.php, UserInterface.class.php,
//     ModuleInterface.class.php and FormTemplate.class.php, so the check being
//     unwired (or Multicall's inner gate, or one of the class-axis gates, being
//     removed) fails here.
//   * The R32 PLACEMENT rows read api/PatientInterface.class.php's
//     MoveEmrAttachments and assert that the gate CALL precedes the FIRST write of
//     the loop, so the earlier revision -- which gated after the two UPDATEs and
//     therefore left a refused call with the record already moved -- fails here.
//     (The served-copy ablation that measures the same ordering live is in
//     tests/security/evidence/relay-allowlist.txt, "FIX ROUND 4".)
//   * The R36 rows pin the reinstated `core` never-allow rule (refused in BOTH
//     stages for an inner call, logged-and-not-refused on the outer path, and
//     the policy in the data file), and two of them keep the round-1 "dead
//     code" claim from coming back into the source.
//   * The class-axis rows parse the '#class-axis' section of the committed
//     enumeration and pin the measured exposure (866 pairs, 803 unlisted for
//     the eight module_function literals). They fail if that section is
//     removed or if the measurement drifts without being re-taken.

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
ra_section('R36 — never-allow namespaces: INNER refused in BOTH stages, OUTER logged only');
// The round-2 never-allow surface may be absent (a tree that predates fix round 2).
// If it is, the rows below cannot run and the FAILING row says so, in the same place,
// instead of taking the whole suite down with an undefined-method fatal.
if ( ! method_exists ( 'Relay_Allowlist', 'never_allowed' )
		or ! method_exists ( 'Relay_Allowlist', 'never_allow_patterns' )
		or ! method_exists ( 'Relay_Allowlist', 'never_allow_rejected' )
		or ! method_exists ( 'Relay_Allowlist', 'never_allow_log' )
		or ! method_exists ( 'Relay_Allowlist', 'reset_never_allow_log' ) ) {
	ra_row('R36 surface: Relay_Allowlist implements never_allowed()/never_allow_patterns()/never_allow_rejected()/never_allow_log()/reset_never_allow_log()', false, true);
} else {
	// ===========================================================================
	// Fix round 2. The pre-2.8 Multicall guard refused every inner call in the
	// `core` namespace outright:
	//     if ( substr($v['method'], 0, 25) == 'org.freemedsoftware.core.' ) { ... return false; }
	// The literal is 25 characters, so the comparison MATCHED -- the guard was live,
	// not dead code, and round 1 removed it. This section pins the reinstated rule:
	// the policy lives in the data file ('never_allow'), it beats 'patterns' for an
	// inner call with a caller-supplied method name, and the OUTER path is
	// asymmetric on purpose (logged, never refused, so shipped outer behaviour is
	// unchanged).
	$NEVER = array('enforce' => false, 'patterns' => $P, 'never_allow' => array('org.freemedsoftware.core.'));
	$NEVER_E = array('enforce' => true,  'patterns' => $P, 'never_allow' => array('org.freemedsoftware.core.'));

	ra_row('never_allowed(core.*) names the matching prefix',
		Relay_Allowlist::never_allowed('org.freemedsoftware.core.User.SetPassword', $NEVER), 'org.freemedsoftware.core.');
	ra_row('never_allowed is case-insensitive (PHP dispatches method names that way)',
		Relay_Allowlist::never_allowed('ORG.FREEMEDSOFTWARE.Core.User.setPassword', $NEVER), 'org.freemedsoftware.core.');
	ra_row('never_allowed(other namespace) is NULL', Relay_Allowlist::never_allowed('org.freemedsoftware.module.Vitals.add', $NEVER), NULL);
	ra_row('never_allowed(prefix itself, no method) still matches', Relay_Allowlist::never_allowed('org.freemedsoftware.core.', $NEVER), 'org.freemedsoftware.core.');
	ra_row('never_allowed(NULL) is NULL', Relay_Allowlist::never_allowed(NULL, $NEVER), NULL);
	ra_row('never_allowed(array) is NULL (a JSON body can put an array in `method`)',
		Relay_Allowlist::never_allowed(array('org.freemedsoftware.core.User.SetPassword'), $NEVER), NULL);
	ra_row('a config with NO never_allow key (every pre-round-2 fixture) never fires',
		Relay_Allowlist::never_allowed('org.freemedsoftware.core.User.SetPassword', $C), NULL);

	// INNER scope: refused in BOTH stages -- the parity rule. This is the row that
	// fails if the reinstated refusal is put behind enforce().
	ra_row('R36 inner: a core.* call is REFUSED in the LOG-ONLY stage (pre-2.8 parity)',
		Relay_Allowlist::refuse('org.freemedsoftware.core.User.SetPassword', $NEVER, true), true);
	ra_row('R36 inner: and refused under enforcement', Relay_Allowlist::refuse('org.freemedsoftware.core.User.SetPassword', $NEVER_E, true), true);
	// even when 'patterns' names it -- the never-allow rule beats the list.
	$NEVER_LISTED = array('enforce' => false, 'patterns' => array('org.freemedsoftware.core.User.setPassword'), 'never_allow' => array('org.freemedsoftware.core.'));
	ra_row('R36 inner: it beats a LISTED pattern (never-allow wins)', Relay_Allowlist::refuse('org.freemedsoftware.core.User.setPassword', $NEVER_LISTED, true), true);
	// and the inner scope adds NOTHING else: an ordinary unlisted method is still
	// only logged in log-only, or the log-only stage would stop measuring.
	ra_row('R36 inner scope adds nothing else: an ordinary unlisted miss is still logged-and-run in log-only',
		Relay_Allowlist::refuse('org.freemedsoftware.module.Vitals.GetRecentRecord', $NEVER, true), false);
	ra_row('R36 inner scope adds nothing else: an ordinary unlisted miss is still refused under enforcement',
		Relay_Allowlist::refuse('org.freemedsoftware.module.Vitals.GetRecentRecord', $NEVER_E, true), true);

	// OUTER scope: logged but NOT refused, in BOTH stages.
	ra_row('R36 outer: a core.* call in log-only is NOT refused (logged only)',
		Relay_Allowlist::refuse('org.freemedsoftware.core.User.SetPassword', $NEVER), false);
	ra_row('R36 outer: and NOT refused by the never-allow rule under enforcement either (a LISTED core.* method still proceeds)',
		Relay_Allowlist::refuse('org.freemedsoftware.core.User.setPassword', array('enforce' => true, 'patterns' => array('org.freemedsoftware.core.User.setPassword'), 'never_allow' => array('org.freemedsoftware.core.'))), false);
	ra_row('R36 outer: a LISTED core.* method still proceeds (core.User.getName is a real outer call)',
		Relay_Allowlist::refuse('org.freemedsoftware.core.User.GetName', array('enforce' => false, 'patterns' => array('org.freemedsoftware.core.User.getName'), 'never_allow' => array('org.freemedsoftware.core.'))), false);
	ra_row('the default (no third argument) IS the outer scope', Relay_Allowlist::refuse('org.freemedsoftware.core.User.SetPassword', $NEVER), false);

	// The log lines are the operator's only signal, so their TEXT is pinned.
	Relay_Allowlist::config($NEVER);
	Relay_Allowlist::reset_never_allow_log();
	Relay_Allowlist::refuse('org.freemedsoftware.core.User.SetPassword', $NEVER, true);
	$ra_nal = Relay_Allowlist::never_allow_log();
	ra_row('R36: the INNER refusal records exactly one line', count($ra_nal), 1);
	ra_row('R36: the INNER line names the namespace AND says it is refused in both stages',
		(count($ra_nal) === 1 and strpos($ra_nal[0], "NEVER-ALLOW namespace 'org.freemedsoftware.core.'") !== false
			and strpos($ra_nal[0], 'REFUSED REGARDLESS') !== false and strpos($ra_nal[0], 'BOTH stages') !== false), true);
	Relay_Allowlist::reset_never_allow_log();
	Relay_Allowlist::refuse('org.freemedsoftware.core.User.SetPassword', $NEVER);
	$ra_nal = Relay_Allowlist::never_allow_log();
	ra_row('R36: the OUTER line says it is logged and NOT refused', count($ra_nal), 1);
	ra_row('R36: the OUTER line states the asymmetry',
		(count($ra_nal) === 1 and strpos($ra_nal[0], 'LOGGED AND NOT REFUSED HERE') !== false), true);

	// normalize() validates the entries: malformed or too broad ones are dropped
	// (a never-allow that cannot be parsed must not start refusing calls, and
	// 'org.freemedsoftware.' would refuse EVERY inner call).
	$NEVER_BAD = array('enforce' => true, 'patterns' => $P, 'never_allow' => array(
		'org.freemedsoftware.core.', 'org.freemedsoftware.', 'org.freemedsoftware.core', '', array('x'),
	));
	$ra_nb = Relay_Allowlist::config($NEVER_BAD);
	ra_row('R36: only the well-formed never_allow entry is honoured', $ra_nb['never_allow'], array('org.freemedsoftware.core.'));
	ra_row('R36: the rejected never_allow entries are reported',
		Relay_Allowlist::never_allow_rejected(), array('org.freemedsoftware.', 'org.freemedsoftware.core'));
	ra_row('R36: every REJECTED entry is logged with its reason',
		(count(array_filter(Relay_Allowlist::warnings(), function ($w) { return strpos($w, 'never_allow') !== false and strpos($w, 'REJECTED') !== false; })) === 2), true);
	ra_row('R36: a non-string/empty never_allow entry is warned about and dropped (both spellings)',
		(count(array_filter(Relay_Allowlist::warnings(), function ($w) { return strpos($w, 'non-string/empty never_allow') !== false; })) === 2), true);
	ra_row('R36: a too-broad never_allow does NOT refuse an inner call that IS listed',
		Relay_Allowlist::refuse('org.freemedsoftware.api.UserInterface.GetUsers', array('enforce' => true, 'patterns' => $P, 'never_allow' => array('org.freemedsoftware.')), true), false);

	// A data file that carries the key loads through the real load() path.
	file_put_contents($ra_tmp . '/never.php', "<?php\nreturn array('enforce' => false, 'patterns' => array('org.freemedsoftware.api.UserInterface.GetUsers'), 'never_allow' => array('org.freemedsoftware.core.'));\n");
	$ra_r = Relay_Allowlist::load($ra_tmp . '/never.php');
	ra_row('R36: load() reads never_allow', $ra_r['never_allow'], array('org.freemedsoftware.core.'));
	// and a file that omits the key (the shape of every install upgrading into this
	// round) yields an EMPTY list, i.e. no refusal -- the fail-open direction.
	file_put_contents($ra_tmp . '/nokey.php', "<?php\nreturn array('enforce' => false, 'patterns' => array('org.freemedsoftware.api.UserInterface.GetUsers'));\n");
	ra_row('R36: load() of a file with no never_allow yields an empty list',
		Relay_Allowlist::load($ra_tmp . '/nokey.php')['never_allow'], array());

} // end R36 section

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

// R36: the never-allow policy lives in the DATA FILE, so a deployment can see
// and change it, and the shipped seed is the pre-2.8 Multicall() refusal.
// (method_exists guard: on a tree that predates fix round 2 the class has no
// never-allow surface at all, and that must be a FAILING row, not a fatal.)
ra_row('shipped never_allow is the pre-2.8 `core` namespace',
	(array_key_exists('never_allow', $shipped) && is_array($shipped['never_allow']) && $shipped['never_allow'] === array('org.freemedsoftware.core.')), true);
if (method_exists('Relay_Allowlist', 'never_allowed')) {
	ra_row('shipped never_allow is honoured through the real loader',
		Relay_Allowlist::never_allowed('org.freemedsoftware.core.User.SetPassword', $shipped), 'org.freemedsoftware.core.');
	ra_row('shipped never_allow does not catch a module method',
		Relay_Allowlist::never_allowed('org.freemedsoftware.module.Vitals.GetRecentRecord', $shipped), NULL);
} else {
	ra_row('shipped never_allow is honoured through the real loader (NO SURFACE: this tree predates fix round 2)', false, true);
	ra_row('shipped never_allow does not catch a module method (NO SURFACE: this tree predates fix round 2)', false, true);
}

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
ra_section('R32/R36 — the CLASS AXIS measured in the enumeration (fix round 2)');
// ===========================================================================
// The fixed-literal wrappers in api/ModuleInterface.class.php dispatch
// `org.freemedsoftware.module.<Class>.<literal>` against a CALLER-CHOSEN class,
// so a per-method allowlist cannot constrain them. relay-callset-enum.php
// enumerates every such pair (module class file x literal, inheritance walked)
// and classifies it against the SHIPPED list with the real matcher. Those pairs
// are EVIDENCE OF EXPOSURE, not a seed, so they are emitted as '#class-axis'
// lines and the drift guard above (which reads the tab-delimited rows and
// compares against the shipped list in both directions) is untouched by them.
//
// N3: this is the corrected measurement. The report stated "146 pairs, 129
// unlisted"; that does not reproduce under its own stated definition. The
// measured figures are pinned below, together with the restriction each uses.
$CLASS_AXIS_LITERALS = array('add', 'del', 'GetRecord', 'GetRecords', 'mod', 'picklist', 'RenderHtmlView', 'to_text');
$axis_all = array('total' => 0, 'unlisted' => 0, 'listed' => 0);
$axis_pdf = array('total' => 0, 'unlisted' => 0, 'listed' => 0);
$axis_badshape = array();
$axis_other_literal = array();
if (!is_file($enum_file)) {
	ra_row('the enumeration carries the class-axis section', false, true);
} else {
	foreach (file($enum_file) as $line) {
		if (strpos($line, '#class-axis') !== 0) { continue; }
		$f = explode("	", rtrim($line, "\n"));
		if (count($f) !== 4) { $axis_badshape[] = rtrim($line, "\n"); continue; }
		$concrete = $f[1];
		$verdict = $f[2];
		if (!preg_match('/^org\.freemedsoftware\.module\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+$/', $concrete)) { $axis_badshape[] = $concrete; }
		$literal = substr($concrete, strrpos($concrete, '.') + 1);
		if ($literal !== 'RenderToPDF' and !in_array($literal, $CLASS_AXIS_LITERALS, true)) { $axis_other_literal[] = $concrete; }
		if ($literal === 'RenderToPDF') {
			$axis_pdf['total']++;
			if ($verdict === 'listed') { $axis_pdf['listed']++; } else { $axis_pdf['unlisted']++; }
		} else {
			$axis_all['total']++;
			if ($verdict === 'listed') { $axis_all['listed']++; } else { $axis_all['unlisted']++; }
		}
	}
	ra_row('every class-axis row is org.freemedsoftware.module.<Class>.<literal>', $axis_badshape, array());
	ra_row('every class-axis row names one of the nine wrapper literals', $axis_other_literal, array());
	// The N3 measurement, pinned. Definition: all 133 module class files,
	// inheritance walked, public methods only, classified against the shipped
	// 323 patterns with the real matcher.
	ra_row('N3: 8 literals, all module classes — total pairs', $axis_all['total'], 866);
	ra_row('N3: 8 literals — NOT in the shipped list (the exposure)', $axis_all['unlisted'], 803);
	ra_row('N3: 8 literals — in the shipped list', $axis_all['listed'], 63);
	ra_row('N3: listed + unlisted === total', ($axis_all['listed'] + $axis_all['unlisted'] === $axis_all['total']), true);
	ra_row('N3: the print wrappers Literal RenderToPDF pairs (separate definition)', $axis_pdf['total'], 37);
	ra_row('N3: the shipped list seeds NONE of the RenderToPDF pairs', $axis_pdf['listed'], 0);
	ra_row('the enumeration is genuinely larger than the list (the R32 quantitative case)',
		($axis_all['unlisted'] > count($patterns) * 2), true);

	// -----------------------------------------------------------------
	// N3, fix round 3: the FOUR published figures in the artifact's header.
	// Round 2 published "the 8 literals, restricted to classes the shipped list
	// names : 448 pairs, 385 NOT listed, 63 listed". That figure is WRONG under
	// its own label: the generator's 'named' accumulator (and its 'own_file'
	// sibling) sat OUTSIDE the `!$is_pdf` guard, so it counted the 25
	// RenderToPDF pairs whose class the shipped list names as well -- while the
	// label, and the comment right above the accumulator, both say the EIGHT
	// literals. 448 - 25 = 423. The figure was published in three places
	// (report section FR4, evidence/relay-allowlist.txt, this artifact) and no
	// test row pinned it, which is why it survived a fix round. The rows below
	// pin it twice: as the published text, and recomputed from the very rows
	// the artifact emits. A generator that regressed would now contradict
	// itself and go red.
	$enum_src = implode('', file($enum_file));
	$axis_pub = array();
	if (preg_match('/all 133 module class files\s+: (\d+) pairs, (\d+) NOT in the shipped list, (\d+) in it/', $enum_src, $m)) {
		$axis_pub['all'] = array((int) $m[1], (int) $m[2], (int) $m[3]);
	}
	if (preg_match('/\+RenderToPDF \([^)]*\): (\d+) pairs, (\d+) NOT in the shipped list, (\d+) in it/', $enum_src, $m)) {
		$axis_pub['pdf'] = array((int) $m[1], (int) $m[2], (int) $m[3]);
	}
	if (preg_match('/the 8 literals, restricted to classes the shipped list names\s*: (\d+) pairs, (\d+) NOT listed, (\d+) listed/', $enum_src, $m)) {
		$axis_pub['named'] = array((int) $m[1], (int) $m[2], (int) $m[3]);
	}
	if (preg_match('/the 8 literals, declared in the module file ITSELF\s*: (\d+) pairs, (\d+) NOT listed, (\d+) listed/', $enum_src, $m)) {
		$axis_pub['own_file'] = array((int) $m[1], (int) $m[2], (int) $m[3]);
	}
	ra_row('N3: the artifact publishes all four class-axis figures', count($axis_pub), 4);
	if (count($axis_pub) === 4) {
		ra_row('N3 published: 8 literals, all module classes', $axis_pub['all'], array(866, 803, 63));
		ra_row('N3 published: +RenderToPDF (the print wrappers, separate definition)', $axis_pub['pdf'], array(37, 37, 0));
		ra_row('N3 published: 8 literals restricted to classes the list names (corrected in fix round 3)', $axis_pub['named'], array(423, 360, 63));
		ra_row('N3 published: 8 literals declared in the module file itself', $axis_pub['own_file'], array(7, 6, 1));
	} else {
		ra_row('N3: the artifact publishes all four class-axis figures', array_keys($axis_pub), array('all', 'pdf', 'named', 'own_file'));
	}

	// The same figure, recomputed from the artifact's own rows + the shipped
	// patterns: "restricted to classes the shipped list names" is a property
	// of the rows, so it cannot be right in the header and wrong in the rows.
	$axis_named_classes = array();
	foreach ($patterns as $p) {
		if (preg_match('/^org\.freemedsoftware\.module\.([A-Za-z0-9_]+)\./i', $p, $m)) {
			$axis_named_classes[strtolower($m[1])] = true;
		}
	}
	$axis_named = array('total' => 0, 'unlisted' => 0, 'listed' => 0);
	$prefix = 'org.freemedsoftware.module.';
	foreach (file($enum_file) as $line) {
		if (strpos($line, '#class-axis') !== 0) { continue; }
		$f = explode("	", rtrim($line, "\n"));
		if (count($f) !== 4) { continue; }
		$concrete = $f[1];
		$literal = substr($concrete, strrpos($concrete, '.') + 1);
		if ($literal === 'RenderToPDF') { continue; }        // the EIGHT literals only
		if (strpos($concrete, $prefix) !== 0) { continue; }
		$class = substr($concrete, strlen($prefix), strrpos($concrete, '.') - strlen($prefix));
		if (!isset($axis_named_classes[strtolower($class)])) { continue; }
		$axis_named['total']++;
		if ($f[2] === 'listed') { $axis_named['listed']++; } else { $axis_named['unlisted']++; }
	}
	ra_row('N3 recomputed from the rows: 8 literals restricted to classes the list names',
		array($axis_named['total'], $axis_named['unlisted'], $axis_named['listed']), array(423, 360, 63));
	ra_row('N3: the restriction contains every listed 8-literal pair (63, not 0)',
		($axis_named['listed'] === $axis_all['listed'] and $axis_named['listed'] > 0), true);
	ra_row('N3: and the corrected figure is NOT the mislabelled one (448 would mean the pdf pairs came back)',
		($axis_named['total'] !== 448), true);
}

// ===========================================================================
ra_section('R32 — the class axis is GATED (api/ModuleInterface.class.php)');
// ===========================================================================
$mi_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/api/ModuleInterface.class.php';
$mi_src = is_file($mi_file) ? file_get_contents($mi_file) : '';
ra_row('ModuleInterface.class.php loads Relay_Allowlist',
	(strpos($mi_src, "LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist')") !== false), true);
// Every wrapper resolves the CONCRETE string it dispatches. The wrappers and
// the literal each of them fixes are enumerated here, so deleting one gate
// fails a named row.
$mi_wrappers = array(
	"ModuleAddMethod ( \$module, \$data )"           => 'add',
	"ModuleDeleteMethod ( \$module, \$id )"          => 'del',
	"ModuleGetRecordMethod ( \$module, \$id )"       => 'GetRecord',
	"ModuleGetRecordsMethod ( \$module, \$count"     => 'GetRecords',
	"ModuleModifyMethod ( \$module, \$data )"        => 'mod',
	"ModuleSupportPicklistMethod ( \$module"         => 'picklist',
	"EMRSupportPicklistMethod ( \$module"            => 'picklist',
	"ModuleRenderHtmlMethod( \$module, \$id )"       => 'RenderHtmlView',
	"ModuleToTextMethod ( \$module, \$id )"          => 'to_text',
);
$mi_gated = array();
$mi_ungated = array();
foreach ($mi_wrappers as $sig => $literal) {
	$body = ra_slice($mi_src, 'public function ' . $sig, '} // end method');
	if ($body === '' or strpos($body, "_allowlist_gate ( \$module, '" . $literal . "' )") === false) { $mi_ungated[] = $literal; }
	else { $mi_gated[] = $literal; }
}
ra_row('every fixed-literal module wrapper gates its concrete string', $mi_ungated, array());
ra_row('...all nine of them', count($mi_gated), 9);
ra_row('the print wrappers gate RenderToPDF too (PrintToFax/PrintToPrinter/PrintToBrowser)',
	count(array_filter(explode("_allowlist_gate ( ", $mi_src), function ($chunk) { return strpos(substr($chunk, 0, 40), "'RenderToPDF' )") !== false; })) > 0, true);
ra_row('the concrete string is built from the module namespace, not guessed',
	(strpos($mi_src, "'org.freemedsoftware.module.'") !== false), true);
ra_row('the gate uses the shared decision point (Relay_Allowlist::refuse), inner scope',
	(strpos($mi_src, 'Relay_Allowlist::refuse ( $concrete, NULL, true )') !== false), true);
ra_row('a refused wrapper answers INVALID_CALL (the same signal the relay returns)',
	(strpos($mi_src, "return 'INVALID_CALL';") !== false), true);
ra_row('the gate degrades when the class file is missing (M1), like the relay',
	(strpos($mi_src, "class_exists ( 'Relay_Allowlist' )") !== false), true);

// ===========================================================================
ra_section('N2 — the FormTemplate re-dispatcher is gated on BOTH axes');
// ===========================================================================
$ft_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/api/FormTemplate.class.php';
$ft_src = is_file($ft_file) ? file_get_contents($ft_file) : '';
ra_row('FormTemplate.class.php loads Relay_Allowlist',
	(strpos($ft_src, "LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist')") !== false), true);
$ft_pd = ra_slice($ft_src, 'function ProcessData ( $data )', '// end method ProcessData');
ra_row('ProcessData exists in the source', ($ft_pd !== ''), true);
ra_row('N2: the object: axis is gated on the concrete core.* string',
	(strpos($ft_pd, "'org.freemedsoftware.core.' . \$objectname . '.' . \$method") !== false), true);
ra_row('N2: the module: axis is gated on the concrete module.* string',
	(strpos($ft_pd, "'org.freemedsoftware.module.' . \$modulename . '.' . \$params[1]") !== false), true);
ra_row('N2: the link: axis is gated too (get_field / to_text)',
	((strpos($ft_pd, "'org.freemedsoftware.module.' . \$params[0] . '.get_field'") !== false)
		and (strpos($ft_pd, "'org.freemedsoftware.module.' . \$data['value'] . '.to_text'") !== false)), true);
ra_row('N2: it uses the SAME decision point (the outer scope, by design)',
	(strpos($ft_src, 'Relay_Allowlist::refuse ( $concrete )') !== false), true);
ra_row('N2: the gate degrades when the class file is missing (M1)',
	(strpos($ft_src, "class_exists ( 'Relay_Allowlist' )") !== false), true);

// ===========================================================================
ra_section('relay wiring (source level — the HTTP proof is in evidence/)');
// ===========================================================================
$relay_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/core/Relay.class.php';
$relay_src = is_file($relay_file) ? file_get_contents($relay_file) : '';
ra_row('Relay.class.php loads Relay_Allowlist',
	(strpos($relay_src, "LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist')") !== false), true);
// Fix round 1: the relay consults the SAME decision point a re-dispatcher must
// use (refuse()), and does not build its own copy of the rule. Fix round 2: the
// row matches the CALL TEXT, not the bare name, because the comment above the
// call also names refuse() -- a bare substring made the row pass on a tree whose
// relay had been unwired.
ra_row('Relay::handle_request consults the allowlist through refuse() only',
	(strpos($relay_src, 'Relay_Allowlist::refuse ( $method )') !== false
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
$pos_check = strpos($relay_src, 'Relay_Allowlist::refuse ( $method )');
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
ra_row('the raw 25/24 `core` guard is gone from Multicall (its POLICY is in never_allow now)',
	(strpos($multicall, "substr(\$v['method'], 0, 25)") === false), true);
// Fix round 2 (R36): round 1 labelled that row "the dead 25/24 `core` guard"
// and claimed the comparison could never match. The literal
// 'org.freemedsoftware.core.' is 25 characters, so the guard FIRED — it was
// live, not dead. The two rows below keep the correction committed: the false
// claim cannot come back into this file, and the premise is asserted directly.
ra_row('R36: the removed guard\'s literal IS 25 characters, so the comparison matched', strlen('org.freemedsoftware.core.'), 25);
ra_row('R36: the false "dead code" claim is not committed anywhere in this file',
	(strpos($ui_src, 'was dead code') === false), true);
ra_row('R36: the correction IS committed (the claim is named and refuted)',
	(strpos($ui_src, 'THAT CLAIM WAS FALSE') !== false), true);
ra_row('Multicall calls the shared gate with the INNER scope (so never-allow applies)',
	(strpos($multicall, 'Relay_Allowlist::refuse ( $inner_method, NULL, true )') !== false), true);

// ===========================================================================
ra_section('R32 fix round 3 — the PatientInterface re-dispatcher (MoveEmrAttachments)');
// ===========================================================================
// Fix round 2 gated all fifteen ModuleInterface dispatch sites but missed this
// one, which is the SAME shape: a FIXED literal ('additional_move') dispatched
// against a class that arrives from a database row the CALLER picks by id. The
// gate is the same mechanism, the same derived string and the same scope.
$pi_file = dirname(__FILE__) . '/../../lib/org/freemedsoftware/api/PatientInterface.class.php';
$pi_src = is_file($pi_file) ? file_get_contents($pi_file) : '';
ra_row('PatientInterface.class.php loads Relay_Allowlist',
	(strpos($pi_src, "LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist')") !== false), true);
$pi_mea = ra_slice($pi_src, 'public function MoveEmrAttachments', '// end method MoveEmrAttachments');
ra_row('MoveEmrAttachments exists in the source', ($pi_mea !== ''), true);
ra_row('R32: the additional_move dispatch is gated on its CONCRETE string',
	(strpos($pi_mea, "_allowlist_gate ( \$resolve['class'], 'additional_move' )") !== false), true);
// Ordering is the whole point: a gate AFTER the dispatch protects nothing, and
// a gate BEFORE the resolve would not have the class name to gate.
$pi_pos_gate = strpos($pi_mea, "_allowlist_gate ( \$resolve['class'], 'additional_move' )");
$pi_pos_mf   = strpos($pi_mea, 'module_function(');
$pi_pos_res  = strpos($pi_mea, "\$resolve = \$GLOBALS['sql']->queryRow");
ra_row('R32: resolve < gate < dispatch (order inside the attachments loop)',
	($pi_pos_res !== false and $pi_pos_gate !== false and $pi_pos_mf !== false
		and $pi_pos_res < $pi_pos_gate and $pi_pos_gate < $pi_pos_mf), true);
// Fix round 4: the gate must sit BEFORE EVERY WRITE. Placed after the two
// UPDATEs (fix round 3) a refusal under enforcement left the record already
// moved -- a partial application -- which is what the move corrects. The class
// the gate needs comes from the resolve query, which only READS, so nothing had
// to be written first.
$pi_pos_upd1 = strpos($pi_mea, '"UPDATE " . $table_q');
$pi_pos_upd2 = strpos($pi_mea, '"UPDATE annotations');
ra_row('R32 fix round 4: the gate CALL precedes the FIRST write (the module table UPDATE)',
	($pi_pos_gate !== false and $pi_pos_upd1 !== false and $pi_pos_gate < $pi_pos_upd1), true);
ra_row('R32 fix round 4: ...and the annotations UPDATE, so a refusal writes NOTHING',
	($pi_pos_gate !== false and $pi_pos_upd2 !== false and $pi_pos_gate < $pi_pos_upd2), true);
ra_row('R32: a refused additional_move skips this attachment entirely (nothing is written) and is reported through $success',
	(strpos(substr($pi_mea, $pi_pos_gate, 200), '$success = false;') !== false
		and strpos(substr($pi_mea, $pi_pos_gate, 200), 'continue;') !== false), true);
ra_row('R32: the gate uses the shared decision point (Relay_Allowlist::refuse), inner scope',
	(strpos($pi_src, 'Relay_Allowlist::refuse ( $concrete, NULL, true )') !== false), true);
ra_row('R32: the concrete string is built from the module namespace, not guessed',
	(strpos($pi_src, "'org.freemedsoftware.module.'") !== false), true);
ra_row('R32: the gate degrades when the class file is missing (M1), like the relay',
	(strpos($pi_src, "class_exists ( 'Relay_Allowlist' )") !== false), true);
// The class-wide property the round-2 search asserted and got wrong: this file
// has exactly ONE module_function() dispatch and it is gated. Deleting the gate
// fails this row; adding a second, ungated dispatch fails it too. The count is
// taken over TOKENS, so a mention of module_function() in a comment (this file
// has two, in the gate's own documentation) cannot make the numbers lie.
$pi_dispatches = 0;
$pi_gates      = 0;
$pi_prev       = NULL;
foreach (token_get_all($pi_src) as $t) {
	if (is_array($t)) {
		if ($t[0] === T_WHITESPACE or $t[0] === T_COMMENT or $t[0] === T_DOC_COMMENT) { continue; }
		if ($t[0] === T_STRING and strtolower($t[1]) === 'module_function') { $pi_dispatches++; }
		if ($t[0] === T_STRING and strtolower($t[1]) === '_allowlist_gate' and $pi_prev === T_OBJECT_OPERATOR) { $pi_gates++; }
		$pi_prev = $t[0];
	} else {
		if (trim($t) !== '') { $pi_prev = $t; }
	}
}
ra_row('R32: one module_function() CALL and one gate CALL in the class (comments excluded)',
	array($pi_dispatches, $pi_gates), array(1, 1));
// The reachability limit below the gate is a MEASUREMENT (fix round 3): in this
// tree the dispatch cannot be reached -- `$patient` is an undefined local (so
// the resolve query is `p.patient = NULL`) and freemed::module_get_meta()
// returns false for every registered module. The gate is defence in depth, and
// the source must keep saying so rather than implying the path is live.
ra_row('R32 fix round 3: the source records the measured reachability limit (both blockers named)',
	((strpos($pi_src, 'is NOT reachable') !== false)
		and (strpos($pi_src, 'module_get_meta') !== false)), true);

// ---------------------------------------------------------------------------
echo "\n";
if ($fail) { echo "{$fail} FAILED of {$rows_done} rows\n"; exit(1); }
echo "all passed ({$rows_done} rows)\n";
exit(0);
