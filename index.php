<?php
 // $Id$
 // $Author$
 // code: jeff b (jeff@ourexchange.net)
 // lic : GPL, v2

$page_name = "index.php";
include_once ("lib/freemed.php");

//----- Set page title
$page_title = PACKAGENAME . " - " . _("Login");

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
