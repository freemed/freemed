<?php
 // $Id$
 // desc: module prototype for reports
 // lic : GPL, v2

if (!defined("__MODULE_REPORTS_PHP__")) {

define ('__MODULE_REPORTS_PHP__', true);

// class freemedReportsModule
class freemedReportsModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Reports";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $form_vars;

	// user
	var $this_user;

	// contructor method
	function freemedReportsModule () {
		// call parent constructor
		$this->freemedModule();
	} // end function freemedReportsModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) 
		{
			trigger_error("Module not Defined", E_ERROR);
		}
		// FIXME!!: check access to facility
		//if (!freemed::check_access_for_patient($patient)) return false;
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $patient;

		if (!isset($this_user))
			$this->this_user    = new User ();

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

	// function view
	// - view stub
	function view () { }

} // end class freemedReportsModule

} // end if not defined

?>
