<?php
 // $Id$
 // desc: routines for managing appointments... for patients (not call-ins)
 // lic : GPL, v2

$page_name = "manage_appointments.php";
include_once ("lib/freemed.php");

freemed::connect ();

// Create scheduler object
$scheduler = CreateObject('FreeMED.Scheduler');

if ($patient<1) {
   $page_title = __("Manage Appointments")." :: ".__("ERROR");
   $display_buffer .= "
     <p/>
     ".__("You must select a patient.")."
     <p/>
     <div align=\"CENTER\">
      <a HREF=\"patient.php\" class=\"button\"
       >".__("Select a Patient")."</a>
     </div>
     <p/>
    ";
   template_display();
} // end checking for patient

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"manageappointment.php|user $user_to_log accesses patient $patient");}	



 switch ($action) {
  case "del":
   if (!freemed::acl('schedule', 'delete')) {
     trigger_error(__("You do not have permission to delete appointments."));
   }
   $page_title = __("Deleting Appointment");
   $display_buffer .= "\n".__("Deleting")." ... \n";
   $query = "DELETE FROM scheduler WHERE id='".addslashes($id)."'";
   $result = $sql->query ($query);
   if ($result) { $display_buffer .= __("done")."."; }
    else        { $display_buffer .= __("ERROR");    }
   $display_buffer .= "<p/>\n".template::link_bar(array(
     __("Manage Appointments") =>
     "$page_name?action=view&patient=$patient",
     __("Manage Patient") =>
     "manage.php?id=$patient" )).
    "<p/>\n";
    // By default, we return to patient emr view
    $refresh = "manage.php?id=".urlencode($patient);
   break; // end delete appointment section
  default: // default action is to view appointments
   // grab patient information
   $this_patient = CreateObject('FreeMED.Patient', $patient);

   // display top of the box
   $page_title = __("Manage Appointments");
   $display_buffer .= freemed::patient_box($this_patient).
     "<p/>\n".template::link_bar(array(
       __("Book Appointment") =>
      "book_appointment.php?patient=$patient&type=pat",
       __("Manage Patient") =>
      "manage.php?id=$patient"
      ))."<p/>\n";

   // form the query
   $query = "SELECT * FROM scheduler WHERE (
             (caldateof  >= '".addslashes($cur_date)."' ) AND
             (calpatient =  '".addslashes($patient)."'  ) AND
             (caltype    =  'pat'                       ) )
             ORDER BY caldateof, calhour, calminute";

   // submit the query
   $result = $sql->query ($query);

   // check for results
   if (!$sql->results($result)) {
     $display_buffer .= "
       <p/>
       <table WIDTH=\"100%\" CLASS=\"reverse\" CELLSPACING=\"2\"
        CELLPADDING=\"2\" BORDER=\"0\" VALIGN=\"CENTER\"
        ALIGN=\"CENTER\"><TR><TD CLASS=\"reverse\"
        ALIGN=CENTER VALIGN=CENTER>
	".__("This patient has no appointments.")."
       </td></tr></table>".
       "<p/>\n".template::link_bar(array(
       __("Book Appointment") =>
      "book_appointment.php?patient=$patient&type=pat",
       __("Manage Patient") =>
      "manage.php?id=$patient"
      ))."<p/>\n";
   } else { // if there are results...

     // first display the top of the table
     $bar_start_color = "cell"; $bar_alt_color = "cell_alt";
     $display_buffer .= "
       <table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"3\"
        CLASS=\"reverse\" BORDER=\"0\"><tr>
        <td>".__("Date")."</td>
        <td>".__("Time/Duration")."</td>
        <td>".__("Location")."</td>
        <td>".__("Note")."</td>
        <td>".__("CPT Code")."</td> 
        <td>".__("Action")."</td> 
       </tr>
      ";

     // loop for all occurances in calendar db
     while ($r = $sql->fetch_array ($result)) {
       extract ($r);
       $calprenote   = htmlentities (stripslashes (
                        substr ($r["calprenote"],0,50) ) ).
                       ( (strlen($r["calprenote"])>50) ? "... " : "" );

       // calculate durational hours & minutes
       $hours        = (int) ($calduration / 60);
       $minutes      = (int) ($calduration % 60);
       if ($calminute==0) $calminute="0";

       // put together location name
       if (($calroom != 0) and ($calfacility != 0)) {
        $location = freemed::get_link_field ($calroom, "room", "roomname")." (".
                    freemed::get_link_field ($calfacility, "facility",
                     "psrname").")";
       } elseif (($calroom != 0) and ($calfacility == 0)) {
        $location = freemed::get_link_field ($calroom, "room", "roomname");
       } else { 
        $location = __("NONE SELECTED");
       } // end of location putting together

       if (($minutes+0)==0)    $minutes="00";   // fix for 0 not 00
       if (($calminute+0)==0)  $calminute="00"; // same as above

       if (($calcptcode+0)==0) $calcptcode="<b>".__("NONE SELECTED")."</b>";

       // actual display
       $display_buffer .= "
        <tr CLASS=\"".(freemed_alternate())."\">
         <td>$caldateof</td>
         <td ALIGN=\"CENTER\">".$scheduler->display_time(
		$calhour,$calminute)."<br/>
             (".$hours."h ".$minutes."m)</td>
         <td>".( !empty($location)   ? $location   : "&nbsp;" )."</td>
         <td>".( !empty($calprenote) ? $calprenote : "&nbsp;" )."</td>
         <td>$calcptcode</td>
         <td><a href=\"book_appointment.php?id=".urlencode($r['id']).
	 	"&patient=".urlencode($patient)."\"
		class=\"button\">".__("Move")."</a>
		<a href=\"$page_name?id=$r[id]&action=del&patient=$patient\"
		class=\"button\">".__("Delete")."</a></td>
        </tr>
        ";
     } // end loop for all occurances (while)

     // bottom of the table
     $display_buffer .= "
       </table>".
       "<p/>\n".template::link_bar(array(
       __("Book Appointment") =>
      "book_appointment.php?patient=$patient&type=pat",
       __("Manage Patient") =>
      "manage.php?id=$patient"
      ))."<p/>\n";
   } // end checking for results (if)

   break;
 } // end master switch

//----- Display template
template_display();

?>
