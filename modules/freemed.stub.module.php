<?php
	// $Id$
	// $Author$
	// note: Module for FreeMED installation. This primarily allows "core"
	//       tables, like "module", "config" and "user" to be updated with
	//       versioning.

LoadObjectDependency('FreeMED.MaintenanceModule');

class FreeMED_Package extends MaintenanceModule {

	var $MODULE_NAME = 'FreeMED';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.3.2';
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

		// Version 0.6.1
		//
		//	Database changes to core tables
		//
		if (!version_check($version, '0.6.1')) {
			// In version 0.6.1, we upgrade the configuration table
			// to have 128 character keys
			$sql->query('ALTER TABLE config CHANGE c_option c_option CHAR(64)');
		}

		// Version 0.6.3
		//
		// 	Insurance module (0.3.3)
		// 	(Actual update from old module name - HACK)
		//	Add inscodef{format,target}e for electronic mappings
		//
		if (!version_check($version, '0.6.3.2')) {
		//if ($GLOBALS['sql']->results($GLOBALS['sql']->query("SELECT * FROM module WHERE module_name='Insurance Company Maintenance'"))) {
			// Remove stale entry
			$GLOBALS['sql']->query(
				'DELETE FROM module WHERE '.
				'module_name=\'Insurance Company Maintenance\''
			);
			// Make changes
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscoidmap TEXT AFTER inscomod'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodefformat VARCHAR(50) AFTER inscoidmap'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodeftarget VARCHAR(50) AFTER inscodefformat'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodefformate VARCHAR(50) AFTER inscodeftarget'
			);
			$GLOBALS['sql']->query(
				'ALTER TABLE insco '.
				'ADD COLUMN inscodeftargete VARCHAR(50) AFTER inscodefformate'
			);
		}
	} // end method _update
}

register_module('FreeMED_Package');

?>
