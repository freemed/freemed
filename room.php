<?php
 // $Id$
 // note: room database functions
 // lic : GPL, v2

  $page_name="room.php"; // for help info, later
  $record_name="Room";
  include "global.var.inc";
  include "freemed-functions.inc"; // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

switch ($action) {

 case "addform": case "modform":
  switch ($action) { // inner switch
    case "addform":
      // do nothing
     break; // end of addform

    case "modform":
     $r = freemed_get_link_rec ($id, "room");
     extract ($r);
     break; // end of modform 
  } // end inner switch

  freemed_display_box_top (_("Add")." "._($record_name));

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
     ( ($action=="addform") ? "add" : "mod" )."\">
  
  ".form_table ( array (
    _("Room Name") =>
    "<INPUT TYPE=TEXT NAME=\"roomname\" SIZE=20 MAXLENGTH=20
     VALUE=\"".prepare($roomname)."\">",

    _("Location") =>
    "<SELECT NAME=\"roompos\">
    ".freemed_display_facilities ($roompos, true)."
    </SELECT>",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"roomdescrip\" SIZE=20 MAXLENGTH=40
     VALUE=\"".prepare($roomdescrip)."\">",

    _("Default Provider") =>
    freemed_display_selectbox (
    fdb_query ("SELECT * FROM physician"),
    "#phylname#, #phyfname#",
    "roomdefphy"),

    _("Surgery Equipped") =>
    "<INPUT TYPE=CHECKBOX NAME=\"roomsurgery\" VALUE=\"y\"
     ".( ($roomsurgery=="y") ? "CHECKED" : "" ).">",

    _("Booking Enabled") =>
    "<INPUT TYPE=CHECKBOX NAME=\"roombooking\" VALUE=\"y\" 
     ".( ($roombooking=="y") ? "CHECKED" : "" ).">",

    _("IP Address") =>
    "<INPUT TYPE=TEXT NAME=\"roomipaddr\" SIZE=16 MAXLENGTH=15
     VALUE=\"".prepare($roomipaddr)."\">"

    )
   ); 

  echo "
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
      ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();
  break; // end of add/mod form

 case "add":
  freemed_display_box_top (_("Adding")." "._($record_name));

  echo "
    <P>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $icdng = $cur_date; // set to current date

  $query = "INSERT INTO room VALUES ( ".
    "'".addslashes($roomname)."',     ".
    "'".addslashes($roompos)."',      ".
    "'".addslashes($roomdescrip)."',  ".
    "'".addslashes($roomdefphy)."',   ".
    "'".addslashes($roomsurgery)."',  ".
    "'".addslashes($roombooking)."',  ".
    "'".addslashes($roomipaddr)."',   ".
    " NULL ) ";

  $result = fdb_query($query);

  if ($result) echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
   <P>
   <CENTER>
   <A HREF=\"$page_name?$_auth\">"._("back")."</A>
   </CENTER>
  ";

  freemed_display_box_bottom ();
  break; // end add action

 case "mod": // modify action
  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Modifying")." ... 
  ";

  $query = "UPDATE room SET ".
    "roomname    = '".addslashes($roomname)."',    ".
    "roompos     = '".addslashes($roompos)."',     ".
    "roomdescrip = '".addslashes($roomdescrip)."', ".
    "roomdefphy  = '".addslashes($roomdefphy)."',  ".
    "roomsurgery = '".addslashes($roomsurgery)."', ".
    "roombooking = '".addslashes($roombooking)."', ".
    "roomipaddr  = '".addslashes($roomipaddr)."'   ". 
    "WHERE id='".addslashes($id)."'";

  $result = fdb_query($query);

  if ($result) echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
   </CENTER>
   <P>
   <CENTER>
   <A HREF=\"$page_name?$_auth\">"._("back")."</A>
   </CENTER>
  ";

  freemed_display_box_bottom ();
  break;

 case "del": case "delete":
  freemed_display_box_top (_("Deleting")." "._($record_name));

  $result = fdb_query("DELETE FROM room WHERE (id = '".addslashes($id)."')");

  echo "
    <P>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
  ";
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end del/delete action

 default: // default action

  freemed_display_box_top (_($record_name));
  echo freemed_display_itemlist (
     fdb_query ("SELECT roomname,roomdescrip,id FROM room ORDER BY roomname"),
     $page_name,
     array (
	_("Name")		=>	"roomname",
	_("Description")	=>	"roomdescrip"
     ),
     array (
	"",
	_("NO DESCRIPTION")
     )
   );

  freemed_display_box_bottom ();
  break; // end of default case
} // end of master case switch

freemed_close_db (); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
