<?php
 // $Id$
 // note: room equipment database
 // lic : GPL, v2

  $page_name="roomequip.php";         // for help info, later
  $db_name  ="roomequip";             // get this from jeff
  $record_name="Room Equipment";      // such as Room for Room module
                                      // or "CPT Modifiers" for cptmod
  $order_field="reqname,reqdescrip";  // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
  $separate_add_section=true;         // if you need the addform action
                                      // keep this, if not, set to false

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

switch ($action) {
 case "addform": case "modform":
  switch ($action) {
   case "addform":
    break;
   case "modform":
    $r = freemed_get_link_rec ($id, $db_name);
    extract ($r);
    break;
  } // interior switch
 
  freemed_display_box_top (_("Add")." "._($record_name));

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
     ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\"  >

  ".form_table ( array (
    _("Name") =>
    "<INPUT TYPE=TEXT NAME=\"reqname\" SIZE=20 MAXLENGTH=100
     VALUE=\"".prepare($reqname)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"reqdescrip\" SIZE=30
     VALUE=\"".prepare($reqdescrip)."\">"
   ) )."  

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".( ($action=="addform") ? _("Add") :
      _("Modify") )." \"  >
    <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("Abandon ".( ($action=="addform") ? "Addition" : "Modification" )).
      "</A>
    </CENTER>
  ";
  break; // end add/modform action

 case "add":
  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO $db_name VALUES ( ".
    "'".addslashes($reqname)."', '".addslashes($reqdescrip)."', ".
    "'$cur_date', '$cur_date',  NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
    <$STDFONT_E></CENTER>
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=addform\"
     ><$STDFONT_B>"._("Add Another")."<$STDFONT_E></A> <B>|</B>
    <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display the bottom of the box
  break; // end add action

 case "mod":

  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Modifying")." ... 
  ";

  $query = "UPDATE $db_name SET ".
    "reqname    = '".addslashes($reqname)."',    ".
    "reqdescrip = '".addslashes($reqdescrip)."', ".
    "reqdatemod = '$cur_date',  ". 
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
    <$STDFONT_E></CENTER>
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
  $result = fdb_query("DELETE FROM $db_name WHERE id='".addslashes($id)."'");

  echo "
    <P><CENTER>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
    </CENTER>
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end of del/delete action

 default:
  $query = "SELECT * FROM $db_name ORDER BY $order_field";

  $result = fdb_query($query);
  freemed_display_box_top (_($record_name));
  echo freemed_display_itemlist (
   fdb_query("SELECT * FROM $db_name ORDER BY $order_field"),
   $page_name,
   array (
     _("Name")		=>	"reqname",
     _("Description")	=>	"reqdescrip"
   ),
   array (
     "", _("NO DESCRIPTION")
   )
  );
  freemed_display_box_bottom (); // display bottom of the box
  break; // end of default action
} 

freemed_close_db(); 
freemed_display_html_bottom ();

?>
