<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class Zipcodes extends MaintenanceModule {

	var $MODULE_NAME = 'Zipcodes';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name = "zipcodes";

	function Zipcodes () {
		$this->table_definition = array (
			'zip' => SQL__CHAR(5),
			'city' => SQL__CHAR(25),
			'state' => SQL__CHAR(3),
			'latitude' => SQL__REAL,
			'longitude' => SQL__REAL,
			'timezone' => SQL__INT(0),
			'dst' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);
		$this->table_keys = array ( 'zip', 'city', 'state' );

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor Zipcodes

} // end class Zipcodes

register_module("Zipcodes");

?>
