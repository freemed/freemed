<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_PHP__")) {

define (__MODULE_PHP__, true);

include "lib/freemed.php";

// class freemedModule extends module
class freemedModule extends module {

	// override variables
	var $PACKAGE_NAME = PACKAGENAME;
	var $PACKAGE_VERSION = VERSION;
	var $page_name = "module_loader.php";

	// contructor method
	function freemedModule () {
		// call parent constructor
		$this->module();
	} // end constructor freemedModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	// override header method
	function header ($nullvar = "") {
		global $LoginCookie;
		freemed_open_db ($LoginCookie);
		freemed_display_html_top();
		freemed_display_box_top (_($this->MODULE_NAME));
	} // end function header

	// override footer method
	function footer ($nullvar = "") {
		freemed_display_box_bottom();
		freemed_display_html_bottom();
	} // end function footer

} // end class freemedModule

} // end if not defined

?>
