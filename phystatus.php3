<?php
  # file: phystatus.php3
  # note: physician status db functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name="phystatus.php3"; // for help info, later
  $record_name="Physician Status";
  include "global.var.inc";

  if ($action=="add") {
    $_refresh_location="$base_href$page_name?$_auth&action=view";
  }

  include "freemed-functions.inc"; // misc functions

  freemed_open_db ($LoginCookie); // user authentication
  freemed_display_html_top ();
  freemed_display_banner ();

if ($action=="add") {

  freemed_display_box_top ("$Adding $record_name", $_ref, $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $query = "INSERT INTO $database.phystatus VALUES ( ".
    "'$phystatus',   NULL ) ";

  $result = fdb_query($query);

  if ($debug==1) {
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

  freemed_display_box_bottom ();

  if ((strlen($_ref)<5) OR ($_ref=="main.php3")) {
    echo "
      <BR><BR>
      <CENTER><A HREF=\"$page_name?$_auth&action=view\"
       >$Return_to $record_name $Menu</A>
      </CENTER>
    ";
  } else {
    echo "
      <P>
      <CENTER><A HREF=\"$_ref?$_auth\"
       >$Return_to_Previous_Menu</A>
      </CENTER>
    ";
  }
  echo "
    <P>
    <CENTER>
    <A HREF=\"main.php3?$_auth\"
     >$Return_to_the_Main_Menu</A>
    </CENTER>
  "; // page footer

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_top ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  # if there _IS_ an ID tag presented, we must extract the record
  # from the database, and proverbially "fill in the blanks"

  $result = fdb_query("SELECT * FROM $database.phystatus ".
    "WHERE ( id = '$id' )");

  if ($debug) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]
  $phystatus = $r["phystatus"];

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Status<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=phystatus SIZE=20 MAXLENGTH=20
     VALUE=\"$phystatus\">
    <BR>

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
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

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $database.phystatus SET ".
    "phystatus = '$phystatus' ". 
    "WHERE id='$id'";

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
    echo ("<B>ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  freemed_display_box_bottom ();

  if ((strlen($_ref)<5) OR ($_ref==$page_name)) {
    echo "
      <P>
      <CENTER><A HREF=\"$page_name?$_auth&action=view\"
       >$Return_to $record_name $Menu</A>
      </CENTER>
    ";
  } else {
    echo "
      <P>
      <CENTER><A HREF=\"$_ref?$_auth\"
       >$Return_to_Previous_Menu</A>
      </CENTER>
    ";
  }
  echo "
    <P>
    <CENTER>
    <A HREF=\"main.php3?$_auth\"
     >$Return_to_the_Main_Menu</A>
    </CENTER>
  "; // page footer 

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

  $result = fdb_query("DELETE FROM $database.phystatus
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
    <BR>
  ";
  if ($debug) {
    echo "
      <BR><B>$RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();

  if ((strlen($_ref)<5) OR ($_ref==$page_name)) {
    echo "
      <P>
      <CENTER><A HREF=\"$page_name?$_auth\"
       >$Return_to $record_name $Menu</A>
      </CENTER>
    ";
  } else {
    echo "
      <P>
      <CENTER><A HREF=\"$_ref?$_auth\"
       >$Return_to_Previous_Menu</A>
      </CENTER>
    ";
  }
  echo "
    <P>
    <CENTER>
    <A HREF=\"main.php3?$_auth\"
     >$Return_to_the_Main_Menu</A>
    </CENTER>
  "; // page footer

} else {

  $query = "SELECT * FROM $database.phystatus ".
   "ORDER BY phystatus";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name", $_ref, $page_name); 

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
       <TD><B>$Status</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ($_alternate);

    while ($r = fdb_fetch_array($result)) {

      $phystatus = $r["phystatus"];
      $id        = $r["id"       ];

      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$phystatus</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level) {
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show delete
      }
      echo "
        </TD></TR>
      ";

    } // while there are no more

      // now, we put the add table part...

    $_alternate = freemed_bar_alternate_color ($_alternate);

    echo "
      <TR BGCOLOR=$_alternate VALIGN=CENTER>
      <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
       ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
       <INPUT NAME=\"phystatus\" LENGTH=20 MAXLENGTH=30></TD>
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
    echo "\n<B>$No_Record_Found</B>\n";
  }
} 

freemed_close_db (); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
