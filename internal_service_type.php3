<?php
 // file: internal_service_type.php3
 // note: (description of this module here)
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="internal_service_type.php3"; // for help info, later
  $db_name  ="intservtype";
  $record_name="Internal Service Type";
  $order_field="intservtype";
  
    // *** includes section ***

  include ("lib/freemed.php");         // load global variables
  include ("lib/API.php");  // API functions

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

  $query = "INSERT INTO $db_name VALUES ( ".
    "'".addslashes($intservtype)."', NULL ) ";

  $result = fdb_query($query);

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

  freemed_display_box_bottom (); // display the bottom of the box
  break; // end of add action

 case "modform":
  freemed_display_box_top (_("Modify")." "._($record_name));

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <P>
    ";

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php?$_auth\"
       >"._("Return to the Main Menu")."</A>
      </CENTER>
    ";
    DIE("");
  }

    // grab record number "id"
  $result = fdb_query("SELECT * FROM $db_name WHERE
    (id='".addslashes($id)."')");

  $r = fdb_fetch_array($result); // dump into array r[]
  extract ($r);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    <CENTER>
    <$STDFONT_B>"._($record_name)." : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"intservtype\" SIZE=25 MAXLENGTH=50 
     VALUE=\"".prepare($intservtype)."\">
    </CENTER>
 
    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

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

  $query = "UPDATE $db_name SET intservtype='".addslashes($intservtype)."'
    WHERE id='".addslashes($id)."'";

  $result = fdb_query($query); // execute query

  if ($result) { echo "<B>"._("done").".</B>"; }
   else        { echo "<B>"._("ERROR")."</B>"; }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display box bottom 
  break; // end modform action

 case "del": case "delete":

  freemed_display_box_top (_("Deleting")." "._($record_name));

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name WHERE id='$id'");

  echo "
    <P><CENTER>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
    </CENTER><P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end del/delete action

 default:
  freemed_display_box_top (_($record_name));
  echo freemed_display_itemlist (
    fdb_query("SELECT * FROM $db_name ORDER BY $order_field"),
    $page_name,
    array (
      _($record_name)	=>	"intservtype"
    )
  );
 
  echo "
    <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
     CELLSPACING=0 CELLPADDING=3>
    <TR VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
     ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
     <INPUT TYPE=TEXT NAME=\"intservtype\" SIZE=20
      MAXLENGTH=50></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></FORM></TD>
    </TR>
    </TABLE>
  ";
  freemed_display_box_bottom (); // display bottom of the box
  break; // end default action
} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
