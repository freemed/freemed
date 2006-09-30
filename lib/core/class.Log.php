<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// Class: org.freemedsoftware.core.Log
//
//	Handle logging to FreeMED's internal event log.
//
class Log {

	// Constructor: Log
	public function __construct ( ) { }

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
		if (!is_object($this_user)) { $this_user = CreateObject('org.freemedsoftware.core.User'); }

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
		if (!is_object($this_user)) { $this_user = CreateObject('org.freemedsoftware.core.User'); }

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
