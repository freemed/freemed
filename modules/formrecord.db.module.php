<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class FormRecord extends MaintenanceModule {

	var $MODULE_NAME = 'Form Record';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "form_record";

	function FormRecord ( ) {
		$this->table_definition = array (
			'fr_id' => SQL__INT_UNSIGNED(0),
			'fr_uuid' => SQL__CHAR(36),
			'fr_name' => SQL__VARCHAR(100),
			'fr_value' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor

} // end class FormRecord

register_module('FormRecord');

?>
