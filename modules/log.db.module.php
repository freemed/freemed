<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class LogModule extends MaintenanceModule {

	var $MODULE_NAME = "Log";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Log";
	var $table_name = 'log';

	function LogModule () {
		// __("Log")
		$this->table_definition = array (
			'logstamp' => SQL__TIMESTAMP(14),
			'loguser' => SQL__INT_UNSIGNED(0),
			'logpatient' => SQL__INT_UNSIGNED(0),
			'logsystem' => SQL__VARCHAR(150),
			'logsubsystem' => SQL__VARCHAR(150),
			'logseverity' => SQL__INT_UNSIGNED(0),
			'logmsg' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		// call parent constructor
		$this->MaintenanceModule();
	} // end constructor Annotations

} // end class LogModule

register_module ("LogModule");

?>
