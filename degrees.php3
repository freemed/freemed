<?php
  // file: degrees.php3
  // note: physician degrees database functions
  // code: jeff b (jeff@univrel.pr.uconn.edu)
  // lic : GPL

  $page_name="degrees.php3";        // for help info, later
  $record_name="Physician Degrees"; // actual name
  include "lib/freemed.php";         // global variables
  include "lib/API.php";  // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top();
  freemed_display_banner();

switch($action) {
 default:
 case "display":
 case "modform":
 case "addform":
  if ($action=="modform") {
    freemed_display_box_top(_("Modify")." "._($record_name));
    $result = fdb_query("SELECT * FROM degrees ".
      "WHERE ( id = '$id' )");
    $r = fdb_fetch_array($result); // dump into array r[]
    extract ($r);
  } else {
    freemed_display_box_top(_($record_name));
  } // modform fetching

  // display the table 
  $query = "SELECT * FROM degrees ".
    "ORDER BY degdegree,degname";
  $result = fdb_query($query);
  echo freemed_display_itemlist(
    $result,
    "degrees.php3", //$page_name,
    array (
      _("Degree") => "degdegree",
      _("Description") => "degname"
    ),
    array ( "", _("NO DESCRIPTION") ), "", "d_page"
  );
  
  echo "
   <CENTER>
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR><TD ALIGN=RIGHT>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".(($action=="modform") ? 
                                                   "mod" : "add")."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"_ref\" VALUE=\"".prepare($_ref)."\">

  ".(($action=="modform") ? "
    <$STDFONT_B>"._("Date Last Modified")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    $degdate
   </TD></TR>
   <TR><TD ALIGN=RIGHT>
  " : "")
  
  ."
    <$STDFONT_B>"._("Degree")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degdegree SIZE=11 MAXLENGTH=10
     VALUE=\"$degdegree\">
   </TD></TR>
 
  <TR><TD ALIGN=RIGHT>
   <$STDFONT_B>"._("Degree Description")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degname SIZE=30 MAXLENGTH=50
     VALUE=\"$degname\">
   </TD></TR>

   <TR><TD COLSPAN=2 ALIGN=CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"".($action=="modform" ? 
        _("Update") : _("Add"))." \">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
   </TD></TR>
  ";

  if ($action=="modform") 
    echo "
   <TR><TD COLSPAN=2 ALIGN=CENTER>
     <$STDFONT_B><A HREF=\"$page_name?$_auth\">".
       _("Abandon Modification")."</A><$STDFONT_E>
   </TD></TR>
    ";
    
  echo "
   </FORM>
   </TABLE>
   </CENTER>
    ";
  freemed_display_box_bottom ();

 break;
 
 case "add":
 case "mod":
 case "delete":
  switch($action) { // inner action switch
   case "add":
    freemed_display_box_top(_("Adding")." "._($record_name));
    echo "
      <P ALIGN=CENTER>
      <$STDFONT_B>"._("Adding")." . . . 
    ";
    $degdate = $cur_date; // set to current date
    $query = "INSERT INTO degrees VALUES ( ".
      "'$degdegree',  ".
      "'$degname',    ".
      "'$degdate',    ".
      " NULL ) ";
   break;
   case "mod":
    freemed_display_box_top(_("Modifying")." "._($record_name));
    echo "
      <P ALIGN=CENTER>
      <$STDFONT_B>"._("Modifying")." . . . 
    ";
    $query = "UPDATE degrees SET ".
      "degdegree ='$degdegree',  ".
      "degname   ='$degname',    ".
      "degdate   ='$cur_date'    ". 
      "WHERE id='$id'";
   break;
   case "delete":
    freemed_display_box_top (_("Deleting")." ". _($record_name));
    $query = "DELETE FROM degrees WHERE (id = \"$id\")";
    echo "
      <P ALIGN=CENTER>
      <$STDFONT_B>"._("Deleting")." . . . 
    ";
   break;
  } // inner action switch

  $result = fdb_query($query);

  if ($result) {
    echo "
      <B>"._("Done").".</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>"._("ERROR")." ($query)</B><$STDFONT_E>"); 
  }
  echo "
    <P>
    <CENTER><$STDFONT_B>
    <A HREF=\"$page_name?$_auth&_ref=$_ref\"
    >"._("Return to $record_name page")."</A>
    <$STDFONT_E></CENTER>
  ";
  freemed_display_box_bottom();
 break;
} // master action switch

freemed_close_db (); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
