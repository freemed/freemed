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

    // these variables you should not touch
define ('PACKAGENAME', "freemed");				// package name
define ('CODED_BY', "The Freemed Project");		// coded by tag
define ('VERSION', "0.5 (Development/CVS)");	// current version
define ('BUGS_EMAIL', "code_bugs@ourexchange.net");	// coder email...

define ('BUGS_ADDRESS', "http://sourceforge.net/project/freemed/");
$cur_date=date("Y-m-d");		// SQL date format (don't touch!)
$cur_date_hash=date("Ymd");		// YYYYMMDD date hash

   // CHANGE THIS FOR YOUR LOCAL DATE DISPLAY
$local_date_display="%Y-%m-%d";       // United States
  // $local_date_display="%d.%m.%Y";    // European countries

    // **********************************
    // ***** customizable variables *****
    // **********************************
define ('INSTALLATION', "Stock Freemed Install"); // installation name
define ('LOCALEDIR',	"/usr/share/locale");     // gettext location
define ('DB_HOST', "localhost");	// database (SQL) host location
define ('DB_NAME', "freemed");	// database name
define ('DB_USER', "root");				// SQL server username
define ('DB_PASSWORD', "password");		// SQL server password
define ('PHYSICAL_LOCATION', "/usr/share/freemed");
define ('PATID_PREFIX', "PAT"); // used to generate internal practice ID
define ('BUG_TRACKER', true);   // set bug tracker on or off
define ('USE_CSS', true);		// do we use cascading style sheets?
define ('TEMPLATE', "default");	// set template

define ('HOST', "localhost");             // host name for this system
define ('BASE_URL', "/freemed");		// offset (i.e. http://here/package)
define ('HTTP', "http");                // http for normal, https for SSL
define ('SESSION_PROTECTION', true);	// strong session protection?
$default_language="EN";               // default language

    // GPG settings
    //
    // customize if you are using the db backup maintenance module with
    // pgp. for keyring, you need to as root create /home/nobody,
    // chown nobody:nobody /home/nobody
    // su nobody
    // export HOME=/home/nobody; cd $HOME
    // use GPG to encrypt a file, run it twice
    // you should now have /home/nobody/.gpg

define ('USE_GPG', "NO");	// encrypt backups? (YES|NO)
define ('GPG_PASSPHRASE_LOCATION', PHYSICAL_LOCATION."/lib/gpg_phrase.php");
define ('GPG_HOME', "/home/nobody");

    // *************************************
    // ** fax subsystem  --please        ***
    // ** read incoming_fax_scripts.mk   ***
    // *************************************

$gifhome = PHYSICAL_LOCATION . "/data/fax/incoming";

    // *************************************
    // ***** language setting routines *****
    // *************************************

if (strlen($u_lang)==2) $language=$u_lang;
  else $language=$default_language;

    // don't touch these variables either...
define ('COMPLETE_URL', HTTP . "://" . HOST . BASE_URL . "/" ); 

$debug=false;  // true=debug info on, false=debug info off
$_mail_handler="mailto:";  // where the mail goes...
    // the _mail_handler variable is so that we can farm
    // this mail to some mail hook in a program.

    // colors
$bar_start_color="#dddddd";
$bar_alt_color  ="#ffffff";
$topbar_color   ="#0022cf";
$darker_bgcolor ="#777777";
$menubar_color  ="#bbbbbb";

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

// if there's no bcmath module, use fake bcadd() function
if (!function_exists("bcadd"))
	include_once ("lib/bcadd.php");
if (!function_exists("bindtextdomain"))
	die ("PHP must be compiled with GNU gettext (--with-gettext)");

  // check for proper template, and load default if not provided
if (!isset($template)) {
	$template = TEMPLATE;
}

 // Include library for template
if (file_exists("lib/template/".$template."/lib.php"))
	include_once("lib/template/".$template."/lib.php");
else include_once("lib/template/default/lib.php");

  // ************ HANDLERS AND OTHER MODULE LOADERS ****************

include_once ("lib/error_handler.php");   // internal error handler
include_once ("/usr/share/phpwebtools/webtools.php"); // webtools toolkit

define ('WEBTOOLS_REQUIRED', "0.2.4");   // version of phpwebtools required

  // version check for webtools
if ( !defined("WEBTOOLS_VERSION") or !version_check(WEBTOOLS_VERSION, WEBTOOLS_REQUIRED) )
	die ("phpwebtools >= ".WEBTOOLS_REQUIRED." is required for this version of freemed ".
		"(http://phpwebtools.sourceforge.net/)\n");

// ********************** START SESSION **************************
if (!defined('SESSION_DISABLE')) {
	// This is *only* disabled when XML-RPC calls are being made,
	// etc, so that it does not require information it can't get.
	session_start();
	session_register("SESSION"); // master session storage

	// Load gettext routines. This can only be done if a session
	// is running, as it stores several variables in session
	// tracking.
	include_once ("lib/i18n.php");
}
// ***************************************************************

include_once ("lib/iso-set.php");         // ISO set handler
include_once ("lib/API.php");             // API functions
include_once ("lib/containers.php");      // class containers
include_once ("lib/macros.php");          // macros/contants
include_once ("lib/xml.php");             // XML import/export routines

  // ****************** INITIALIZE SQL CONNECTION ******************

    // *** database engine ***
    //   SQL_MYSQL    - MySQL backend
    //   SQL_ODBC     - ODBC compliant (i.e. M$ SQL Server)
    //   SQL_POSTGRES - PostgreSQL backend
    //   SQL_MSQL     - mSQL backend
define ('DB_ENGINE', SQL_MYSQL);

$sql = new sql (DB_ENGINE, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  // ***************************************************************

} // end checking for __FREEMED_PHP__

?>
