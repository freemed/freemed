<?php
  # file: phygroup.php3
  # note: physician groups, used for booking? and user levels
  # code: [used the template because of pure sloth]
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL
  # 
  # please note that you _can_ remove the comments down below,
  # but everything above here should remain untouched. please
  # do _not_ remove my name or address from this file, since I
  # have worked very hard on it. the license must also always
  # remain GPL.                                     -- jeff b

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="phygroup.php3";         // for help info, later
  $db_name  ="phygroup";              // get this from jeff
  $record_name="Physician Group";     // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="phygroupname";        // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** setting _ref cookie ***
    // if you are going to be "chaining" out from this
    // function and want users to be able to return to
    // it, uncomment this and it will set the cookie to
    // return people using the bar.
  //SetCookie("_ref", $page_name, time()+$_cookie_expire);

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

    // build the query to MySQL:
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $db_name VALUES ( ".
    "'$phygroupname',  ".
    "'$phygroupfac',   ".
    "'$phygroupdtadd', ".
    "'$phygroupdtmod', ".
    " NULL ) ";

    // query the db with new values
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
      <B>$Done.</B></TT>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  freemed_display_box_bottom (); // display the bottom of the box
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
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

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = fdb_query("SELECT * FROM $db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

    // this dumps the result of the query (the record to
    // be modified) into the variables with those names,
    // for easy use by us.
  $phygroupname  = $r["phygroupname" ];
  $phygroupfac   = $r["phygroupfac"  ];
  $phygroupdtadd = $r["phygroupdtadd"];
  $phygroupdtmod = $r["phygroupdtmod"];
  // and so on and so forth...

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Physician_Group_Name : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=phygroupname SIZE=20 MAXLENGTH=100
     VALUE=\"$phygroupname\">
    <BR>

    <$STDFONT_B>$Default_Facility : <$STDFONT_E>
    <SELECT NAME=\"phygroupfac\">
  ";

  freemed_display_facilities ($phygroupfac);

  echo "
    </SELECT>

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

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

    // build update query:
    // only set the values that need to be
    // changed... for example, don't set the
    // creation date in a modify. also,
    // remember the commas...

  $query = "UPDATE $db_name SET ".
    "phygroupname  = '$phygroupname', ".
    "phygroupfac   = '$phygroupfac',  ". 
    "phygroupdtmod = '$cur_date'      ".
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

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
  } // end of error reporting clause

  freemed_display_box_bottom (); // display box bottom 
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
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
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // if you would rather have the add form built onto the view
    // menu, uncomment the next few lines. 

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

    // and comment this line:
    //freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Group_Name</B></TD>
       <TD><B>$Default_Facility</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $phygroupname = $r["phygroupname"];
      $phygroupfac  = freemed_get_link_field (
        $r["phygroupfac"], "facility", "psrname");
      $id      = $r["id"     ];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$phygroupname</TD>
        <TD><I>$phygroupfac</I></TD>
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

      // now, we put the add table part...
      // uncomment if needed.

    $_alternate = freemed_bar_alternate_color ($_alternate);
    echo "
      <TR BGCOLOR=$_alternate VALIGN=CENTER>
      <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
       ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
       <INPUT TYPE=TEXT NAME=\"phygroupname\" SIZE=20
        MAXLENGTH=100></TD>
      <TD VALIGN=CENTER>
      <SELECT NAME=\"phygroupfac\">
    ";
    freemed_display_facilities ();
    echo "
      </SELECT></TD>
      <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"$lang_ADD\"></FORM></TD>
      </TR>
    ";

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    //  if you would rather have the add form built onto the view
    //  menu, just uncomment the next few lines for a bar without
    //  the add function...

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

    freemed_display_box_bottom (); // display bottom of the box
    freemed_display_bottom_links ($record_name, $page_name, $_ref);

  } else {
    echo "\n<B>$No_Record_Found</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
