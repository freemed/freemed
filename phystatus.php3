<?php
 // file: phystatus.php3
 // note: physician status db functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL

  $page_name="phystatus.php3"; // for help info, later
  $record_name="Physician Status";

  include "global.var.inc";
  include "freemed-functions.inc"; // misc functions

  freemed_open_db ($LoginCookie); // user authentication
  freemed_display_html_top ();

switch ($action) {
 case "add":
  freemed_display_box_top (_("Adding")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO phystatus VALUES ( ".
    "'".addslashes($phystatus)."',   NULL ) ";

  $result = fdb_query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A>
    </CENTER>
  "; // page footer
  break; // end of mod action

 case "modform": 
  freemed_display_box_top (_("Modify")." "._($record_name));

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
     </CENTER>

     <P>
    ";

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"$page_name?$_auth\"
       >"._("back")."</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, "phystatus");
  extract ($r);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    ".form_table ( array (
      _("Status") =>
     "<INPUT TYPE=TEXT NAME=\"phystatus\" SIZE=20 MAXLENGTH=20
       VALUE=\"".prepare($phystatus)."\">"
    ) )."

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
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

  $query = "UPDATE phystatus SET ".
    "phystatus = '".addslashes($phystatus)."' ". 
    "WHERE id='".addslashes($id)."'";

  $result = fdb_query($query);

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A>
    </CENTER>
  "; // page footer 
  break; // end modform action

 case "del": case "delete":
  freemed_display_box_top (_("Deleting")." "._($record_name));

  $result = fdb_query("DELETE FROM phystatus WHERE id='".addslashes($id)."'");

  echo "
    <P><CENTER>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
    </CENTER>
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end del/delete action

 default:
  freemed_display_box_top ("$record_name", $_ref, $page_name); 
  echo freemed_display_itemlist (
    fdb_query("SELECT phystatus,id FROM phystatus ORDER BY phystatus"),
    $page_name,
    array (
      _("Status") => "phystatus" 
    ),
    array (
      ""
    )
  );
    
  echo "
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3>
    <TR BGCOLOR=\"".
      ($_alternate = freemed_bar_alternate_color ($_alternate))
    ."\" VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
     ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
     <INPUT NAME=\"phystatus\" LENGTH=20 MAXLENGTH=30></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR></TABLE>

    <P>
  ";

  freemed_display_box_bottom ();
  break; // end default action
} // end master switch

freemed_close_db (); 
freemed_display_html_bottom ();

?>
