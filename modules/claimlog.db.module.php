<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ClaimLogTable extends MaintenanceModule {

	var $MODULE_NAME = 'Claim Log';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.7.1';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $table_name = "claimlog";

	function ClaimLog ( ) {
		$this->table_definition = array (
			'cltimestamp' => SQL__TIMESTAMP(14),
			'cluser' => SQL__INT_UNSIGNED(0),
			'clprocedure' => SQL__INT_UNSIGNED(0),
			'clpayrec' => SQL__INT_UNSIGNED(0),
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
	} // end constructor

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Version 0.7.1
		//
		//	Add ability to track events by payment record (clpayrec)
		if (!version_check($version, '0.7.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN clpayrec INT UNSIGNED AFTER clprocedure');
			// Set to 0 by default (not associated with any payrec)
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET clpayrec=\'0\'');
		}
	} // end method _update

} // end class ClaimLog

register_module('ClaimLogTable');

?>
