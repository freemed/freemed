<?php
 // file: patient_status.php3
 // note: patient status functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name     = "patient_status.php3";
  $record_name   = "Patient Status";
  $db_name       = "ptstatus";

  include ("lib/freemed.php");
  include ("lib/API.php");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();

switch ($action) {
 case "add":
  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO $db_name VALUES ( 
           '".addslashes($ptstatus)."',
           '".addslashes($ptstatusdescrip)."',
            NULL ) ";

  $result = $sql->query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
   <$STDFONT_E>
   </CENTER>
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";

  freemed_display_box_bottom ();
  break; // end add action

 case "modform":
  freemed_display_box_top (_("Modifying")." "._($record_name));

  if (empty($id)) {
    echo "
     <P>
    ";

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php?$_auth\"
       >"._("back")."</A>
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

    _("Status") =>
    "<INPUT TYPE=TEXT NAME=\"ptstatus\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($ptstatus)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"ptstatusdescrip\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($ptstatusdescrip)."\">"

    ) )."

    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
     <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER>

    </FORM>
  ";
  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
  break; // end modform action

 case "mod":
  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Modifying")." ... 
  ";

  $query = "UPDATE $db_name SET 
     ptstatus        = '".addslashes($ptstatus)."',       
     ptstatusdescrip = '".addslashes($ptstatusdescrip)."'  
     WHERE id='".addslashes($id)."'";

  $result = $sql->query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
   <$STDFONT_E>
   </CENTER>
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";

  freemed_display_box_bottom ();
  break; // end mod action

 case "del": case "delete":
  freemed_display_box_top (_("Deleting")." "._($record_name));

  $result = $sql->query("DELETE FROM $db_name WHERE id='".addslashes($id)."'");

  echo "
    <P>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end del/delete action

 default:

  $query = "SELECT * FROM $db_name 
            ORDER BY ptstatusdescrip, ptstatus";

  $result = $sql->query($query);
  freemed_display_box_top (_($record_name));
  echo freemed_display_itemlist (
    $sql->query ("SELECT ptstatusdescrip,ptstatus,id FROM $db_name
      ORDER BY ptstatusdescrip,ptstatus"),
    $page_name,
    array (
      _("Status")	=>	"ptstatus",
      _("Description")	=>	"ptstatusdescrip"
    ),
    array (
      "", _("NO DESCRIPTION")
    )
  );  

  echo "
    <TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=3>
    <TR BGCOLOR=\"".
     ($_alternate = freemed_bar_alternate_color ($_alternate))
    ."\" VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$page_name\">
     <INPUT TYPE=HIDDEN NAME=\"_auth\" VALUE=\"".prepare($_auth)."\">
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
     <INPUT TYPE=TEXT NAME=\"ptstatus\" SIZE=3
      MAXLENGTH=2></TD>
    <TD VALIGN=CENTER>
     <INPUT TYPE=TEXT NAME=\"ptstatusdescrip\" SIZE=20
      MAXLENGTH=30></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR></TABLE>
    <P>
  ";

  freemed_display_box_bottom ();
  break; // end default action
} // end master switch 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
