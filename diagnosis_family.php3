<?php
 // file: diagnosis_family.php3
 // note: diagnosis family module
 // code: jeff b (jeff@univrel.pr.uconn.edu) -- template
 // lic : GPL, v2

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="diagnosis_family.php3"; // for help info, later
  $db_name  ="diagfamily";            // get this from jeff
  $record_name="Diagnosis Family";    // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="dfname, dfdescrip";   // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")
  $separate_add_section=false;        // if you need the addform action
                                      // keep this, if not, set to false

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
    <P>
    <$STDFONT_B>"._("Adding")." . . . 
  ";

    // build the query to database backend (usually MySQL):
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $db_name VALUES ( 
    '".prepare($dfname)."',
    '".prepare($dfdescrip)."',
    NULL ) ";

    // query the db with new values
  $result = $sql->query($query);

  if ($result) {
    echo "
      <B>"._("done").".</B><$STDFONT_E>
    ";
  } else {
    echo "<B>"._("ERROR")." ($result)</B>\n"; 
  }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom (); // display the bottom of the box
  break; // end action add

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
  $result = $sql->query("SELECT * FROM $db_name ".
    "WHERE ( id = '$id' )");

  $r = $sql->fetch_array($result); // dump into array r[]
  extract ($r);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    <CENTER><TABLE CELLSPACING=0 CELLPADDING=3 BORDER=0>

    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Name")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"dfname\" SIZE=20 MAXLENGTH=100
      VALUE=\"".prepare($dfname)."\"></TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Description")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"dfdesrip\" SIZE=30 MAXLENGTH=100
      VALUE=\"".prepare($dfdescrip)."\"></TD>
    </TR>

    <TR>
     <TD ALIGN=CENTER COLSPAN=2>
     <CENTER>
      <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
      <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
     </CENTER>
     </TD>
    </TR>
    </TABLE></CENTER>

    </FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
  break; // end action modform

 case "mod":
  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P>
    <$STDFONT_B>"._("Modifying")." . . . 
  ";

  $query = "UPDATE $db_name SET ".
    "dfname    = '".prepare($dfname)."',    ".
    "dfdescrip = '".prepare($dfdescrip)."'  ". 
    "WHERE id='$id'";

  $result = $sql->query($query); // execute query

  if ($result) {
    echo "
      <B>"._("done").".</B><$STDFONT_E>
    ";
  } else {
    echo "<B>"._("ERROR")." ($result)</B>\n"; 
  } // end of error reporting clause

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display box bottom 
  break; // end action mod

 case "del":
  freemed_display_box_top (_("Deleting")." "._($record_name));

    // select only "id" record, and delete
  $result = $sql->query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <$STDFONT_B>"._("Deleting")." ... 
  ";
  if ($result) echo _("done");
   else echo _("ERROR");
  echo "
    <$STDFONT_E>
    <P>
    
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();

 default:
  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = $sql->query($query);
  if ($result) {
    freemed_display_box_top (_($record_name));
    echo freemed_display_itemlist (
      $result,
      $page_name,
      array (
        _("Name")		=>	"dfname",
        _("Description")	=>	"dfdescrip"

      ),
      array ("", _("NO DESCRIPTION")), "", "t_page"
    );

    $_alternate = freemed_bar_alternate_color ($_alternate);
    echo "
     <CENTER>
     <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
     <TABLE CELLSPACING=0 CELLPADDING=3 BORDER=0>

      <TR><TD ALIGN=RIGHT>
       <$STDFONT_B>"._("Name")." : <$STDFONT_E>
      </TD><TD ALIGN=LEFT> 
       <INPUT TYPE=TEXT NAME=\"dfname\" SIZE=20
        MAXLENGTH=100>
      </TD></TR>

      <TR><TD ALIGN=RIGHT>
       <$STDFONT_B>"._("Description")." : <$STDFONT_E>
      </TD><TD ALIGN=LEFT> 
       <INPUT TYPE=TEXT NAME=\"dfdescrip\" SIZE=30
        MAXLENGTH=100>
      </TD></TR>
	
      <TR><TD COLSPAN=2 ALIGN=CENTER>
        <INPUT TYPE=SUBMIT VALUE=\""._("ADD")."\">
      </TD></TR>
      </TABLE>
      </FORM>
      </CENTER>
    ";
    freemed_display_box_bottom (); // display bottom of the box
  } 
  break; // end default case
} // end of master action switch 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
