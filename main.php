<?php
 // $Id$
 // note: main menu module
 // code: jeff b (jeff@ourexchange.net), max k <amk@span.ch>
 // lic : GPL

$page_name = "main.php";
include_once ("lib/freemed.php");

// checking for _ref tag
if ((strlen($_ref)>0) AND ($_ref != "main.php")) {
	SetCookie("_ref", "main.php", time()+$_cookie_expire);
	// set _ref cookie to be current menu...
} // if there is a _ref cookie...

//----- Generic page opening stuff
freemed::connect ();

//----- HIPAA Logging
// This is too high level to log...

$this_user = CreateObject('FreeMED.User');

//----- Set title (default, can be overridden in lib/template/*/main_menu.php)
$page_title = PACKAGENAME." ".__("Main Menu");

//----- Load template with main menu
if (file_exists("./lib/template/".$template."/main_menu.php")) {
	include_once ("./lib/template/".$template."/main_menu.php");
} else {
	include_once ("./lib/template/default/main_menu.php");
}

//----- Finish display template
template_display();

?>
