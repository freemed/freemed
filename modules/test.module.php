<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

LoadObjectDependency('FreeMED.BaseModule');

class TestModule extends BaseModule {

	// override variables
	var $MODULE_NAME = "Test Module";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $CATEGORY_NAME = "Test Category";
	var $CATEGORY_VERSION = "0.1";

	// contructor method
	function TestModule ($nullvar = "") {
		// call parent constructor
		$this->BaseModule($nullvar);
	} // end function TestModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function
	function main () {
		global $display_buffer;
		global $patient;

		$buffer = "";
		$buffer .= "This is a test of this.";

		if ($patient>0) {
			$this_patient = CreateObject('FreeMED.Patient', 
				$patient
			);
			$buffer .= freemed::patient_box($this_patient);
		} // end checking for patient

		return $buffer;
	} // end function main

} // end class TestModule

register_module("TestModule");

?>
