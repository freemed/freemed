<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

include "global.var.inc";

// class freemedModule extends module
class freemedModule extends module {

	// override variables
	var $PACKAGE_NAME = PACKAGENAME;
	var $PACKAGE_VERSION = VERSION;

	// contructor method
	function freemedModule ($nullvar = "") {
		// call parent constructor
		$this->module($nullvar);
	} // end fnction freemedModule

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

?>
