<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

if (!defined("__FREEMED_PHP__")) {

define ('__FREEMED_PHP__', true);

    // These variables you should not touch
define ('PACKAGENAME', "FreeMED");
define ('CODED_BY', "FreeMED Software Foundation");
define ('VERSION', "0.8.8");	// current version
define ('DISPLAY_VERSION', "0.9.0-dev");
define ('PHYSICAL_LOCATION', dirname(dirname(__FILE__)) );
define ('GWTPHP_DIR', PHYSICAL_LOCATION . '/lib/gwtphp');
define ('LOG4PHP_DIR', PHYSICAL_LOCATION . '/lib/log4php');
define ('SHM_CACHE', false );

//	Override to allow login image
//define ('LOGIN_IMAGE', 'login-image.png');

//----- Import settings
if (file_exists(dirname(__FILE__).'/settings.php')) {
	include_once(dirname(__FILE__).'/settings.php');
} else {
	if (!defined('SKIP_SQL_INIT')) {
		die("FreeMED cannot find the configuration file <b>lib/settings.php</b>. Make sure you have run the <a href=\"install.php\">installer</a> before proceeding.");
	}
}

//----- Make sure we have enough memory without having to edit {php,php4,php5}.ini
if (ini_get('memory_limit')+0 < 64) {
	@ini_set('memory_limit', '64M');
}
if (ini_get('post_max_size')+0 < 64) {
	@ini_set('post_max_size', '64M');
}

//----- Use our *own* stuff, no one else's stuff
if (function_exists('set_include_path')) {
	set_include_path(dirname(dirname(__FILE__)).PATH_SEPARATOR.dirname(__FILE__).'/pear/');
} else {
	ini_set('include_path', dirname(dirname(__FILE__)).PATH_SEPARATOR.dirname(__FILE__).'/pear/');
}

  // related to the calendar --
  //   times are given in 24 hour format, then reformatted for
  //   am and pm by the program
  // these are settable as calshr/calehr in the config file,
  // but these are there by default
$cal_starting_hour = "8";  // start at 8 o'clock
$cal_ending_hour   = "18"; // end at 6 o'clock pm

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

  // ************ HANDLERS AND OTHER MODULE LOADERS ****************

include_once ( dirname(__FILE__)."/loader.php" );
include_once ( dirname(__FILE__)."/module.php" );

// ********************** START SESSION **************************
if (!defined('SESSION_DISABLE')) {
	session_cache_limiter('nocache');
	session_start();

	session_register(
		'authdata',
		'current_patient',
		'default_facility',
		'ipaddr',
		'language',
		'page_history',
		'page_history_name',
		'patient_history'
	);

	//----- Gettext and language settings
	if (isset($_REQUEST['_l'])) {
		// Handle template language changes
		$_SESSION['language'] = $_REQUEST['_l'];
	} elseif ($_SESSION['language']) {
		// Pull from cookie (do nothing)
	} else {
		// Use the default
		$_SESSION['language'] = DEFAULT_LANGUAGE;
	}
	$GLOBALS['freemed']['__language'] = $_SESSION['language'];

	// Set default facility from parameter if it exists
	if (isset($_REQUEST['_f'])) {
		// Handle template language changes
		$_SESSION['default_facility'] = ( $_REQUEST['_f'] + 0 );
	}

	// Load Gettext routines (most non-session things don't need it).
	include_once (dirname(__FILE__)."/i18n.php");

	// Load ACL routines
	if (!defined('SKIP_SQL_INIT')) {
		include_once (dirname(__FILE__)."/acl.php");
	}
}
// ***************************************************************

include_once (dirname(__FILE__)."/API.php");             // API functions
include_once (dirname(__FILE__)."/macros.php");          // macros/contants

  // ****************** INITIALIZE SQL CONNECTION ******************

define ('DB_ENGINE', 'mysqli');

//----- Create SQL database object
if (!defined('SKIP_SQL_INIT')) {
	$sql = CreateObject ( 'org.freemedsoftware.core.FreemedDb' );
}

//----- Create Log target
openlog( "freemed", LOG_PID | LOG_PERROR, LOG_LOCAL0 );

  // ***************************************************************

} // end checking for __FREEMED_PHP__

?>
