<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class LabResults extends MaintenanceModule {

	var $MODULE_NAME = 'Lab Results';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "labresults";

	function LabResults ( ) {
		$this->table_definition = array (
			'labid' => SQL__INT_UNSIGNED(0),
			'labpatient' => SQL__INT_UNSIGNED(0),
			'labobsnote' => SQL__TEXT, // HL7 NTE segment
			'labobscode' => SQL__VARCHAR(150), // OBX 03-04
			'labobsdescrip' => SQL__VARCHAR(250), // OBX 03-05
			'labobsvalue' => SQL__TEXT, // OBX 05 / NTE
			'labobsunit' => SQL__VARCHAR(150), // OBX 06
			'labobsranges' => SQL__VARCHAR(50), // OBX 07
			'labobsabnormal' => SQL__CHAR(5), // OBX 08
			'labobsstatus' => SQL__CHAR(1), // OBX 11
			'labobsreported' => SQL__TIMESTAMP(14), // OBX 14
			'labobsfiller' => SQL__VARCHAR(60), // OBX 15 / OBR 21-01
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor

} // end class LabResults

register_module('LabResults');

?>
