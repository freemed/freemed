<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_PHP__")) {

define ('__MODULE_PHP__', true);

include "lib/freemed.php";

// class freemedModule extends module
class freemedModule extends module {

	// override variables
	var $PACKAGE_NAME = PACKAGENAME;
	var $PACKAGE_VERSION = VERSION;
	var $MODULE_AUTHOR = "jeff b (jeff@univrel.pr.uconn.edu)";
	var $MODULE_DESCRIPTION = "No description.";
	var $MODULE_VENDOR = "Stock Module";

	// all modules use this one loader
	var $page_name = "module_loader.php";

	// contructor method
	function freemedModule () {
		// call parent constructor
		$this->module();
		// call setup
		$this->setup();

		// Globalize record_name and page_title
		if (page_name() == $this->page_name) {
			$GLOBALS["record_name"] = $this->record_name;
			$GLOBALS["page_title"] = $this->record_name;
		}
	} // end constructor freemedModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			 trigger_error("No Module Defined",E_ERROR);
		}
		return true;
	} // end function check_vars

	// override header method
	function header ($nullvar = "") {
		global $display_buffer, $page_name;
		global $LoginCookie;
		freemed_open_db ($LoginCookie);
		// freemed_display_html_top();
		// freemed_display_box_top (_($this->MODULE_NAME));
		$page_name = _($this->MODULE_NAME);
	} // end function header

	// override footer method
	function footer ($nullvar = "") {
		global $display_buffer, $page_name;
		//freemed_display_box_bottom();
		//freemed_display_html_bottom();
	} // end function footer

	// calling function
	function setup () {
		global $display_buffer;
		if (!freemed_module_check($this->MODULE_NAME,$this->MODULE_VERSION)) {
			// check if it is installed *AT ALL*
			if (!freemed_module_check($this->MODULE_NAME, "0.0001")) {
				// run internal setup routine
				$val = $this->_setup();
			} else {
				// run internal update routine
				$val = $this->_update();
			} // end checking to see if installed at all

			// register module
			freemed_module_register($this->MODULE_NAME, $this->MODULE_VERSION);

			return $val;
		} // end checking for module
	} // end function setup

	// _setup (in this case, wrapped in classes...)
	function _setup () { return true; }

	// _update (in this case, wrapped in classes...)
	function _update () { return true; }

} // end class freemedModule

// rest of module loaders
//include ("lib/module_emr.php");
//include ("lib/module_maintenance.php");
//include ("lib/module_reports.php");
//include ("lib/module_billing.php");
//include ("lib/module_edi.php");

} // end if not defined

?>
