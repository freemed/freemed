<?php
 // $Id$
 // $Author$
 // note: all billing functions accessable from this menu, which is called
 //       by the main menu
 // lic : GPL, v2

$page_name = "billing_functions.php";
include ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//----- Create user object
$this_user = CreateObject('FreeMED.User');

//----- Set page title
$page_title = __("Billing Functions");

//----- Add page to stack
page_push();

//----- Check for "current_patient" in $_SESSION
if ($_SESSION['current_patient'] != 0) {
	$patient = $_SESSION['current_patient'];
}

$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"billingfunctions.php|user $user_to_log accesses patient $patient");}	

$patient_information = "<b>".__("NO PATIENT SPECIFIED")."</b>\n";
if ($patient>0) {
	$this_patient = CreateObject('FreeMED.Patient', $patient);
	$patient_information = freemed::patient_box ($this_patient);
} // if there is a patient

//
// payment links removed till billing module is
// complete. use manage to make payments
//

   // here is the actual guts of the menu
if (freemed::user_flag(USER_DATABASE)) {
	$display_buffer .= "
	<p/>

	<div ALIGN=\"CENTER\">
	$patient_information
	</div>

	<p/>

	<table border=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\"
	 VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
	".($this_patient ? "" :
	"<tr>
		<td COLSPAN=\"2\" ALIGN=\"CENTER\">
		<div>
			<a class=\"button\" href=\"patient.php\"
			>".__("Select a Patient")."</a>
		</div>
		</td>
	</tr>" )."
	</table> 
	<p/>
	";

	$category = "Billing";
	$module_template = "
		<tr>
        	<td>
        	<a HREF=\"module_loader.php?module=#class#&patient=$patient\"
        	>#name#</a>
        	</td>
		</tr>\n";
	// modules list
	$module_list = CreateObject(
		'PHP.module_list',
		PACKAGENAME,
		array(
			'cache_file' => 'data/cache/modules'
		)
	);
	$display_buffer .= "<div ALIGN=\"CENTER\"><table>\n";
	$display_buffer .= $module_list->generate_list($category, 0, $module_template);
	$display_buffer .= "</table></div>\n";
} else { 
	$display_buffer .= "
	<p/>
	".__("You don't have access for this menu.")."
	<p/>
	";
}

//----- Finish template display
template_display ();

?>
