<?php
  # file: roomequip.php3
  # note: room equipment database
  # code: mr-i-am-too-lazy-to-rewrite-anything-myself
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL, v2

  $page_name="roomequip.php3";        // for help info, later
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

if (($action=="addform") AND ($separate_add_section)) {

  freemed_display_box_top ("$Add $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Name_of_Equipment<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=reqname SIZE=20 MAXLENGTH=100
     VALUE=\"$reqname\">
    <BR>

    <$STDFONT_B>$Description<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=reqdescrip SIZE=30
     VALUE=\"$reqdescrip\">
    <BR>

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \"  >
    <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $query = "INSERT INTO $db_name VALUES ( ".
    "'".addslashes($reqname)."', '".addslashes($reqdescrip)."', ".
    "'$cur_date', '$cur_date',  NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=addform\"
     ><$STDFONT_B>Add Another<$STDFONT_E></A> <B>|</B>
    <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display the bottom of the box

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <P>
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, $db_name);

  $reqname    = fm_prep($r["reqname"]);
  $reqdescrip = fm_prep($r["reqdescrip"]);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Name_of_Equipment : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=reqname SIZE=20 MAXLENGTH=100
     VALUE=\"$reqname\">
    <BR>

    <$STDFONT_B>$Description : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=reqdescrip SIZE=30
     VALUE=\"$reqdescrip\">
    <BR>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $db_name SET ".
    "reqname    = '".addslashes($reqname)."',    ".
    "reqdescrip = '".addslashes($reqdescrip)."', ".
    "reqdatemod = '$cur_date',  ". 
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

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

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <BR><BR>
    <I>$record_name <B>$id</B> $deleted<I>.
    <BR>
  ";
  if ($debug) {
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

} elseif ($action=="export") {

  freemed_display_box_top ("$Export $record_name", $_ref, $page_name);

  echo "
    <P>
    <$STDFONT_B>$Exporting_data ...
  ";

  $result = freemed_export_stock_data ($db_name);

  if ($result) echo "$Done.";
   else echo "$ERROR";

  echo "
    <P>

    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>

    <P>
  ";

  freemed_display_box_bottom ();

} else {

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // and comment this line:
    freemed_display_actionbar();

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Equipment</B></TD>
       <TD><B><I>$Description</I></B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $reqname    = fm_prep($r["reqname"]);
      $reqdescrip = fm_prep($r["reqdescrip"]);
      $id        = $r["id"];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$reqname</TD>
        <TD><I>$reqdescrip</I>&nbsp;</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

    echo "
      </TABLE>
    ";

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // then comment this:
    freemed_display_actionbar ();
    freemed_display_box_bottom (); // display bottom of the box

  } else {
    echo "\n<B>no $record_name found with that criteria.</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
