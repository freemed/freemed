<?php
 // $Id$
 // note: main menu module
 // code: jeff b (jeff@univrel.pr.uconn.edu), max k <amk@span.ch>
 // lic : GPL

$page_name="main.php";
include ("lib/freemed.php");
include ("lib/API.php");

   // checking for _ref tag.... (19990607) 
 if ((strlen($_ref)>0) AND ($_ref != "main.php")) {
   SetCookie("_ref", "main.php", time()+$_cookie_expire);
      // set _ref cookie to be current menu...
 } // if there is a _ref cookie...

//----- Generic page opening stuff
freemed_open_db ();
$this_user = CreateObject('FreeMED.User');

//----- Set title (default, can be overridden in lib/template/*/main_menu.php)
$page_title = PACKAGENAME." "._("Main Menu");

//----- Load template with main menu
if (file_exists("./lib/template/".$template."/main_menu.php")) {
	include_once ("./lib/template/".$template."/main_menu.php");
} else {
	include_once ("./lib/template/default/main_menu.php");
}

//----- Finish display template
template_display();

?>
