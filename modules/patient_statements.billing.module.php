<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class PatientStatements extends BillingModule {

	var $MODULE_NAME = "Patient Statements";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = true;
	
	var $MODULE_FILE = __FILE__;

	function PatientStatements () {
		// Add appropriate handler information
		$this->_SetHandler('BillingFunctions', 'view');
		$this->_SetMetaInformation('BillingFunctionName', __("Patient Statements"));
		$this->_SetMetaInformation('BillingFunctionDescription', __("Generate patient statements as Adobe Acrobat (PDF) or Postscript."));

		// Call parent constructor
		$this->BillingModule();
	} // end constructor PatientStatements

	function view () {
		switch (strtolower($_REQUEST['module_action'])) {
			case 'print':
			return $this->printaction();
			break;
		}
		
		$buffer = "
		<div class=\"section\">".__("Patient Statements")."</div>
		<div>
		".__("Current patient statements can be retrieved as either Postscript or PDF files.")."<br/>
		<form method=\"POST\">
		<input type=\"HIDDEN\" name=\"module\" value=\"".$_REQUEST['module']."\" />
		<input type=\"HIDDEN\" name=\"action\" value=\"".$_REQUEST['action']."\" />
		<input type=\"HIDDEN\" name=\"module_action\" value=\"print\" />
		<input type=\"HIDDEN\" name=\"type\" value=\"".$_REQUEST['type']."\" />
		<input type=\"SUBMIT\" name=\"format\" value=\"Postscript\"/>
		<input type=\"SUBMIT\" name=\"format\" value=\"PDF\"/>
		<input type=\"BUTTON\" value=\"".__("Back")."\" ".
			"onClick=\"history.go(-1); return true;\"/>
		</form>
		";
		return $buffer;
	} // end method view

	// Method: printaction
	//
	//	Override default print handler, so we can generate PDFs
	//	without having to use the print system by default.
	//
	function printaction ( ) {
		// Create patient_statement report
		$agata = CreateObject('FreeMED.Agata');
		$agata->CreateReport(
			'Merge',
			'patient_statement',
			'Patient Statements',
			NULL // parameters (use in future)
		);
		switch (strtolower($_REQUEST['format'])) {
			case 'ps': case 'postscript':
			$agata->ServeReport();
			break;

			case 'pdf':
			$agata->ServeMergeAsPDF();
			break;
		}
		die(); // die, otherwise we would template, and that's ugly
	} // end method printaction

} // end class PatientStatements

register_module('PatientStatements');

?>
