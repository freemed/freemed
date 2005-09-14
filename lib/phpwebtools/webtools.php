<?php
	// $Id$
	// $Author$
	// desc: loader for phpwebtools
	// lic : LGPL

if (!defined('__WEBTOOLS_PHP__')) {

define ('__WEBTOOLS_PHP__', true);

define ('WEBTOOLS_ROOT', dirname(__FILE__));

// Current version of phpwebtools
define ('WEBTOOLS_VERSION', '0.4.5');
define ('ALWAYS_GLOBALS', true);

// Check for php versions prior to 4.1.0
$GLOBALS['__phpwebtools']['phpver'] = explode ('.', phpversion());
if (($GLOBALS['__phpwebtools']['phpver'][0]<4) or 
		(($GLOBALS['__phpwebtools']['phpver'][0]==4) and
		 ($GLOBALS['__phpwebtools']['phpver'][1]<1))) {
	define('PHP_PRE_4_1_0', true);
} else {
	define('PHP_PRE_4_1_0', false);
}
unset($__my_php_version);

// If PHP_PRE_4_1_0, kludge the supervariables
if (PHP_PRE_4_1_0) {
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_FILES_VARS;
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
}

// Check for register globals, and kludge if not enabled
$GLOBALS['__phpwebtools']['register_globals'] = get_cfg_var('register_globals');
if (!$GLOBALS['__phpwebtools']['register_globals'] and ALWAYS_GLOBALS) {
	// This is not a good idea for performance or anything else, but it
	// is needed for a lot of things. We'll just have to rewrite all of the
	// webtools and dependent code to fix this, since register_globals is
	// turned off by default in new versions of PHP.
	extract($_REQUEST);
	extract($_SERVER);
	extract($_COOKIE);
	extract($_ENV);
}

// Define httpd base dir (for creation of directories, etc)
if (isset($_ENV['DOCUMENT_ROOT'])) {
	define ('HTTPD_ROOT', $HTTP_ENV_VARS['DOCUMENT_ROOT']);
} else {
	// IIS/PWS Kludge
	define ('HTTPD_ROOT', $HTTP_SERVER_VARS['DOCUMENT_ROOT']);
}

// Handle broken IIS/PWS REQUEST_URI handling
if (empty($_ENV['REQUEST_URI'])) {
	$_ENV['REQUEST_URI'] = $PHP_SELF . 
		( !empty($_SERVER['QUERY_STRING']) ? '?' : '' ) .
		$_SERVER['QUERY_STRING'];
}

// Define "NULL" if we don't know what it is...
if (!defined('NULL')) define ('NULL', '');

// Predefine phpwebtools macros, so that everything has a place.
include_once WEBTOOLS_ROOT."macros.php";

// *** CLASSES ***
// (note that the only classes that are included are those
//  that are called as class::method with no object
//  creation. everything else is called via object_loader)
include_once WEBTOOLS_ROOT."class.counter.php";
include_once WEBTOOLS_ROOT."class.html_form.php";

// *** FUNCTIONS ***
include_once WEBTOOLS_ROOT."authentication.php";
include_once WEBTOOLS_ROOT."date_tools.php";
include_once WEBTOOLS_ROOT."file_tools.php";
include_once WEBTOOLS_ROOT."language_tools.php";
include_once WEBTOOLS_ROOT."misc_tools.php";
include_once WEBTOOLS_ROOT."module_tools.php";
include_once WEBTOOLS_ROOT."object_loader.php";
include_once WEBTOOLS_ROOT."phone_tools.php";
include_once WEBTOOLS_ROOT."sql_tools.php";
include_once WEBTOOLS_ROOT."verify.php";
include_once WEBTOOLS_ROOT."wap_tools.php";
include_once WEBTOOLS_ROOT."xmlrpc_tools.php";

} // end checking if defined

?>
