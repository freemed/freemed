<?php
	// $Id$
	// $Author$
	// note: external system billing keys

LoadObjectDependency('FreeMED.MaintenanceModule');

class BillKey extends MaintenanceModule {

	var $MODULE_NAME = 'Bill Keys';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.2';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.2';

	var $table_name = "billkey";

	function BillKey () {
		$this->table_definition = array (
			'billkeydate' => SQL__DATE,
			'billkey' => SQL__BLOB,
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor BillKey

} // end module BillKey

register_module('BillKey');

?>
