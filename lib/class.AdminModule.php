<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

LoadObjectDependency('FreeMED.BaseModule');

// Class: FreeMED.AdminModule
//
//	Administration module superclass. This is descended from
//	<BaseModule>.
//
class AdminModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "FreeMED Admin";
	var $CATEGORY_VERSION = "0.2";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;

	// contructor method
	function AdminModule () {
		// call parent constructor
		$this->BaseModule();
	} // end function AdminModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) {
			trigger_error("Module not Defined", E_ERROR);
		}
		if (!freemed::user_flag(USER_ADMIN)) {
			$page_title = __("Administration")." :: ".__("ERROR");
			$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\">".__("No administrative access!")."</div>	
			<p/>
			<div ALIGN=\"CENTER\">
			<a class=\"button\" HREF=\"main.php\"
			>".__("Return to the Main Menu")."</a>
			</div>
			<p/>
			";
			template_display();
		}
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action;

		switch ($action) {
			case "action":
				$display_buffer .= $this->action();
				break;

			case "menu":
			default:
				$display_buffer .= $this->menu();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	function action () {
		return "STUB!";
	} // end function action

	function menu () {
		return "STUB!";
	} // end function menu

	// override _setup with create_table
	function _setup () {
		if (!$this->create_table()) return false;
		return freemed_import_stock_data ($this->record_name);
	} // end function _setup

	// function create_table
	// - used to initially create SQL table
	function create_table () {
		if (!isset($this->table_definition)) return false;
		$query = $sql->create_table_query(
			$this->table_name,
			$this->table_definition
		);
		$result = $sql->query ($query);
		return !empty($result);
	} // end function create_table

} // end class AdminModule

?>
