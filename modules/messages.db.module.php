<?php
	// $Id$
	// $Author$
	// note: stub module for messages table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class MessagesTable extends MaintenanceModule {

	var $MODULE_NAME = 'Messages Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "messages";

	function MessagesTable () {
		$this->table_definition = array (
			'msgby' => SQL__INT_UNSIGNED(0),
			'msgtime' => SQL__TIMESTAMP(14),
			'msgfor' => SQL__INT_UNSIGNED(0),
			'msgpatient' => SQL__INT_UNSIGNED(0),
			'msgperson' => SQL__VARCHAR(50),
			'msgurgency' => SQL__INT_UNSIGNED(0),
			'msgsubject' => SQL__VARCHAR(75),
			'msgtext' => SQL__TEXT,
			'msgread' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor MessagesTable

	// Use _update to update table definitions with new versions
	function _update () {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.6.0.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN msgby INT UNSIGNED FIRST');
		}
	} // end function _update
}

register_module('MessagesTable');

?>
