<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

include_once("lib/freemed.php");

LoadObjectDependency('PHP.module');

// class freemedModule extends module
class BaseModule extends module {

	// override variables
	var $PACKAGE_NAME = PACKAGENAME;
	var $PACKAGE_VERSION = VERSION;
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_DESCRIPTION = "No description.";
	var $MODULE_VENDOR = "Stock Module";

	// Set package versioning information
	var $PACKAGE_VERSION = VERSION;

	// All FreeMED modules use this one loader
	var $page_name = "module_loader.php";

	// contructor method
	function BaseModule () {
		// Call parent constructor
		$this->module();
		// Call setup
		$this->setup();
		// Load language files, if necessary
		GettextXML::textdomain(strtolower(get_class($this)));
	} // end constructor BaseModule

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
	function _header ($nullvar = "") {
		global $display_buffer, $page_name;
		freemed_open_db ();
		$page_name = __($this->MODULE_NAME);

		// Check for existance of separate "record_name"
		if (!isset($this->record_name)) {
			$this->record_name = _($this->MODULE_NAME);
		}

		// Globalize record_name and page_title
		if (page_name() == $this->page_name) {
			$GLOBALS['record_name'] = $this->record_name;
			$GLOBALS['page_title'] = $this->record_name;
		}
	} // end function _header
	function header ( ) { $this->_header(); }

	// override footer method
	function footer ($nullvar = "") {
	} // end function footer

	// calling function
	function setup () {
		global $display_buffer;
		if (!freemed::module_check($this->MODULE_NAME,$this->MODULE_VERSION)) {
			// check if it is installed *AT ALL*
			if (!freemed::module_check($this->MODULE_NAME, "0.0001")) {
				// run internal setup routine
				$val = $this->_setup();
			} else {
				// run internal update routine
				$val = $this->_update();
			} // end checking to see if installed at all

			// register module
			freemed::module_register($this->MODULE_NAME, $this->MODULE_VERSION);

			return $val;
		} // end checking for module
	} // end function setup

	// _setup (in this case, wrapped in classes...)
	function _setup () { return true; }

	// _update (in this case, wrapped in classes...)
	function _update () { return true; }

	function init($test) {
		global $sql;
	
		$result = $sql->query("DROP TABLE module"); 

		$result = $sql->query($sql->create_table_query(
			'module',
			array(
				'module_name' => SQL_VARCHAR(100),
				'module_version' => SQL_VARCHAR(50),
				'id' => SQL_SERIAL
			), array('id')
		));
		return $result;
	} // end method BaseModule->init

	//----- Internal module functions

	function _GetAssociations () {
		if (!is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES'])) {
			$modules = CreateObject('PHP.module_list', PACKAGENAME);
		}
		$associations = array();
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $__crap => $v) {
			$a = $v['META_INFORMATION']['__associations'];
			foreach ($a as $_k => $_v) {
				if (strtolower($_k) == strtolower($this->MODULE_CLASS)) {
					$associations[] = $_v;
				}
			}
		}
		return $associations;
	} // end method BaseModule->_GetAssociations

	function _SetAssociation ($with) {
		$this->META_INFORMATION['__associations']["$with"] = get_class($this);
	} // end method BaseModule->_SetAssociation

} // end class BaseModule

?>
