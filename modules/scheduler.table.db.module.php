<?php
 // $Id$
 // $Author$
 // note: stub module for scheduler table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class SchedulerTable extends MaintenanceModule {

	var $MODULE_NAME = 'Scheduler Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "scheduler";

	function SchedulerTable () {
		$this->table_definition = array (
			'caldateof' => SQL__DATE,
			'caltype' => SQL__ENUM(array('temp', 'pat')),
			'calhour' => SQL__INT_UNSIGNED(0),
			'calminute' => SQL__INT_UNSIGNED(0),
			'calduration' => SQL__INT_UNSIGNED(0),
			'calfacility' => SQL__INT_UNSIGNED(0),
			'calroom' => SQL__INT_UNSIGNED(0),
			'calphysician' => SQL__INT_UNSIGNED(0),
			'calpatient' => SQL__INT_UNSIGNED(0),
			'calcptcode' => SQL__INT_UNSIGNED(0),
			'calstatus' => SQL__INT_UNSIGNED(0),
			'calprenote' => SQL__VARCHAR(100),
			'calpostnote' => SQL__TEXT,
			'calmark' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor SchedulerTable

	// Use _update to update table definitions with new versions
	function _update () {
		$version = freemed::module_version($this->MODULE_NAME);
		/* 
			// Example of how to upgrade with ALTER TABLE
			// Successive instances change the structure of the table
			// into whatever its current version is, without having
			// to reload the table at all. This pulls in all of the
			// changes a version at a time. (You can probably use
			// REMOVE COLUMN as well, but I'm steering away for now.)

		if (!version_check($version, '0.1.0')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptglucose INT UNSIGNED AFTER id');
		}
		if (!version_check($version, '0.1.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN somedescrip TEXT AFTER ptglucose');
		}
		if (!version_check($version, '0.1.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN fakefield AFTER ptglucose');
		}
		*/
	} // end function _update
}

register_module("SchedulerTable");

?>
