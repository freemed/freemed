<?php
// tests/security/relay-callset-enum.php
//
// Task 2.8 — enumerate the relay call set for the deny-by-default allowlist.
//
//   php tests/security/relay-callset-enum.php > tests/security/evidence/relay-callset.txt
//
// The shipped relay (relay.php + Relay::handle_request) dispatches ANY method of
// ANY resolvable class, with attacker-controlled arguments. The control being
// added is an allowlist of relay method strings. To build it we need the real
// call set, and the honest problem is that this checkout cannot produce the
// best one: there is no compiled GWT client here (`find ui/gwt -name '*.js'`
// returns only vendored viewerjs), so the compiled-JS call set and the
// click-through smoke pass the brief asks for do not exist in this environment.
//
// What DOES exist, and what this script collects:
//
//   A. gwtphpmap — lib/org/freemedsoftware/gwt/**/*.gwtphpmap.inc.php. Each file
//      declares `mappedBy` (the PHP relay namespace the GWT service maps to) and
//      one entry per method with `mappedName`/`mappedBy` (the PHP method name).
//      This is the authoritative, machine-readable client->relay mapping, and it
//      is the strongest evidence available here: it is the *generator input* for
//      the GWT RPC proxies, not a comment. Source label: `gwtphpmap`.
//
//   B. source-mine — the brief's grep over the client trees, but RESOLVED rather
//      than taken raw. The raw grep returns ~375 distinct
//      `org.freemedsoftware.*` strings; most are Java class references
//      (`org.freemedsoftware.gwt.client.Api.*`), javadoc mentions, or
//      class-only prefixes, and are NOT relay method strings. Every raw string
//      is therefore resolved to `<namespace>.<Class>.<Method>` and kept only
//      when the class file exists AND the method exists AND is public. Trees:
//      ui/gwt/src/main/java, ui/gwt/*.js, js, ui/dojo, data/emrview,
//      data/**/*.tpl and the root request scripts. Source label: `source-mine`.
//
//   C. live-probe — the relay methods this mitigation has already exercised
//      against the running server, which is the only "smoke pass" this
//      environment can produce (the method sets of tests/security/
//      repro-relay-sqli.sh and of the batch-B / batch-C / 2.6f probe scripts
//      recorded in the SDD reports under tests/security/evidence/ and
//      .superpowers/sdd/). Source label: `live-probe`.
//
//   D. public-namespace — relay.php's only validation is the provider name and
//      Relay::handle_request skips the login check for
//      `org.freemedsoftware.public.*`, but the allowlist check runs for EVERY
//      method. The public call sites the client actually uses are enumerated
//      from ui/gwt/src/main/java and the templates; the remaining public
//      methods of those same classes are enumerated too, because a listed
//      public method that the installer/login flow needs and that is missing
//      would break the flow itself. Source label: `public-namespace`.
//
//   E. access-log — the harness's Apache access log, which DOES retain relay calls
//      (the method is in the request line). Ruling R26.2(c) asks for this, and it
//      is a genuinely independent source: it is the only one that can name a call
//      the source trees and the curated live-probe list both missed. It is still
//      probe traffic, not a client session — see
//      tests/security/evidence/relay-accesslog.txt, which records how it was
//      captured. Source label: `access-log`.
//      Passed as an optional third argument (or RELAY_ACCESSLOG); the generator
//      reads a capture file, never the container, so the artifact stays
//      reproducible from committed inputs.
//
// Regenerate (the third argument is the access-log capture, and is part of the
// command because it contributes rows):
//
//   php tests/security/relay-callset-enum.php <head-sha> <branch> \
//       tests/security/evidence/relay-accesslog.txt > tests/security/evidence/relay-callset.txt
//
// Output (stdout) is one row per relay method string:
//
//   <method>\t<source>\t<evidence>
//
// and it is committed as tests/security/evidence/relay-callset.txt. The data
// file data/config/relay-allowlist.php is seeded from it; every pattern in that
// file must appear here.
//
// Exit status: 0 when the enumeration is non-empty and every live-probe method
// resolved (a live-probe method that does not resolve would mean the resolver
// disagrees with the server that dispatched it — a bug in this script, not in
// the server). 1 otherwise.

$ROOT = dirname(dirname(dirname(__FILE__)));
$LIB = $ROOT . '/lib';
$FS = $ROOT . '/lib/org/freemedsoftware';

// The REAL matcher, so the class-axis classification below cannot drift from
// what the relay itself decides. Relay_Allowlist.class.php is self-contained
// (no file-scope dependencies), so a plain require is enough here.
require_once $FS . '/core/Relay_Allowlist.class.php';

$rows = array();   // strtolower(method) => array( 'method' => display, 'source' => ..., 'evidence' => array(...) )
$SOURCE_RANK = array('live-probe' => 3, 'access-log' => 3, 'gwtphpmap' => 2, 'public-namespace' => 2, 'source-mine' => 1);

function record ( $method, $source, $evidence ) {
    global $rows, $SOURCE_RANK;
    $method = trim($method);
    if ($method === '' or strpos($method, '.') === false) { return; }
    $k = strtolower($method);
    if (!isset($rows[$k])) {
        $rows[$k] = array('method' => $method, 'source' => $source, 'evidence' => array());
    }
    if ($SOURCE_RANK[$source] > $SOURCE_RANK[$rows[$k]['source']]) {
        $rows[$k]['source'] = $source;
    }
    if (!in_array($evidence, $rows[$k]['evidence'], true)) {
        $rows[$k]['evidence'][] = $evidence;
    }
} // end function record

// ---------------------------------------------------------------------------
// class/method resolution, mirroring lib/loader.php ResolveObjectPath()'s
// namespace rules for the non-module namespaces. A method counts as
// relay-callable when the class file exists and declares the method public
// (PHP's implicit visibility is public), which is exactly what CallMethod()
// requires of it.
// ---------------------------------------------------------------------------

function class_file_for ( $class_ns ) {
    global $LIB, $FS;
    static $cache = array();
    if (isset($cache[$class_ns])) { return $cache[$class_ns]; }
    $prefix = 'org.freemedsoftware.';
    if (strpos($class_ns, $prefix) !== 0) { return $cache[$class_ns] = NULL; }
    $rest = substr($class_ns, strlen($prefix));
    $seg = explode('.', $rest);
    if (count($seg) < 2) { return $cache[$class_ns] = NULL; }
    $class = array_pop($seg);
    if ($seg[0] === 'module') {
        // lib/org/freemedsoftware/module/<Class>.class.php — the module tree is
        // flat (ResolveObjectPath resolves the real path through the DB-backed
        // module registry; the file name is what we can check here).
        $cands = array($FS . '/module/' . $class . '.class.php');
    } else {
        $dir = $FS . '/' . implode('/', $seg);
        $cands = array($dir . '/' . $class . '.class.php', $dir . '/class.' . $class . '.php', $dir . '/' . $class . '.php');
    }
    foreach ($cands as $c) { if (is_file($c)) { return $cache[$class_ns] = $c; } }
    return $cache[$class_ns] = NULL;
} // end function class_file_for

function class_index () {
    global $FS;
    static $idx = NULL;
    if ($idx !== NULL) { return $idx; }
    $idx = array();
    $skip = array('/gwt/', '/acl/', '/net/');
    $ri = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($FS, FilesystemIterator::SKIP_DOTS));
    foreach ($ri as $f) {
        if (!$f->isFile()) { continue; }
        $p = $f->getPathname();
        $skip_this = false;
        foreach ($skip as $s) { if (strpos($p, $s) !== false) { $skip_this = true; break; } }
        if ($skip_this) { continue; }
        if (!preg_match('/\.(class\.php|php)$/', $p)) { continue; }
        $src = @file_get_contents($p);
        if ($src === false) { continue; }
        if (preg_match('/(?:^|\n)[ \t]*class[ \t]+([A-Za-z0-9_]+)(?:[ \t]+extends[ \t]+([A-Za-z0-9_]+))?/', $src, $m)) {
            $k = strtolower($m[1]);
            if (!isset($idx[$k])) {
                $idx[$k] = array('file' => $p, 'parent' => isset($m[2]) ? strtolower($m[2]) : NULL);
            }
        }
    }
    return $idx;
} // end function class_index

// Public methods of a class namespace, INCLUDING inherited ones, because that is
// what CallMethod() sees: `module.Allergies.picklist` is resolved by PHP on
// Allergies' parent EMRModule, and a resolver that only read
// Allergies.class.php would wrongly report every inherited relay method as
// unresolvable. Walks `extends` to a bounded depth.
function public_methods_of ( $class_ns ) {
    static $cache = array();
    if (isset($cache[$class_ns])) { return $cache[$class_ns]; }
    $out = array();
    $seen = array();
    $cur_file = class_file_for($class_ns);
    $depth = 0;
    while ($cur_file !== NULL and $depth < 12) {
        $depth++;
        $src = @file_get_contents($cur_file);
        if ($src === false) { break; }
        if (preg_match('/[ \t]*class[ \t]+([A-Za-z0-9_]+)(?:[ \t]+extends[ \t]+([A-Za-z0-9_]+))?/', $src, $cm)) {
            $parent = isset($cm[2]) ? strtolower($cm[2]) : NULL;
        } else {
            $parent = NULL;
        }
        foreach (public_methods_in_file($cur_file) as $m) { $out[strtolower($m)] = $m; }
        if ($parent === NULL) { break; }
        if (isset($seen[$parent])) { break; }
        $seen[$parent] = true;
        $idx = class_index();
        if (!isset($idx[$parent])) { break; }
        $cur_file = $idx[$parent]['file'];
    }
    return $cache[$class_ns] = array_values($out);
} // end function public_methods_of

function public_methods_in_file ( $file ) {
    static $cache = array();
    if (isset($cache[$file])) { return $cache[$file]; }
    $src = @file_get_contents($file);
    if ($src === false) { return $cache[$file] = array(); }
    $out = array();
    if (preg_match_all('/(?:^|\n)[ \t]*((?:(?:public|protected|private|static|final|abstract)[ \t]+)*)function[ \t]+([A-Za-z0-9_]+)[ \t]*\(/', $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $d) {
            $mods = strtolower($d[1]);
            $name = $d[2];
            if (strpos($name, '__') === 0) { continue; }
            if (strpos($mods, 'private') !== false or strpos($mods, 'protected') !== false) { continue; }
            $out[$name] = true;
        }
    }
    return $cache[$file] = array_keys($out);
} // end function public_methods_in_file

// Does `<class_ns>.<method>` name a public method of a resolvable class?
// Method names are compared case-insensitively because PHP dispatches them that
// way; the returned string is the class's own spelling when one matches.
function resolves_method ( $class_ns, $method ) {
    $have = public_methods_of($class_ns);
    if (!$have) { return false; }
    foreach ($have as $m) { if (strcasecmp($m, $method) === 0) { return $m; } }
    return false;
} // end function resolves_method

// Split a candidate `org.freemedsoftware.<pkg...>.<Class>.<Method>` string at
// its last dot and check it; if that fails, walk the split point left, because a
// javadoc-style mention can carry extra trailing segments. Returns the
// canonical method string or NULL.
function resolve_candidate ( $s ) {
    global $FS;
    $seg = explode('.', $s);
    if (count($seg) < 4 or $seg[0] !== 'org' or $seg[1] !== 'freemedsoftware') { return NULL; }
    // The GWT Java client's own packages are never relay namespaces.
    if ($seg[2] === 'gwt' or $seg[2] === 'controller' or $seg[2] === 'hook') { return NULL; }
    for ($k = count($seg) - 1; $k >= 3; $k--) {
        $class_ns = implode('.', array_slice($seg, 0, $k));
        $method = $seg[$k];
        $real = resolves_method($class_ns, $method);
        if ($real !== false) { return $class_ns . '.' . $real; }
    }
    return NULL;
} // end function resolve_candidate

// ---------------------------------------------------------------------------
// A. gwtphpmap — the authoritative client -> relay mapping.
// ---------------------------------------------------------------------------
$maps = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($FS . '/gwt', FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if (substr($f->getFilename(), -18) === '.gwtphpmap.inc.php') { $maps[] = $f->getPathname(); }
}
sort($maps);
// The map files are PHP that reference helper classes when executed, so they are
// PARSED, not included: a service block is
//   array ( 'className' => '<gwt java iface>', 'mappedBy' => '<php namespace>',
//           'methods' => array ( array ( 'name' => 'X'
//                                       , 'mappedName' => 'X' ... ) ... ) )
// and a handful of blocks spell the relay method name with `mappedBy` instead of
// `mappedName` (Api/TableMaintenance does). Both spellings are read.
foreach ($maps as $map_file) {
    $src = @file_get_contents($map_file);
    if ($src === false) { continue; }
    $rel_raw = str_replace($ROOT . '/', '', $map_file);
    $head = $src; $tail = '';
    if (($p = strpos($src, "'methods'")) !== false) { $head = substr($src, 0, $p); $tail = substr($src, $p); }
    if (!preg_match("/'mappedBy'\\s*=>\\s*'([^']*\\.[^']*)'/", $head, $hm)) { continue; }
    $class_ns = $hm[1];
    $names = array();
    if (preg_match_all("/'mappedName'\\s*=>\\s*'([^']+)'/", $tail, $nm)) {
        foreach ($nm[1] as $n) { $names[$n] = $n; }
    }
    if (preg_match_all("/'mappedBy'\\s*=>\\s*'([^'.]+)'/", $tail, $bm)) {
        foreach ($bm[1] as $n) { $names[$n] = $n; }
    }
    if (!preg_match("/'className'\\s*=>\\s*'([^']+)'/", $head, $cn)) { $cn = array('', ''); }
    $java_class = isset($cn[1]) ? $cn[1] : '';
    $note = '';
    if ($java_class !== '' and strcasecmp(basename($map_file, '.gwtphpmap.inc.php'), basename(str_replace('.', '/', $java_class))) !== 0) {
        $note .= ' (NOTE: the file is named ' . basename($map_file, '.gwtphpmap.inc.php')
            . ' but its className is ' . $java_class . ' — an inconsistency in the shipped map)';
    }
    foreach ($names as $name) {
        $full = $class_ns . '.' . $name;
        $real = resolves_method($class_ns, $name);
        record($full, 'gwtphpmap', $rel_raw . ' mappedBy=' . $class_ns . ' mappedName=' . $name
            . $note
            . ($real === false ? ' (NOTE: no matching public method on the class file or its parents)' : ''));
    }
}

// ---------------------------------------------------------------------------
// B. source-mine — the brief's grep, resolved.
// ---------------------------------------------------------------------------
$trees = array(
    'ui/gwt/src/main/java',
    'ui/dojo',
    'data/emrview',
    'js',
);
$globs = array('ui/gwt/*.js', 'data/*.tpl', 'data/**/*.tpl', '*.php');
$files = array();
foreach ($trees as $t) {
    $p = $ROOT . '/' . $t;
    if (is_dir($p)) {
        $ri = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS));
        foreach ($ri as $f) { if ($f->isFile()) { $files[] = $f->getPathname(); } }
    }
}
foreach (array('ui/gwt/*.js', 'data/emrview/*.tpl', 'data/report/*.tpl', '*.php') as $g) {
    foreach (glob($ROOT . '/' . $g) as $f) { $files[] = $f; }
}
$files = array_values(array_unique($files));
sort($files);

$raw_seen = array();      // every distinct raw string the grep shape produces
$raw_kept = 0;
foreach ($files as $f) {
    $rel = str_replace($ROOT . '/', '', $f);
    $src = @file_get_contents($f);
    if ($src === false) { continue; }
    if (!preg_match_all('/org\.freemedsoftware\.[A-Za-z0-9_.]+/', $src, $m)) { continue; }
    foreach (array_unique($m[0]) as $cand) {
        $raw_seen[strtolower($cand)] = $cand;
        $resolved = resolve_candidate($cand);
        if ($resolved !== NULL) {
            record($resolved, 'source-mine', $rel . ' literal "' . $cand . '"');
            $raw_kept++;
        }
    }
}

// ---------------------------------------------------------------------------
// C. live-probe — the method sets this mitigation has actually exercised.
//    Kept as literals with the artifact each came from; this is evidence, not
//    inference, so it is written down rather than re-derived.
// ---------------------------------------------------------------------------
$live = array(
    // tests/security/repro-relay-sqli.sh (the 2.2-2.5 regression script)
    'org.freemedsoftware.public.Login.LoggedIn'                          => 'tests/security/repro-relay-sqli.sh M_LOGGEDIN',
    'org.freemedsoftware.api.UserInterface.GetUsers'                     => 'tests/security/repro-relay-sqli.sh M_GETUSERS',
    'org.freemedsoftware.api.ModuleSearch.picklist'                      => 'tests/security/repro-relay-sqli.sh M_PICKLIST',
    'org.freemedsoftware.module.EncounterNotesTemplate.getTemplates'     => 'tests/security/repro-relay-sqli.sh M_TEMPLATES',
    'org.freemedsoftware.api.Remitt.RenderStatementXML'                  => 'tests/security/repro-relay-sqli.sh M_REMITT',
    // batch B: 39 probes over 24 module methods (SDD sweep-batch-B-report.md)
    'org.freemedsoftware.module.i18nLanguages.GetAll'                    => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.UserPreferences.GetAll'                  => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.UserPreferences.GetConfigSections'       => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.FacilityModule.GetAll'                   => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.UnfiledDocuments.GetAll'                 => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.UnfiledDocuments.GetCount'               => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.UnreadDocuments.GetAll'                  => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.Callin.GetAll'                           => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.ClinicRegistration.GetAll'               => 'sweep-batch-B-report.md #no-parameter builders',
    'org.freemedsoftware.module.Zipcodes.CityStateZipPicklist'           => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.PhoneNumbers.GetTypeNumber'              => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.PhoneNumbers.GetRecentNumber'            => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.ProgressNotes.NoteForDate'               => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.PaymentModule.getLastRecord'             => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.ScannedDocuments.GetPatientAllRecords'   => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.Referrals.GetAllActiveByPatient'         => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.ModuleFieldCheckerType.getModuleInfo'    => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.ProviderModule.internalPicklist'         => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.Callin.GetDetailedRecord'                => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.Callin.GetDetailedRecordWithIntake'      => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.Authorizations.getActionItems'           => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.ProcedureModule.getPatientProcHistory'   => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.SuperBill.GetSuperbill'                  => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.CalendarGroup.GetDetailedRecord'         => 'sweep-batch-B-report.md #parameterised builders',
    'org.freemedsoftware.module.EpisodeOfCare.getAllValues'              => 'sweep-batch-B-report.md #parameterised builders',
    // batch C: 31 probes over 12 methods (SDD sweep-batch-C-report.md)
    'org.freemedsoftware.api.PatientInterface.picklist'                  => 'sweep-batch-C-report.md #api/PatientInterface::picklist',
    'org.freemedsoftware.api.Scheduler.GetDailyAppointmentsRange'        => 'sweep-batch-C-report.md #api/Scheduler',
    'org.freemedsoftware.api.Ledger.WriteoffItems'                       => 'sweep-batch-C-report.md #api/Ledger',
    'org.freemedsoftware.api.Ledger.queue_for_rebill'                    => 'sweep-batch-C-report.md #api/Ledger',
    'org.freemedsoftware.module.i18nLanguages.GetRecord'                 => 'sweep-batch-C-report.md #module/SupportModule',
    'org.freemedsoftware.module.i18nLanguages.GetRecords'                => 'sweep-batch-C-report.md #module/SupportModule',
    'org.freemedsoftware.module.UserGroups.GetRecords'                   => 'sweep-batch-C-report.md #module/SupportModule',
    'org.freemedsoftware.module.Allergies.picklist'                      => 'sweep-batch-C-report.md #module/EMRModule',
    'org.freemedsoftware.module.Allergies.GetRecentRecord'               => 'sweep-batch-C-report.md #module/EMRModule',
    'org.freemedsoftware.module.Vitals.GetRecentRecord'                  => 'sweep-batch-C-report.md #module/EMRModule',
    // Task 2.6f live probe set (harness relay-2.6f.sh; SDD task-2.6f-report.md)
    'org.freemedsoftware.module.EncounterNotesTemplate.GetList'          => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.module.Allergies.GetList'                       => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.api.Ledger.collection_warning'                  => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.api.Scheduler.date_add'                         => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.api.Scheduler.GetDailyAppointmentScheduler'     => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.api.Scheduler.FindDateAppointments'             => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.api.Scheduler.FindGroupAppointments'            => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.api.Scheduler.FindGroupAppointmentsDates'       => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.module.SuperBill.GetForDates'                   => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.module.PatientCoverages.GetCoverages'           => 'task-2.6f-report.md harness relay-2.6f.sh',
    'org.freemedsoftware.module.WorkflowStatus.StatusMapForDate'         => 'task-2.6f-report.md harness relay-2.6f.sh',
);
// Live-probe strings that provably are NOT relay-callable. They are kept here
// (rather than quietly dropped) because the server was measured calling them and
// the reason they do not resolve is itself evidence: the resolver in this script
// says exactly what the server did at dispatch.
$live_known_missing = array(
    'org.freemedsoftware.module.EncounterNotesTemplate.GetList'
        => 'EncounterNotesTemplate extends SupportModule; GetList is declared by EMRModule, a sibling branch, so this string names no method of the class or its parents. Measured live: relay dispatch fails with "Uncaught TypeError: call_user_func_array(): Argument #1 ($callback)..." for BOTH the benign and the hostile call — a pre-existing dispatch failure, see task-2.6f-report.md.',
);
$live_unresolved = array();
$live_not_callable = array();
foreach ($live as $method => $evidence) {
    $pos = strrpos($method, '.');
    $class_ns = substr($method, 0, $pos);
    $name = substr($method, $pos + 1);
    $real = resolves_method($class_ns, $name);
    if ($real === false) {
        if (isset($live_known_missing[$method])) { $live_not_callable[$method] = $live_known_missing[$method]; continue; }
        $live_unresolved[] = $method;
    }
    record($method, 'live-probe', $evidence . ' (probe: ' . $method . ')');
}

// ---------------------------------------------------------------------------
// D. public-namespace — relay.php's validation and the login bypass.
// ---------------------------------------------------------------------------
$public_classes = array();
foreach (glob($FS . '/public/*.class.php') as $f) {
    $public_classes[] = 'org.freemedsoftware.public.' . basename($f, '.class.php');
}
sort($public_classes);

// literal call sites anywhere in the client/UI trees
$public_callsites = array();   // method(lower) => 'file:line'
$all_files = $files;
$all_files[] = $ROOT . '/relay.php';
$all_files[] = $ROOT . '/help.php';
$all_files[] = $ROOT . '/controller.php';
$all_files = array_values(array_unique($all_files));
foreach ($all_files as $f) {
    if (!is_file($f)) { continue; }
    $rel = str_replace($ROOT . '/', '', $f);
    foreach (file($f) as $i => $line) {
        if (!preg_match_all('/org\.freemedsoftware\.public\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+/', $line, $mm)) { continue; }
        foreach ($mm[0] as $cand) {
            $pos = strrpos($cand, '.');
            $class_ns = substr($cand, 0, $pos);
            $name = substr($cand, $pos + 1);
            $real = resolves_method($class_ns, $name);
            if ($real === false) { continue; }
            $canon = $class_ns . '.' . $real;
            $k = strtolower($canon);
            if (!isset($public_callsites[$k])) { $public_callsites[$k] = array($rel . ':' . ($i + 1), $canon); }
        }
    }
}
foreach ($public_classes as $class_ns) {
    $file = class_file_for($class_ns);
    foreach (public_methods_of($class_ns) as $name) {
        $canon = $class_ns . '.' . $name;
        $k = strtolower($canon);
        if (isset($public_callsites[$k])) {
            record($canon, 'public-namespace', 'client call site ' . $public_callsites[$k][0]);
        } else {
            record($canon, 'public-namespace', str_replace($ROOT . '/', '', $file) . ' public method ' . $name
                . ' (no literal client call site found; listed because the namespace bypasses the login guard)');
        }
    }
}

// ---------------------------------------------------------------------------
// E. access-log — the harness's access log as a source (ruling R26.2(c)).
// ---------------------------------------------------------------------------
$accesslog_file = isset($argv[3]) ? $argv[3] : (getenv('RELAY_ACCESSLOG') ?: NULL);
$accesslog_calls = array();      // method => count
$accesslog_not_callable = array();
if ($accesslog_file !== NULL and is_file($accesslog_file)) {
    foreach (file($accesslog_file) as $line) {
        // capture format: "<count>\t<provider>\t<method as received>"  (see the
        // evidence file); tolerate a raw access-log line too.
        $method = NULL; $count = 1;
        if (preg_match('/^\s*(\d+)\t[a-z]+\t(.+?)\s*$/', $line, $m)) {
            $count = (int) $m[1]; $method = $m[2];
        } elseif (preg_match('#POST /relay\.php/[a-z]+/([A-Za-z0-9_.]+)#', $line, $m)) {
            $method = $m[1];
        }
        if ($method === NULL or $method === '') { continue; }
        $accesslog_calls[$method] = (isset($accesslog_calls[$method]) ? $accesslog_calls[$method] : 0) + $count;
        $resolved = resolve_candidate($method);
        if ($resolved !== NULL) {
            record($resolved, 'access-log', basename($accesslog_file) . ': ' . $accesslog_calls[$method]
                . ' request(s) as "' . $method . '"');
        } elseif (!isset($accesslog_not_callable[$method])) {
            $accesslog_not_callable[$method] = 'names no public method of that class or its parents';
        }
    }
}

// ---------------------------------------------------------------------------
// F. class axis (fix round 2, R32/R36) — the concrete strings the fixed-literal
//    wrappers in lib/org/freemedsoftware/api/ModuleInterface.class.php can
//    dispatch against a CALLER-CHOSEN module class.
//
//    The wrappers call module_function($module, '<literal>') with the literal
//    fixed in the source ('add', 'del', 'GetRecord', 'GetRecords', 'mod',
//    'picklist', 'RenderHtmlView', 'to_text') and the print wrappers call it
//    with 'RenderToPDF'. module_function() resolves $module to
//    lib/<...>/module/<Class>.class.php and instantiates it, so the dispatch IS
//    the relay method string `org.freemedsoftware.module.<Class>.<literal>` —
//    which is what the allowlist has to be able to name. A per-method list that
//    names the WRAPPER cannot constrain the class.
//
//    The pairs are enumerated over every module class file, with inheritance
//    walked (a literal counts for a class when the class OR AN ANCESTOR
//    declares it public — that is what call_user_func reaches), and they are
//    classified against the SHIPPED data file with the real matcher. Definition
//    matters, so the four counts are reported separately below.
//
//    These strings are EVIDENCE OF EXPOSURE, not a seed: the shipped list does
//    NOT contain them (that is the point of R32 — the gate refuses what the
//    enumeration never evidenced). They are emitted as '#class-axis' lines so
//    that the shipped-list drift check (which reads the tab-delimited rows)
//    keeps comparing the list against the tiers it was actually seeded from.
// ---------------------------------------------------------------------------
$CLASS_AXIS_LITERALS = array('add', 'del', 'GetRecord', 'GetRecords', 'mod', 'picklist', 'RenderHtmlView', 'to_text');
$CLASS_AXIS_LITERAL_PDF = 'RenderToPDF';

$shipped_data_file = $ROOT . '/data/config/relay-allowlist.php';
$shipped_data = is_file($shipped_data_file) ? include $shipped_data_file : NULL;
$class_axis_config = array(
    'enforce' => false,
    'patterns' => (is_array($shipped_data) && isset($shipped_data['patterns']) && is_array($shipped_data['patterns']))
        ? $shipped_data['patterns'] : array(),
);

$AXIS = array(                     // restriction => counts
    'all'      => array('total' => 0, 'unlisted' => 0, 'listed' => 0, 'pdf_total' => 0, 'pdf_unlisted' => 0, 'pdf_listed' => 0),
    'named'    => array('total' => 0, 'unlisted' => 0, 'listed' => 0),
    'own_file' => array('total' => 0, 'unlisted' => 0, 'listed' => 0),
);
$axis_rows = array();              // concrete => array('listed' => bool, 'evidence' => string)
$axis_named_classes = array();
foreach ($class_axis_config['patterns'] as $p) {
    if (preg_match('/^org\.freemedsoftware\.module\.([A-Za-z0-9_]+)\./i', $p, $m)) {
        $axis_named_classes[strtolower($m[1])] = true;
    }
}
$axis_classes = array();
foreach (glob($FS . '/module/*.class.php') as $f) { $axis_classes[basename($f, '.class.php')] = $f; }
ksort($axis_classes);
foreach ($axis_classes as $class => $file) {
    $ns = 'org.freemedsoftware.module.' . $class;
    $deep = public_methods_of($ns);           // inherited included: what call_user_func reaches
    $own  = public_methods_in_file($file);    // declared in this file only
    $deep_lc = array(); foreach ($deep as $m) { $deep_lc[strtolower($m)] = $m; }
    $own_lc  = array(); foreach ($own as $m)  { $own_lc[strtolower($m)]  = $m; }
    foreach (array_merge($CLASS_AXIS_LITERALS, array($CLASS_AXIS_LITERAL_PDF)) as $literal) {
        if (!isset($deep_lc[strtolower($literal)])) { continue; }
        $concrete = $ns . '.' . $literal;
        $listed = Relay_Allowlist::allowed($concrete, $class_axis_config);
        $is_pdf = ($literal === $CLASS_AXIS_LITERAL_PDF);
        $axis_rows[$concrete] = array(
            'listed' => $listed,
            'evidence' => 'ModuleInterface wrapper: ' . $class . ' + literal \'' . $literal . '\'',
        );
        // the headline (8 module_function literals) ...
        if (!$is_pdf) {
            $AXIS['all']['total']++;
            if ($listed) { $AXIS['all']['listed']++; } else { $AXIS['all']['unlisted']++; }
        }
        // ... and the print wrappers' literal, kept separately so the two
        // definitions cannot be confused for one another.
        if ($is_pdf) {
            $AXIS['all']['pdf_total']++;
            if ($listed) { $AXIS['all']['pdf_listed']++; } else { $AXIS['all']['pdf_unlisted']++; }
        }
        if (isset($axis_named_classes[strtolower($class)])) {
            $AXIS['named']['total']++;
            if ($listed) { $AXIS['named']['listed']++; } else { $AXIS['named']['unlisted']++; }
        }
        if (isset($own_lc[strtolower($literal)])) {
            $AXIS['own_file']['total']++;
            if ($listed) { $AXIS['own_file']['listed']++; } else { $AXIS['own_file']['unlisted']++; }
        }
    }
}

// Provenance: the commit this enumeration was generated at. `git` is not
// necessarily present in the php:8.3-cli image, so the caller passes it in
// (or it is read from the environment); this is evidence, so it is never
// guessed.
$sha = isset($argv[1]) ? $argv[1] : (getenv('RELAY_ENUM_SHA') ?: NULL);
$branch = isset($argv[2]) ? $argv[2] : (getenv('RELAY_ENUM_BRANCH') ?: NULL);
if ($sha === NULL) { $sha = trim((string) @shell_exec('git -C ' . escapeshellarg($ROOT) . ' rev-parse HEAD 2>/dev/null')); }
if ($branch === NULL) { $branch = trim((string) @shell_exec('git -C ' . escapeshellarg($ROOT) . ' rev-parse --abbrev-ref HEAD 2>/dev/null')); }

echo "# tests/security/evidence/relay-callset.txt\n";
echo "# Task 2.8 relay call-set enumeration — generated by tests/security/relay-callset-enum.php.\n";
echo "#\n";
echo "# Regenerate: php tests/security/relay-callset-enum.php <head-sha> <branch> \\\n";
echo "#               tests/security/evidence/relay-accesslog.txt \\\n";
echo "#               > tests/security/evidence/relay-callset.txt\n";
echo "# Generated at commit: " . ($sha !== '' ? $sha : '(not a git checkout)') . "\n";
echo "# Branch: " . ($branch !== '' ? $branch : '(unknown)') . "\n";
echo "#\n";
echo "# Columns: <relay method string>\\t<source>\\t<evidence>\n";
echo "#   source = live-probe | access-log | gwtphpmap | public-namespace | source-mine\n";
echo "#   (rank, when a method is reachable from several sources: live-probe =\n";
echo "#    access-log > gwtphpmap = public-namespace > source-mine; the weaker\n";
echo "#    evidences are kept in the third column.)\n";
echo "#\n";
echo "# CLASS AXIS (fix round 2, R32) - the fixed-literal wrappers in\n";
echo "# lib/org/freemedsoftware/api/ModuleInterface.class.php dispatch\n";
echo "#   org.freemedsoftware.module.<Class>.<literal>\n";
echo "# against a CALLER-CHOSEN class, so listing the WRAPPER method cannot constrain\n";
echo "# the class. Measured over every lib/<...>/module/*.class.php module class file,\n";
echo "# inheritance walked (a literal counts when the class OR AN ANCESTOR declares it\n";
echo "# public - what call_user_func reaches), classified against the SHIPPED list with\n";
echo "# the real matcher (Relay_Allowlist::allowed):\n";
echo "#   8 literals (add, del, GetRecord, GetRecords, mod, picklist, RenderHtmlView, to_text),\n";
echo "#     all 133 module class files          : " . $AXIS['all']['total'] . " pairs, "
    . $AXIS['all']['unlisted'] . " NOT in the shipped list, " . $AXIS['all']['listed'] . " in it\n";
echo "#   +RenderToPDF (PrintToFax/PrintToPrinter/PrintToBrowser): " . $AXIS['all']['pdf_total']
    . " pairs, " . $AXIS['all']['pdf_unlisted'] . " NOT in the shipped list, " . $AXIS['all']['pdf_listed'] . " in it\n";
echo "#   the 8 literals, restricted to classes the shipped list names : " . $AXIS['named']['total']
    . " pairs, " . $AXIS['named']['unlisted'] . " NOT listed, " . $AXIS['named']['listed'] . " listed\n";
echo "#   the 8 literals, declared in the module file ITSELF           : " . $AXIS['own_file']['total']
    . " pairs, " . $AXIS['own_file']['unlisted'] . " NOT listed, " . $AXIS['own_file']['listed'] . " listed\n";
echo "#\n";
echo "# These strings are EVIDENCE OF EXPOSURE, NOT a seed. The shipped list does NOT\n";
echo "# contain them - that is the point of R32: the gate refuses what the enumeration\n";
echo "# never evidenced, and the log-only stage is what measures which ones a site\n";
echo "# actually needs. Every pair is emitted below as a '#class-axis' line, so the\n";
echo "# shipped-list drift check (which reads the tab-delimited rows) keeps comparing\n";
echo "# the list against the tiers it was actually seeded from.\n";
echo "#\n";
echo "# WHAT THIS IS NOT: there is no compiled GWT client in this checkout, so the\n";
echo "# compiled-JS call set and the click-through UI smoke pass could not be\n";
echo "# produced. This is the best available enumeration, and it is why the shipped\n";
echo "# default is log-only (enforce = false). See data/config/relay-allowlist.php\n";
echo "# for the enable procedure and doc/RELAY_ALLOWLIST for the operator notes.\n";
echo "#\n";

$by_source = array();
foreach ($rows as $r) { $by_source[$r['source']] = (isset($by_source[$r['source']]) ? $by_source[$r['source']] : 0) + 1; }

$raw_total = count($raw_seen);

echo "# Raw `org.freemedsoftware.*` strings seen by the brief's grep shape in the\n";
echo "# mined trees (before resolution): " . $raw_total . "\n";
echo "# Canonical relay method strings after resolution: " . count($rows) . "\n";
echo "# By source (ranked label): live-probe=" . (isset($by_source['live-probe']) ? $by_source['live-probe'] : 0)
    . " access-log=" . (isset($by_source['access-log']) ? $by_source['access-log'] : 0)
    . " gwtphpmap=" . (isset($by_source['gwtphpmap']) ? $by_source['gwtphpmap'] : 0)
    . " public-namespace=" . (isset($by_source['public-namespace']) ? $by_source['public-namespace'] : 0)
    . " source-mine=" . (isset($by_source['source-mine']) ? $by_source['source-mine'] : 0) . "\n";
echo "#\n";
echo "# Access-log capture used: " . ($accesslog_file !== NULL ? basename($accesslog_file) : '(none supplied)')
    . ($accesslog_calls ? ' — ' . count($accesslog_calls) . ' distinct methods in ' . array_sum($accesslog_calls) . ' requests' : '') . "\n";
echo "#\n";
echo "# Access-log methods that are NOT relay-callable (recorded, and deliberately\n";
echo "# not in the allowlist):\n";
if (count($accesslog_not_callable)) {
    foreach ($accesslog_not_callable as $m => $why) { echo "#   " . $m . " — " . $why . "\n"; }
} else {
    echo "#   none\n";
}
echo "#\n";
echo "# Live-probe methods that did NOT resolve to a public method: "
    . (count($live_unresolved) ? implode(', ', $live_unresolved) : 'none') . "\n";
echo "#\n";
echo "# Live-probe strings that are NOT relay-callable (measured, and deliberately\n";
echo "# not in the allowlist — the string names no method of that class or its\n";
echo "# parents, so refusing it is strictly better than the current dispatch fatal):\n";
if (count($live_not_callable)) {
    foreach ($live_not_callable as $m => $why) { echo "#   " . $m . "\n#     " . $why . "\n"; }
} else {
    echo "#   none\n";
}
echo "#\n";

$keys = array_keys($rows);
usort($keys, function ($a, $b) {
    $c = strcmp($a, $b);
    return $c;
});
foreach ($keys as $k) {
    $r = $rows[$k];
    $ev = implode(' | ', $r['evidence']);
    echo $r['method'] . "\t" . $r['source'] . "\t" . $ev . "\n";
}

// The class-axis pairs (section F). Emitted as '#class-axis' comment lines so
// that the shipped-list drift check in tests/security/relay_allowlist.test.php
// keeps comparing the data file against the SEED tiers only.
echo "#\n";
echo "# ---- class axis (R32): the " . count($axis_rows) . " concrete strings the fixed-literal wrappers\n";
echo "# ---- can dispatch, against the shipped list. 'listed' = named in the shipped data file.\n";
foreach ($axis_rows as $concrete => $row) {
    echo "#class-axis\t" . $concrete . "\t" . ($row['listed'] ? 'listed' : 'unlisted') . "\t" . $row['evidence'] . "\n";
}

if (count($rows) === 0) { fwrite(STDERR, "relay-callset-enum: EMPTY enumeration\n"); exit(1); }
if (count($live_unresolved)) { fwrite(STDERR, "relay-callset-enum: live-probe method(s) did not resolve: " . implode(', ', $live_unresolved) . "\n"); exit(1); }
fwrite(STDERR, "relay-callset-enum: class axis (8 literals): " . $AXIS['all']['total'] . " pairs, "
    . $AXIS['all']['unlisted'] . " not in the shipped list, " . $AXIS['all']['listed'] . " in it\n");
fwrite(STDERR, "relay-callset-enum: " . count($rows) . " relay method strings (" . $raw_total . " raw strings before resolution)\n");
exit(0);
