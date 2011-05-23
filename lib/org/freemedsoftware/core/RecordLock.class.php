<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.RecordLock
//
//	Handle individual table row locking.
//

class RecordLock {

	// Constructor: RecordLock
	//
	//	Creates a recordlock object for a particular table.
	//
	// Parameters:
	//
	//	$table - Table name
	//
	public function __construct ( $table ) {
		$this->table = $table;
		$this->user = CreateObject('org.freemedsoftware.core.User');
		$this->expiry = (RECORD_LOCK_TIMEOUT + 0) ? RECORD_LOCK_TIMEOUT : 180;
	} // end constructor

	// Method: IsLocked
	//
	//	Determine if a particular row has been locked.
	//
	// Parameters:
	//
	//	$row - id of record to be checked
	//
	// Returns:
	//
	//	User number if a particular user has the record locked, otherwise
	//	returns false.
	//
	function IsLocked ( $row ) {
		if ( !$row ) { return false; }
		$query = "SELECT * FROM recordlock WHERE ".
			"locksession <> '".session_id()."' AND ".
			"locktable='".addslashes($this->table)."' AND ".
			"lockrow='".addslashes($row)."' AND ".
			"( (CURRENT_TIMESTAMP + 0) - (lockstamp + 0) ) < ".$this->expiry;
		$result = $GLOBALS['sql']->queryRow( $query );
		if ($result['id']) {
			return $result['lockuser'];
		} else {
			return false;
		}
	} // end method IsLocked

	// Method: LockRow
	//
	//	Lock the specified row, or renew a currently held lock.
	//
	// Parameters:
	//
	//	$row - Row id in question
	//
	// Returns:
	//
	//	Boolean, success.
	//
	function LockRow ( $row ) {
		if ( !$row ) { return false; }
		// Find out if we already have *a* row locked ...
		$u = CreateObject('org.freemedsoftware.core.User');
		$query = "SELECT * FROM recordlock WHERE ".
			"locksession='".session_id()."' AND ".
			"locktable='".addslashes($this->table)."' AND ".
			"lockrow='".addslashes($row)."' AND ".
			"( (CURRENT_TIMESTAMP + 0) - (lockstamp + 0) ) < ".$this->expiry;
		$result = $GLOBALS['sql']->queryRow( $query );
		if ($result['id']) {
			$query = $GLOBALS['sql']->update_query(
				'recordlock',
				array ( 'lockstamp' => SQL__NOW ),
				array ( 'id' => $result['id'] )
			);
		} else {
			$query = $GLOBALS['sql']->insert_query(
				'recordlock',
				array (
					'lockstamp' => SQL__NOW,
					'locksession' => session_id(),
					'lockuser' => $u->user_number,
					'locktable' => $this->table,
					'lockrow' => $row
				)
			);
		}
		$result = $GLOBALS['sql']->query ( $query );
		if ($result) {
			return true;
		} else {
			return false;
		}
	} // end method LockRow

	// Method: UnlockRow
	//
	//	Remove any locks held on a particular table row
	//
	// Parameters:
	//
	//	$row - Table row in question
	//
	// Returns:
	//
	//	Boolean, success.
	//
	function UnlockRow ( $row ) {
		$query = "DELETE FROM recordlock WHERE ".
			"locktable='".addslashes($this->table)."' AND ".
			"lockrow='".addslashes($row)."'";
		$result = $GLOBALS['sql']->query( $query );
		if ($result) {
			return true;
		} else {
			return false;
		}
	} // end method UnlockRow

} // end class RecordLock

?>
