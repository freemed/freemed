<?php
  # file: room.php3
  # note: room database functions
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name="room.php3"; // for help info, later
  $record_name="Room";
  include "global.var.inc";
  include "freemed-functions.inc"; // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

//
//  MAIN BODY OF MODULE
//
//  LARGE IF LOOP THROUGH ACTIONS, WITH DEFAULT BEING

if ($action=="addform") {

  freemed_display_box_top ("$Add $record_name", $page_name);

  if ($debug==1) {
    echo "
      date = ($cur_date)<BR> default_facility = $default_facility <BR>
    ";
  }
  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    
    <$STDFONT_B>$Room_Name<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=roomname SIZE=20 MAXLENGTH=20
     VALUE=\"$roomname\">
    <BR>

    <$STDFONT_B>$Location<$STDFONT_E>
    <SELECT NAME=\"roompos\">
  ";

  freemed_display_facilities ($roompos, true);

  echo "
    </SELECT>
    <BR>
 
    <$STDFONT_B>$Room_Description<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=roomdescrip SIZE=20 MAXLENGTH=40
     VALUE=\"$roomdescrip\">
    <BR>

    <$STDFONT_B>$Default_Physician<$STDFONT_E>
    <SELECT NAME=\"roomdefphy\">
  ";

  freemed_display_physicians ($roomdefphy);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>$Surgery_Equipped<$STDFONT_E>
    <INPUT TYPE=CHECKBOX NAME=roomsurgery $_surgery>
    <BR>

    <$STDFONT_B>$Is_Booking_Enabled<$STDFONT_E>
    <INPUT TYPE=CHECKBOX NAME=roombooking $_booking>
    <BR>

    <$STDFONT_B>$IP_Address ($For_Future_Use)<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=roomipaddr SIZE=16 MAXLENGTH=15
     VALUE=\"$roomipaddr\">
    <BR>
 
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();

} elseif ($action=="add") {

  freemed_display_box_top ("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $icdng = $cur_date; // set to current date

  $query = "INSERT INTO $database.room VALUES ( ".
    "'$roomname',    '$roompos',     ".
    "'$roomdescrip', '$roomdefphy',  ".
    "'$roomsurgery', '$roombooking', ".
    "'$roomipaddr',   NULL ) ";

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
  freemed_display_bottom_links ();

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a room!</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <P>
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

  # if there _IS_ an ID tag presented, we must extract the record
  # from the database, and proverbially "fill in the blanks"

  $result = fdb_query("SELECT * FROM $database.room ".
    "WHERE ( id = '$id' )");

  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

  $roomname     = $r["roomname"    ];
  $roompos      = $r["roompos"     ];
  $roomdescrip  = $r["roomdescrip" ];
  $roomdefphy   = $r["roomdefphy"  ];
  $roomsurgery  = $r["roomsurgery" ];
  $roombooking  = $r["roombooking" ];
  $roomipaddr   = $r["roomipaddr"  ];

  // this is for check boxes
  switch ($roomsurgery) {
    case "y":  $_surgery="CHECKED"; break;
    default:   $_surgery="";
  } // set default in config, in future...

  // this is for check boxes
  switch ($roombooking) {
    case "y":  $_booking="CHECKED"; break;
    default:   $_booking="";
  } // set default in config, in future...

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Room_Name<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=roomname SIZE=20 MAXLENGTH=20
     VALUE=\"$roomname\">
    <BR>

    <$STDFONT_B>$Location<$STDFONT_E>
    <SELECT NAME=\"roompos\">
  ";

  freemed_display_facilities ($roompos);

  echo "
    </SELECT>
    <BR>
 
    <$STDFONT_B>$Room_Description<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=roomdescrip SIZE=20 MAXLENGTH=40
     VALUE=\"$roomdescrip\">
    <BR>

    <$STDFONT_B>$Default_Physician<$STDFONT_E>
    <SELECT NAME=\"roomdefphy\">
  ";

  freemed_display_physicians ($roomdefphy);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>$Surgery_Equipped<$STDFONT_E>
    <INPUT TYPE=CHECKBOX NAME=roomsurgery $_surgery>
    <BR>

    <$STDFONT_B>$Is_Booking_Enabled<$STDFONT_E>
    <INPUT TYPE=CHECKBOX NAME=roombooking $_booking>
    <BR>

    <$STDFONT_B>$IP_Address ($For_Future_Use)<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=roomipaddr SIZE=16 MAXLENGTH=15
     VALUE=\"$roomipaddr\">
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

  $query = "UPDATE $database.room SET ".
    "roomname    ='$roomname',    ".
    "roompos     ='$roompos',     ".
    "roomdescrip ='$roomdescrip', ".
    "roomdefphy  ='$roomdefphy',  ".
    "roomsurgery ='$roomsurgery', ".
    "roombooking ='$roombooking', ".
    "roomipaddr  ='$roomipaddr'   ". 
    "WHERE id='$id'";

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
  } // end of error reporting clause

  freemed_display_box_bottom ();
  freemed_display_bottom_links ();

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

  $result = fdb_query("DELETE FROM $database.room
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
  ";
  if ($debug==1) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  } // debug code
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();

} else {

  // this is the _DEFAULT_
  // --- deal with it.

  $query = "SELECT * FROM $database.room ".
   "ORDER BY roomname";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$record_name ($For_Scheduling)", 
       $_ref, $page_name);

    freemed_display_actionbar($page_name);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$record_name</B></TD>
       <TD><B>$Description</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ($_alternate);

    while ($r = fdb_fetch_array($result)) {

        // possibility of allowing selection from master
        // package options database whether to use/display
        // ICD9 or ICD10 codes...?
    
      $roomname    = $r["roomname"];
      $roomdescrip = $r["roomdescrip"];
      $id          = $r["id"];

      if (empty($roomdescrip)) $roomdescrip = "&nbsp;";

      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$roomname</TD>
        <TD><I>$roomdescrip</I></TD> 
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie))
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

    freemed_display_actionbar($page_name); // show bar at the bottom
    freemed_display_box_bottom ();

  } else {
    echo "\n<B>$No_Records_Found.</B>\n";
  }

} 

freemed_close_db (); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
