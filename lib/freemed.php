<?php
 // $Id$
 // note: global variables for entire freemed code base
 // lic : GPL, v2

  // This is the lib/freemed.php file, which keeps all
  // variables that the program needs to know, to
  // eliminate needless typing...

if (!defined("__FREEMED_PHP__")) {

define (__FREEMED_PHP__, true);

    // these variables you should not touch
  define (PACKAGENAME, "freemed");				// package name
  define (CODED_BY, "The Freemed Project");		// coded by tag
  define (VERSION, "0.2.1 (cvs)");				// current version
  define (BUGS_EMAIL, "code_bugs@ourexchange.net");	// coder email...

  define (BUGS_ADDRESS, "http://sourceforge.net/project/freemed/");
  $cur_date=date("Y-m-d");		// SQL date format (don't touch!)
  $cur_date_hash=date("Ymd");		// YYYYMMDD date hash
  $_auth="default_value=yes";		// authentication (KFM fix)

   // CHANGE THIS FOR YOUR LOCAL DATE DISPLAY
  $local_date_display="%Y-%m-%d";       // United States
  // $local_date_display="%d.%m.%Y";    // European countries

    // **********************************
    // ***** customizable variables *****
    // **********************************
  define (INSTALLATION, "Stock Freemed Install"); // installation name
  define (LOCALEDIR,	"/usr/share/locale");     // gettext location
  define (DB_HOST, "localhost");	// database (SQL) host location
  define (PHYSICAL_LOCATION, "/usr/freemed");
  $database="freemed";			// SQL db name (for places

  $host="localhost";                    // host name for this system
  $physical_loc=PHYSICAL_LOCATION;      // skip the eval, speed hack
  $database="freemed";					// SQL db name (for places
										// with multiple iterations...)
  $base_url="/freemed";					// offset (i.e. http://here/package)
  $http="http";                         // http for normal, https for SSL
    // these are for the SQL server, since it needs at least one real
    // account to function. please change at will
  define(DB_USER, "root");				// SQL server username
  define(DB_PASSWORD, "password");		// SQL server password
  $default_language="EN";               // default language

    // *************************************
    // ** fax subsystem  --please        ***
    // ** read incoming_fax_scripts.mk   ***
    // *************************************

  $gifhome="$physical_loc/data/fax/incoming";

    // *************************************
    // ***** language setting routines *****
    // *************************************

  if (strlen($u_lang)==2) $language=$u_lang;
  else $language=$default_language;

    // don't touch these variables either...
  $complete_url="$http://$host$base_url"; 
  $_cookie_expire="36000";     // cookies expire in 1 hour

    // *** database engine ***
    //   mysql    - MySQL backend
    //   odbc     - ODBC compliant (i.e. M$ SQL Server)
    //   postgres - PostgreSQL backend
    //   msql     - mSQL backend
  define (DB_ENGINE, "mysql");

  $debug=false;  // true=debug info on, false=debug info off
  $_mail_handler="mailto:";  // where the mail goes...
    // the _mail_handler variable is so that we can farm
    // this mail to some mail hook in a program.

    // user level qualifiers
  $admin_level   =8; // administrator user level
  $delete_level  =5; // user level above which someone can delete
  $export_level  =3; // user level to export databases
  $database_level=2; // level above which you can add db recs...

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
    // header fonts, begin and end
  $HEADERFONT_B = "CENTER><FONT FACE=\"Arial, Helvetica, Verdana\"><B";
  $HEADERFONT_E = "/B></FONT></CENTER";
    // standard fonts, begin and end
  $STDFONT_B    = "FONT FACE=\"Arial, Helvetica, Verdana\"";
  $STDFONT_E    = "/FONT";

  $brackets     = "[]";

  //if ($default_facility>0) {
  // SetCookie ("default_facility", $default_facility, time()+$_cookie_expire); 

  // set the maximum timeout...
  set_time_limit (0);

  // quick hack for Lynx caching pages problem
  if (strstr($HTTP_USER_AGENT, "Lynx")) {
    Header ("Pragma: no-cache"); // force no caching
  } // end checking for lynx

  // ****************** CHECK FOR PHP MODULES **********************

  if (!function_exists("bcadd"))
    die ("PHP must be compiled with bcmath module (--with-bcmath)");
  if (!function_exists("bindtextdomain"))
    die ("PHP must be compiled with GNU gettext (--with-gettext)");

  // ************ HANDLERS AND OTHER MODULE LOADERS ****************

  include ("webtools.php");            // webtools toolkit

  // version check for webtools
  if ( !defined("WEBTOOLS_VERSION") or ((WEBTOOLS_VERSION + 0) < 0.2) ) {
    die ("phpwebtools >= 0.2 is required for this version of freemed ".
         "(http://sourceforge.net/projects/phpwebtools/)");
  }

  include ("lib/iso-set.php");         // ISO set handler
  include ("lib/containers.php");      // class containers
  include ("lib/language-loader.php"); // external language loader
  include ("lib/macros.php");          // macros/contants
  include ("lib/module.php");          // loadable module support (phpwebtools)

  // ***************************************************************

} // end checking for __FREEMED_PHP__

?>
