<?php
 // $Id$
 // $Author$
 // note: stub module for anesth table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class AnesthTable extends MaintenanceModule {

	var $MODULE_NAME = 'Anesthesiology Scheduler Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "anesth";

	function AnesthTable () {
		$this->table_definition = array (
			'andate' => SQL_DATE,
			'anphysician' => SQL_INT_UNSIGNED(0),
			'anfacility' => SQL_INT_UNSIGNED(0),
			'id' => SQL_SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor AnesthTable

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

register_module('AnesthTable');

?>
