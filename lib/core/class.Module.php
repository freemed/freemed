<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

class Module {
		// package related variables
	var $PACKAGE_NAME;
	var $PACKAGE_VERSION = 0;

		// subpackage/category related variables
	var $CATEGORY_NAME;
	var $CATEGORY_VERSION = 0;

		// module related variables
	var $MODULE_NAME;
	var $MODULE_CLASS;
	var $MODULE_VERSION = 0;
	var $MODULE_AUTHOR;
	var $MODULE_DESCRIPTION;
	var $MODULE_VENDOR;
	var $MODULE_HIDDEN;

		// versioning related variables
	var $PACKAGE_MINIMUM_VERSION = 0;
	var $CATEGORY_MINIMUM_VERSION = 0;

		// what other modules is this dependent on?
	var $DEPENDENCY = NULL;

		// path to icon, if there is one
	var $ICON = NULL;

		// no meta information to start with either
	var $META_INFORMATION = NULL;

	// constructor module
	public function __construct ( ) {
		$this->MODULE_CLASS = get_class($this);
		if (!$this->check_version()) {
			$this->error("module needs higher version of superclass");
		}
	} // end constructor module

	// function check_dependency
	// - checks variables provided, returns false if not properly provided
	public function check_dependency ($nullval="") {
		// if there are no dependencies, always succeeds
		if ($this->DEPENDENCY == NULL) return true;

		if (!is_array ($this->DEPENDENCY)) {
			return check_module ($this->DEPENDENCY);
		} else {
			foreach ($this->DEPENDENCY AS $k => $v) {
				// Check for no key present
				if ( (($k+0)>0) or (empty($k)) ) {
					if (!check_module ($v)) return false;
				} else {
					if (!check_module ($k, $v)) return false;
				}
			} // end of looping through $this->DEPENDENCY
			return true;
		} // end if array/dependencies
	} // end function check_dependency

	// function check_vars (STUB)
	// - checks variables provided, returns false if not properly provided
	public function check_vars ($nullval="") {
		return true;
	} // end function check_vars

	// function check_version
	// - checks variables provided, returns false if not properly provided
	public function check_version ($nullval="") {
		// check version for this class
		$parent_class = strtolower(get_parent_class($this));
		if ( (!empty($parent_class)) and ($parent_class != "module") ) {
			// check for minimum version, and return
			return version_compare( $this->PACKAGE_VERSION, $this->PACKAGE_MINIMUM_VERSION ) >= 0;
		} else {
			// if this isn't a subclass, of course it is a good version
			return true;
		} // end  
	} // end function check_vars

	// Method: OnError
	//
	//	Generic module error handling. Terminates execution by default.
	//
	// Parameters:
	//
	//	$error_string - (optional) Error message to display.
	//
	public function OnError ($error_string = "ERROR") {
		die(get_class($this)." :: ".$error_string);
	} // end method OnError

	// function setup
	// - this function calls the setup routine for the module
	protected function _setup () {
		// STUB
		return true;
	} // end function _setup
	public function setup () { return $this->_setup(); }

	// Method: _SetMetaInformation
	//
	//	Set stored information associated with module.
	//
 	// Parameters:
	//
	//	$key - Information key.
	//
	//	$value - Value to be stored.
	//
	protected function _SetMetaInformation($key, $value) {
		$this->META_INFORMATION["$key"] = $value;
	} // end function _SetMetaInformation

} // end class module

?>
