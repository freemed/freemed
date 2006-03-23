<?php
	// $Id$
	// desc: administrative import module for older databases

$page_name = "import.php";
include ("lib/freemed.php");

freemed::connect ();
$this_user = CreateObject('FreeMED.User');

if (!freemed::acl('admin', 'menu')) {
	//------HIPAA Logging
	$user_to_log=$_SESSION['authdata']['user'];
	if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"import.php|user $user_to_log attempt to access import failed, user does not have ADMIN privileges");}	
	trigger_error(__("You do not have access to import functions."), E_USER_ERROR);
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"import.php|user $user_to_log import access GLOBAL ACCESS");}	

switch ($action) {
 case "import":
  $page_title = __("Import Database");
  $display_buffer .= "
   <P>
   ".__("Importing Database")." \"$db\" ... 
  ";
  if (freemed_import_stock_data ($db)) { $display_buffer .= __("done");  }
   else                                { $display_buffer .= __("ERROR"); }
  $display_buffer .= "
   <P>
    <CENTER>
     <A HREF=\"$page_name\"
     >".__("Import Another Database")."</A> <B>|</B>
     <A HREF=\"admin.php\"
     >".__("Return to Administration Menu")."</A>
    </CENTER>
   <P>
  ";
  break;
 default:
  $page_title = __("Import Database");
  $display_buffer .= "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"import\">
    <P>
    ".__("Select Database to Import")." : 
    <SELECT NAME=\"db\">
  ";
  $m = freemed::module_tables();
  foreach ($m AS $k => $v) {
    $display_buffer .= "<option value=\"${v}\">${k} (${v})</option>\n";
  }
  $display_buffer .= "
    </SELECT>
    <P>
    <CENTER>
     <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Import")."\"/>
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"admin.php\"
     >".__("Return to Administration Menu")."</A>
    </CENTER>
    <P>
   </FORM>
  ";
  break;
} // end action switch

template_display();

?>
