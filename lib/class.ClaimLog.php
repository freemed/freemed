<?php
	// $Id$
	// $Author$

// Class: FreeMED.ClaimLog
//
//	Allows access to functions involving the internal FreeMED claim
//	log, which is used by the system to track billing, rebilling,
//	bill keys, et cetera.
//
class ClaimLog {

	// STUB constructor
	function ClaimLog ( ) { }

	// Method: log_billing
	//
	//	Recursively log all procedures from a particular billkey
	//	as being billed NOW.
	//
	// Parameters:
	//
	//	$billkey - Billkey id key
	//
	//	$format - Text name of the format used to send
	//
	//	$target - Text name of the target used to send
	//
	//	$comment - (optional) Optional text comment to be attached
	//	to the claim record.
	//
	// Returns:
	//
	//	Boolean, if successful.
	//
	function log_billing ( $billkey, $format, $target, $comment = '' ) {
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('_FreeMED.User');
		}

		// Extract all procedures from the billing hash
		$billkey_hash = unserialize(
			freemed::get_link_field ( $billkey, 'billkey', 'billkey' )
		);
		$procedures = $billkey_hash['procedures'];

		// Loop through procedures
		$result = true;
		print_r($procedures); print "<br/>\n";
		foreach ( $procedures AS $procedure ) {
			$query = $GLOBALS['sql']->insert_query (
				'claimlog',
				array (
					'cltimestamp' => SQL__NOW,
					'cluser' => $this_user->user_number,
					'clprocedure' => $procedure,
					'clbillkey' => $billkey,
					'claction' => __("Bill"),
					'clformat' => $format,
					'cltarget' => $target,
					'clcomment' => $comment
				)
			);
			$this_result = $GLOBALS['sql']->query ( $query );
			if (!$this_result) { $result = false; }
		}
		return $result;
	} // end method log_billing

	// Method: mark_billed
	//
	//	Mark all procedures in a billkey as being billed. The
	//	billing interface should use this function.
	//
	// Parameters:
	//
	//	$billkey - Billkey id
	//
	// Returns:
	//
	//	Boolean, if successful.
	//
	function mark_billed ( $billkey ) {
		// Get the actual bill key
		$this_billkey = unserialize (
			freemed::get_link_field (
				$billkey,
				'billkey',
				'billkey'
			)
		);

		// Create procedure set
		$set = join(',', $this_billkey['procedures']);

		
		// Perform update to procedure table
		$query = 'UPDATE procrec SET '.
			'procbilled = \'1\' '.
			'WHERE FIND_IN_SET(id, \''.$set.'\')';
		$result = $GLOBALS['sql']->query ( $query );

		return $result;
	} // end method mark_billed

	// Method: set_rebill
	//
	//	Add a rebill to the claim log.
	//
	// Parameters:
	//
	//	$procedure - Procedure key
	//
	//	$comment - Text comment
	//
	//	$date - (optional) SQL date format describing the date
	//	of the rebill. If not passed, the default is today.
	//
	// Returns:
	//
	//	Boolean, if successful.
	//
	function set_rebill ( $procedure, $comment, $date = '' ) {
		// Determine patient from procedure
		$patient = $this->_procedure_to_patient($procedure);

		// Perform update to procedure table
		$query = $GLOBALS['sql']->update_query (
			'procrec',
			array ( 'procbilled' => '0' ),
			array ( 'id' => $procedure )
		);
		$proc_query = $GLOBALS['sql']->query ( $query );

		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('_FreeMED.User');
		}

		// Perform record insertion for claim log
		$query = $GLOBALS['sql']->insert_query (
			'claimlog',
			array (
				'cltimestamp' => SQL__NOW,
				'cluser' => $this_user->user_number,
				'clprocedure' => $procedure,
				'claction' => __("Queued for Rebill"),
				'clcomment' => $comment
			)
		);
		$cl_query = $GLOBALS['sql']->query ( $query );

		return ($pay_query and $cl_query);
	} // end method set_rebill

	//------------------------------------- INTERNAL METHODS ------------

	// Method: _procedure_to_patient
	//
	//	Look up a patient by a procedure id
	//
	// Parameters:
	//
	//	$procedure - Procedure id
	//
	// Returns:
	//
	//	Patient id
	//
	function _procedure_to_patient ( $procedure ) {
		return freemed::get_link_field ( $procedure, 'procrec', 'procpatient' );
	} // end method _procedure_to_patient

	// Method: _query_to_result_array
	//
	//	Internal helper function to convert SQL queries into
	//	arrays of associative arrays.
	//
	// Parameters:
	//
	//	$query - SQL query text
	//
	//	$sequential - (optional) Number sequentially instead of
	//	indexing by identifier. Defaults to false.
	//
	// Returns:
	//
	//	Array of associative arrays.
	//
	function _query_to_result_array ( $query, $sequential = false ) {
		$result = $GLOBALS['sql']->query ( $query );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			if ($sequential) {
				$return[] = $r;
			} else {
				$return[$r['id']] = $r;
			}
		}
		return $return;
	} // end method _query_to_result_array

} // end class ClaimLog

?>
