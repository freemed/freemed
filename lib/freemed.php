<?php
 // $Id$
 // $Author$
 // note: global variables for entire freemed code base
 // lic : GPL, v2

  // This is the lib/freemed.php file, which keeps all
  // variables that the program needs to know, to
  // eliminate needless typing...

if (!defined("__FREEMED_PHP__")) {

define ('__FREEMED_PHP__', true);

    // These variables you should not touch
define ('PACKAGENAME', "FreeMED");				// package name
define ('CODED_BY', "The FreeMED Project");		// coded by tag
define ('VERSION', "0.6.4");	// current version
define ('DISPLAY_VERSION', "0.7.0b3 (CVS)");
define ('BUGS_EMAIL', "code_bugs@ourexchange.net");	// coder email...

define ('BUGS_ADDRESS', "http://sourceforge.net/project/freemed/");
$cur_date=date("Y-m-d");		// SQL date format (don't touch!)
$cur_date_hash=date("Ymd");		// YYYYMMDD date hash

   // CHANGE THIS FOR YOUR LOCAL DATE DISPLAY
$local_date_display="%Y-%m-%d";       // United States
  // $local_date_display="%d.%m.%Y";    // European countries

//----- Import settings
if (file_exists('lib/settings.php')) {
	include_once('lib/settings.php');
} else {
	die("FreeMED cannot find the configuration file ".
		"<b>lib/settings.php</b>.");
}

//----- Fax subsystem
$gifhome = PHYSICAL_LOCATION . '/data/fax/incoming';

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
if (!function_exists("bcadd")) include_once ("lib/bcadd.php");

  // Check for proper template, and load default if not provided
if (!isset($template)) { $template = TEMPLATE; }

 // Include library for template
if (file_exists("lib/template/".$template."/lib.php")) {
	include_once("lib/template/".$template."/lib.php");
} else { include_once("lib/template/default/lib.php"); }

  // ************ HANDLERS AND OTHER MODULE LOADERS ****************

include_once ("lib/error_handler.php");   // internal error handler

if (file_exists(PHPWEBTOOLS_LOCATION."/webtools.php")) {
	include_once (PHPWEBTOOLS_LOCATION."/webtools.php"); // webtools toolkit
} else {
	die("FreeMED requires that phpwebtools be installed at <b>".
		PHPWEBTOOLS_LOCATION."</b>. This location can be changed in ".
		"<b>lib/settings.php</b>.");
}

define ('WEBTOOLS_REQUIRED', '0.4.2');   // version of phpwebtools required

  // version check for webtools
if ( !defined("WEBTOOLS_VERSION") or
		!version_check(WEBTOOLS_VERSION, WEBTOOLS_REQUIRED) ) {
	die ("phpwebtools >= ".WEBTOOLS_REQUIRED." is required ".
		"for this version of FreeMED ".
		"(http://phpwebtools.sourceforge.net/)\n");
}

// ********************** START SESSION **************************
if (!defined('SESSION_DISABLE')) {
	// This is *only* disabled when XML-RPC calls are being made,
	// etc, so that it does not require information it can't get.
	session_start();

	session_register(
		'authdata',
		'current_patient',
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
	include_once ("lib/i18n.php");

	// Load ACL routines
	include_once ("lib/acl.php");
}
// ***************************************************************

include_once ("lib/iso-set.php");         // ISO set handler
include_once ("lib/API.php");             // API functions
include_once ("lib/macros.php");          // macros/contants
include_once ("lib/xml.php");             // XML import/export routines

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
