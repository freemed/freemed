<?php
  // file: tos.php3
  // note: type of service (TOS) database module
  // code: guess-who-is-too-lazy-to-not-use-the-template
  //       jeff b (jeff@univrel.pr.uconn.edu) -- template
  //       adam b (gdrago23@yahoo.com) -- modified a lot
  // lic : GPL
  // 
  // please note that you _can_ remove the comments down below,
  // but everything above here should remain untouched. please
  // do _not_ remove my name or address from this file, since I
  // have worked very hard on it. the license must also always
  // remain GPL.                                     -- jeff b

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="tos.php3";              // for help info, later
  $db_name  ="tos";                   // get this from jeff
  $record_name="Type of Service";     // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="tosname,tosdescrip";  // what field the records are
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

// *** main action loop ***
// (default action is "view")

switch($action) {
 case "add":
  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P ALIGN=CENTER>
    <$STDFONT_B>$Adding . . . 
  ";

    // build the query to database backend (usually MySQL):
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $db_name VALUES ( ".
    "'$tosname', '$tosdescrip', '$cur_date', '$cur_date', NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // readability fix 19990714

  freemed_display_box_bottom (); // display the bottom of the box
 break;

 case "addform":
 case "modform":
  freemed_display_box_top ((($action=="addform") ? "$Add" : "$Modify")
                           ."$record_name", $page_name);

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  
  if ($action=="modform") { 
    $result = fdb_query("SELECT * FROM $db_name WHERE ( id = '$id' )");

    $r = fdb_fetch_array($result); // dump into array r[]

    $tosname    = $r["tosname"   ];
    $tosdescrip = $r["tosdescrip"];
  } // if loading values

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
    ($action=="modform" ? "mod" : "add")."\">";
  if ($action=="modform") echo "
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >";

  echo "
   <TABLE>
   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>Type of Service Name : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=tosname SIZE=20 MAXLENGTH=75
     VALUE=\"$tosname\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>Type of Service Description : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=tosdescrip SIZE=25 MAXLENGTH=200
     VALUE=\"$tosdescrip\">
   </TD></TR>

   <TR><TD ALIGN=CENTER COLSPAN=2>
    <INPUT TYPE=SUBMIT VALUE=\" Add/Modify \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </FORM>
   </TD></TR>
   </TABLE>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";
  break;
  case "mod":

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P ALIGN=CENTER>
    <$STDFONT_B>$Modifying . . . 
  ";

    // build update query:
    // only set the values that need to be
    // changed... for example, don't set the
    // creation date in a modify. also,
    // remember the commas...
  $query = "UPDATE $db_name SET ".
    "tosname    = '$tosname',    ".
    "tosdescrip = '$tosdescrip', ".
    "tosdtmod   = '$cur_date'    ". 
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // usability patch 19990714

  freemed_display_box_bottom (); // display box bottom 
 break;

 case "delete":
  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <BR>
    <$STDFONT_B>$record_name <B>$id</B> $Deleted.<$STDFONT_E>
    <BR>
  ";
  if ($debug==1) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
 break;

 default:
  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  freemed_display_box_top ($record_name, $_ref, $page_name);

  if (strlen($_ref)<5) {
    $_ref="main.php3";
  } // if no ref, then return to home page...

  echo freemed_display_itemlist (
    $result,
    "tos.php3",
    array (
      "Code" => "tosname",
      "Description" => "tosdescrip"
    ),
    array ("", "NO DESCRIPTION")
  );
    
    // now, we put the add table part...
    // uncomment if needed.

  echo "
   <TABLE CELLSPACING=0 CELLPADDING=3 BORDER=0>
    <TR BGCOLOR=".($_alt=freemed_bar_alternate_color($_alt))." VALIGN=CENTER>
    <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
     ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
     <INPUT TYPE=TEXT NAME=\"tosname\" SIZE=5
      MAXLENGTH=75></TD>
    <TD VALIGN=CENTER>
     <INPUT TYPE=TEXT NAME=\"tosdescrip\" SIZE=30
      MAXLENGTH=200></TD>
    <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"ADD\"></FORM></TD>
    </TR>
   </TABLE>
  ";

  if (strlen($_ref)<5) {
    $_ref="main.php3";
  } // if no ref, then return to home page...

  freemed_display_box_bottom (); // display bottom of the box
  break;
} // end master switch

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
