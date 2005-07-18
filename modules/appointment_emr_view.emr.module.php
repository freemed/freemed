<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class AppointmentEMRView extends EMRModule {

	var $MODULE_NAME = "Patient Appointments";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function AppointmentEMRView () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor 

	// The EMR box; probably the most important part of this module
	function summary ($patient, $num_summary_items) {
		// Create scheduler object
		$scheduler = CreateObject('FreeMED.Scheduler');
	
		// Get last few appointments
		$query =
			"SELECT * FROM scheduler WHERE ".
			"calpatient='".addslashes($patient)."' AND ".
			"caltype='pat' AND ".
			"( caldateof > '".date("Y-m-d")."' OR ".
			  "( caldateof = '".date("Y-m-d")."' AND ".
			  "  calhour >= '".date("H")."' )".
			") LIMIT ".$num_summary_items;
		$appoint_result = $GLOBALS['sql']->query($query);
		if (!$GLOBALS['sql']->results($appoint_result)) {
			$buffer .= "
			<b>".__("NONE")."</b>
			";
		} else {
			$buffer .= "
			<table WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=0
			 BORDER=0 CLASS=\"thinbox\"><tr>
			<td VALIGN=\"MIDDLE\" ALIGN=\"LEFT\"
			 CLASS=\"menubar_info\">
				<b>".__("Date")."</b>
			</td><td VALIGN=\"MIDDLE\" ALIGN=\"LEFT\"
			 CLASS=\"menubar_info\">
				<b>".__("Time")."</b>
			</td><td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Provider")."</b>
			</td><td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Description")."</b>
			</td></tr>
			";
			while ($appoint_r=$GLOBALS['sql']->fetch_array($appoint_result)) {
				$my_phy = CreateObject('FreeMED.Physician', $appoint_r['calphysician']);
				$buffer .= "
				<tr>
				<td VALIGN=\"MIDDLE\" ALIGN=\"LEFT\">
				<small>".prepare(fm_date_print(
					$appoint_r["caldateof"]
				))."</small>
				</td><td VALIGN=\"MIDDLE\" ALIGN=\"LEFT\">
				<small>".prepare($scheduler->get_time_string(
					$appoint_r["calhour"],
					$appoint_r["calminute"]
				))."</small>
				</td><td VALIGN=\"MIDDLE\" ALIGN=\"LEFT\">
				<small>".prepare($my_phy->fullName())."</small>
				</td><td VALIGN=\"MIDDLE\" ALIGN=\"LEFT\">
				<small>".prepare(stripslashes($appoint_r["calprenote"]))."</small>
				</td></tr>
				";
			} // end of looping through results
			// Show last few appointments
			$buffer .= "
			</table>
			";
		} // end of checking for results

		return $buffer;
	} // end method summary

	// Disable summary bar
	function summary_bar() {
		$buffer .= "
		<A HREF=\"book_appointment.php?selected_date=".
		urlencode(freemed_get_date_next(date("Y-m-d")))."&".
		"patient=".$_REQUEST['id']."&type=pat\"
		>".__("Add")."</A> |
		<A HREF=\"manage_appointments.php?patient=".$_REQUEST['id']."\"
		>".__("View/Manage")."</A> |
		<A HREF=\"show_appointments.php?patient=".$_REQUEST['id']."&type=pat\"
		>".__("Show Today")."</A>
		";
		return $buffer;
	} // end method summary_bar

} // end class AppointmentEMRView

register_module ("AppointmentEMRView");

?>
