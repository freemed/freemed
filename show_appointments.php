<?php
 // $Id$
 // note: show appointments
 // lic : GPL, v2

$page_name = "show_appointments.php";
include ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"showappointment.php|user $user_to_log");}	




if (strlen($selected_date)!=10) {
	$selected_date = $cur_date;
} // fix date if not correct

if ($show=="all") { $day_criteria = "0 = 0";                   }
 else             { $day_criteria = "caldateof = '$cur_date'"; }

//----- Create patient object
if ($patient>0) {
	$this_patient = CreateObject('FreeMED.Patient', $patient, ($type=="temp"));
} // end generating this_patient object

    // display header
$page_title = __("Show Appointments");

if ($patient>0) $display_buffer .= freemed::patient_box ($this_patient);
$display_buffer .= "
    <p/>
    <table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\"
     VALIGN=\"CENTER\" ALIGN=\"CENTER\">
";

if ($patient>0) { 
	$qualifier = "(calpatient='".addslashes($patient)."')";
	switch ($type) {
		case "temp":
		$qualifier .= " AND (caltype='temp')";
		$master_patient_link_location =
			"call-in.php?action=display&id=$patient";
		break;

		case "pat": case "default": default:
		$qualifier .= " AND (caltype != 'temp')";
		$master_patient_link_location =
			"manage.php?id=$patient";
		break;
	} // end switch
} else {
	$qualifier = "0 = 0";
}

$query = "SELECT * FROM scheduler WHERE (($day_criteria) ".
	"AND ($qualifier)) ORDER BY caldateof, calhour, calminute";
$result = $sql->query ($query);
if ($debug) $display_buffer .= "query=\"$query\"";
if ($sql->num_rows ($result) < 1) {
	$display_buffer .= "
      <tr><td ALIGN=\"CENTER\">
       <I>".__("No appointments today.")."</I>
      </td></tr>
      </table>
      <P>
	";

	if ($patient>0) { // if there is a patient link
		$display_buffer .= "
      <CENTER><A HREF=\"$master_patient_link_location\"
       >".__("Manage Patient")."</A> |
       <A HREF=\"book_appointment.php?patient=$patient&type=$type\"
       >".__("Book Appointment")."</A>
      </CENTER>
      <P>
    ";
	} else {
		$display_buffer .= "
      <CENTER><A HREF=\"main.php\"
      >".__("Return to Main Menu")."</A> |
      <A HREF=\"patient.php\"
      >".__("Choose a Patient")."</A>
      </CENTER>
      <P>
		";
	}
	template_display();
} // end checking if there are any results

$any_appointments = false;            // until there are, there aren't
while ($r = $sql->fetch_array ($result)) {
	if (freemed::check_access_for_facility ($r["calfacility"])) {
	if (!$any_appointments) // if this is the first appointment...
	$display_buffer .= "
          <tr CLASS=".(freemed_alternate()).">
           <td><b>".__("Time")."</b></td>
           <td><b>".__("Patient")."</b></td>
           <td><b>".__("Provider")."</b></td>
           <td><b>".__("Facility")."</b></td>
           <td><b>".__("Room")."</b></td>
          </tr>
        ";
	$any_appointments = true;         // now there are appointments
	$ptid = $r["calpatient"];         // get patient id for links

	$calminute = $r["calminute"];
	if ($calminute==0) $calminute="00";

	// time checking/creation if/else clause
	if ($r["calhour"]<12) {
		$_time = $r["calhour"].":".$calminute." am";
	} elseif ($r["calhour"]==12) {
		$_time = $r["calhour"].":".$calminute." pm";
	} else {
		$_time = ($r["calhour"]-12).":".$calminute." pm";
	}

	$calpatient = $r["calpatient"];
	// prepare the patient and physician names
	switch ($r["caltype"]) {
	case "temp":
	$ptlname = freemed::get_link_field ($r["calpatient"], "callin",
		"cilname"); 
	$ptfname = freemed::get_link_field ($r["calpatient"], "callin",
                   "cifname");
	$ptmname = freemed::get_link_field ($r["calpatient"], "callin",
                   "cimname");
	$patient_link_location = "call-in.php?action=view&".
                   "id=$calpatient";
	break;
	case "pat": default:
	$ptlname = freemed::get_link_field ($r["calpatient"], "patient",
                   "ptlname");
	$ptfname = freemed::get_link_field ($r["calpatient"], "patient",
                   "ptfname");
	$ptmname = freemed::get_link_field ($r["calpatient"], "patient",
                   "ptmname");
        $patient_link_location = "manage.php?id=$patient";
	break;
	} // end of switch (getting proper patient info

	$phylname = freemed::get_link_field ($r["calphysician"],
                 "physician", "phylname"); // physician last name
	$phyfname = freemed::get_link_field ($r["calphysician"],
                 "physician", "phyfname"); // physician first name

       // get facility and room names
	$psrname = freemed::get_link_field ($r["calfacility"],
                 "facility", "psrname");
	$roomname = freemed::get_link_field ($r["calroom"],
                  "room", "roomname");
	if (strlen($psrname)<1) $psrname = "&nbsp;";
	if ($show=="all") $_date = $r["caldateof"]." <BR>";
	if (freemed::check_access_for_facility ($r["calfacility"])){
       $display_buffer .= "
         <tr CLASS=\"".
          ( ($r["calpatient"]==$current_patient) ?
	  "#aaaaaa" :
	  (freemed_alternate()) )."\">
          <td>$_date$_time</td>
	  <td><A HREF=\"$patient_link_location\"
          ><FONT".
	   ( ($r["calpatient"]==$current_patient) ?
	     " COLOR=\"#ffffff\"" : "" )
	  .">$ptlname, $ptfname $ptmname</FONT></A></td>
          <td>$phylname, $phyfname</td>         
          <td>$psrname</td>         
          <td>$roomname</td>
         </tr>
		"; // only display if we have access...
		} // end of if...
	} // if there is something here
} // end the universal while loop

if (!$any_appointments)
	$display_buffer .= "
      <tr><td ALIGN=\"CENTER\">
       <I>".__("No appointments today.")."</I>
      </td></tr>
      </table>
      <P>
	";
else $display_buffer .= "
    </table>
    <P>
	";

if ($patient>0) // if there is a patient link
    $display_buffer .= "
      <CENTER><A HREF=\"$master_patient_link_location\"
       >".__("Manage Patient")."</A> |
       <A HREF=\"book_appointment.php?patient=$patient&type=$type\"
       >".__("Book Appointment")."</A>
      </CENTER>
      <P>
";

template_display();
?>
