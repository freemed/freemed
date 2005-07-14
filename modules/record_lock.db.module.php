<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class RecordLockModule extends MaintenanceModule {

	var $MODULE_NAME = 'Record Lock';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $table_name = "recordlock";

	function RecordLockModule () {
		$this->table_definition = array (
			'lockstamp' => SQL__TIMESTAMP(16),
			'locksession' => SQL__VARCHAR(128),
			'lockuser' => SQL__INT_UNSIGNED(0),
			'locktable' => SQL__VARCHAR(128),
			'lockrow' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor RecordLockModule

} // end class RecordLockModule

register_module("RecordLockModule");

?>
