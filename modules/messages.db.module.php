<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class MessagesTable extends MaintenanceModule {

	var $MODULE_NAME = 'Messages Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.3';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.2';

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

		// Add main menu handler item
		$this->_SetHandler('MainMenu', 'UnreadMessages');

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor MessagesTable

	function UnreadMessages ( ) {
		// Ask the API how many messages we have
		$m = CreateObject('FreeMED.Messages');
		if (($c = count($m->view_per_user(true))) > 0) {
			return array (
				__("Unread Messages"),
				sprintf(__("You have %s unread messages."), $c).
				" <a href=\"messages.php\">[".__("View")."]</a>"
			);
		} else {
			// Don't show up if there are no unread messages
			return false;
		}
	} // end method UnreadMessages

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
