<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.Ledger
class Ledger {

	// STUB constructor
	public function __constructor ( ) { }

	// Method: _validYmdDate
	//
	//	ImportDate() with a Y-m-d shape check (Task 2.6f, the F4 rule):
	//	ImportDate() answers boolean false for input it cannot parse, and the
	//	driver's quote(false) emits a bare 0 - which MySQL compares equal to its
	//	zero-date ('0000-00-00'), so an unparseable date could match a row that
	//	the old hand-quoted literal could not. Only a real Y-m-d value is
	//	returned; anything else is false and the caller refuses the query
	//	(logged) rather than emitting a predicate with a wrong value in it.
	//	Same contract and log text as Scheduler::_ValidDate (batch C, F4).
	//
	// Parameters:
	//
	//	$input - Date string in any format ImportDate() accepts
	//
	// Returns:
	//
	//	'Y-m-d' string, or boolean false
	//
	protected function _validYmdDate ( $input ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$date = $s->ImportDate( $input );
		if ( !preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', (string) $date ) ) {
			syslog( LOG_ERR, get_class($this).'| refusing non-Y-m-d date '.var_export($input, true) );
			return false;
		}
		return $date;
	} // end method _validYmdDate

	// Method: AgingReportQualified
	//
	//	Provide an "aging summary" (with number of claims and
	//	amount due) for a range of agings, grouped by patient.
	//
	//	This function is primarily used by the accounts
	//	receivable module, but could conceivably be used by
	//	other financial parts of FreeMED.
	//
	// Parameters:
	//
	//	$criteria - Associative array of criteria types as
	//	keys and parameters as values.
	//
	// Returns:
	//
	//	Array of associative arrays containing aging information.
	//
	public function AgingReportQualified ( $criteria ) {
		freemed::acl_enforce( 'financial', 'read' );
		foreach ($criteria AS $k => $v) {
			//print "criteria key = $k, value = $v<hr/>\n";
			switch ($k) {
				case 'aging':
				switch ($v) {
					case '0-30': case '31-60':
					case '61-90': case '91-120':
					list($lower,$upper)=explode('-', $v);
					break;

					case '120+':
					$lower='120'; $upper='10000';
					break;
				} // end inner aging switch
				// Category A (2.6f): both operands sit in an UNQUOTED numeric
				// context (an age in days), so they are cast rather than
				// escaped - addslashes() on a number was never escaping.
				if ($upper) $q[] =
				"(TO_DAYS(NOW()) - TO_DAYS(pa.payrecdt) >= ".intval( $lower ).") AND ".
				"(TO_DAYS(NOW()) - TO_DAYS(pa.payrecdt) <= ".intval( $upper ).")";
				break; // end aging case

				case 'billed':
				// Category A (2.6f): procbilled is a numeric flag.
				if ($v == '0' or $v == '1') { $q[] = "p.procbilled = ".intval($v); }
				break; // end billed case

				case 'date':
				// Category A + F4 (2.6f): a date *criterion* - validated as
				// Y-m-d and refused (log + this method's empty answer) rather
				// than quoted, because quote(false) is a bare 0 which matches
				// MySQL's zero-date. Refused rather than dropped: dropping the
				// criterion would silently return a wider financial report.
				if ($v) {
					$dtv = $this->_validYmdDate( $v );
					if ( $dtv === false ) { return array(); }
					$q[] = "pa.payrecdt = ".$GLOBALS['sql']->quote( $dtv );
				}
				break; // end date

				case 'date_of':
				// Category A + F4 (2.6f): a date criterion, as above.
				if ($v) {
					$dtv = $this->_validYmdDate( $v );
					if ( $dtv === false ) { return array(); }
					$q[] = "p.procdt = ".$GLOBALS['sql']->quote( $dtv );
				}
				break; // end procedure date

				case 'procedure':
				// Category A (2.6f): record ids, cast (numeric columns).
				if ($v) $q[] = "p.id = ".intval($v);
				break; // end procedure case

				case 'provider':
				// Category A (2.6f): provider id, cast.
				if ($v) $q[] = "pr.id = ".intval($v);
				break; // end provider case

				case 'facility':
				// Category A (2.6f): facility id, cast.
				if ($v) $q[] = "p.procpos = ".intval($v);
				break; // end facility case
				
				case 'patient':
				// Category A (2.6f): patient id, cast.
				if ($v) $q[] = "pt.id = ".intval($v);
				break; // end patient case

				case 'first_name':
				// Category A (2.6f): a value, so the driver quotes it (with
				// the wildcards inside the quoted literal).
				if ($v) $q[] = "pt.ptfname LIKE ".$GLOBALS['sql']->quote('%'.$v.'%');
				break; // end first name

				case 'last_name':
				// Category A (2.6f): as above.
				if ($v) $q[] = "pt.ptlname LIKE ".$GLOBALS['sql']->quote('%'.$v.'%');
				break; // end last name

				case 'type':
				// Category A (2.6f): payreccat is a numeric category.
				if ($v) $q[] = "pa.payreccat = ".intval($v);
				break;
				
				case 'date_from':
				// Category A + F4 (2.6f): a date criterion, so it is validated
				// as Y-m-d (via ImportDate) and refused with a log on failure
				// instead of being quoted - pre-sweep an unparseable value was
				// compared as '' (i.e. the zero-date), which is exactly the
				// hazard F4 names.
				if ($v) {
					$dtv = $this->_validYmdDate( $v );
					if ( $dtv === false ) { return array(); }
					$q[] = "pa.payrecdtadd >= ".$GLOBALS['sql']->quote( $dtv );
				}
				break;
				
				case 'date_to':
				// Category A + F4 (2.6f): as above.
				if ($v) {
					$dtv = $this->_validYmdDate( $v );
					if ( $dtv === false ) { return array(); }
					$q[] = "pa.payrecdtadd <= ".$GLOBALS['sql']->quote( $dtv );
				}
				break;
				
				case 'tag':
				$tag_object = CreateObject('org.freemedsoftware.module.PatientTag');
				$obj = $tag_object->SimpleTagSearch($v);
				for($i = 0; $i < count($obj); $i++){
					$patient_ids[] = "p.procpatient = '".$obj[$i]['patient_record']."'";
				}
				$condition = join(' OR ', $patient_ids);
				if($condition != "") {
					$condition = '('.$condition.')';
				}
				$q[] = $condition;
				break;
			} // end outer criteria type switch
		} // end criteria foreach loop

		//print "debug: criteria = ".join(' AND ', $q)." <br/>\n";

		$query = "SELECT ".
			"CONCAT(pt.ptlname, ', ', pt.ptfname, ' ', pt.ptmname) AS patient, ".
			"pt.id AS patient_id, ".
			"CONCAT(pr.phyfname, ' ', pr.phylname) AS provider, ".
			"pr.id AS provider_id, ".
			"ROUND(p.procamtpaid, 2) AS total_amount_paid, ".
			"ROUND(p.procbalcurrent, 2) AS total_balance, ".
			// Simulate double ledger....
			"ROUND(IF(FIND_IN_SET(pa.payreccat, '0,1,7,8,11'), pa.payrecamt, 0), 2) AS money_in, ".
			"ROUND(IF(FIND_IN_SET(pa.payreccat, '0,1,7,8,11'), 0, pa.payrecamt), 2) AS money_out, ".
			"p.id AS procedure_id, ".
			"p.procdt AS date_of, ".
			"DATE_FORMAT(p.procdt, '%m/%d/%Y') AS date_of_mdy, ".
			"pa.payrecdtadd AS payment_date, ".
			"DATE_FORMAT(pa.payrecdt, '%m/%d/%Y') AS payment_date_mdy, ".
			"pa.payreccat AS item_type_id, ".
			"CASE pa.payreccat ".
				"WHEN 0 THEN ".$GLOBALS['sql']->quote(__("Payment"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 1 THEN ".$GLOBALS['sql']->quote(__("Adjustment"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 2 THEN ".$GLOBALS['sql']->quote(__("Refund"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 3 THEN ".$GLOBALS['sql']->quote(__("Denial"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 4 THEN ".$GLOBALS['sql']->quote(__("Rebill"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 5 THEN ".$GLOBALS['sql']->quote(__("Charge"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 6 THEN ".$GLOBALS['sql']->quote(__("Transfer"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 7 THEN ".$GLOBALS['sql']->quote(__("Withholding"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 8 THEN ".$GLOBALS['sql']->quote(__("Deductable"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 9 THEN ".$GLOBALS['sql']->quote(__("Fee Adjustment"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 10 THEN ".$GLOBALS['sql']->quote(__("Billed"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 11 THEN ".$GLOBALS['sql']->quote(__("Copayment"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"WHEN 12 THEN ".$GLOBALS['sql']->quote(__("Writeoff"))." ". // 2.6f: label quoted by the driver, not addslashes()ed inside hand-written quotes
				"ELSE ".$GLOBALS['sql']->quote(__("Unknown"))." END AS item_type, ". // 2.6f: same class as the labels above
			"pa.id AS item ".
			"FROM procrec p ".
			"LEFT OUTER JOIN payrec pa ON pa.payrecproc=p.id ".
			"LEFT OUTER JOIN patient pt ON pt.id=p.procpatient ".
			"LEFT OUTER JOIN physician pr ON pr.id=p.procphysician ".
			"WHERE ".
			( is_array($q) ? join(' AND ', $q) : ' ( 1 > 0 ) ' )." ".
			"ORDER BY date_of DESC, item DESC";
		//print "<hr/>query = \"$query\"<hr/>\n";
		$result = $GLOBALS['sql']->queryAll ( $query );
		$return = array ( );
		foreach ( $result AS $r ) {
			// Make sure to deserialize the id map, since
			// we can't actually extract values from it using
			// SQL regex's, or if we could, it would be a
			// huge waste of processor time...
			if (is_array(@unserialize($r['id_map']))) {
				$id_map = unserialize($r['id_map']);
				$r['id_map'] = $id_map[$r['_provider']];
			} else {
				$id_map = array ();
			}
			$return[] = $r;
			 // patient, claims, paid, balance, ratio
		} 
		return $return;
	} // end method AgingReportQualified

	// Method: collection_warning
	//
	//	Determine if the selected patient is in collections
	//	status (>180 days unpaid).
	//
	// Parameters:
	//
	//	$pid - Patient record id
	//
	// Returns:
	//
	//	Amount in collections, or a testing false value (0)?
	//
	function collection_warning ( $pid ) {
		// Category A (2.6f): procpatient is BIGINT UNSIGNED, so the patient id is
		// cast in this UNQUOTED context rather than addslashes()ed inside
		// hand-written quotes.
		$r = $GLOBALS['sql']->queryRow(
		       	"SELECT	sum(procbalcurrent) AS outstanding ".
			"FROM procrec ".
			"WHERE TO_DAYS(NOW())-TO_DAYS(procdt) > 180 ".
			"AND procpatient=".intval($pid));
		if ($r['outstanding']) { return bcadd($r['outstanding'],0,2); }
		return false; // fall through to this
	} // end method collection_warning
	
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
		return $GLOBALS['sql']->queryAll( $query );
	} // end method get_list

	// Method: GetClaims
	//
	//	Retrieve a list of claims from the system.
	//
	// Parameters:
	//
	//	$p - Array of procedure ids
	//
	// Returns:
	//
	//	Array of hashes containing procedure data.
	//
	public function GetClaims ( $p ) {
		$use = array();
		foreach ($p AS $v ) { $use[] = $v + 0; }
		$q = "SELECT pr.id AS claim_id, pr.procdt AS date_of_service, DATE_FORMAT(pr.procdt, '%m/%d/%Y') AS date_of_service_mdy, TRUNCATE(pr.proccharges, 2) AS billed_amount, CONCAT( c.cptcode, ' (', c.cptnameint, ')' ) AS cpt, pr.procamtpaid AS amount_paid FROM procrec pr LEFT OUTER JOIN cpt c ON c.id=pr.proccpt WHERE FIND_IN_SET(pr.id, ".$GLOBALS['sql']->quote(join(',', $use)).")";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetClaims

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
		$this_procedure = $GLOBALS['sql']->get_link( 'procrec', $proc );
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
			// Category A (2.6b F6c): the coverage type is a code and the
			// procedure id is a record id - both are quoted/cast where the
			// statement is composed, rather than addslashes()ed inside
			// hand-written quotes.
			//
			// NOTE (2.6f): $disallow is a MONEY amount. The sweep's intval()
			// TRUNCATED THE CENTS (3.50 -> 3) where the pre-sweep ($disallow + 0)
			// kept them, so this repairs a regression the sweep introduced.
			// number_format((float) ..., 2, '.', '') is locale-independent and
			// emits a plain numeric literal (never scientific notation), so the
			// amount stays a number in this arithmetic context - a quoted string
			// would additionally depend on MySQL's implicit cast.
			$query = sprintf( "UPDATE procrec SET procbilled = '0', proccurcovtp = %s, procbalcurrent = procbalcurrent - %s WHERE id = %d",
				$GLOBALS['sql']->quote( $type ),
				number_format( (float) $disallow, 2, '.', '' ),
				intval( $proc ) );
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
		syslog(LOG_INFO, "queue_for_rebill | query = $query");
		$result = $GLOBALS['sql']->query ( $query );

		// Adjust internal proccurcovid
		if ($type > 0) {
			// Category A (2.6b F6c): the coverage column selector and the record
			// id are both cast; no addslashes() value sits inside quotes here
			// either (same method, same statement family as above).
			$query = sprintf( 'SELECT proccov%d AS coverage FROM procrec WHERE id = %d',
				intval( $type ), intval( $proc ) );
			$result = $GLOBALS['sql']->queryRow($query);
			$coverage=$result['coverage'];
			extract( $result );
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
		syslog(LOG_INFO, "queue_for_rebill | query = $query");
		$result &= $GLOBALS['sql']->query ( $query );
		return (boolean) $result;
	} // end method queue_for_rebill

	// Method: post_adjustment
	//
	//	Post an adjustment for the specified procedure.
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
	function post_adjustment ( $procedure, $amount, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecdt' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreccat' => ADJUSTMENT,
				'payrecproc' => $procedure,
				'payrecamt' => abs($amount),
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		return (boolean) $pay_result;
	} // end method post_adjustment

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
	function post_copay ( $data) {
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => $data['pdate'],
				'payrecdtmod' => $data['pdate'],
				'payrecdt' => $data['pdate'],
				'payrecpatient' => $data['patient'],
				'payreccat' => COPAY,
				'payrecproc' => $data['procedure'],
				'payrecsource' => $data['source'],
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

		return (boolean) $pay_result;
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
	function post_deductable ( $data) {
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => $data['pdate'],
				'payrecdtmod' => $data['pdate'],
				'payrecdt' => $data['pdate'],
				'payrecpatient' => $data['patient'],
				'payreccat' => DEDUCTABLE,
				'payrecproc' => $data['procedure'],
				'payrecsource' => $data['source'],
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

		return (boolean) $pay_result;
	} // end method post_deductable
	
	// Method: post_withhold
	//
	//	Post a withhold for the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$amount - Amount of the withhold, as a positive number
	//
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function post_withhold( $procedure, $amount, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecdt' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreccat' => WITHHOLD,
				'payrecproc' => $procedure,
				'payrecamt' => $amount,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		return (boolean) $pay_result;
	} // end method post_withhold
	
	// Method: post_refund
	//
	//	Post a refund for the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$amount - Amount of the withhold, as a positive number
	//
	//	$destination
	//
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	
	function post_refund( $procedure, $amount, $destination, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecdt' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreclink' => $destination,
				'payreccat' => REFUND,
				'payrecproc' => $procedure,
				'payrecamt' => $amount,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		return (boolean) $pay_result;
	} // end method post_refund
	
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
	function post_fee_adjustment ( $procedure, $source,
				$amount, $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Figure out who gave us this
		if($source!=0){
			$coverage=$this_procedure['proccov'.$source];
			$coverage_object = CreateObject('org.freemedsoftware.core.Coverage', $coverage);
			$this_coverage = $coverage_object->get_coverage();
			$who = $this_coverage['covinsco'];
		}
		else{
			$source=0;
		}
		// Calculate the new proc charges
		$new_amount = $this_procedure['proccharges'] - abs($amount);

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecdt' => date('Y-m-d'),
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
				$this_procedure['procbalcurrent'] - $new_amount,

				'proccharges' =>
				$this_procedure['proccharges'] - $new_amount
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
				'payrecdtadd' => $data['pdate'],
				'payrecdtmod' => $data['pdate'],
				'payrecdt' => $data['pdate'],
				'payrecpatient' => $data['patient'],
				'payreccat' => '0', // payment
				'payrecproc' => $data['procedure'],
				'payrecsource' => $data['source'],
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

		return (boolean) $pay_result;
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
	function post_payment_cash ( $procedure, $pdate,
				$amount, $comment, $ptid='',$cov='',$category = PAYMENT  ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );
		
		if($cov!=''){
			$data['patient'] = $ptid;			
			if($cov==0){
				$coverage=0;
				$source=0;
			}
			else{				
				$coverage=$cov;
				$coverage_object = CreateObject('org.freemedsoftware.core.Coverage', $cov);
				$this_coverage = $coverage_object->get_coverage( );
				$source=$this_coverage["covtype"];
			}
		}
		else{
			$data['patient'] = $this->_procedure_to_patient($procedure);
			$source=$this_procedure['proccurcovtp'];
			if($source==0){
				$coverage=0;
			}
			else{				
				$coverage=$this_procedure['proccurcovid'];
			}
		}
		// Determine patient from procedure
		$data['pdate']=$pdate;
		
		$data['procedure'] = $procedure;
		$data['amount'] = $amount;
		$data['coverage'] = $coverage;
		$data['type'] = '0'; // cash
		$data['source']=$source;
		$data['comment'] = $comment;
		if($category == PAYMENT)
			return $this->post_payment ( $data );
		else if($category == DEDUCTABLE)
			return $this->post_deductable ( $data );
		else if($category == COPAY)
			return $this->post_copay ( $data );	
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
	function post_payment_check ( $procedure, $pdate,
				$check_number, $type, $amount, $comment, $ptid='', $cov='',$category = PAYMENT   ) {
		
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );
		if($cov!=''){	
			$data['patient'] = $ptid;		
			if($cov==0){
				$coverage=0;
				$source=0;
			}
			else{				
				$coverage=$cov;
				$coverage_object = CreateObject('org.freemedsoftware.core.Coverage', $cov);
				$this_coverage = $coverage_object->get_coverage( );
				$source=$this_coverage["covtype"];
			}
		}
		else{
			$data['patient'] = $this->_procedure_to_patient($procedure);
			$source=$this_procedure['proccurcovtp'];
			if($source==0){
				$coverage=0;
			}
			else{				
				$coverage=$this_procedure['proccurcovid'];
			}
		}
		
		// Determine patient from procedure
		$data['pdate']=$pdate;
		$data['procedure'] = $procedure;
		$data['amount'] = $amount;
		$data['coverage'] = $coverage;
		$data['payment_detail_number'] = $check_number;
		$data['type'] = $type;
		$data['source']=$source;
		$data['comment'] = $comment;
		if($category == PAYMENT)
			return $this->post_payment ( $data );
		else if($category == DEDUCTABLE)
			return $this->post_deductable ( $data );	
		else if($category == COPAY)
			return $this->post_copay ( $data );
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
	function post_payment_credit_card ( $procedure, $pdate,
				$cc_number, $exp_m, $exp_y, $amount, $comment, $ptid='', $cov='',$category = PAYMENT  ) {
		
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );
		$this_procedure = $procedure_object->get_procedure( );
		if($cov!=''){
			$data['patient'] = $ptid;			
			if($cov==0){
				$coverage=0;
				$source=0;
			}
			else{				
				$coverage=$cov;
				$coverage_object = CreateObject('org.freemedsoftware.core.Coverage', $cov);
				$this_coverage = $coverage_object->get_coverage( );
				$source=$this_coverage["covtype"];
			}
		}
		else{
			$data['patient'] = $this->_procedure_to_patient($procedure);
			$source=$this_procedure['proccurcovtp'];
			if($source==0){
				$coverage=0;
			}
			else{				
				$coverage=$this_procedure['proccurcovid'];
			}
		}
		// Determine patient from procedure
		$data['pdate']=$pdate;
		$data['procedure'] = $procedure;
		$data['amount'] = $amount;
		$data['coverage'] = $coverage;
		$data['payment_detail_number'] = $cc_number. ':' .
				$exp_m . ':' . $exp_y;
		$data['type'] = '3';
		$data['source']=$source;
		$data['comment'] = $comment;
		if($category == PAYMENT)
			return $this->post_payment ( $data );
		else if($category == DEDUCTABLE)
			return $this->post_deductable ( $data );
		else if($category == COPAY)
			return $this->post_copay ( $data );
					
	} // end method post_payment_credit_card

	// Method: post_transfer
	//
	//	Post a transfer of a particular type to the system for
	//	the specified procedure.
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$coverage - Coverage that check is related to
	//	$comment - (optional) Text comment for the record. Defaults
	//	to a null string.
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function post_transfer ( $procedure, $source,
				 $comment = '' ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
		$this_procedure = $procedure_object->get_procedure( );

		// Derive the patient from the procedure
		$patient = $this->_procedure_to_patient ( $procedure );

		// Figure out who gave us this
		if($source!=0)
		{
			$coverage=$this_procedure['proccov'.$source];
		}
		else
		{
			$coverage=0;
		}

		// Calculate the new proc charges
		$new_amount = $this_procedure['procbalcurrent'];

		// Create payment record query
		$query = $GLOBALS['sql']->insert_query (
			'payrec',
			array (
				'payrecdtadd' => date('Y-m-d'),
				'payrecdtmod' => date('Y-m-d'),
				'payrecdt' => date('Y-m-d'),
				'payrecpatient' => $patient,
				'payreclink' => $coverage,
				'payreccat' => TRANSFER,
				'payrecsource' => $source,
				'payrecproc' => $procedure,
				'payrecamt' => $new_amount ,
				'payrecdescrip' => $comment,
				'payreclock' => 'unlocked'
			)
		);
		$pay_result = $GLOBALS['sql']->query ( $query );

		$query = $GLOBALS['sql']->update_query(
			'procrec',
			array (
				'proccurcovtp' => $source,
				'proccurcovid' => $coverage,
				'procbilled'   => '0',
			), array ( 'id' => $procedure )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );
		                             
		return ($proc_result and $pay_result);
	} // end method post_transfer
	
	
	// Method: PostWriteoff
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
	public function PostWriteoff ( $procedure, $comment = '', $category = WRITEOFF ) {
		// Get information about this procedure
		$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procedure);
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
				'payrecdt' => date('Y-m-d'),
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
				'proccharges' => 
				$this_procedure['proccharges'] - $current_balance,

				'procbalcurrent' =>
				$this_procedure['proccharges'] - $current_balance - $this_procedure['procamtpaid']
			), array ( 'id' => $procedure )
		);
		$proc_result = $GLOBALS['sql']->query ( $query );
                             
		return ($proc_result and $pay_result);
	} // end method PostWriteoff

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
	//	Boolean, successful
	//
	function unpostable ( $amount, $comment ) {
		$cl = CreateObject('org.freemedsoftware.api.ClaimLog');
		return $cl->log_event ( 0, array(
			'comment' => '$'.bcadd($amount,0,2).' '.
				__("Unpostable")." (".$comment.")"
			)
		);
	} // end method unpostable

	// Method: WriteoffItems 
	//
	//	Write off an array of items
	//
	// Parameters:
	//
	//	$a - Array of items
	//
	// Returns:
	//
	//	Boolean, successful
	//
	public function WriteoffItems ( $a ) {
		// Category A (2.6b F6c): the id list is cast element-wise and then
		// quoted by the driver - FIND_IN_SET() takes a string list, so quote()
		// supplies the quotes (the batch-B CalendarGroup IN () fix is the same
		// shape). It was addslashes()ed inside hand-written quotes.
		$query = sprintf( "SELECT pr.id AS procedure_id FROM payrec AS p LEFT OUTER JOIN procrec pr ON p.payrecproc=pr.id WHERE FIND_IN_SET(p.id, %s) AND p.payrecproc = pr.id",
			$GLOBALS['sql']->quote( join(',', array_map('intval', (array) $a)) ) );
		$res = $GLOBALS['sql']->queryAll( $query );
		foreach ( $res AS $r ) {
			$items[$r['procedure_id']] = $r['procedure_id'];
		} // end while fetch array

		$result = true;
		foreach ($items AS $v) {
			$result &= $this->PostWriteoff ( $v );
		}
		return $result;
	} // end method WriteoffItems

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
		$result = $GLOBALS['sql']->queryAll ( $query );
		foreach ( $result AS $r ) {
			if ($sequential) {
				$return[] = $r;
			} else {
				$return[$r['id']] = $r;
			}
		}
		return $return;
	} // end method _query_to_result_array

	public function getLedgerInfo($procid){
		$pay_query  = "SELECT id AS Id, payrecdt AS pay_date, payrecdescrip AS pay_desc, payrecamt AS pay_amount, payrectype AS pay_type, ".
		"payrecproc as pay_proc,payreccat as pay_cat,payrecsource as pay_src FROM payrec WHERE payrecproc=".$GLOBALS['sql']->quote( $procid )." ORDER BY id ASC";
		//return $pay_query;
		$pay_result = $GLOBALS['sql']->queryAll ( $pay_query );
		//return $pay_result;
		$total_charges = 0;
		$total_payments =0;
		for ($i=0;$i<count($pay_result);$i++) {
			$r = $pay_result[$i];
	                $payrecdate      = $r["pay_date"];
	                $payrecdescrip   = $r["pay_desc"];
	                $payrecamt       = $r["pay_amount"];
	                $payrectype      = $r["pay_type"];
	                $payreccat	= $r["pay_cat"];	
			$payrecsource	=$r["pay_src"];
			$data[$i]['date']=$payrecdate;
			$data[$i]['desc']=$payrecdescrip;
			
			switch ($payreccat) {
		                case ADJUSTMENT: // adjustments 1
		                   $data[$i]['type'] = 'Adjustment';
	                    	   $data[$i]['payment']= bcadd($payrecamt, 0, 2);
		                   $data[$i]['charge'] = "";
		                   $total_payments += $payrecamt;
		                   break;
		                case REFUND: // refunds 2
		                    $data[$i]['type'] = 'Refund';
		                    $data[$i]['charge'] = bcadd($payrecamt, 0, 2);;
		                    $data[$i]['payment'] = "";
		                    $total_charges  += $payrecamt;
		                    break;
		                case DENIAL: // denial 3
		                    $data[$i]['type'] = 'Denial';
		                    $data[$i]['charge']= "".(-1.0*bcadd(($payrecamt), 0, 2));
		                    $data[$i]['payment'] = "";
		                     $total_charges  -= $payrecamt;
		                    break;
		                case WRITEOFF: // writeoff 12 
		                    $data[$i]['type'] = 'Writeoff';
		                    $data[$i]['charge'] = "".(-1.0*bcadd(($payrecamt), 0, 2));
		                    $data[$i]['payment'] = "";
		                    $total_charges  -= $payrecamt;
		                    break;
		                case REBILL: // rebill 4
		                    $data[$i]['type']  = 'Rebill';
		                    $data[$i]['charge'] = "";
		                    $data[$i]['payment'] = "";
		                    break;
		                case PROCEDURE: // charge 5
		                    $data[$i]['type'] = 'Charge';
		                    $data[$i]['charge'] = bcadd($payrecamt, 0, 2);
		                    $data[$i]['payment'] = "";
		                     $total_charges  += $payrecamt;
		                    break;
		                case TRANSFER: // transfer 6
		                     $ptypearray=unserialize(PAYER_TYPES);
		                    $data[$i]['type'] = "Transfer to ".$ptypearray[$payrecsource+0];
		                    $data[$i]['charge'] = "";
		                    $data[$i]['payment'] = "";
		                    break;
		                case WITHHOLD: // withhold 7
		                    $data[$i]['type'] = 'Withhold';
		                     $data[$i]['charge']= "".(-1.0*bcadd(($payrecamt), 0, 2));
		                    $data[$i]['payment'] = "";
		                     $total_charges  -= $payrecamt;
		                    break;
		                case DEDUCTABLE: // deductable 8
		                    $data[$i]['type'] = 'Deductable';
		                    $data[$i]['charge']= "".(-1.0*bcadd(($payrecamt), 0, 2));
		                    $data[$i]['payment'] = "";
		                     $total_charges  -= $payrecamt;
		                    break;
		                case FEEADJUST: // feeadjust 9
		                    $data[$i]['type'] = 'Allowed Amount - Fee Adjusted';
		                    $data[$i]['charge']= "".(-1.0*bcadd(($payrecamt), 0, 2));
		                    $data[$i]['payment'] = "";
		                    $total_charges  -= $payrecamt;
		                    break;
		                case BILLED: // billed 10
		                   $ptypearray=unserialize(PAYER_TYPES);
		                   $data[$i]['type'] = "Billed ".$ptypearray[$payrecsource+0];
		                   $data[$i]['charge'] = "";
		                   $data[$i]['payment'] = "";
		                   break;
		                case COPAY: // COPAY 11
		                   $data[$i]['type'] = 'Copay';
		                   $data[$i]['payment']= bcadd($payrecamt, 0, 2);
		                   $data[$i]['charge'] = "";
		                   $total_payments += $payrecamt;
		                   break;
		                case PAYMENT: 		                  
		                default:  // default is payment
		                    $ptypearray=unserialize(PAYER_TYPES);
		                    $data[$i]['type'] = "Payment ".$ptypearray[$payrecsource+0];
		                    $data[$i]['payment']= bcadd($payrecamt, 0, 2);
		                    $data[$i]['charge'] = "";
		                    $total_payments += $payrecamt;
		                    break;
	                } // end of categry switch (name)
	          	
           	 } // wend?
           	 $index=count($data);
           	 $data[$index]['total_charges']="".$total_charges;
           	  $data[$index]['total_payments']="".$total_payments;
                return $data;
	}
	
	public function getCoveragesCopayInfo($ptid, $procid){
		if($procid==0){
			$query="SELECT c.id AS Id, i.insconame AS cov_ins, c.covcopay AS copay, c.covtype AS type from coverage c ".
			"LEFT OUTER JOIN insco i ON c.covinsco = i.id where c.covcopay >0 AND c.covpatient=".$GLOBALS['sql']->quote( $ptid )." ORDER BY c.covtype LIMIT 1";
		}
		else{
			$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procid);
			$this_procedure = $procedure_object->get_procedure( );
			$covid=$this_procedure['proccurcovid'];
			$query="SELECT c.id AS Id, i.insconame AS cov_ins, c.covcopay AS copay, c.covtype AS type from coverage c ".
			"LEFT OUTER JOIN insco i ON c.covinsco = i.id where c.covcopay >0 AND c.id=".intval($covid);			
		}
		
		$result = $GLOBALS['sql']->queryRow($query);
		if($result!= NULL){
			$type=$result['type']+0;
			$ptypearray=unserialize(PAYER_TYPES);
			$result['type']=$ptypearray[$type];
		}

		return $result;
	}
	
	public function getCoveragesDeductableInfo($ptid, $procid){
		if($procid==0){
			$query="SELECT c.id AS Id, i.insconame AS cov_ins, c.covdeduct AS deduct, c.covtype AS type from coverage c ".
			"LEFT OUTER JOIN insco i ON c.covinsco = i.id where c.covdeduct >0 AND c.covpatient=".$GLOBALS['sql']->quote( $ptid )." ORDER BY c.covtype LIMIT 1";
		}
		else{
			$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $procid);
			$this_procedure = $procedure_object->get_procedure( );
			$covid=$this_procedure['proccurcovid'];
			$query="SELECT c.id AS Id, i.insconame AS cov_ins, c.covdeduct AS deduct, c.covtype AS type from coverage c ".
			"LEFT OUTER JOIN insco i ON c.covinsco = i.id where c.covdeduct >0 AND c.id=".intval($covid);
		}
		$result = $GLOBALS['sql']->queryRow($query);
		if($result!=NULL){
			$type=$result['type']+0;
			$ptypearray=unserialize(PAYER_TYPES);
			$result['type']=$ptypearray[$type];
		}

		return $result;
	}
	
	public function mistake($procid) {

		$query = "DELETE FROM payrec WHERE payrecproc=".$GLOBALS['sql']->quote( $procid );
                $pay_result = $GLOBALS['sql']->queryAll($query);
                
                $query = "DELETE FROM procrec WHERE id=".$GLOBALS['sql']->quote( $procid );
                $proc_result = $GLOBALS['sql']->queryAll($query);
               
                return ($proc_result and $pay_result); 
        }

} // end class Ledger

?>
