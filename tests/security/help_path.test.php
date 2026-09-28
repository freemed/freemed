<?php
// tests/security/help_path.test.php — run: php tests/security/help_path.test.php
require_once dirname(__FILE__) . '/../../lib/help-path.php';

$docroot = '/usr/share/freemed';
$cases = array(
    // PATH_INFO                                     => expected resolved path (or false)
    '/gwt/en_US/main'                                => null, // must resolve under the help dir
    '/gwt/en_US/main.en_US'                          => null,
    '/gwt/en_US/../../../../lib/settings.php'        => false,
    '/gwt/en_US/../../../../../../../../etc/passwd'  => false,
    '/gwt/en_US/..%2f..%2f..%2f..%2flib/settings.php' => false,
    '/gwt/en_US/....//....//lib/settings.php'        => false,
    '/gwt/en_US/'                                    => false,
    '/gwt/en_US/%00main'                             => false,
);
$fail = 0;
foreach ($cases as $path_info => $expect) {
    $got = help_resolve_path($path_info, $docroot);
    $ok = is_null($expect) ? ($got !== false && strpos($got, "$docroot/ui/gwt/help/en_US/") === 0)
                           : ($got === $expect);
    printf("%-52s => %-60s %s\n", $path_info, var_export($got, true), $ok ? 'OK' : 'FAIL');
    if (!$ok) { $fail++; }
}
echo $fail ? "\n$fail FAILED\n" : "\nall passed\n";
exit($fail ? 1 : 0);
