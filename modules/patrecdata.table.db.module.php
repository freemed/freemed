<?php
 // $Id$
 // $Author$
 // note: stub module for patrecdata table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class PatRecDataTable extends MaintenanceModule {

	var $MODULE_NAME = 'Patient Record Custom Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "patrecdata";

	function PatRecDataTable () {
		$this->table_definition = array (
			'prpatient' => SQL_INT_UNSIGNED(0),
			'prtemplate' => SQL_INT_UNSIGNED(0),
			'prdtadd' => SQL_DATE,
			'prdtmod' => SQL_DATE,
			'prdata' => SQL_TEXT,
			'id' => SQL_SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor PatRecDataTable

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

register_module('PatRecDataTable');

?>
