<?php
// tests/security/help_path.test.php — run: php tests/security/help_path.test.php
//
// Hermetic by ruling R8. The suite materialises its own fixture tree under
// sys_get_temp_dir() and removes it again on exit, so the planned command
//
//   docker run --rm -v "$PWD":/app -w /app php:8.3-cli php tests/security/help_path.test.php
//
// is the whole story: no extra mounts, no host docroot, no set-up. A second,
// environment-guarded block additionally asserts against a real docroot when one
// is present and is skipped (never failed) otherwise.
//
// The fixture also contains "counter-fixture" files: names that a *weaker*
// resolver would happily resolve and serve. They exist so that every hostile row
// below is able to fail — a row that can only ever return false proves nothing.
// See the verification log in task-1.2-report.md, "Fix round 1".

require_once dirname(__FILE__) . '/../../lib/help-path.php';

$fail = 0;
$rows = 0;

// ---------------------------------------------------------------------------
// helpers
// ---------------------------------------------------------------------------

function hp_mkdir_p ( $dir ) {
	return is_dir($dir) ? true : @mkdir($dir, 0777, true);
} // end function hp_mkdir_p

function hp_rmtree ( $dir ) {
	if (!is_dir($dir) or is_link($dir)) { return @unlink($dir); }
	$entries = @scandir($dir);
	if ($entries === false) { return false; }
	foreach ($entries as $e) {
		if ($e === '.' or $e === '..') { continue; }
		$p = $dir . '/' . $e;
		if (is_link($p) or !is_dir($p)) { @unlink($p); } else { hp_rmtree($p); }
	}
	return @rmdir($dir);
} // end function hp_rmtree

function hp_row ( $label, $got, $ok ) {
	global $fail, $rows;
	$rows++;
	printf("%-56s => %-64s %s\n", $label, var_export($got, true), $ok ? 'OK' : 'FAIL');
	if (!$ok) { $fail++; }
} // end function hp_row

// ---------------------------------------------------------------------------
// fixture tree
// ---------------------------------------------------------------------------

$root = sys_get_temp_dir() . '/help-path-fixture-' . getmypid();
if (is_dir($root)) { hp_rmtree($root); }        // leftover from a crashed same-pid run
register_shutdown_function('hp_rmtree', $root); // belt and braces: runs even on a fatal

$helpdir_disk = $root . '/ui/gwt/help/en_US';
if (!hp_mkdir_p($helpdir_disk) or !hp_mkdir_p($root . '/outside')) {
	fwrite(STDERR, "fixture: cannot build $root (is sys_get_temp_dir() writable?)\n");
	exit(2);
}

$page_html = "<html><body>fixture help page: %s</body></html>\n";

$docroot = realpath($root);
$helpdir = realpath($helpdir_disk);
if ($docroot === false or $helpdir === false) {
	fwrite(STDERR, "fixture: realpath() failed on the fixture tree\n");
	exit(2);
}

printf("fixture root: %s\n", $docroot);
printf("(candidate counter-fixtures: %s)\n\n",
	implode(', ', array('a..b.en_US.html', '..\\..\\etc\\passwd.en_US.html', 'main.en_US.extra.en_US.html',
		'main.en_US.en_US.html (catches a resolver that does not strip the suffix)',
		'leak.en_US.html -> second.en_US.html', 'escape.en_US.html -> ../../../../outside/secret.en_US.html',
		'help/main.en_US.html (target of /gwt/en_US/../main)', 'etc/passwd.en_US.html (target of /gwt/en_US//etc/passwd)',
		'lib/settings.php (target of ../../../../lib/settings.php)',
		'..../..../lib/settings.php (target of the ....// form)',
		'ui/help/etc/passwd.etc.html (target of //etc/passwd)')));

// Fixture files, relative to the fixture root. Real help pages first, then
// counter-fixtures: exactly the files a resolver missing one check would serve,
// each of which makes the hostile row beside it able to fail. Every write is
// verified afterwards — a counter-fixture that silently failed to appear would
// put the suite straight back into the vacuity this fix exists to remove.
$fixture_files = array(
	// real help pages
	'ui/gwt/help/en_US/main.en_US.html'                 => sprintf($page_html, 'main'),
	'ui/gwt/help/en_US/second.en_US.html'               => sprintf($page_html, 'second'),
	// a single token containing "..": legal as a filename, refused on purpose
	'ui/gwt/help/en_US/a..b.en_US.html'                 => sprintf($page_html, 'dotdot-token'),
	// backslash "dot segments": a legal Linux filename, refused on purpose
	'ui/gwt/help/en_US/..\\..\\etc\\passwd.en_US.html'   => sprintf($page_html, 'backslash-token'),
	// dotted token (real ones exist: accounts.receivable.en_US.html)
	'ui/gwt/help/en_US/main.en_US.extra.en_US.html'     => sprintf($page_html, 'dotted-token'),
	// makes the trailing ".<locale>" strip an assertion, not an assumption:
	// without the strip, '/gwt/en_US/main.en_US' resolves THIS file instead
	'ui/gwt/help/en_US/main.en_US.en_US.html'           => sprintf($page_html, 'unstripped-suffix'),
	// target of '/gwt/en_US//etc/passwd' for a resolver that allows a slash
	'ui/gwt/help/en_US/etc/passwd.en_US.html'           => sprintf($page_html, 'double-slash-absolute'),
	// target of '/gwt/en_US/../main' for a resolver that skips token validation
	'ui/gwt/help/main.en_US.html'                       => sprintf($page_html, 'one-level-above-helpdir'),
	// target of '//etc/passwd' for a resolver that skips ui/locale validation
	'ui/help/etc/passwd.etc.html'                       => sprintf($page_html, 'absolute-path-shape'),
	// target of the escaping symlink
	'outside/secret.en_US.html'                         => sprintf($page_html, 'outside-the-help-tree'),
	// traversal targets for a plain string-concatenating resolver (pre-fix shape):
	// four ".." from ui/gwt/help/en_US lands on the fixture root, and "....//" is a
	// non-separator only on Windows, so here it is a literal directory name
	'lib/settings.php'                                  => "<?php // fixture stand-in for the DB-credential file\n",
	'ui/gwt/help/en_US/..../..../lib/settings.php'      => "<?php // fixture stand-in, ....// shape\n",
);

$fixture_ok = true;
foreach ($fixture_files as $rel => $content) {
	$abs = $root . '/' . $rel;
	if (!hp_mkdir_p(dirname($abs)) or
	    @file_put_contents($abs, $content) === false or !is_file($abs)) {
		hp_row('fixture ' . $rel, 'MISSING', false);
		$fixture_ok = false;
	}
}
if ($fixture_ok) {
	hp_row('fixture tree (' . count($fixture_files) . ' files)', 'present', true);
}

// Symlinks: in-tree (aliases another help page) and out-of-tree (escapes the
// help dir). Both must be false; a resolver without the basename()/confinement
// checks resolves the first and serves the second.
$symlinks_ok = true;
foreach (array(
	'ui/gwt/help/en_US/leak.en_US.html'   => 'second.en_US.html',
	'ui/gwt/help/en_US/escape.en_US.html' => '../../../../outside/secret.en_US.html',
) as $rel => $target) {
	$abs = $root . '/' . $rel;
	if (!@symlink($target, $abs) or !is_link($abs)) {
		hp_row('fixture symlink ' . basename($rel), 'MISSING', false);
		$symlinks_ok = false;
	}
}
if ($symlinks_ok) {
	hp_row('fixture symlinks (2)', 'present', true);
}

// ---------------------------------------------------------------------------
// positive cases: the exact resolved path, not a prefix of a literal
// ---------------------------------------------------------------------------

$positive = array(
	'/gwt/en_US/main'             => 'main',    // bare form the server has always served
	'/gwt/en_US/main.en_US'       => 'main',    // GWT client form (Util.java:241): trailing .<locale>
	'/gwt/en_US/second'           => 'second',  // a different page, so a black-hole stub cannot pass
	'/gwt/en_US/second.en_US'     => 'second',  //   either request form, second page
	'/gwt/en_US/main.en_US.extra' => 'main.en_US.extra', // a dotted token: exactly one suffix appended
);

foreach ($positive as $path_info => $page) {
	$got    = help_resolve_path($path_info, $docroot);
	$expect = $helpdir . '/' . $page . '.en_US.html';
	$ok = ($got === $expect)                                // exact, canonical (realpath) path
	   && (substr($got, -strlen('ui/gwt/help/en_US/' . $page . '.en_US.html'))
	          === 'ui/gwt/help/en_US/' . $page . '.en_US.html'); // ...and really ends in the help page
	hp_row($path_info, $got, $ok);
}

// ---------------------------------------------------------------------------
// hostile cases: every one must return false. The counter-fixtures above are
// what make these rows non-vacuous.
// ---------------------------------------------------------------------------

$hostile = array(
	// task 0.2's six originals (rejected by the ui/locale/page validation)
	'/gwt/en_US/../../../../lib/settings.php'         => 'dot segments + a slash in the page token',
	'/gwt/en_US/../../../../../../../../etc/passwd'   => 'dot segments clamping at /',
	'/gwt/en_US/..%2f..%2f..%2f..%2flib/settings.php' => 'urldecoded %2f, then dot segments',
	'/gwt/en_US/....//....//lib/settings.php'         => 'the ....// non-separator trick',
	'/gwt/en_US/'                                     => 'empty page (no default index page)',
	'/gwt/en_US/%00main'                              => 'NUL byte in the page token',
	// symlinks
	'/gwt/en_US/leak'                                 => 'in-tree symlink to another help page (basename() check)',
	'/gwt/en_US/escape'                               => 'symlink out of the help tree (realpath() confinement)',
	// single token containing ..
	'/gwt/en_US/a..b'                                 => 'a..b as a page token; the file exists and is still refused',
	// absolute-path shapes
	'//etc/passwd'                                    => 'absolute path shape (ui empty, locale becomes "etc")',
	'/gwt/en_US//etc/passwd'                          => 'absolute path after a valid ui/locale prefix',
	// backslash variants
	'/gwt/en_US/..\\..\\etc\\passwd'                   => 'backslash dot segments within a valid prefix',
	'..\\..\\etc\\passwd'                              => 'backslash dot segments as the whole PATH_INFO',
	// traversing locale / dot segment where the locale belongs
	'/gwt/en_US/../main'                              => 'dot segment outside the page position',
	// existence check
	'/gwt/en_US/nosuchpage'                           => 'well-formed token, no such file',
);

foreach ($hostile as $path_info => $why) {
	if (!$symlinks_ok and ($path_info === '/gwt/en_US/leak' or $path_info === '/gwt/en_US/escape')) {
		printf("%-56s => %-64s SKIP (symlink() unavailable here)\n", $path_info, '-');
		continue;
	}
	$got = help_resolve_path($path_info, $docroot);
	hp_row($path_info, $got, $got === false);
}

// ---------------------------------------------------------------------------
// environment-guarded block: a real docroot, only if one exists.
// ---------------------------------------------------------------------------

$candidates = array();
$env_root = getenv('FREEMED_DOCROOT');
if ($env_root !== false and $env_root !== '') { $candidates[] = $env_root; }
$candidates[] = '/usr/share/freemed';
$candidates[] = dirname(dirname(dirname(__FILE__))); // the checkout itself (== /app in the container)

$realdoc = false;
foreach ($candidates as $c) {
	if ($c and is_file($c . '/ui/gwt/help/en_US/main.en_US.html')) { $realdoc = realpath($c); break; }
}

if ($realdoc === false) {
	printf("\n[real-docroot block SKIPPED: no docroot holding ui/gwt/help/en_US/main.en_US.html among: %s]\n",
		implode(', ', $candidates));
} else {
	printf("\n-- real docroot: %s\n", $realdoc);
	foreach (array('/gwt/en_US/main', '/gwt/en_US/main.en_US') as $path_info) {
		$got    = help_resolve_path($path_info, $realdoc);
		$expect = $realdoc . '/ui/gwt/help/en_US/main.en_US.html';
		$ok = ($got === $expect)
		   && (substr($got, -strlen('ui/gwt/help/en_US/main.en_US.html')) === 'ui/gwt/help/en_US/main.en_US.html');
		hp_row($path_info, $got, $ok);
	}
	foreach (array_keys(array_slice($hostile, 0, 6, true)) as $path_info) {
		$got = help_resolve_path($path_info, $realdoc);
		hp_row($path_info, $got, $got === false);
	}
}

// ---------------------------------------------------------------------------
// teardown + summary
// ---------------------------------------------------------------------------

hp_rmtree($root);
printf("\nfixture cleaned up: %s\n", is_dir($root) ? 'NO - still present!' : 'yes');
if (is_dir($root)) { $fail++; }

echo $fail ? "\n$fail FAILED\n" : "\nall passed\n";
exit($fail ? 1 : 0);
