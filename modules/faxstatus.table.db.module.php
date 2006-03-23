<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class FaxStatus extends MaintenanceModule {

	var $MODULE_NAME = 'Fax Status';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "faxstatus";

	function FaxStatus () {
		// __("Fax Status")
		$this->table_definition = array (
			'fsid' => SQL__VARCHAR(16),
			'fsmodule' => SQL__VARCHAR(50),
			'fsrecord' => SQL__INT_UNSIGNED(0),
			'fsuser' => SQL__INT_UNSIGNED(0),
			'fspatient' => SQL__INT_UNSIGNED(0),
			'fsdestination' => SQL__VARCHAR(50),
			'fsstatus' => SQL__VARCHAR(250),
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor FaxStatus

} // end module FaxStatus

register_module('FaxStatus');

?>
