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

/*
if(file_exists("/usr/share/phpwebtools/webtools.php")) {
	require_once("/usr/share/phpwebtools/webtools.php");
}else die (
	"FreeMED requires that phpwebtools be installed at /usr/share/phpwebtools."."<br/>\n".
	"FreeMED cannot find the phpwebtools file webtools.php"."<br/>\n"
);
*/

CreateApplicationMap(array( 'FreeMED' => 'lib/class.*.php' ));
$test = CreateObject('FreeMED.FreeMEDSelfTest');

include_once ("lib/freemed.php");

if (ALWAYS_SELFTEST) {
	$test->SelfTest();
}

//----- Set page title
$page_title = PACKAGENAME . " - " . __("Login");

//----- Set no menu bar for login screen
$GLOBALS['__freemed']['no_menu_bar'] = true;

//----- *DON'T* Reset default facility session cookie

//----- Load template with main menu
if (file_exists("./lib/template/".$template."/login.php")) {
	include_once ("./lib/template/".$template."/login.php");
} else {
	include_once ("./lib/template/default/login.php");
}

//----- Finish display template
template_display();

?>
