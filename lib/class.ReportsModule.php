<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.BaseModule');

class ReportsModule extends BaseModule {

	var $CATEGORY_NAME = "Reports";
	var $CATEGORY_VERSION = "0.2";

	// vars to be passed from child modules
	var $form_vars;

	// user
	var $this_user;

	// contructor method
	function ReportsModule () {
		// call parent constructor
		$this->BaseModule();
	} // end function ReportsModule

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
			$this->this_user = CreateObject('FreeMED.User');

		switch ($action) {

			case "display";
				$this->display();
				break;

			case "view":
			default:
				$this->view();
				// Create return links
				$display_buffer .= 
				template::link_bar(array(
				__("Reports") =>
				"reports.php",
				__("Return to Main Menu") =>
				"main.php"
				));
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

	// override _setup with create_table
	// Note: This has almost *no* application outside of limited
	//       tables that things like the qmaker reports module use.
	function _setup () {
		global $display_buffer;
		if (!$this->create_table()) return false;
		return freemed_import_stock_data ($this->table_name);
	} // end function _setup

	// function create_table
	// - used to initially create SQL table
	function create_table () {
		global $display_buffer;
		if (!isset($this->table_definition)) return false;
		$query = $GLOBALS['sql']->create_table_query(
			$this->table_name,
			$this->table_definition,
			( is_array($this->table_keys) ?
				array_merge(array("id"), $this->table_keys) :
				array("id")
			)
		);
		$result = $GLOBALS['sql']->query ($query);
		return !empty($result);
	} // end function create_table

} // end class ReportsModule

?>
