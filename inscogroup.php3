<?php
  # file: inscogroup.php3
  # note: insurance company group(s) functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name="inscogroup.php3"; // for help info, later
  include ("global.var.inc");
  include ("freemed-functions.inc"); // API

  if ($action=="add") {
    $_refresh_location="$base_href$page_name?$_auth&action=view";
  }

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top();
  freemed_display_banner();

if ($action=="add") {

  freemed_display_box_top("$Adding Group", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $query = "INSERT INTO $database.inscogroup VALUES ( ".
    "'$inscogroup',   NULL ) ";

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
      <B>done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
     ><$STDFONT_B>Return to the Insurance Groups Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom();

  echo "
    <P>
    <CENTER>
    <A HREF=\"main.php3?$_auth\">Return to the
     Main Menu</A>
    </CENTER>
  "; // page footer

} elseif ($action=="modform") {

  freemed_display_box_top ("Modify Insurance Company Group", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >Return to the Main Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  # if there _IS_ an ID tag presented, we must extract the record
  # from the database, and proverbially "fill in the blanks"

  $result = fdb_query("SELECT * FROM $database.inscogroup ".
    "WHERE ( id = '$id' )");

  if ($debug) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]
  $inscogroup = $r["inscogroup"];

  echo "
    <BR><BR>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>Group Name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscogroup SIZE=20 MAXLENGTH=20
     VALUE=\"$inscogroup\">
    <P>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" Update \">
    <INPUT TYPE=RESET  VALUE=\"Remove Changes\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >Abandon Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

  freemed_display_box_top ("$Modifying Insurance Co. Group", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  $query = "UPDATE $database.inscogroup SET ".
    "inscogroup = '$inscogroup' ". 
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
      <B>done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  freemed_display_box_bottom ();

} elseif ($action=="del") {

  freemed_display_box_top ("Deleted Insurance Co. Group", $page_name);

  $result = fdb_query("DELETE FROM $database.inscogroup
    WHERE (id = '$id')");

  echo "
    <P>
    <I>Group <B>$id</B> deleted<I>.
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
     >Update/Delete Another</A></CENTER>
  ";
  freemed_display_box_bottom ();

} else {

  $query = "SELECT * FROM $database.inscogroup ".
   "ORDER BY inscogroup";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("Insurance Company Groups", $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    echo "
      <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
       CELLSPACING=0 CELLPADDING=3 VALIGN=TOP>
      <TR BGCOLOR=#000000>
      <TD ALIGN=LEFT>&nbsp;</TD>
      <TD WIDTH=30%>&nbsp;</TD>
      <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
       ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
       SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
      </TR></TABLE>
    ";

    echo "
      <BR>
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>Group Name</B></TD>
       <TD><B>Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ($_alternate);

    while ($r = fdb_fetch_array($result)) {

      $inscogroup = $r["inscogroup"];
      $i_id       = $r["id"        ];

      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$i_id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      if (($id>0) AND ($id==$i_id)) {
        echo "
          <TR BGCOLOR=$_alternate>
          <TD><FORM ACTION=\"$page_name\" METHOD=POST>
          <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\">
          <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
          <INPUT TYPE=TEXT NAME=\"inscogroup\" VALUE=\"$inscogroup\"
           SIZE=20 MAXLENGTH=30>
          </TD>
          <TD><INPUT TYPE=SUBMIT VALUE=\"modify\"></FORM>
        ";
      } else    // otherwise show regular
        echo "
          <TR BGCOLOR=$_alternate>
          <TD>$inscogroup</TD>
          <TD><A HREF=
           \"$page_name?$_auth&id=$i_id&action=view\"
           ><FONT SIZE=-1>MOD$id_mod</FONT></A>
        ";
      if ((freemed_get_userlevel($LoginCookie)>=$delete_level) and ($id<1)) {
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$i_id&action=del\"
          ><FONT SIZE=-1>DEL$id_mod</FONT></A>
        "; // show delete
      }
      echo "
        </TD></TR>
      ";

    } // while there are no more

      // now, we put the add table part...

    $_alternate = freemed_bar_alternate_color ($_alternate);

    #if ((strlen($id)<1) OR ($id<1)) 
      echo "
        <TR BGCOLOR=$_alternate VALIGN=CENTER>
        <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
         ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
         <INPUT NAME=\"inscogroup\" LENGTH=20 MAXLENGTH=30></TD>
        <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"ADD\"></FORM></TD>
        </TR></TABLE>
  
        <BR>
      ";
    #else echo "
    #    </TABLE> 
    #  ";

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

    freemed_display_box_bottom ();

  } else {
    echo "\n<B>no groups found with that criteria.</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!

freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
