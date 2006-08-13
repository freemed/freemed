<?php
	// $Id$

class module {
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
	function module ($nullval="") {
		$this->MODULE_CLASS = get_class($this);
		if (!$this->check_version())
			$this->error("module needs higher version of superclass");
	} // end constructor module

	// function check_dependency
	// - checks variables provided, returns false if not properly provided
	function check_dependency ($nullval="") {
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
	function check_vars ($nullval="") {
		return true;
	} // end function check_vars

	// function check_version
	// - checks variables provided, returns false if not properly provided
	function check_version ($nullval="") {
		// check version for this class
		$parent_class = get_parent_class($this);
		if ( (!empty($parent_class)) and ($parent_class != "module") ) {
			// check for minimum version, and return
			return version_check($this->PACKAGE_VERSION,
				 $this->PACKAGE_MINIMUM_VERSION);
		} else {
			// if this isn't a subclass, of course it is a good version
			return true;
		} // end  
	} // end function check_vars

	// function error
	// - this is the stub for the modules' common error message
	function error ($error_string = "ERROR") {
		DIE(get_class($this)." :: ".$error_string);
	} // end function error

	// function execute
	// - this is the code for "executing" the module. It's very
	//   useful for page generation modules, but not much else.
	//   for anything else, you should probably call
	//   module_function().
	function execute ($nullval="") {
		if (!$this->check_vars()) $this->error();
		echo $this->header();
		echo $this->main();
		echo $this->footer();
	} // end function execute

	// function footer (STUB)
	// - this is the stub for the modules' common footer
	function footer ($nullval="") {
		return "";
	} // end function footer

	// function header (STUB)
	// - this is the stub for the modules' common header
	function header ($nullval="") {
		return "";
	} // end function header

	// function main (STUB)
	// - this is the stub for the functionality of the module
	function main ($nullval="") {
		return "";
	} // end function main

	// function run
	// - this just calls this->execute()
	function run ($nullval="") {
		$this->execute($nullval);
	} // end function run

	// function set_dependency
	// - this is an array of the modules that have to be present
	function set_dependency ($deps) {
		$this->DEPENDENCY = $deps;
	} // end function set_dependency

	// function set_icon
	// - this allows an icon to be set for the current module
	function set_icon ($icon_path) {
		$this->ICON = $icon_path;
	} // end function set_icon

	// function setup
	// - this function calls the setup routine for the module
	function _setup () {
		// STUB
		return true;
	} // end function _setup
	function setup () { return $this->_setup(); }

	protected function _SetMetaInformation($key, $value) {
		$this->META_INFORMATION["$key"] = $value;
	} // end function _SetMetaInformation

} // end class module

?>
