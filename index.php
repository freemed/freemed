<?php
 // $Id$
 // $Author$
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

$page_name = "index.php";
include_once ("lib/freemed.php");
include_once ("lib/API.php");

//----- Set page title
$page_title = PACKAGENAME . " - " . _("Login");

//----- Set no menu bar for login screen
$no_menu_bar = true;

//----- Reset default facility cookie
SetCookie ("default_facility", "0", time()-100);

//----- Load template with main menu
if (file_exists("./lib/template/".$template."/login.php")) {
	include_once ("./lib/template/".$template."/login.php");
} else {
	include_once ("./lib/template/default/login.php");
}

//----- Finish display template
template_display();

?>
