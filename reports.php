<?php
 // $Id$
 // note: reports modules
 // lic : GPL

$page_name = basename($GLOBALS["PHP_SELF"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"reports.php|user $user_to_log reports access GLOBAL ACCESS");}	

//----- Set page title
$page_title = __("Reports");

//----- Add page to history
page_push();

//----- Create user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

 // Check for appropriate access level
if (!freemed::acl('report', 'menu')) {
	trigger_error(__("You don't have access for this menu."), E_USER_ERROR);
} // end checking ACLs

//----- Load template with reports menu
if (file_exists("./lib/template/".$template."/reports_menu.php")) {
	include_once ("./lib/template/".$template."/reports_menu.php");
} else {
	include_once ("./lib/template/default/reports_menu.php");
}

//----- Show template
template_display();

?>
