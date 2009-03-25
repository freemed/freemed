<?php
	// $Id$
	// $Author$
	// lic : GPL, v2

if (!defined("__FREEMED_PHP__")) {

define ('__FREEMED_PHP__', true);

    // These variables you should not touch
define ('PACKAGENAME', "FreeMED");				// package name
define ('CODED_BY', "The FreeMED Project");		// coded by tag
define ('VERSION', "0.8.5");	// current version
define ('DISPLAY_VERSION', "0.8.5");
define ('BUGS_EMAIL', "support@freemedsoftware.org");	// coder email...

define ('BUGS_ADDRESS', "http://sourceforge.net/project/freemed/");
$cur_date=date("Y-m-d");		// SQL date format (don't touch!)
$cur_date_hash=date("Ymd");		// YYYYMMDD date hash

   // CHANGE THIS FOR YOUR LOCAL DATE DISPLAY
$local_date_display="%Y-%m-%d";       // United States
  // $local_date_display="%d.%m.%Y";    // European countries

//----- Import settings
if (file_exists(dirname(__FILE__).'/settings.php')) {
	include_once(dirname(__FILE__).'/settings.php');
} else {
	die("FreeMED cannot find the configuration file <b>lib/settings.php</b>.");
}

//----- Make sure we have enough memory without having to edit {php,php4}.ini
if (ini_get('memory_limit')+0 < 64) {
	@ini_set('memory_limit', '64M');
}

//----- Use our *own* stuff, no one else's stuff
if (function_exists('set_include_path')) {
	set_include_path(dirname(dirname(__FILE__)).PATH_SEPARATOR.dirname(__FILE__).'/pear/');
} else {
	ini_set('include_path', dirname(dirname(__FILE__)).PATH_SEPARATOR.dirname(__FILE__).'/pear/');
}

define ('COMPLETE_URL', HTTP . "://" . HOST . BASE_URL . "/" ); 

$debug=false;  // true=debug info on, false=debug info off
$_mail_handler="mailto:";  // where the mail goes...
    // the _mail_handler variable is so that we can farm
    // this mail to some mail hook in a program.

  // related to the calendar --
  //   times are given in 24 hour format, then reformatted for
  //   am and pm by the program
  // these are settable as calshr/calehr in the config file,
  // but these are there by default
$cal_starting_hour = "8";  // start at 8 o'clock
$cal_ending_hour   = "18"; // end at 6 o'clock pm

  // maximum number of returned results in multipage result queries
$max_num_res = 15;

  // now, some all-purpose time savers
  // don't touch unless you -KNOW- what you are doing.

$brackets     = "[]";

  // set the maximum timeout...
set_time_limit (0);

  // quick hack for Lynx caching pages problem
if (strstr($HTTP_USER_AGENT, "Lynx")) {
	// force no caching
	Header ("Cache-Control: no-cache, must-revalidate");
	Header ("Pragma: no-cache");
} // end checking for lynx

  // ****************** CHECK FOR PHP MODULES **********************

  // If there's no bcmath module, use fake bcadd() function
if (!function_exists("bcadd")) include_once (dirname(__FILE__).'/bcadd.php');

  // Check for proper template, and load default if not provided
if (!isset($template)) { $template = TEMPLATE; }

 // Include library for template
if (file_exists("lib/template/".$template."/lib.php")) {
	include_once("lib/template/".$template."/lib.php");
} else { include_once("lib/template/default/lib.php"); }

  // ************ HANDLERS AND OTHER MODULE LOADERS ****************

include_once (dirname(__FILE__)."/error_handler.php");   // internal error handler

include_once (dirname(__FILE__)."/phpwebtools/webtools.php"); // webtools toolkit

// Quick IE/Gecko browser check
if (ereg('MSIE ([0-9].[0-9]{1,2})',$_SERVER['HTTP_USER_AGENT'])) {
	$GLOBALS['__freemed']['IE'] = true;
} elseif (ereg('Mozilla/([0-9].[0-9]{1,2})',$_SERVER['HTTP_USER_AGENT'])) {
	$GLOBALS['__freemed']['Mozilla'] = true;
}

// ********************** START SESSION **************************
if (!defined('SESSION_DISABLE')) {
	// This is *only* disabled when XML-RPC calls are being made,
	// etc, so that it does not require information it can't get.
	if ($_REQUEST['action'] == 'print') {
		session_cache_limiter('public');
	} else {
		session_cache_limiter('nocache');
	}
	session_start();

	session_register(
		'authdata',
		'current_patient',
		'default_facility',
		'ipaddr',
		'page_history',
		'page_history_name',
		'patient_history'
	);

	// Bring session and request variables into the global scope.
	if (is_array($_SESSION)) { extract($_SESSION); }

	// Create object map for FreeMED
	CreateApplicationMap(array(
		'FreeMED' => 'lib/class.*.php',
		'Agata' => 'lib/agata/lib/class.*.php',
		// Protected namespaces:
		'_FreeMED' => 'lib/class.*.php',
		'_ACL' => 'lib/acl/*.class.php'
	));

	//----- Gettext and language settings
	if (isset($_POST['_l'])) {
		// Handle template language changes
		$_SESSION['language'] = $_POST['_l'];
	} elseif ($_SESSION['language']) {
		// Pull from cookie (do nothing)
	} else {
		// Use the default
		$_SESSION['language'] = DEFAULT_LANGUAGE;
	}
	$GLOBALS['freemed']['__language'] = $_SESSION['language'];

	// Load GettextXML routines (most non-session things don't need it).
	include_once (dirname(__FILE__)."/i18n.php");

	// Load ACL routines
	include_once (dirname(__FILE__)."/acl.php");
}
// ***************************************************************

include_once (dirname(__FILE__)."/iso-set.php");         // ISO set handler
include_once (dirname(__FILE__)."/API.php");             // API functions
include_once (dirname(__FILE__)."/macros.php");          // macros/contants

  // ****************** INITIALIZE SQL CONNECTION ******************

    // *** database engine ***
    //   SQL_MYSQL    - MySQL backend
    //   SQL_ODBC     - ODBC compliant (i.e. M$ SQL Server)
    //   SQL_POSTGRES - PostgreSQL backend
    //   SQL_MSQL     - mSQL backend
define ('DB_ENGINE', SQL_MYSQL);

//----- Create SQL database object
if (!defined('SKIP_SQL_INIT')) {
	$sql = CreateObject (
		'PHP.sql', 
		DB_ENGINE,
		array(
			'host' => DB_HOST, 
			'user' => DB_USER, 
			'password' => DB_PASSWORD, 
			'database' => DB_NAME,
		)
	);
}

//----- Create Log target
openlog("freemed", LOG_PID | LOG_PERROR, LOG_LOCAL0);

  // ***************************************************************

} // end checking for __FREEMED_PHP__

?>
