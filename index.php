<?php

 // $Id$
 // $Author$
 // code: jeff b (jeff@ourexchange.net)
 // lic : GPL, v2

$page_name = "index.php";

// Fred Trotter: I have seperated all of the health checks that the system
// should perform to this file. It should catch the following configuration 
// problems...
// 1. PHP not installed (accomplished by index.html)
// 2. Data Base connection failure
// 3. Data Base selection failure
// 4. Uninitialized database failure
// These have now been modularized by jeff...
// In order to accomblish this jeff uses phpwebtools, so I have moved to check for php webtools
// to this file...

// Import settings from our global settings file
include_once('lib/settings.php');

if(file_exists(PHPWEBTOOLS_LOCATION . '/webtools.php')) {
	require_once(PHPWEBTOOLS_LOCATION . '/webtools.php');
} else die (
	"FreeMED requires that phpwebtools be installed at ".PHPWEBTOOLS_LOCATION."<br/>\n".
	"FreeMED cannot find the phpwebtools file webtools.php"."<br/>\n"
);

define('SKIP_SQL_INIT', true);
include_once ("lib/freemed.php");

// Deal with removing auth information from the session
unset($_SESSION['authdata']);

$test = CreateObject('FreeMED.FreeMEDSelfTest');

if (ALWAYS_SELFTEST) {
	$test->SelfTest();
}

// Unfortunately, we have to *manually* create the SQL object after selftest
if (!is_object($sql)) {
	$sql = CreateObject(
		'PHP.sql',
		DB_ENGINE,
		array(
			'host' => DB_HOST,
			'user' => DB_USER,
			'password' => DB_PASSWORD,
			'database' => DB_NAME
		)
	);
}

//----- Set page title
$page_title = PACKAGENAME . " - " . __("Login");

//----- Set no menu bar for login screen
$GLOBALS['__freemed']['no_menu_bar'] = true;

//----- *DON'T* Reset default facility session cookie

//----- Load template with main menu
include_once (freemed::template_file('login.php'));

//----- Finish display template
template_display();

?>
