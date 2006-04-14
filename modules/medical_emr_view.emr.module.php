<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class MedicalEMRView extends EMRModule {

	var $MODULE_NAME = "Patient Medical Information";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function MedicalEMRView () {
		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
		$this->EMRModule();
	} // end constructor 

	// The EMR box; probably the most important part of this module
	function summary ($patient, $num_summary_items) {
		global $this_patient;
		if (!is_object($this_patient)) { $this_patient = CreateObject('_FreeMED.Patient', $patient); }

		$buffer .= "
		<table WIDTH=\"100%\" BORDER=\"0\">".
		($this_patient->local_record['ptblood'] != '-' ? "
		<!--
		<tr><TD ALIGN=\"LEFT\"><B>".__("Blood Type")."</B></TD> 
		<TD ALIGN=\"RIGHT\">".prepare($this_patient->local_record['ptblood'])."</TD></tr>
		-->
		" : "" );
		// Loop through last diagnoses
		for ($diag=1; $diag<=4; $diag++) {
			if ($this_patient->local_record['ptdiag'.$diag] > 0) {
				$buffer .= "
				<tr><td align=\"left\">".__("Diagnosis")." ".
				$diag."</td>
				<td align=\"right\">".prepare(module_function(
					'IcdMaintenance',
					'display_short',
					$this_patient->local_record['ptdiag'.$diag]
					))."</td></tr>
				";
			}
		}
		$buffer .= "</table>\n";

		return $buffer;
	} // end method summary

	// Disable summary bar
	function summary_bar() {
		$buffer .= "(".__("no actions").")";
		return $buffer;
	} // end method summary_bar

} // end class MedicalEMRView

register_module ("MedicalEMRView");

?>
