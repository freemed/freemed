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
// 4. PHP webtools version failure (moved from lib/freemed.php)
// 5. Uninitialized database failure

include_once("/usr/share/phpwebtools/webtools.php");
CreateApplicationMap(array( 'FreeMED' => 'lib/class.*.php' ));
$check = CreateObject('FreeMED.HealthCheck');

include_once ("lib/freemed.php");

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
