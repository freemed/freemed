<?php
	// $Id$
	// $Author$

// Class: FreeMED.Ledger
class Ledger {

	// STUB constructor
	function Ledger ( ) { }

	// Method: get_list
	//
	//	Get a list of ledger items from the system
	//
	// Returns:
	//
	//	Array of associative arrays, containing ledger records.
	//
	function get_list ( ) {
		$query = "SELECT * FROM payrec ORDER BY payrecdtmod";
		// Return sequentially
		return $this->_query_to_result_array ( $query, true );
	} // end method get_list

	// Method: move_to_next_coverage
	//
	//	Moves a procedure to the next available coverage.
	//
	// Parameters:
	//
	//	$proc - Procedure id key
	//
	//	$disallow - (optional) Disallowment amount that cannot
	//	be passed to the patient if coverages run out.
	//
	// Returns:
	//
	//	Boolean, successful
	//
	// See Also:
	//	<next_coverage>
	//
	function move_to_next_coverage ( $proc, $disallow = NULL ) {
		// Get next coverage
		$next = $this->next_coverage ( $proc );

		// Decide what to do based on what it is
		if ($next < 0) {
			// What do we do? Can't rebill!
			// FIXME
		} elseif ($next == 0) {
			// Patient responsibility
			return $this->queue_for_rebill($proc, 0, $disallow );
		} else {
			return $this->queue_for_rebill($proc, $next);
		} // end decide
	} // end method move_to_next_coverage

	// Method: next_coverage
	//
	//	Determine if there is another coverage that this should
	//	be moved to, based on the current coverage, and whether
	//	it should be moved to the patient or is not able to be
	//	billed any further
	//
	// Parameters:
	//
	//	$proc - Procedure id key
	//
	// Returns:
	//
	//	Coverage type for next round of coverage, 0 if the rest
	//	of the bill has to be handled by the patient, or -1 if
	//	the rest of the bill is unbillable.
	//
	function next_coverage ( $proc ) {
		// Get procedure record
		$this_procedure = freemed::get_link_rec($proc, 'procrec', true);
		$current_type = $this_procedure['proccurcovtp'];
		for ($i=1; $i<=4; $i++) {
			// Determine if a certain coverage exists
			if ($this_procedure['proccov'.$i] > 0) {
				$cov_exists[$i] = true;
			}
		}

		// If this is set to be patient billed *NOW* and it
		// won't go through, it is garbage.
		if ($current_type == 0) { return -1; }

		// If we haven't run out of possible coverage slots yet...
		if ($current_type < 4) {
			// If a next coverage exists ...
			if ($cov_exists[($current_type + 1)]) {
				// Return next coverage slot
				return ($current_type + 1);
			} else {
				// Move billing to patient for remainder
				return 0;
			} // end checking for next coverage exists
		} else {
			// If we're on the 4th slot, move to patient
			return 0;
		} // end checking for running out of slots

	} // end method next_coverage

	// Method: queue_for_rebill
	//
	//	Set a procedure to be rebilled at the next billing, moving
	//	it to be associated to the proper coverage.
	//
	// Parameters:
	//
	//	$proc - Procedure id key
	//
	//	$type - Coverage number (0 - 4)
	//
	//	$disallow - (optional) Disallowment amount that cannot
	//	be passed to the patient.
	//
	// Returns:
	//
	//	Boolean, successful
	//
	function queue_for_rebill ( $proc, $type, $disallow = NULL ) {
		// If passing to a patient, handle disallowments
		if (($type == 0) and $disallow) {
			$query = "UPDATE procrec ".
				"SET procbilled = '0', ".
				"proccurcovtp = '".addslashes($type)."', ".
				"procbalcurrent = procbalcurrent - ".
				( $disallow + 0 )." ".
				"WHERE id = '".addslashes($proc)."'";
		} else {
			$query = $GLOBALS['sql']->update_query(
				'procrec',
				array (
					'procbilled' => '0',
					'proccurcovtp' => $type
				),
				array ( 'id' => $proc )
			);
		}
		$result = $GLOBALS['sql']->query ( $query );

		// Adjust internal proccurcovid
		if ($type > 0) {
			$query = "SELECT proccov".($type + 0)." AS ".
				"coverage FROM procrec WHERE ".
				"id = '".addslashes($proc)."'";
			$result = $GLOBALS['sql']->query($query);
			extract($GLOBALS['sql']->fetch_array($result));
		} else {
			$coverage = 0;
		}

		// Update the current coverage id
		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'proccurcovid' => $coverage
			),
			array ( 'id' => $proc )
		);
		$result &= $GLOBALS['sql']->query ( $query );
			
		return $result;
	} // end method queue_for_rebill


/* ---------------- FIX / REMOVE --------------------------------------------
	// Method -- post_adjustment
	function post_adjustment ( $procedure, $category,
				$amount, $comment, $who = 0 ) {
		// Determine patient from procedure
		$data['patient'] = $this->_procedure_to_patient($procedure);

		$actual_amount = 0;
		$allowed = 0;

		switch ($category) {
			case ADJUSTMENT:
			case DEDUCTABLE:
			case WITHHOLD:
				$actual_amount = abs ( $amount );
				break;

			case DENIAL:
				$charges = $this_procedure['proccharges'] -
						$amount;
				$current_balance = $charges -
						$this_procedure['procamtpaid'];
				break;

			case FEEADJUST:
				// TODO: logic is 
				//$allowed = $this_procedure['proccharges'] -
				//		$this_procedure['payrecamt'];
				//$charges = $allowed;
				//$current_balance = $charges - $amount_paid;
				//
				break;

			case WRITEOFF:
				// Write off the entire amount
				$actual_amount = freemed::get_link_field (
					$procedure, 'procrec',
					'procbalcurrent'
				);
				break;
		} // end switch category

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreccat' => $category,
				'payrecproc' => $procedure,
				//'payrecsource' => '1',
				'payreclink' => $who,
				'payrecamt' => $actual_amount,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		// Get information about this procedure
		$procedure_object = CreateObject('FreeMED.Procedure', $data['procedure']);
		$this_procedure = $procedure_object->get_procedure( );

		$amount_paid = $data['amount'] + $this_procedure['procamtpaid'];
		$current_balance = $this_procedure['procbalorig'] -
				$amount_paid;

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'procbalcurrent' => $current_balance,
				'procamtpaid' => $amount_paid
			), array ( 'id' => $data['procedure'] )
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		return ($proc_query and $pay_query);
	} // end method post_adjustment
  ---------------- FIX / REMOVE ------------------------------------------ */

	// Method: post_copay
	//
	//	Post a copay for the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$amount - Amount of the copay as a positive number
	//
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function post_copay ( $procedure, $amount, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('FreeMED.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreccat' => COPAY,
				'payrecproc' => $procedure,
				'payrecamt' => $amount,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'procbalcurrent' => 
				$this_procedure['procbalcurrent'] - $amount,

				'procamtpaid' =>
				$this_procedure['procamtpaid'] + $amount
			), array ( 'id' => $procedure )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );

		return ($proc_result and $pay_result);
	} // end method post_copay

	// Method: post_deductable
	//
	//	Post a deductable for the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$amount - Amount of the deductable, as a positive number
	//
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function post_deductable ( $procedure, $amount, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('FreeMED.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreccat' => DEDUCTABLE,
				'payrecproc' => $procedure,
				'payrecamt' => $amount,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'procbalcurrent' => 
				$this_procedure['procbalcurrent'] - $amount,

				'procamtpaid' =>
				$this_procedure['procamtpaid'] + $amount
			), array ( 'id' => $procedure )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );

		return ($proc_result and $pay_result);
	} // end method post_deductable

	// Method: post_fee_adjustment
	//
	//	Post a fee adjustment for the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$coverage - Coverage id key
	//
	//	$amount - Amount of the fee adjustment
	//
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function post_fee_adjustment ( $procedure, $coverage,
				$amount, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('_FreeMED.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Figure out who gave us this
		$coverage_object = CreateObject('_FreeMED.Coverage', $coverage);
		$who = $coverage_object->covinsco;

		// Calculate the new proc charges
		$new_amount = $this_procedure['proccharges'] - abs($amount);

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreclink' => $who,
				'payreccat' => FEEADJUST,
				'payrecproc' => $procedure,
				'payrecamt' => $new_amount,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'procbalcurrent' => 
				$this_procedure['procbalcurrent'] - $amount,

				'proccharges' =>
				$this_procedure['proccharges'] - $amount
			), array ( 'id' => $procedure )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );

		return ($proc_result and $pay_result);
	} // end method post_fee_adjustment

	// Method: post_payment
	//
	//	Posts payment record based on passed data.
	//
	// Parameters:
	//
	//	$data - Associative array containing information passed
	//	to the ledger.
	//
	// Returns:
	//
	//	Boolean, if successful.
	//
	// See Also:
	//	<post_payment_cash>
	//	<post_payment_check>
	//
	function post_payment ( $data ) {
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecpatient' => $data['patient'],
				'payreccat' => '0', // payment
				'payrecproc' => $data['procedure'],
				'payrecsource' => '1',
				'payreclink' => $data['coverage'],
				'payrectype' => $data['type'], 
					// 1 for check, etc
				'payrecnum' => $data['payment_detail_number'],
				'payrecamt' => $data['amount'],
				'payrecdescrip' => $data['comment'],
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		// Get information about this procedure
		$procedure_object = CreateObject('FreeMED.Procedure', $data['procedure']);
		$this_procedure = $procedure_object->get_procedure( );

		$amount_paid = $data['amount'] + $this_procedure['procamtpaid'];
		$current_balance = $this_procedure['procbalorig'] -
				$amount_paid;

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'procbalcurrent' => $current_balance,
				'procamtpaid' => $amount_paid
			), array ( 'id' => $data['procedure'] )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );

		return ($proc_query and $pay_query);
	} // end method post_payment

	// Method: post_payment_cash
	//
	//	Posts a cash payment. This is a wrapper for post_payment.
	//
	// Parameters:
	//
	//	$procedure - Procedure id number
	//
	//	$coverage - Coverage that check is related to
	//
	//	$amount - Numeric amount of the check
	//
	//	$comment - Text comment to be attached to the ledger
	//
	// Returns:
	//
	//	Boolean, successful
	//
	// See Also:
	//	<post_payment>
	//
	function post_payment_cash ( $procedure, $coverage,
				$amount, $comment ) {
		// Determine patient from procedure
		$data['patient'] = $this->_procedure_to_patient($procedure);
		$data['procedure'] = $procedure;
		$data['amount'] = $amount;
		$data['coverage'] = $coverage;
		$data['type'] = '0'; // cash
		$data['comment'] = $comment;
		return $this->post_payment ( $data );		
	} // end method post_payment_cash

	// Method: post_payment_check
	//
	//	Posts a check payment. This is a wrapper for post_payment.
	//
	// Parameters:
	//
	//	$procedure - Procedure id number
	//
	//	$coverage - Coverage that check is related to
	//
	//	$check_number - Number appearing on the check
	//
	//	$amount - Numeric amount of the check
	//
	//	$comment - Text comment to be attached to the ledger
	//
	// Returns:
	//
	//	Boolean, successful
	//
	// See Also:
	//	<post_payment>
	//
	function post_payment_check ( $procedure, $coverage,
				$check_number, $amount, $comment ) {
		// Determine patient from procedure
		$data['patient'] = $this->_procedure_to_patient($procedure);
		$data['procedure'] = $procedure;
		$data['amount'] = $amount;
		$data['coverage'] = $coverage;
		$data['payment_detail_number'] = $check_number;
		$data['type'] = '1';
		$data['comment'] = $comment;
		return $this->post_payment ( $data );		
	} // end method post_payment_check

	// Method: post_payment_credit_card
	//
	//	Posts a credit card payment. This is a wrapper for
	//	post_payment.
	//
	// Parameters:
	//
	//	$procedure - Procedure id number
	//
	//	$coverage - Coverage that check is related to
	//
	//	$cc_number - Credit card number
	//
	//	$exp_m - Month expiration
	//
	//	$exp_y - Year expiration
	//
	//	$amount - Numeric amount of the check
	//
	//	$comment - Text comment to be attached to the ledger
	//
	// Returns:
	//
	//	Boolean, successful
	//
	// See Also:
	//	<post_payment>
	//
	function post_payment_credit_card ( $procedure, $coverage,
				$cc_number, $exp_m, $exp_y, $amount, $comment ) {
		// Determine patient from procedure
		$data['patient'] = $this->_procedure_to_patient($procedure);
		$data['procedure'] = $procedure;
		$data['amount'] = $amount;
		$data['coverage'] = $coverage;
		$data['payment_detail_number'] = $cc_number. ':' .
				$exp_m . ':' . $exp_y;
		$data['type'] = '3';
		$data['comment'] = $comment;
		return $this->post_payment ( $data );		
	} // end method post_payment_credit_card

	// Method: post_writeoff
	//
	//	Post a write-off of a particular type to the system for
	//	the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	//	$category - (optional) Category of writeoff. Defaults to
	//	WRITEOFF. Possible values are:
	//		* DENIAL
	//		* WRITEOFF
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function post_writeoff ( $procedure, $comment = '' ,
				$category = WRITEOFF ) {
		// Get information about this procedure
		$procedure_object = CreateObject('FreeMED.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Write-off to current balance amount
		$current_balance = $this_procedure['procbalcurrent'];

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreccat' => $category,
				'payrecproc' => $procedure,
				'payrecamt' => $current_balance,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'procbalcurrent' => '0'
			), array ( 'id' => $procedure )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );

		return ($proc_result and $pay_result);
	} // end method post_writeoff

	// Method: unpostable
	//
	//	Adds an "unpostable" sum to the global claim log. Currently
	//	updates the "global" event log (where procedure = 0).
	//
	// Parameters:
	//
	//	$amount - Unpostable amount
	//
	//	$comment - Text comment (with check number, etc)
	//
	// Returns:
	//
	//	Boolean, succesful
	//
	function unpostable ( $amount, $comment ) {
		$cl = CreateObject('_FreeMED.ClaimLog');
		return $cl->log_event ( 0, array(
			'comment' => '$'.bcadd($amount,0,2).' '.
				__("Unpostable")." (".$comment.")"
			)
		);
	} // end method unpostable

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

} // end class Ledger

?>
