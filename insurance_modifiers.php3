<?php
 // file: insurance_modifiers.php3
 // note: internal attributes for insurance companies
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name="insurance_modifiers.php3"; // for help info, later
  $db_name  ="insmod";
  $record_name="Insurance Modifiers";
  $order_field="insmoddesc";
  
    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

// *** main action loop ***
// (default action is "view")

switch ($action) {
 case "add":
  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO $db_name VALUES (
    '".addslashes($insmod).    "',
    '".addslashes($insmoddesc)."',
    NULL ) ";

  $result = fdb_query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
    <$STDFONT_E>
    </CENTER>
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display the bottom of the box
  break; // end add action

 case "modform":
  freemed_display_box_top (_("Modify")." "._($record_name));

  if (empty($id)) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <P>
    ";

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >"._("Return to the Main Menu")."</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, $db_name);
  extract ($r);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    ".form_table ( array (

    _("Modifier") =>
    "<INPUT TYPE=TEXT NAME=\"insmod\" SIZE=16 MAXLENGTH=15 
     VALUE=\"".prepare($insmod)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"insmoddesc\" SIZE=20 MAXLENGTH=50 
     VALUE=\"".prepare($insmoddesc)."\">"

    ) )."

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
     ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("Abandon ".( ($action=="addform") ? "Addition" : "Modification" )).
     "</A>
    </CENTER>
  ";
  break; // end add/mod form

 case "mod":
  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $db_name SET
    insmod     = '".addslashes($insmod).    "',
    insmoddesc = '".addslashes($insmoddesc)."'
    WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
    <$STDFONT_E>
    </CENTER>
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display box bottom 
  break; // end mod action

 case "del": case "delete": 
  freemed_display_box_top (_("Deleting")." "._($record_name));

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name WHERE (id = '".prepare($id)."')");

  echo "
    <P><CENTER>
    <I>"._("Modifier")." <B>$id</B> "._("Deleted")."<I>.
    </CENTER>
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end action del/delete

 default: 
  $query = "SELECT * FROM $db_name ORDER BY $order_field";

  $result = fdb_query($query);
  freemed_display_box_top (_($record_name));

  echo freemed_display_itemlist (
   fdb_query("SELECT * FROM $db_name ORDER BY $order_field"),
   $page_name,
   array (
     _("Modifier")	=>	"insmod",
     _("Description")	=>	"insmoddesc"
   ),
   array (
     "",
     _("NO DESCRIPTION")
   )
  );  
  echo "
   <CENTER>
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
    <TR>
     <TD>"._("Modifier")."</TD>
     <TD>"._("Description")."</TD>
     <TD>&nbsp;</TD>
    </TR>
    <TR VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$page_name\" METHOD=POST
     ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
      <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">
     <INPUT TYPE=TEXT NAME=\"insmod\" SIZE=15
      MAXLENGTH=16></TD>
    <TD VALIGN=CENTER>
     <INPUT TYPE=TEXT NAME=\"insmoddesc\" SIZE=20
      MAXLENGTH=50></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR>
   </TABLE>
   </CENTER>
  ";
  freemed_display_box_bottom (); // display bottom of the box
  break; // end default action
} // end master switch

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
