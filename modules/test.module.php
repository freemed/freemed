<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__TEST_MODULE_PHP__")) {

define (__TEST_MODULE_PHP__, true);

// class testModule extends freemedModule
class testModule extends freemedModule {

	// override variables
	var $MODULE_NAME = "Test Module";
	var $MODULE_VERSION = "0.1";

	var $PACKAGE_MINIMUM_VERSION = "0.2.1";

	var $CATEGORY_NAME = "Test Category";
	var $CATEGORY_VERSION = "0";

	// contructor method
	function testModule ($nullvar = "") {
		// call parent constructor
		$this->freemedModule($nullvar);
	} // end function testModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override main function
	function main () {
		global $patient;

		$buffer = "";
		$buffer .= "This is a test of this.";

		if ($patient>0) {
			$this_patient = new Patient ($patient);
			$buffer .= freemed_patient_box($this_patient);
		} // end checking for patient

		return $buffer;
	} // end function main

} // end class testModule

register_module("testModule");

} // end if not defined

?>
