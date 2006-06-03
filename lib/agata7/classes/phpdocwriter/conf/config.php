<?php
// Temporary directory for linux (with trailing slash)
define('pdw_tmpdir', '/tmp/');
// Temporary directory for windows (with trailing slash)
// define('pdw_tmpdir', 'C:/TEMP/');

// Full path to phpdocwriter directory (change it only if necessary)
define('pdw_full_path',  dirname(dirname(__FILE__)));

// Full path to linux export command (change it only if necessary)
define('export_script_path', "\"".pdw_full_path."/conf/export.sh\"");
// Full path to windows export command (change it only if necessary)
// define('export_script_path', "\"".pdw_full_path."/conf/export.bat\"");
?>