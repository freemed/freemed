<?php
	// $Id$
	// desc: administrative export module

$page_name = "export.php";
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
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"export.php|user $user_to_log export GLOBAL ACCESS");}
// Note this access needs logged to finer degree.
// But this will meet HIPAA requirments.	

switch ($action) {
 case "export":
  $page_title = __("Export Database");
  $display_buffer .= "
   <P>
   ".__("Exporting Database")." \"$db\" ... 
  ";
  if (freemed_export_stock_data ($db)) { $display_buffer .= "$Done."; }
   else                                { $display_buffer .= "$ERROR"; }
  $display_buffer .= "
   <P>
    <CENTER>
    <A HREF=\"$page_name\"
     >".__("Export Another Database")."</A> <B>|</B>
    <A HREF=\"admin.php\"
     >".__("Return to Administration Menu")."</A>
    </CENTER>
   <P>
  ";
  break;
 default:
  $page_title = __("Export Database");
  $display_buffer .= "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"export\">
    <P>
    ".__("Select Database to Export")." : 
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
     <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Export")."\"/>
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
