<?php
  # file: scheduler.php3
  # note: scheduling module for freemed-project
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name = "scheduler.php3";
  include ("global.var.inc");
  include ("freemed-functions.inc");

    // function to see if in the past (returns 1)
  function date_in_the_past ($datestamp) {
    include ("global.var.inc");
    $y_c = substr ($cur_date, 0, 4);
    $m_c = substr ($cur_date, 5, 2);
    $d_c = substr ($cur_date, 8, 2);
    $y   = substr ($datestamp, 0, 4);
    $m   = substr ($datestamp, 5, 2);
    $d   = substr ($datestamp, 8, 2);
    if ($y<$y_c) return 1;
    elseif ($m<$m_c) return 1;
    elseif ($d<$d_c) return 1;
    else return 0; 
  }

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

if (strlen($selected_date)!=10) {
  $selected_date = $cur_date;
} // fix date if not correct

switch ($action) {
 case "select": // select date (show calendar for month)

  break;
 case "dayview": // dayview schedule for selected date

  freemed_display_box_top ("Calendar Day View", $page_name);

  $query = "SELECT * FROM $database.scheduler
    WHERE (dateof = '$selected_date')
    ORDER BY calhour,calminute"; // if not selected by loc
  $result = fdb_query ($query); // get results

    // get starting hour value
  if (freemed_config_value("calshr")=="") {
    $cur_hour = $cal_starting_hour - 1;
  } else {
    $cur_hour = freemed_config_value("calshr") - 1;
  } // end get starting hour value

    // get ending hour value
  if (freemed_config_value("calehr")=="") {
    $end_hour = $cal_ending_hour;
  } else {
    $end_hour = freemed_config_value("calehr");
  } // end getting ending hour value

  // starting tags for table...
  echo "
    <TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=3
     BGCOLOR=#777777><TR>
    <TD><CENTER>
     <FONT FACE=\"Arial, Helvetica, Verdana\" SIZE=-1>
     <B>TIME</B></FONT></CENTER></TD>
    <TD><CENTER>
     <FONT FACE=\"Arial, Helvetica, Verdana\" SIZE=-1>
     <B>APPOINTMENTS</B></FONT></CENTER></TD>
  ";

  $showed_calendar = 0; // have not showed calendar yet

  $_alternate = freemed_bar_alternate_color ();

  if (!$result) {
    // if the calendar has _not_ been shown (i.e. nothing has been
    // booked) we generate it anyways...
    echo "
      </TR>
    ";
    $_alternate = freemed_bar_alternate_color ();
    for ($i=$cur_hour;$i<=$end_hour;$i++) {
      $_alternate = freemed_bar_alternate_color ($_alternate);
      if ($i > 11) { 
        $ampm = "pm"; 
        if ($i>12) $ampm_t = $i - 12;
          elseif ($i==12) $ampm_t=$i;
      } else { $ampm = "am"; $ampm_t = $i;}
      echo "
        <TR BGCOLOR=$_alternate>
        <TD ALIGN=RIGHT VALIGN=TOP>
        <FONT FACE=\"Arial, Helvetica, Verdana\">
        <A HREF=\"$page_name?$_auth&action=addform&patient=$patient&hour=$i\"
        >$ampm_t $ampm</A></FONT></TD>
        </TR>
      ";
    } // end for loop (hours)
  } else { // if not, let's while...
  while ($row = fdb_fetch_array($result)) {
    $showed_calendar = 1; // now we have shown calendar
    while ($row["calhour"]>$cur_hour) { // if next hour...
      $cur_hour++; // increment current hour
      $_alternate = freemed_bar_alternate_color ($_alternate);
      if ($cur_hour > 11) { 
        $ampm = "pm"; 
        if ($cur_hour>12) $ampm_t = $cur_hour - 12;
          elseif ($cur_hour==12) $ampm_t=$i;
      } else { $ampm = "am"; $ampm_t = $cur_hour;}
      echo "
         &nbsp;
        </TR>
        <TR BGCOLOR=$_alternate>
        <TD ALIGN=RIGHT VALIGN=TOP>
        <FONT FACE=\"Arial, Helvetica, Verdana\">
        <A HREF=\"$page_name?$_auth&action=addform&patient=$patient&hour=$cur_hour\"
         >$ampm_t $ampm</A></FONT></TD>
        <TD ALIGN=LEFT>
      ";
    } // end while not this hour yet...

    // here is where we display the results
    $pat_lname = freemed_get_link_field ($row["patient"],
      "patient", "ptlname");
    $pat_fname = freemed_get_link_field ($row["patient"],
      "patient", "ptfname");
    $pat_num   = $row["patient"];

    // when CPT db is implemented, uncomment this...
    // $cpt_descrip = freemed_get_link_field ($row["calcptcode"],
    //  "cpt", "code--"); // put this in when

    $minutes = $row["calminute"];
    if (($minutes=="") OR ($minutes==0))
      { $minutes="00"; } // if top of the hour
    $id      = $row["id"];
    echo "
      <B>:$minutes</B> - <A HREF=
      \"patient.php3?action=modform&id=$pat_num\"
      >$pat_lname, $pat_fname</A><BR>
    ";
  } // while there are more results...
  if ($showed_calendar==0) {
    // if the calendar has _not_ been shown (i.e. nothing has been
    // booked) we generate it anyways...
    echo "
      </TR>
    ";
    $_alternate = freemed_bar_alternate_color ();
    for ($i=$cur_hour;$i<=$end_hour;$i++) {
      $_alternate = freemed_bar_alternate_color ($_alternate);
      if ($i > 12) { $ampm = "pm"; } else { $ampm = "am"; }
      echo "
        <TR BGCOLOR=$_alternate>
        <TD ALIGN=RIGHT VALIGN=TOP>
        <FONT FACE=\"Arial, Helvetica, Verdana\">
        <A HREF=\"$page_name?$_auth&action=addform&patient=$patient&hour=$i\"
        >$i $ampm</A></FONT></TD>
        </TR>
      ";
    } // end for loop (hours)
  } // end if calendar not shown
  } // end of master result loop
  // now, end calendar table...
  echo "
    </TABLE>
  ";
  freemed_display_box_bottom ();
  break;

 case "addform":
      // STAGE ONE:

      // BROWSE DATES ON THE CALENDAR TO DECIDE WHERE
      // AND WHAT DAY WE ARE LOOKING FOR...

  freemed_display_box_top ("Add Appointment", $_ref);
  $next = freemed_get_date_next ($selected_date);

  // calc next week
  $next_wk = $selected_date;
  for ($i=1;$i<=7;$i++)
    $next_wk = freemed_get_date_next ($next_wk);
  
  $prev = freemed_get_date_prev ($selected_date);

  // calc prev week
  $prev_wk = $selected_date;
  for ($i=1;$i<=7;$i++)
    $prev_wk = freemed_get_date_prev ($prev_wk);

  echo "
    <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2
     WIDTH=100% VALIGN=CENTER ALIGN=CENTER><TR>
    <TD ALIGN=LEFT><A HREF=
    \"$page_name?$_auth&action=addform&patient=$patient&selected_date=$prev_wk\"
    ><FONT FACE=\"Arial, Helvetica, Verdana\"
    >&lt;&lt;[WEEK]</FONT></A>&nbsp;<A HREF=
    \"$page_name?$_auth&action=addform&patient=$patient&selected_date=$prev\"
    ><FONT FACE=\"Arial, Helvetica, Verdana\"
    >&lt;[PREV]</FONT></A></TD>
    <TD ALIGN=RIGHT><A HREF=
    \"$page_name?$_auth&action=addform&patient=$patient&selected_date=$next\"
    ><FONT FACE=\"Arial, Helvetica, Verdana\"
    >[NEXT]&gt;</FONT></A>&nbsp;<A HREF=
    \"$page_name?$_auth&action=addform&patient=$patient&selected_date=$next_wk\"
    ><FONT FACE=\"Arial, Helvetica, Verdana\"
    >[WEEK]&gt;&gt;</FONT></A></TD>
    </TR>
    </TABLE><BR>

    <CENTER>
     <B><FONT FACE=\"Arial, Helvetica, Verdana\">
     Current Date is $selected_date
     </FONT></B>
    </CENTER>
    <BR>
  ";

  if (date_in_the_past($selected_date)==1)
    echo "
      <CENTER><I><FONT SIZE=-2 FACE=\"Arial, Helvetica, Verdana\"
      >this date occurs in the past</FONT></I></CENTER>
      <BR>
    ";

  if ($patient > 0) {
    switch ($type) {
     case "temp":
      $pt_lname = freemed_get_link_field ($patient, "callin",
        "cilname");
      $pt_fname = freemed_get_link_field ($patient, "callin",
        "cifname");
      break;
     case "pat":
     default:
      $pt_lname = freemed_get_link_field ($patient, "patient",
        "ptlname");
      $pt_fname = freemed_get_link_field ($patient, "patient",
        "ptfname");
    }
    echo "
      <CENTER><B>
      <FONT FACE=\"Arial, Helvetica, Verdana\">
      Current Patient: $pt_lname, $pt_fname
      </FONT></B></CENTER>
      <BR>
    ";
  }

  echo "
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"addform2\">
    <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
    <INPUT TYPE=HIDDEN NAME=\"type\"    VALUE=\"$type\">
    <INPUT TYPE=HIDDEN NAME=\"selected_date\"
     VALUE=\"$selected_date\">
 
    <TT>Room : </TT>
    <SELECT NAME=\"room\">
  ";
  freemed_display_rooms ($room);
  echo "
    </SELECT>

    <CENTER>
      <INPUT TYPE=SUBMIT VALUE=\"Check Room\">
    </CENTER>
    </FORM>
    <P>
  ";
  if ($type=="pat") echo "
    <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></CENTER>
    </P>
  ";
  freemed_display_box_bottom (); // show box bottom
  break;
 case "addform2":

      // STAGE TWO:

      // SHOW CALENDAR FOR ROOM(S) OR EVERYTHING, AND
      // FIND OUT WHAT TIME...

  freemed_display_box_top ("Add Apointment", $_ref);
  if ($room < 1) {
    echo "
      <CENTER>
      <B>No room selected</B>
      </CENTER>
      <P>
      <A HREF=\"$page_name?$_auth&patient=$patient&action=addform&type=$type\"
       ><$STDFONT_B>Try Again<$STDFONT_E></A> |
      <A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
      <P>
    ";
  } else { // if there is one selected, display name, etc
    $rm_name = freemed_get_link_field ($room, "room",
      "roomname");
    $rm_desc = freemed_get_link_field ($room, "room",
      "roomdescrip");

    if (strlen($rm_desc)<1) $rm_desc="";
    else $rm_desc="(".$rm_desc.")";

    if ($debug==1) $debug_var = "[$room]";

    echo "
      <CENTER><B>
      Room: $rm_name $rm_desc $debug_var
      <BR>
      Date: $selected_date
      </B></CENTER>
      <P>
    ";

    // now, find if it is "booked"
    if ($room > 0) { // only if it is specific
      $result = fdb_query ("SELECT * FROM $database.scheduler
        WHERE (calroom='$room' AND caldateof='$selected_date')
        ORDER BY calhour, calminute");
      if (fdb_num_rows ($result) < 1) {
        echo "
          <CENTER>
           <I>The selected room is free all day.</I>
          </CENTER>
          <BR>
        ";

        // display calendar here as well, but empty...

        echo "
          <TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=3
           BGCOLOR=#777777><TR>
          <TD COLSPAN=2><CENTER>
           <FONT FACE=\"Arial, Helvetica, Verdana\" SIZE=-1
            COLOR=#ffffff><B>TIME</B></FONT></CENTER></TD>
        ";

          // get starting hour value
        if (freemed_config_value("calshr")=="") {
          $cur_hour = $cal_starting_hour - 1;
        } else {
          $cur_hour = freemed_config_value("calshr") - 1;
        } // end get starting hour value

          // get ending hour value
        if (freemed_config_value("calehr")=="") {
          $end_hour = $cal_ending_hour;
        } else {
          $end_hour = freemed_config_value("calehr");
        } // end getting ending hour value


        $_alternate = freemed_bar_alternate_color ();
        for ($i=$cur_hour;$i<=$end_hour;$i++) {
          $_alternate = freemed_bar_alternate_color ($_alternate);
          if ($i > 11) { 
            $ampm = "pm"; 
            if ($i>12) $ampm_t = $i - 12;
              elseif ($i==12) $ampm_t=$i;
          } else { $ampm = "am"; $ampm_t = $i;}
          echo "
            <TR BGCOLOR=$_alternate>
            <TD ALIGN=RIGHT VALIGN=TOP>
            <FONT FACE=\"Arial, Helvetica, Verdana\">
            <A HREF=\"$page_name?$_auth&action=addform3&patient=$patient&hour=$i".
            "&minute=00&room=$room&selected_date=$selected_date&type=$type\"
            >$ampm_t $ampm</A></FONT></TD><TD ALIGN=CENTER>
          ";

          for ($j=15;$j<=45;$j+=15) {
            echo "
              <FONT FACE=\"Arial, Helvetica, Verdana\">
              <A HREF=\"$page_name?$_auth&action=addform3&patient=$patient&".
              "hour=$i&minute=$j&room=$room&selected_date=$selected_date&".
              "type=$type\"
              ><B>:$j</B></A></FONT>&nbsp;
            ";
          }

          echo "
            </TD></TR>
          "; // end row
        } // end for loop (hours)
        //echo "
        //  </TABLE>
        //";
      

      } else { // if there _are_ results, show calendar with other stuff.
        // here we display the calendar with times for
        // booking...
        // starting tags for table...
        $result = fdb_query ("SELECT * FROM $database.scheduler
          WHERE (calroom='$room' AND caldateof='$selected_date')
          ORDER BY calhour, calminute");
        echo "
          <TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=3
           BGCOLOR=#777777><TR>
          <TD><CENTER>
           <$STDFONT_B SIZE=-1>
           <B>TIME</B><$STDFONT_E></CENTER></TD>
           <TD><CENTER>
           <$STDFONT_B SIZE=-1>
           <B>APPOINTMENTS</B><$STDFONT_E></CENTER></TD>
        ";

          // get starting hour value
        if (freemed_config_value("calshr")=="") {
          $begin_hour = $cal_starting_hour;
        } else {
          $begin_hour = freemed_config_value("calshr");
        } // end get starting hour value
        $cur_hour = $begin_hour - 1; // start at the beginning

          // get ending hour value
        if (freemed_config_value("calehr")=="") {
          $end_hour = $cal_ending_hour;
        } else {
          $end_hour = freemed_config_value("calehr");
        } // end getting ending hour value

        $row = fdb_fetch_array ($result);
        while ($cur_hour<$end_hour) { // if next hour...
          $cur_hour++; // increment current hour
          $_alternate = freemed_bar_alternate_color ($_alternate);
          if ($cur_hour > 11) { 
            $ampm = "pm"; 
            if ($cur_hour>12) $ampm_t = $cur_hour - 12;
              elseif ($cur_hour==12) $ampm_t=$cur_hour;
          } else { $ampm = "am"; $ampm_t = $cur_hour; }
          echo "
            &nbsp;
            </TR>
            <TR BGCOLOR=$_alternate>
            <TD ALIGN=RIGHT VALIGN=TOP>
            <FONT FACE=\"Arial, Helvetica, Verdana\">
            <A HREF=\"$page_name?$_auth&action=addform3&patient=$patient&hour=$cur_hour&room=$room&type=$type\"
             >$ampm_t $ampm</A></FONT></TD>
            <TD ALIGN=LEFT>
          ";
        //} // end while not this hour yet...

        // when CPT db is implemented, uncomment this...
        // $cpt_descrip = freemed_get_link_field ($row["calcptcode"],
        //  "cpt", "code--"); // put this in when

        //$minutes = $row["calminute"];
        //if (($minutes=="") OR ($minutes==0))
        //   $minutes="00";  // if top of the hour
        //$id      = $row["id"];

        for ($j=15;$j<=45;$j+=15) {
          // now for some magic -- we need to determine if there are any appointments
          // that are running over this period of time...
          $prev_booked=false; // previously booked initially false
          $skew=$j;           // initial skew (how far off it has to be to conflict)
            // determine same hour conflicts
          if (fdb_num_rows(fdb_query("SELECT * FROM $database.scheduler WHERE
             (calroom='$room' AND calhour='$cur_hour' AND
             (((calminute-($skew+1))>calduration) OR calminute='$j'))")) > 0)
               $prev_booked = true;
          for($k=($cur_hour-1);$k>=$begin_hour;$k--) {
            $skew += 60; // increase the skew by an hour
            if (fdb_num_rows(fdb_query("SELECT * FROM $database.scheduler WHERE
               (calroom='$room' AND calhour='$k' AND
                calminute>($skew - calminute))")) > 0) $prev_booked = true;
          } // end confict loop
          if (!$prev_booked) // if it is free...
           echo "
             <FONT FACE=\"Arial, Helvetica, Verdana\">
             <A HREF=\"$page_name?$_auth&action=addform3&patient=$patient&".
              "hour=$i&minute=$j&room=$room&selected_date=$selected_date&".
              "type=$type\"
             ><B>:$j</B></A><$STDFONT_E>&nbsp;
           ";
          else // otherwise just show the number with no link
           echo "
            <$STDFONT_B>
            <B>:$j</B><$STDFONT_E>&nbsp;
           ";
        } // end of for loop
      } // while there are more results...
      } //while
    echo "</TD></TR></TABLE>";
      
  } 
      // display full calendar for today... (all rooms)

    echo "
      <P>
      <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>Manage Patient<$STDFONT_E></CENTER>
      <P>
    ";

  } // end if...else for room (whether > 1 or not)

  freemed_display_box_bottom ();
  break;
 case "addform3":

      // STAGE THREE:

      // ACTUALLY BOOKING SOMETHING... REQUIRES ROOM, HOUR,
      // PATIENT NUMBER, PHYSICIAN, ETC... THIS IS THE
      // FINAL FORM.

   freemed_display_box_top ("Add Appointment", $_ref);

   if (strlen($room)>0) {
     $rm_name = freemed_get_link_field ($room, "room",
       "roomname");
     $rm_desc = freemed_get_link_field ($room, "room",
       "roomdescrip");

     if (strlen($rm_desc)<1) $rm_desc="";
     else $rm_desc="(".$rm_desc.")";
   } else {
     $rm_name = "NO PREFERENCE";
     $rm_desc = "";
   } // checking if room

   switch ($type) {
    case "temp":
     $pt_lname = freemed_get_link_field ($patient, "callin",
       "cilname");
     $pt_fname = freemed_get_link_field ($patient, "callin",
       "cifname");
     break;
    case "pat":
    default:
     $pt_lname = freemed_get_link_field ($patient, "patient",
       "ptlname");
     $pt_fname = freemed_get_link_field ($patient, "patient",
       "ptfname");
   }

   if ($hour > 11) { 
     $ampm = "pm"; 
     if ($hour>12) $ampm_t = $hour - 12;
       elseif ($hour==12) $ampm_t=12;
   } else { $ampm = "am"; $ampm_t = $hour;}
   
     // find default physician by room, if there is one
   if ($room!=0)
     if (freemed_get_link_field($room, "room", "roomdefphy")!=0)
       $physician = freemed_get_link_field($room, "room", "roomdefphy");

   if ($debug==1) $debug_var = "[$room]";
   echo "
     <FORM ACTION=\"$page_name\">
     <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"add\">
     <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"$patient\">
     <INPUT TYPE=HIDDEN NAME=\"room\"    VALUE=\"$room\">
     <INPUT TYPE=HIDDEN NAME=\"type\"    VALUE=\"$type\">
     <INPUT TYPE=HIDDEN NAME=\"selected_date\" VALUE=\"$selected_date\">
     <INPUT TYPE=HIDDEN NAME=\"hour\"    VALUE=\"$hour\">
     <INPUT TYPE=HIDDEN NAME=\"minute\"  VALUE=\"$minute\">

     <B>Room</B>:    $rm_name $rm_desc<BR>
     <B>Patient</B>: $pt_lname, $pt_fname<BR>
     <B>Date</B>:    $selected_date<BR>
     <B>Time</B>:    $ampm_t $minute $ampm<BR>

     <B>Duration</B>:
      <SELECT NAME=\"duration\">
       <OPTION VALUE=\"15\" >15 min
       <OPTION VALUE=\"30\" >30 min
       <OPTION VALUE=\"45\" >45 min
       <OPTION VALUE=\"60\" >1 hour
       <OPTION VALUE=\"75\" >1h 15m
       <OPTION VALUE=\"90\" >1h 30m
       <OPTION VALUE=\"105\">1h 45m
       <OPTION VALUE=\"120\">2 hours
      </SELECT><BR>

     <B>Physician</B>:
      <SELECT NAME=\"physician\">
   ";

   freemed_display_physicians ($physician);

   echo "
      </SELECT>
     <BR>

     <B>Note</B>:
     <INPUT TYPE=TEXT NAME=\"note\" VALUE=\"\" SIZE=40 MAXLENGTH=100>

     <BR>
     <CENTER>
      <INPUT TYPE=SUBMIT VALUE=\" Commit Booking \">
     </CENTER>
     </FORM>
   ";
   freemed_display_box_bottom ();
  break;
 case "add":
  freemed_display_box_top ("Adding Appointment", $_ref);
  echo "Adding... ";
  $query = "INSERT INTO $database.scheduler VALUES (
    '$selected_date',
    '$type',
    '$hour',
    '$minute',
    '$duration',
    '$facility',
    '$room',     
    '$physician',
    '$patient',
    '$cptcode',
    '$status',
    '$note',
    '',
    NULL )";
  $result = fdb_query ($query);

  if ($debug==1) {
    echo "
      <BR>
      <B>RESULT</B>: $result
      <BR>
      <B>QUERY</B>: $query
      <BR>
    ";
  } // end debug...
  echo "
    done.
    <BR>
    <CENTER>
    <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
    </CENTER>
  ";

  freemed_display_box_bottom ();
  break;

 case "displayday": // DISPLAYS ONE DAY ON THE CALENDAR WITH ACCESS CHECKS
    // display header
  freemed_display_box_top ("Scheduler Day View", $_ref, $page_name);
  echo "
    <P>
    <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
  ";
  $query = "SELECT * FROM $database.scheduler WHERE caldateof='$cur_date'
    ORDER BY calhour, calminute";
  $result = fdb_query ($query);
  if (fdb_num_rows ($result) < 1) {
    echo "
      <TR><TD ALIGN=CENTER>
       <$STDFONT_B><I>No appointments today.</I><$STDFONT_E>
      </TD></TR>
      </TABLE>
      <P>
   ";
   if ($patient>0) // if there is a patient link
    echo "
      <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>Manage Patient<$STDFONT_E></A> |
       <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
       ><$STDFONT_B>Book an Appointment<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
    freemed_display_box_bottom ();
    break;
  } // end checking if there are any results
  $_alternate = freemed_bar_alternate_color ();
  $any_appointments = false;            // until there are, there aren't
  while ($r = fdb_fetch_array ($result)) {
    if (freemed_check_access_for_facility ($LoginCookie, $r["calfacility"])) {
      if (!$any_appointments) // if this is the first appointment...
        echo "
          <TR BGCOLOR=$_alternate>
           <TD><$STDFONT_B><B>Time</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>Patient</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>Doctor</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>Facility</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>Room</B><$STDFONT_E></TD>
          </TR>
        ";
      $any_appointments = true;         // now there are appointments
      $_alternate = freemed_bar_alternate_color ($_alternate); // rotate colors
      $ptid = $r["calpatient"];         // get patient id for links

       // time checking/creation if/else clause
      if ($r["calhour"]<12)
        $_time = $r["calhour"].":".$r["calminute"]." am";
      elseif ($r["calhour"]==12)
        $_time = $r["calhour"].":".$r["calminute"]." pm";
      else
        $_time = ($r["calhour"]-12).":".$r["calminute"]." pm";

       // prepare the patient and physician names
      $ptlname = freemed_get_link_field ($r["calpatient"], "patient",
                 "ptlname"); // patient last name
      $ptfname = freemed_get_link_field ($r["calpatient"], "patient",
                 "ptfname"); // patient first name
      $phylname = freemed_get_link_field ($r["calphysician"],
                 "physician", "phylname"); // physician last name
      $phyfname = freemed_get_link_field ($r["calphysician"],
                 "physician", "phyfname"); // physician first name

       // get facility and room names
      $psrname = freemed_get_link_field ($r["calfacility"],
                 "facility", "psrname");
      $roomname = freemed_get_link_field ($r["calroom"],
                  "room", "roomname");
      echo "
        <TR BGCOLOR=$_alternate>
         <TD><$STDFONT_B>$_time<$STDFONT_E></TD>
         <TD><$STDFONT_B><A HREF=\"manage.php3?$_auth&id=$ptid\"
          >$ptlname, $ptfname</A><$STDFONT_E></TD>         
         <TD><$STDFONT_B>$phylname, $phyfname<$STDFONT_E></TD>         
         <TD><$STDFONT_B>$psrname<$STDFONT_E></TD>         
         <TD><$STDFONT_B>$roomname<$STDFONT_E></TD>
        </TR>
      ";
      } // if there is something here
  } // end the universal while loop
  if (!$any_appointments)
    echo "
      <TR><TD ALIGN=CENTER>
       <$STDFONT_B><I>No appointments today.</I><$STDFONT_E>
      </TD></TR>
      </TABLE>
      <P>
    ";
  else echo "
    </TABLE>
    <P>
  ";
  if ($patient>0) // if there is a patient link
    echo "
      <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>Manage Patient<$STDFONT_E></A> |
       <A HREF=\"$page_name?$_auth&patient=$patient&action=addform\"
       ><$STDFONT_B>Book an Appointment<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
  freemed_display_box_bottom ();
  break;

 default: // default action -- probably a menu
  echo "
    <B>CALENDAR VIEW</B>
    <P>
    Not ready yet -- remind me to finish this.
  ";
  break;
} // end master switch

  freemed_close_db (); // close the db
  freemed_display_html_bottom (); // show bottom of HTML code

?>
