<?php
  # file: insurance_modifiers.php3
  # note: internal attributes for insurance companies
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

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

if ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $query = "INSERT INTO $db_name VALUES (
    '".addslashes($insmod).    "',
    '".addslashes($insmoddesc)."',
    NULL ) ";

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
    <CENTER><A HREF=\"$page_name?$_auth\"
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

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>Modifier : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"insmod\" SIZE=16 MAXLENGTH=15 
     VALUE=\"".prepare($r[insmod])."\">

    <$STDFONT_B>Description : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"insmoddesc\" SIZE=20 MAXLENGTH=50 
     VALUE=\"".prepare($r[insmoddesc])."\">

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

  $query = "UPDATE $db_name SET
    insmod     = '".addslashes($insmod).    "',
    insmoddesc = '".addslashes($insmoddesc)."'
    WHERE id='$id'";

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
  ";

  freemed_display_box_bottom (); // display box bottom 

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <BR><BR>
    <I>$record_name <B>$id</B> $Deleted<I>.
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

} else {

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    echo "
      <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
       CELLSPACING=0 CELLPADDING=3>
      <TR BGCOLOR=#000000>
      <TD ALIGN=LEFT>&nbsp;</TD>
      <TD WIDTH=30%>&nbsp;</TD>
      <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
       ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
       SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
      </TR></TABLE>
    ";

    // and comment this line:
    //freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>Modifier</B></TD>
       <TD><B>Description</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $id      = $r["id"     ];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      $id_mod = ( ($debug) ? " [$id]" : "" );

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>".prepare($r[insmod])."</TD>
        <TD>".prepare($r[insmoddesc])."</TD>
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

    $_alternate = freemed_bar_alternate_color ($_alternate);
    echo "
      <TR BGCOLOR=$_alternate VALIGN=CENTER>
      <TD VALIGN=CENTER><FORM ACTION=\"$page_name\" METHOD=POST
       ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
        <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">
       <INPUT TYPE=TEXT NAME=\"insmod\" SIZE=15
        MAXLENGTH=16></TD>
      <TD VALIGN=CENTER>
       <INPUT TYPE=TEXT NAME=\"insmoddesc\" SIZE=20
        MAXLENGTH=50></TD>
      <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"$lang_ADD\"></FORM></TD>
      </TR>
    ";

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    echo "
      <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
       CELLSPACING=0 CELLPADDING=3>
      <TR BGCOLOR=#000000>
      <TD ALIGN=LEFT>&nbsp;</TD>
      <TD WIDTH=30%>&nbsp;</TD>
      <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
       ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
       SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
      </TR></TABLE>
    ";

    freemed_display_box_bottom (); // display bottom of the box

  } else {
    echo "\n<B>$No_Record_Found</B>\n";
  }

} // end action if/then/else 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
