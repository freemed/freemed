<?php
 // $Id$
 // note: show appointments
 // lic : GPL, v2

  $page_name = "show_appointments.php";
  include ("lib/freemed.php");
  include ("lib/API.php");
  include ("lib/calendar-functions.php");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

  if (strlen($selected_date)!=10) {
    $selected_date = $cur_date;
  } // fix date if not correct

  if ($show=="all") { $day_criteria = "0 = 0";                   }
   else             { $day_criteria = "caldateof = '$cur_date'"; }

    // generate object
  if ($patient>0) {
    $this_patient = new Patient ($patient, ($type=="temp"));
  } // end generating this_patient object

    // display header
  freemed_display_box_top (_("Show Appointments"));
  if ($patient>0) echo freemed_patient_box ($this_patient);
  echo "
    <P>
    <TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
  ";

  if ($patient>0) { 
    $qualifier = "(calpatient='".addslashes($patient)."')";
    switch ($type) {
      case "temp":
        $qualifier .= " AND (caltype='temp')";
        $master_patient_link_location =
          "call-in.php3?$_auth&action=display&id=$patient";
        break;
      case "pat": case "default":
        $qualifier .= " AND (caltype='pat')";
        $master_patient_link_location =
          "manage.php?$_auth&id=$patient";
        break;
    } // end switch
  } else { $qualifier = "0 = 0"; }

  $query = "SELECT * FROM scheduler WHERE (($day_criteria)
    AND ($qualifier)) ORDER BY caldateof, calhour, calminute";
  $result = fdb_query ($query);
  if ($debug) echo "query=\"$query\"";
  if (fdb_num_rows ($result) < 1) {
    echo "
      <TR><TD ALIGN=CENTER>
       <$STDFONT_B><I>"._("No appointments today.")."</I><$STDFONT_E>
      </TD></TR>
      </TABLE>
      <P>
   ";

   if ($patient>0) { // if there is a patient link
    echo "
      <CENTER><A HREF=\"$master_patient_link_location$patient\"
       ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> |
       <A HREF=\"book_appointment.php3?$_auth&patient=$patient&type=$type\"
       ><$STDFONT_B>"._("Book Appointment")."<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
    } else {
     echo "
      <CENTER><A HREF=\"main.php?$_auth\"
      ><$STDFONT_B>"._("Return to Main Menu")."<$STDFONT_E></A> |
      <A HREF=\"patient.php?$_auth\"
      ><$STDFONT_B>"._("Choose a Patient")."<$STDFONT_E></A>
      </CENTER>
      <P>
     ";
    }
    freemed_display_box_bottom ();
    freemed_display_html_bottom ();
    freemed_close_db ();
    DIE("");
  } // end checking if there are any results
  $any_appointments = false;            // until there are, there aren't
  while ($r = fdb_fetch_array ($result)) {
    if (freemed_check_access_for_facility ($LoginCookie, $r["calfacility"])) {
      if (!$any_appointments) // if this is the first appointment...
        echo "
          <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).
	   ">
           <TD><$STDFONT_B><B>"._("Time")."</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>"._("Patient")."</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>"._("Provider")."</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>"._("Facility")."</B><$STDFONT_E></TD>
           <TD><$STDFONT_B><B>"._("Room")."</B><$STDFONT_E></TD>
          </TR>
        ";
      $any_appointments = true;         // now there are appointments
      $ptid = $r["calpatient"];         // get patient id for links

      $calminute = $r["calminute"];
      if ($calminute==0) $calminute="00";

       // time checking/creation if/else clause
      if ($r["calhour"]<12)
        $_time = $r["calhour"].":".$calminute." am";
      elseif ($r["calhour"]==12)
        $_time = $r["calhour"].":".$calminute." pm";
      else
        $_time = ($r["calhour"]-12).":".$calminute." pm";

      $calpatient = $r["calpatient"];
       // prepare the patient and physician names
      switch ($r["caltype"]) {
       case "temp":
        $ptlname = freemed_get_link_field ($r["calpatient"], "callin",
                   "cilname"); 
        $ptfname = freemed_get_link_field ($r["calpatient"], "callin",
                   "cifname");
        $ptmname = freemed_get_link_field ($r["calpatient"], "callin",
                   "cimname");
        $patient_link_location = "call-in.php3?$_auth&action=view&".
                   "id=$calpatient";
        break;
       case "pat": default:
        $ptlname = freemed_get_link_field ($r["calpatient"], "patient",
                   "ptlname");
        $ptfname = freemed_get_link_field ($r["calpatient"], "patient",
                   "ptfname");
        $ptmname = freemed_get_link_field ($r["calpatient"], "patient",
                   "ptmname");
        $patient_link_location = "manage.php?$_auth&id=$patient";
        break;
      } // end of switch (getting proper patient info

      $phylname = freemed_get_link_field ($r["calphysician"],
                 "physician", "phylname"); // physician last name
      $phyfname = freemed_get_link_field ($r["calphysician"],
                 "physician", "phyfname"); // physician first name

       // get facility and room names
      $psrname = freemed_get_link_field ($r["calfacility"],
                 "facility", "psrname");
      $roomname = freemed_get_link_field ($r["calroom"],
                  "room", "roomname");
      if (strlen($psrname)<1) $psrname = "&nbsp;";
      if ($show=="all") $_date = $r["caldateof"]." <BR>";
      if (freemed_check_access_for_facility ($LoginCookie, $r["calfacility"])){
       echo "
         <TR BGCOLOR=\"".
          ( ($r["calpatient"]==$current_patient) ?
	  "#aaaaaa" :
	  ($_alternate=freemed_bar_alternate_color($_alternate)) )."\">
          <TD><$STDFONT_B>$_date$_time<$STDFONT_E></TD>
	  <TD><A HREF=\"$patient_link_location\"
          ><$STDFONT_B".
	   ( ($r["calpatient"]==$current_patient) ?
	     " COLOR=\"#ffffff\"" : "" )
	  .">$ptlname, $ptfname $ptmname<$STDFONT_E></A></TD>
          <TD><$STDFONT_B>$phylname, $phyfname<$STDFONT_E></TD>         
          <TD><$STDFONT_B>$psrname<$STDFONT_E></TD>         
          <TD><$STDFONT_B>$roomname<$STDFONT_E></TD>
         </TR>
        "; // only display if we have access...
       } // end of if...
      } // if there is something here
  } // end the universal while loop
  if (!$any_appointments)
    echo "
      <TR><TD ALIGN=CENTER>
       <$STDFONT_B><I>"._("No appointments today.")."</I><$STDFONT_E>
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
      <CENTER><A HREF=\"$master_patient_link_location\"
       ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> |
       <A HREF=\"book_appointment.php3?$_auth&patient=$patient&type=$type\"
       ><$STDFONT_B>"._("Book Appointment")."<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
  freemed_display_box_bottom ();

freemed_close_db (); // close the db
freemed_display_html_bottom (); // show bottom of HTML code

?>
