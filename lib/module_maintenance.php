<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_MAINTENANCE_PHP__")) {

define (__MODULE_MAINTENANCE_PHP__, true);

include "lib/freemed.php";

// class freemedMaintenanceModule extends freeMedmodule
class freemedMaintenanceModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Database Maintenance";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;
    var $page_name = "module_loader.php"; 

	// contructor method
	function freemedMaintenanceModule ($nullvar = "") {
		// call parent constructor
		$this->freemedModule($nullvar);
	} // end function freemedMaintenanceModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $action;

		switch ($action) {
			case "add":
				$this->add();
				break;

			case "addform":
				$this->addform();
				break;

			case "del":
			case "delete":
				$this->del();
				break;

			case "mod":
			case "modify":
				$this->mod();
				break;

			case "modform":
				$this->modform();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function add
	// - addition stub
	function add () {
	} // end function add

	// function del
	// - delete stub
	function del () {
	} // end function del

	// function mod
	// - modification stub
	function mod () {
	} // end function mod

	// function add/modform
	// - wrappers for form
	function addform () { $this->form(); }
	function modform () { $this->form(); }

	// function form
	// - add/mod form stub
	function form () {
		global $action, $id;

		if (is_array($form_vars)) {
			reset ($form_vars);
			while (list ($k, $v) = each ($form_vars)) global $$v;
		} // end if is array

		switch ($action) {
			case "addform":
				break;

			case "modform":
				$result = fdb_query ("SELECT * FROM ".$this->table_name.
					" WHERE ( id = '".prepare($id)."' )");
				$r = fdb_fetch_array ($result);
				extract ($r);
				break;
		} // end of switch action
		
	} // end function form

	// function view
	// - view stub
	function view () {
		$result = fdb_query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		echo freemed_display_itemlist (
			$result,
			"module_loader.php",
			$form_vars,
			array ("", _("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

} // end class freemedMaintenanceModule

} // end if not defined

?>
