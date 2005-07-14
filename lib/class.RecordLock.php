<?php
	// $Id$
	// $Author$

// Class: FreeMED.RecordLock
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
	function RecordLock ( $table ) {
		$this->table = $table;
		$this->user = CreateObject('_FreeMED.User');
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
		$query = "SELECT * FROM recordlock WHERE ".
			"locksession <> '".session_id()."' AND ".
			"locktable='".addslashes($this->table)."' AND ".
			"lockrow='".addslashes($row)."' AND ".
			"( (CURRENT_TIMESTAMP + 0) - (lockstamp + 0) ) < ".$this->expiry;
		$result = $GLOBALS['sql']->query( $query );
		if ($GLOBALS['sql']->results($result)) {
			$a = $GLOBALS['sql']->fetch_array($result);
			return $a['lockuser'];
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
		// Find out if we already have *a* row locked ...
		$u = CreateObject('_FreeMED.User');
		$query = "SELECT * FROM recordlock WHERE ".
			"locksession='".session_id()."' AND ".
			"locktable='".addslashes($this->table)."' AND ".
			"lockrow='".addslashes($row)."' AND ".
			"( (CURRENT_TIMESTAMP + 0) - (lockstamp + 0) ) < ".$this->expiry;
		$result = $GLOBALS['sql']->query( $query );
		if ($GLOBALS['sql']->results($result)) {
			$a = $GLOBALS['sql']->fetch_array($result);
			$query = $GLOBALS['sql']->update_query(
				'recordlock',
				array ( 'lockstamp' => SQL__NOW ),
				array ( 'id' => $a['id'] )
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
