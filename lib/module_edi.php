<?php
 // $Id$
 // desc: module prototype for EDI
 // lic : GPL, v2

// CURRENTLY, THIS IS A STUB, AND SHOULD NOT BE USED UNTIL IT IS
// FLESHED OUT                              -- THE MANAGEMENT :)

if (!defined("__MODULE_EDI_PHP__")) {

define (__MODULE_EDI_PHP__, true);

// class freemedEDIModule
class freemedEDIModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Electronic Data Interchange";
	var $CATEGORY_VERSION = "0.1";

	var $transaction_reference_num;
	var $current_transaction_set = "0000";
	var $record_terminator = "~";
	var $start_envelope;	// this holds the ISA/GC headers
	var $end_envelope;		// this holds the ISA/GC trailer
	var $error_buffer;
	var $edi_buffer;
	var $transaction_reference_number;

	// contructor method
	function freemedEDIModule () {

		// call parent constructor
		$this->freemedModule();

		// form proper transaction reference number
		$random = rand (1, 99);
		if ( strlen ( $random ) < 2 ) 
			$this->transaction_reference_number =
				"0" . $this->transaction_reference_number;

	} // end function freemedEDIModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $action, $patient, $LoginCookie;

		if (!isset($this_user))
			$this->this_user    = new User ($LoginCookie);

		switch ($action) {

			case "display";
				$this->display();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action

	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function display
	// by default, a wrapper for view
	function display () { $this->view(); }

} // end class freemedEDIModule

} // end if not defined

?>
