<?php
	// $Id$
	// $Author$
	// note: Module for FreeMED installation. This primarily allows "core"
	//       tables, like "module", "config" and "user" to be updated with
	//       versioning.

LoadObjectDependency('FreeMED.BaseModule');

class FreeMED_Package extends BaseModule {

	var $MODULE_NAME = 'FreeMED';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = VERSION;
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = VERSION;

	function FreeMED_Package () {
		// Call parent constructor
		$this->BaseModule();
	} // end constructor FreeMED_Package

	// Use _update to perform upgrade-specific activities.
	function _update () {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.6.1 database changes to core tables
		// ---------------------------------------------
		if (!version_check($version, '0.6.1')) {
			// In version 0.6.1, we upgrade the configuration table
			// to have 128 character keys
			$sql->query('ALTER TABLE config CHANGE c_option c_option CHAR(64)');
		}
	} // end function _update
}

register_module('FreeMED_Package');

?>
