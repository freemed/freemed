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

	// Method: aging_summary_full
	//
	//	Provide aging summary grouped by payer, for the common
	//	age ranges (0-30, 31-60, 61-90, 91-120, 120+)
	//
	// Parameters:
	//
	//	$provider - (optional) Provider id key if the search is to
	//	be restricted by provider. Defaults to disable.
	//
	// Return:
	//
	//	Multidimensional array containing aging information.
	//
	// See Also:
	//	<aging_summary_range>
	//
	function aging_summary_full ( $provider = 0 ) {
		$p = $provider;
		$summary['0-30'] = $this->aging_summary_range(0, 30, $p);
		$summary['31-60'] = $this->aging_summary_range(31, 60, $p);
		$summary['61-90'] = $this->aging_summary_range(61, 90, $p);
		$summary['91-120'] = $this->aging_summary_range(91, 120, $p);
		$summary['120+'] = $this->aging_summary_range(121, 100000, $p);

		// Re-sort everything into some kind of sense
		foreach ($summary AS $k => $_v) {
			foreach ($_v AS $v) {
				$key = $v['payer'];
			//	print "v = "; print_r($v)."\n";
			//	print "v[payer] = ".$v['payer']."\n";
				$result[$key]['paid'] += $v['paid'];
				$result[$key]['total_amount'] += $v['balance'];
				$result[$key]['total_claims'] += $v['claims'];
				$result[$key][$k]['amount'] = $v['balance'];
				$result[$key][$k]['claims'] = $v['claims'];
			}
		}
		return $result;
	} // end method aging_summary_full

	// Method: aging_summary_range
	//
	//	Provide an "aging summary" (with number of claims and
	//	amount due) for a range of ages. Can be restricted by
	//	provider.
	//
	// Parameters:
	//
	//	$lower - Lower aging range in days.
	//
	//	$upper - Upper aging range in days.
	//
	//	$provider - (optional) Provider to restrict search by.
	//	Defaults to disabled.
	//
	// Returns:
	//
	//	Array of associative arrays containing aging information.
	//
	// See Also:
	//	<aging_summary_full>
	//
	function aging_summary_range ( $lower, $upper, $provider = 0 ) {
		$query = "SELECT i.insconame AS payer,".
			"COUNT(p.id) AS claims, ".
			"SUM(p.procamtpaid) AS paid, ".
			"SUM(p.procbalcurrent) AS balance, ".
			// support ratio of paid $ to not paid $
			"1 / (SUM(p.procamtpaid) / SUM(p.procbalcurrent)) AS ratio ".
			"FROM procrec AS p, coverage AS c, insco AS i ".
			"WHERE p.proccurcovid=c.id AND ".
			// Handle narrowing by provider
			( $provider > 0 ? "p.procphy = '".addslashes($provider)."' AND " : "" ).
			"c.covinsco=i.id AND ".
			// lower bounds
			"(TO_DAYS(NOW()) - TO_DAYS(p.procdt) >= ".addslashes($lower).") AND ".
			// upper bounds
			"(TO_DAYS(NOW()) - TO_DAYS(p.procdt) <= ".addslashes($upper).") ".
			"GROUP BY i.id";
		//print "query = \"$query\"<br/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		$return = array ( );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[] = $r;
			 // payer, claims, paid, balance, ratio
		} 
		return $return;
	} // end method aging_summary_range

/*
SELECT i.insconame AS payer, COUNT(p.id) AS claims, 
SUM(p.procamtpaid) AS paid, 
SUM(p.procbalcurrent) AS balance, 
1 / (SUM(p.procamtpaid) / SUM(p.procbalcurrent)) AS r_money
FROM procrec AS p, coverage AS c, insco AS i WHERE p.proccurcovid=c.id AND c.covinsco=i.id AND (TO_DAYS(NOW()) - TO_DAYS(p.procdt)) <= 90 GROUP BY i.id;
*/

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
