<?php
  # file: patient_status.php3
  # note: patient status functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

  $page_name     = "patient_status.php3";
  $record_name   = "Patient Status";
  $db_name       = "ptstatus";

  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

if ($action=="add") {
  freemed_display_box_top("$Adding $record_name");

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $query = "INSERT INTO $db_name VALUES ( 
           '".addslashes($ptstatus)."',
           '".addslashes($ptstatusdescrip)."',
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
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
     ><$STDFONT_B>Return to $record_name Menu<$STDFONT_E></A>
    </CENTER>
   <P>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modifying $record_name");

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       CPT modifier!</B>
     </CENTER>

     <P>
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, $db_name);
  $ptstatus        = fm_prep($r["ptstatus"       ]);
  $ptstatusdescrip = fm_prep($r["ptstatusdescrip"]);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>Status : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptstatus\" SIZE=3 MAXLENGTH=2
     VALUE=\"$ptstatus\">
    <BR>

    <$STDFONT_B>Description : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"ptstatusdescrip\" SIZE=20 MAXLENGTH=30
     VALUE=\"$ptstatusdescrip\">
    <BR>

    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" $Update \">
     <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER>

    </FORM>
  ";
  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

  freemed_display_box_top ("$Modifying $record_name");

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $db_name SET 
     ptstatus        = '$ptstatus',       
     ptstatusdescrip = '$ptstatusdescrip'  
     WHERE id='$id'";

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
  } // end of error reporting clause

  echo "
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
     ><$STDFONT_B>Return to $record_name Menu<$STDFONT_E></A>
    </CENTER>
   <P>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="del") {
  freemed_display_box_top ("$Deleting $record_name", $_ref, $page_name);

  $result = fdb_query("DELETE FROM $db_name
                       WHERE id='$id'");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
    <P>
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

  $query = "SELECT * FROM $db_name 
            ORDER BY ptstatusdescrip, ptstatus";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name");

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
       SIZE=-1><B>$RETURN_TO_MENU</B></FONT></A></TD>
      </TR></TABLE>
    ";

    echo "
      <P>

      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>Status</B></TD>
       <TD><B>Description</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color (); // alternate bar color

    while ($r = fdb_fetch_array($result)) {

      $ptstatus        = $r["ptstatus"       ];
      $ptstatusdescrip = $r["ptstatusdescrip"];
      $id              = $r["id"             ];

      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$ptstatus</TD>
        <TD><I>$ptstatusdescrip</I>&nbsp;</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$MOD$id_mod</FONT></A>
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
      <TD VALIGN=CENTER><FORM ACTION=\"$page_name\">
       <INPUT TYPE=HIDDEN NAME=\"_auth\" VALUE=\"$_auth\">
       <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
       <INPUT TYPE=TEXT NAME=\"ptstatus\" SIZE=3
        MAXLENGTH=2></TD>
      <TD VALIGN=CENTER>
       <INPUT TYPE=TEXT NAME=\"ptstatusdescrip\" SIZE=20
        MAXLENGTH=30></TD>
      <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"$lang_ADD\"></FORM></TD>
      </TR></TABLE>

      <P>
    ";

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
       SIZE=-1><B>$RETURN_TO_MENU</B></FONT></A></TD>
      </TR></TABLE>
    ";

    freemed_display_box_bottom ();
  } else {
    echo "\n<B>no statuses found with that criteria.</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
