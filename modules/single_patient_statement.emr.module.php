<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class SinglePatientStatement extends EMRModule {

	var $MODULE_NAME = "Statement";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.3';

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function SinglePatientStatement () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor 

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS[this_patient])) {
			global $this_patient;
		} else {
			$this_patient = CreateObject('FreeMED.Patient', $patient);
		}

		// Check to see if we *can* generate a statement for this
		// patient
		$query = "SELECT COUNT(id) AS count FROM procrec ".
			"WHERE procpatient='".addslashes($patient)."' ".
			"AND procbalcurrent > 0 ".
			"AND proccurcovtp = '0'";
		$result = $GLOBALS['sql']->query( $query );
		$r = $GLOBALS['sql']->fetch_array ( $result );
		if ($r['count'] < 1) {
			$buffer .= "
			<div align=\"center\">
			".__("This patient does not have any statements to generate.")."
			</div>
			";
			return $buffer;
		}

		$buffer .= "
			<div ALIGN=\"CENTER\">
			<form ACTION=\"module_loader.php\" METHOD=\"POST\">
			<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".
				prepare($this->MODULE_CLASS)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
				"view\"/>
			<input TYPE=\"HIDDEN\" NAME=\"return\" VALUE=\"".
				"manage\"/>
			<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".
				prepare($patient)."\"/>
			<input type=\"SUBMIT\" NAME=\"output\" VALUE=\"".
				__("Generate Statement")."\" />
			</form>
			</div>
			";
		return $buffer;
	} // end method summary

	// view actually prints
	function view ( ) {
		// We have to use REMITT to do this; the crazy part is that REMITT forks
		// its render process into the background, so we're going to have to
		// loop and wait for it to come back with something.

		$pat = CreateObject('_FreeMED.Patient', $_REQUEST['patient']);
		$procs = $pat->get_procedures_to_bill ( true );

		if (!is_array($procs)) {
			trigger_error(__("No procedures to bill seem to exist for this patient."), E_USER_ERROR);
		}

		$remitt = CreateObject('_FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login (
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);

		$result = $remitt->ProcessStatement( $procs );

		$waitcount = 0;
		while (!($status = $remitt->GetStatus($result))) {
			// Wait for a half a second
			usleep (500000);
			
			// If this is taking hideously long, exit with error
			if ($waitcount > 60) {
				trigger_error(__("Operation timed out."), E_USER_ERROR);
			}
			$waitcount++;
		} // end while getstatus loop

		// Retrieve report from "status"
		$this->display_report ( $remitt, $status ); die();
	} // end view action

	function display_report ( $remitt, $report ) {
		$r = $remitt->_call (
			'Remitt.Interface.GetFile',
			array (
				CreateObject('PHP.xmlrpcval', 'output', 'string'),
				CreateObject('PHP.xmlrpcval', $report, 'string'),
			),
			false
		);
		if (eregi('\%PDF\-1', $r)) {
			Header('Content-type: application/x-pdf');
		}
		Header('Content-Disposition: inline; filename="'.$report.'"');
		print $r;
	} // end method display_report

	// Disable summary bar
	function summary_bar() { }

} // end class SinglePatientStatement

register_module ("SinglePatientStatement");

?>
