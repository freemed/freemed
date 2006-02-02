<?php
	// $Id$
	// $Author$

// Class: FreeMED.Log
//
//	Handle logging to FreeMED's internal event log.
//
class Log {

	// Constructor: Log
	function Log ( ) { }

	// Method: SystemLog
	//
	//	Log system event.
	//
	// Parameters:
	//
	//	$sev - Severity of message, by LOG__xxx macros
	//
	//	$sys - System
	//
	//	$subsys - Subsystem
	//
	//	$msg - Text of log message
	//
	function SystemLog ( $sev, $sys, $subsys, $msg ) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }

		$q = $GLOBALS['sql']->insert_query(
			'log',
			array (
				'logstamp' => SQL__NOW,
				'loguser' => $this_user->user_number,
				'logpatient' => 0,
				'logsystem' => $sys,
				'logsubsystem' => $subsys,
				'logseverity' => $sev,
				'logmsg' => $msg
			)
		);
		$GLOBALS['sql']->query ( $q );
	} // end method SystemLog

	// Method: PatientLog
	//
	//	Log patient event.
	//
	// Parameters:
	//
	//	$sev - Severity of message, by LOG__xxx macros
	//
	//	$patient - Patient id number
	//
	//	$sys - System
	//
	//	$subsys - Subsystem
	//
	//	$msg - Text of log message
	//
	function PatientLog ( $sev, $patient, $sys, $subsys, $msg ) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }

		$q = $GLOBALS['sql']->insert_query(
			'log',
			array (
				'logstamp' => SQL__NOW,
				'loguser' => $this_user->user_number,
				'logpatient' => $patient,
				'logsystem' => $sys,
				'logsubsystem' => $subsys,
				'logseverity' => $sev,
				'logmsg' => $msg
			)
		);
		$GLOBALS['sql']->query ( $q );
	} // end method PatientLog

} // end class Log

?>
