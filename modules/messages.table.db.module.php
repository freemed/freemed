<?php
 // $Id$
 // $Author$
 // note: stub module for messages table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class MessagesTable extends MaintenanceModule {

	var $MODULE_NAME = 'Messages Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "messages";

	function MessagesTable () {
		$this->table_definition = array (
			'msgtime' => SQL_TIMESTAMP(14),
			'msgfor' => SQL_INT_UNSIGNED(0),
			'msgpatient' => SQL_INT_UNSIGNED(0),
			'msgperson' => SQL_VARCHAR(50),
			'msgurgency' => SQL_INT_UNSIGNED(0),
			'msgsubject' => SQL_VARCHAR(75),
			'msgtext' => SQL_TEXT,
			'msgread' => SQL_INT_UNSIGNED(0),
			'id' => SQL_SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor MessagesTable

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

register_module('MessagesTable');

?>
