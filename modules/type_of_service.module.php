<?php
  // $Id$
  // note: type of service (TOS) database module
  // code: adam b (gdrago23@yahoo.com) -- modified a lot
  // lic : GPL, v2

if (!defined(__TYPEOFSERVICEMAINTENANCE_MODULE_PHP__)) {

define (__TYPEOFSERVICEMAINTENANCE_MODULE_PHP__, true);

class typeOfServiceMaintenance extends freemedModule {

	var $MODULE_NAME = "Type of Service Maintenance";
	var $MODULE_VERSION = "0.1";

	var $CATEGORY_NAME = "Database Maintenance";

	function typeOfServiceMaintenance () {
		// run constructor
		$this->freemedModule();
	}	

	function main() {
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

// hacked module begins here

  $page_name="module_loader.php";
  $db_name  ="tos";
  $record_name="Type of Service";
  $order_field="tosname,tosdescrip";



switch($action) {
 default:
 case "addform":
 case "modform":
  //freemed_display_box_top ( _( (($action=="addform") ? "Add" : "Modify")
  //                         ." $record_name"), $page_name);

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  
  if ($action=="modform") { 
    $result = fdb_query("SELECT tosname,tosdescrip FROM $db_name
                         WHERE ( id = '$id' )");

    $r = fdb_fetch_array($result); // dump into array r[]
    extract ($r);
  } // if loading values

  $query = "SELECT tosname,tosdescrip,id FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);

  if (strlen($_ref)<5) {
    $_ref="main.php3";
  } // if no ref, then return to home page...

  echo freemed_display_itemlist (
    $result,
    $page_name,
    array (
      _("Code") => "tosname",
      _("Description") => "tosdescrip"
    ),
    array ("", _("NO DESCRIPTION")), "", "t_page"
  );
  echo "
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
    ($action=="modform" ? "mod" : "add")."\">";
  if ($action=="modform") echo "
    <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">";

  echo "
   <TABLE WIDTH=\"100%\" BORDER=0 CELLPADDING=2 CELLSPACING=2>
   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Type of Service")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"tosname\" SIZE=20 MAXLENGTH=75
     VALUE=\"".prepare($tosname)."\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Description")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"tosdescrip\" SIZE=25 MAXLENGTH=200
     VALUE=\"".prepare($tosdescrip)."\">
   </TD></TR>

   <TR><TD ALIGN=CENTER COLSPAN=2>
    <INPUT TYPE=SUBMIT VALUE=\"".(
      ($action=="modform") ? _("Modify") : _("Add"))."\">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
    </FORM>
   </TD></TR>
   </TABLE>
  ";
  if ($action=="modform") echo "
    <P>
    <CENTER><$STDFONT_B>
    <A HREF=\"$page_name?$_auth&module=$module&action=view\"
     >"._("Abandon Modification")."</A>
    <$STDFONT_E></CENTER>
    ";

 break;
 
 case "add":
 case "mod":
 case "delete":
  switch($action) { // inner actionswitch
   case "add":
    //freemed_display_box_top(_("Adding $record_name"), $page_name);
    echo "
      <P ALIGN=CENTER>
      <$STDFONT_B>"._("Adding")." . . . 
    ";
    $query = "INSERT INTO $db_name VALUES ( ".
      "'$tosname', '$tosdescrip', '$cur_date', '$cur_date', NULL ) ";
   break;
   case "mod":
    //freemed_display_box_top (_("Modifying")." "._("$record_name"));
    echo "
      <P ALIGN=CENTER>
      <$STDFONT_B>"._("Modifying")." . . . 
    ";
    $query = "UPDATE $db_name SET ".
      "tosname    = '".prepare($tosname)."',    ".
      "tosdescrip = '".prepare($tosdescrip)."', ".
      "tosdtmod   = '".prepare($cur_date)."'    ". 
      "WHERE id='$id'";
   break;
   case "delete":
    //freemed_display_box_top (_("Deleting $record_name"), $page_name);
    echo "
      <P ALIGN=CENTER>
      <$STDFONT_B>"._("Deleting")." . . . 
    ";
    $query = "DELETE FROM $db_name WHERE (id = '".prepare($id)."')";
   break;
  } // inner actionswitch
  $result = fdb_query($query);
  if ($result) {
    echo "
      <B>"._("Done").".</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>"._("ERROR")." ($result)</B>\n"); 
  }
  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth&module=$module\"
     ><$STDFONT_B>"._("Return to $record_name Menu")."<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // readability fix 19990714

			 break;
		} // end master switch

	} // end of main function

} // end of class typeOfServiceMaintenance

register_module ("typeOfServiceMaintenance");

} // end of "if defined"

?>
