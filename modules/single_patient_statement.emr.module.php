<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.EMRModule');

class SinglePatientStatement extends EMRModule {

	var $MODULE_NAME = "Statement";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.3';

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function QuickmedsModule () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor QuickmedsModule

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
				__("PDF")."\" />
			<input type=\"SUBMIT\" NAME=\"output\" VALUE=\"".
				__("Postscript")."\" />
			</form>
			</div>
			";
		return $buffer;
	} // end function QuickmedsModule->summary

	// view actually prints
	function view ( ) {
		$agata = CreateObject('FreeMED.Agata');
		$agata->CreateReport(
			'Merge',
			'patient_statement',
			'Patient Statement',
			array ("'where' = 'where'" => "pat.id = '".
				addslashes($_REQUEST['patient'])."'" )
		);
		switch ($_REQUEST['output']) {
			case _("PDF"):
			$agata->ServeMergeAsPDF();
			break; // pdf

			case _("Postscript"):
			$agata->ServeReport();
			break; // ps
		} // end
	} // end redirect view action

	// Disable summary bar
	function summary_bar() { }

} // end class SinglePatientStatement

register_module ("SinglePatientStatement");

?>
