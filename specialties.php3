<?php
  // file: specialties.php3
  // note: physician/provider specialties db
  // code: mr-jeff-is-too-lazy-to-not-use-the-template ;)
  //       jeff b (jeff@univrel.pr.uconn.edu) -- template
  //       adam b (gdrago23@yahoo.com) -- complete rewrite
  // lic : GPL, v2

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="specialties.php3";      // for help info, later
  $db_name  ="specialties";           // get this from jeff
  $record_name="Specialty";           // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="specname";            // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

switch($action) { // master action switch
 case "add":
 case "mod":
 case "delete":
  switch ($action) {
   case "add":
    freemed_display_box_top(_("Adding $record_name"), $page_name);
    echo "
      <P>
      <CENTER>
      <$STDFONT_B>"._("Adding").". . .
    ";
    $query = "INSERT INTO $db_name VALUES ( ".
      "'$specname', '$specdesc', '$cur_date',  NULL ) ";
   break;
   case "mod":
    freemed_display_box_top(_("Modifying $record_name"), $page_name);
    echo "
      <P>
      <CENTER>
      <$STDFONT_B>"._("Modifying").". . .
    ";
    $query = "UPDATE $db_name SET ".
      "specname = '$specname', ".
      "specdesc = '$specdesc'  ". 
      "WHERE id='$id'";
   break;
   case "delete":
    freemed_display_box_top(_("Deleting $record_name"), $page_name);
    echo "
      <P>
      <CENTER>
      <$STDFONT_B>"._("Deleting").". . .
    ";
    $query = "DELETE FROM $db_name WHERE id='$id'";
   break;
  } // inner action switch
  $result = fdb_query($query);
  if ($result) {
    echo "
      <B>"._("Done").".</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>"._("ERROR")." ($result)</B><$STDFONT_E>"); 
  }
  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("Return to $record_name Menu")."<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // readability fix 19990714

  freemed_display_box_bottom();
 break;

 default:
 case "addform":
 case "modform":
  switch($action) { // inner action
   case "addform": default:
    freemed_display_box_top(_("Add $record_name"), $page_name);
   break;
   case "modform":
    freemed_display_box_top (_("Modify $record_name"), $page_name);
    $result = fdb_query("SELECT * FROM $db_name ".
      "WHERE ( id = '$id' )");
    $r = fdb_fetch_array($result); // dump into array r[]
    $specname = $r["specname"];
    $specdesc = $r["specdesc"];
   break;
  } // inner action

  $list_q = "SELECT * FROM $db_name ";
  if (strlen($_s_val)>0)
    $list_q .= "WHERE $_s_field LIKE '%".fm_secure($_s_val)."%' ";
  $list_q .= "ORDER BY specname,specdesc ";
  $list_r = fdb_query($list_q);

  echo
   freemed_display_itemlist(
     $list_r,
     $page_name,
     array (
       _("Specialty") => "specname",
       _("Specialty Description") => "specdesc"
     ),
     array ("", _("NO DESCRIPTION")), "", "s_page"
   )."
   <CENTER>
   <TABLE>".
    (($action=="modform") ? "
      <FORM ACTION=\"$page_name\">
      <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
      <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >
      " : "
      <FORM ACTION=\"$page_name\">
      <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    ")."
      
   
   <TR><TD ALIGN=RIGHT>   
    <$STDFONT_B>"._("Specialty")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=specname SIZE=10 MAXLENGTH=50 
     VALUE=\"$specname\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>   
    <$STDFONT_B>"._("Specialty Description")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=specdesc SIZE=30 MAXLENGTH=100
     VALUE=\"$specdesc\">
   </TD></TR>
   
   <TR><TD ALIGN=CENTER COLSPAN=2>   
    <INPUT TYPE=SUBMIT VALUE=\"".(($action=="modform") ? 
      _("Update") : _("Add"))." \">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
    </FORM>
   </TD></TR>
   </TABLE>
   </CENTER>
  ";
  
  if ($action=="modform") echo "
    <CENTER><$STDFONT_B>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("Abandon Modification")."</A>
    <$STDFONT_E></CENTER>
  ";

  freemed_display_box_bottom (); // show the bottom of the box
} // master action switch

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
