<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class ClaimLog extends MaintenanceModule {

	var $MODULE_NAME = 'Claim Log';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.7.0';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.3';

	var $table_name = "claimlog";

	function ClaimLog ( ) {
		$this->table_definition = array (
			'cltimestamp' => SQL__TIMESTAMP(14),
			'cluser' => SQL__INT_UNSIGNED(0),
			'clprocedure' => SQL__INT_UNSIGNED(0),
			'claction' => SQL__VARCHAR(50),
			'clcomment' => SQL__TEXT,

			// Billing specific
			'clformat' => SQL__VARCHAR(32),
			'cltarget' => SQL__VARCHAR(32),
			'clbillkey' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor ClaimLog

} // end class ClaimLog

register_module('ClaimLog');

?>
