<?php
	// $Id$
	// $Author$

// Function: ___sort_aging
//
//	It is a helper function which sorts by an embedded array. It should
//	only be used in <ClaimLog::aging_summary_full>, and should not have
//	ever been written; it makes up for a language shortcoming. It is
//	called by uasort().
//
// Parameters:
//
//	$a - Parameter 1
//
//	$b - Parameter 2
//
// Returns:
//
//	Sorted array
//
function ___sort_aging ( $a, $b ) {
	if ( $a['balance'] == $b['balance'] ) {
		return 0;
	}
	return ( ($a['balance'] > $b['balance']) ? -1 : 1 );
}

// Class: FreeMED.ClaimLog
//
//	Allows access to functions involving the internal FreeMED claim
//	log, which is used by the system to track billing, rebilling,
//	bill keys, et cetera.
//
class ClaimLog {

	// STUB constructor
	function ClaimLog ( ) { }

	// Method: aging_report_qualified
	//
	//	Provide an "aging summary" (with number of claims and
	//	amount due) for a range of agings, grouped by patient.
	//	Can be restricted by payer.
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
	function aging_report_qualified ( $criteria ) {
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
				if ($upper) $q[] =
				"(TO_DAYS(NOW()) - TO_DAYS(p.procdt) >= ".addslashes($lower).") AND ".
				"(TO_DAYS(NOW()) - TO_DAYS(p.procdt) <= ".addslashes($upper).")";
				break; // end aging case

				case 'date':
				if ($v) $q[] = "p.procdt = '".addslashes($v)."'";
				break; // end date

				case 'patient':
				if ($v) $q[] = "pt.id = '".addslashes($v)."'";
				break; // end patient case

				case 'first_name':
				if ($v) $q[] = "pt.ptfname LIKE '%".addslashes($v)."%'";
				break; // end first name

				case 'last_name':
				if ($v) $q[] = "pt.ptlname LIKE '%".addslashes($v)."%'";
				break; // end last name

				case 'payer':
				if ($v) $q[] = "c.covinsco = '".addslashes($v)."'";
				break; // end payer case

				case 'plan':
				if ($v) $q[] = "c.covplanname = '".addslashes($v)."'";
				break;

				case 'status':
				if ($v) $q[] = "p.procstatus = '".addslashes($v)."'";
				break;
			} // end outer criteria type switch
		} // end criteria foreach loop

		//print "debug: criteria = ".join(' AND ', $q)." <br/>\n";

		$query = "SELECT CONCAT(pt.ptlname, ', ', pt.ptfname, ".
			"' ', pt.ptmname) AS patient_name, ".
			"pt.id AS patient_id, ".
			"p.procdt AS date_of, ".
			"p.procstatus AS status, ".
			"p.id AS claim, ".
			"c.covinsco AS payer, ".
			"CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate, ')') AS payer_name, ".
			"p.procamtpaid AS paid, ".
			"p.procbalcurrent AS balance ".
			"FROM ".
				"procrec AS p, ".
				"coverage AS c, ".
				"insco AS i, ".
				"patient AS pt ".
			"WHERE ".
			"c.covinsco = i.id AND ".
			"p.procpatient = pt.id AND ".
			"p.proccurcovid = c.id AND ".
			( is_array($q) ? join(' AND ', $q) : ' ( 1 > 0 ) ' )." ".
			"ORDER BY patient_name, balance DESC";
		//print "<hr/>query = \"$query\"<hr/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		$return = array ( );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[] = $r;
			 // patient, claims, paid, balance, ratio
		} 
		return $return;
	} // end method aging_report_qualified

	// Method: aging_summary_payer_full
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
	//	<aging_summary_payer_range>
	//
	function aging_summary_payer_full ( $provider = 0 ) {
		$p = $provider;
		$summary['everything'] = $this->aging_summary_payer_range(0, 10000, $p);
		$summary['0-30'] = $this->aging_summary_payer_range(0, 30, $p);
		$summary['31-60'] = $this->aging_summary_payer_range(31, 60, $p);
		$summary['61-90'] = $this->aging_summary_payer_range(61, 90, $p);
		$summary['91-120'] = $this->aging_summary_payer_range(91, 120, $p);
		$summary['120+'] = $this->aging_summary_payer_range(121, 100000, $p);

		uasort($summary['everything'], "___sort_aging");

		// Re-sort everything into some kind of sense
		foreach ($summary AS $k => $_v) {
			foreach ($_v AS $v) {
				$key = $v['payer'];
			//	print "v = "; print_r($v)."\n";
			//	print "v[payer] = ".$v['payer']."\n";
				$result[$key]['payer_id'] = $v['payer_id'];
				$result[$key]['paid'] += $v['paid'];
				$result[$key]['total_amount'] += $v['balance'];
				$result[$key]['total_claims'] += $v['claims'];
				$result[$key][$k]['amount'] = $v['balance'];
				$result[$key][$k]['claims'] = $v['claims'];
			}

		}
		return $result;
	} // end method aging_summary_payer_full

	// Method: aging_summary_payer_range
	//
	//	Provide an "aging summary" (with number of claims and
	//	amount due) for a range of agings, grouped by payer.
	//	Can be restricted by provider.
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
	//	<aging_summary_payer_full>
	//
	function aging_summary_payer_range ( $lower, $upper, $provider = 0 ) {
		$query = "SELECT i.insconame AS payer,".
			"COUNT(p.id) AS claims, ".
			"SUM(p.procamtpaid) AS paid, ".
			"SUM(p.procbalcurrent) AS balance, ".
			"i.id AS payer_id, ".
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
	} // end method aging_summary_payer_range

/*
PAYER:

SELECT i.insconame AS payer, COUNT(p.id) AS claims, 
SUM(p.procamtpaid) AS paid, 
SUM(p.procbalcurrent) AS balance, 
1 / (SUM(p.procamtpaid) / SUM(p.procbalcurrent)) AS r_money
FROM procrec AS p, coverage AS c, insco AS i WHERE p.proccurcovid=c.id AND c.covinsco=i.id AND (TO_DAYS(NOW()) - TO_DAYS(p.procdt)) <= 90 GROUP BY i.id;

PATIENT:

SELECT CONCAT(pt.ptlname, ', ',pt.ptfname, ' ', pt.ptmname) AS patient_name,
pt.id AS patient_id,
i.insconame AS payer, COUNT(p.id) AS claims, 
SUM(p.procamtpaid) AS paid, 
SUM(p.procbalcurrent) AS balance, 
1 / (SUM(p.procamtpaid) / SUM(p.procbalcurrent)) AS r_money
FROM procrec AS p, coverage AS c, insco AS i, patient AS pt
WHERE p.proccurcovid=c.id AND c.covinsco=i.id AND
i.id = '3' AND
( TO_DAYS(NOW()) - TO_DAYS(p.procdt) ) <= 90
GROUP BY pt.id
ORDER BY patient;


SELECT 
	CONCAT(pt.ptlname, ', ',pt.ptfname, ' ', pt.ptmname) AS patient_name,
	COUNT(p.id) AS claims,
	covinsco AS payer,
	SUM(p.procbalcurrent) AS balance,
	SUM(p.procamtpaid) AS paid
FROM
	patient AS pt,
	coverage AS c,
	procrec AS p
WHERE
	p.procpatient = pt.id AND
	p.proccurcovid = c.id AND
	c.covinsco = '3' AND
	(  TO_DAYS( NOW() ) - TO_DAYS(p.procdt) ) <= 90
GROUP BY
	pt.id
ORDER BY
	balance DESC;

*/

	// Method: aging_insurance_companies
	//
	//	Get a picklist of all insurance companies which have
	//	outstanding balances in the system. Can be limited by
	//	provider, if optional parameter is given.
	//
	// Parameters:
	//
	//	$provider - (optional) Provider/physician id key to limit
	//	the search. Defaults to disabled.
	//
	// Returns:
	//
	//	Associative array of payers.
	//
	function aging_insurance_companies ( $provider = NULL ) {
		$query = "SELECT CONCAT(i.insconame, ' (', ".
			"i.inscocity, ', ',i.inscostate, ')') AS payer, ".
			"i.id AS payer_id, ".
			"SUM(p.procbalcurrent) AS balance ".
			// support ratio of paid $ to not paid $
			"FROM procrec AS p, coverage AS c, insco AS i ".
			"WHERE p.proccurcovid=c.id AND ".
			// Handle narrowing by provider
			( $provider > 0 ? "p.procphy = '".addslashes($provider)."' AND " : "" ).
			"c.covinsco=i.id AND ".
			// lower bounds
			"(TO_DAYS(NOW()) - TO_DAYS(p.procdt) > '0') ".
			"GROUP BY i.id ".
			"ORDER BY payer";
			// next line orders by remaining balance:
			//"ORDER BY balance DESC";
		//print "query = \"$query\"<br/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		$return = array ( '----' => '' );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[$r['payer']] = $r['payer_id'];
		}
		return $return;
	} // end method aging_insurance_companies

	// Method: aging_insurance_plans
	//
	//	Get a picklist of all insurance plans which have
	//	outstanding balances in the system. Can be narrowed
	//	to only search by one payer.
	//
	// Parameters:
	//
	//	$payer - (optional) Insurance company id key to limit
	//	the search. Defaults to disabled.
	//
	// Returns:
	//
	//	Associative array of plan names.
	//
	function aging_insurance_plans ( $payer = NULL ) {
		$query = "SELECT ".
				"DISTINCT(c.covplanname) AS plan ".
			"FROM ".
				"procrec AS p, coverage AS c ".
			"WHERE ".
				"p.proccurcovid=c.id AND ".
				// Handle by payer
				( $payer>0 ? "c.covinsco='".addslashes($payer)."' AND " : "" ).
				"p.procbalcurrent > 0 ".
			"ORDER BY plan";
		//print "query = \"$query\"<br/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		$return = array ( '----' => '' );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[$r['plan']] = $r['plan'];
		}
		return $return;
	} // end method aging_insurance_plans

	// Method: claim_information
	//
	//	Get associative array of information related to a
	//	particular claim item (procedure).
	//
	// Parameters:
	//
	//	$proc - Procedure id key
	//
	// Returns:
	//
	//	Associative array of information about the specified
	//	procedure
	//
	function claim_information ( $proc ) {
		$query = "SELECT ".
			"CONCAT(pt.ptlname, ', ', pt.ptfname, ".
				"' ', pt.ptmname) AS patient_name, ".
			"CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate) AS payer_name, ".
			"d.icd9code AS diagnosis, ".
			"pt.ptssn AS ssn, ".
			"IF(c.covrel != 'S', ".
				"CONCAT(c.covlname, ', ', c.covfname, ".
					"' ', c.covmname), ".
				"CONCAT(pt.ptlname, ', ', pt.ptfname, ".
					"' ', pt.ptmname) ) AS rp_name, ".
			"IF(c.covrel != 'S', c.covssn, pt.ptssn) AS rp_ssn, ".
			"f.psrname AS facility, ".
			"pc.cptcode AS cpt_code, ".
			"p.proccharges AS fee, ".
			"p.procamtpaid AS paid, ".
			"p.procbalcurrent AS balance, ".
			"p.id AS proc ".
			"FROM ".
				"patient AS pt, ".
				"icd9 AS d, ".
				"insco AS i, ".
				"coverage AS c, ".
				"procrec AS p, ".
				"facility AS f, ".
				"cpt AS pc ".
			"WHERE ".
				"p.procpos = f.id AND ".
				"p.procdiag1 = d.id AND ".
				"p.proccpt = pc.id AND ".
				"p.proccurcovid = c.id AND ".
				"c.covinsco = i.id AND ".
				"p.procpatient = pt.id AND ".
				"p.id = '".addslashes($proc)."'";
		//print "query = \"$query\"<br/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		$r = $GLOBALS['sql']->fetch_array ( $result );
		return $r;
	} // end method claim_information

	// Method: events_for_procedure
	//
	//	Get an associative array with information containing
	//	events related to a particular procedure.
	//
	// Parameters:
	//
	//	$proc - Procedure id key
	//
	// Returns:
	//
	//	Array of associative arrays containing billing event
	//	data from the claimlog table.
	//
	function events_for_procedure ( $proc ) {
		$query = "SELECT ".
			"u.username AS user, ".
			"e.claction AS action, ".
			"DATE_FORMAT(e.cltimestamp, ".
				"'%Y-%m-%d %h:%i%p') AS date, ".
			"e.clcomment AS comment ".
			"FROM ".
				"claimlog AS e, ".
				"user AS u ".
			"WHERE ".
				"e.cluser = u.id AND ".
				"e.clprocedure = '".addslashes($proc)."' ".
			"ORDER BY e.cltimestamp DESC";
		//print "query = \"$query\"<br/>\n";
		return $this->_query_to_result_array ( $query, true );
	} // end method events_for_procedure

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
		//print_r($procedures); print "<br/>\n";
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

	// Method: log_event
	//
	//	Add an event to the claim log
	//
	// Parameters:
	//
	//	$procedure - Procedure id key
	//
	//	$param - Additional parameters in an associative array.
	//
	// Returns:
	//
	//	Record id key of new claim log record, or false if
	//	failed.
	//
	function log_event ( $procedure, $param ) {
		global $this_user;
		if (!is_object($this_user)) $this_user = CreateObject('_FreeMED.User');
	
		$query = $GLOBALS['sql']->insert_query (
			'claimlog',
			array (
				'cltimestamp' => SQL__NOW,
				'cluser' => $this_user->user_number,
				'clprocedure' => $procedure,
				'clbillkey' => $param['billkey'],
				'claction' => $param['action'],
				'clformat' => $param['format'],
				'cltarget' => $param['target'],
				'clcomment' => $param['comment']
			)
		);
		//print "query = ".$query."<hr/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		return $GLOBALS['sql']->last_record ( $result );
	} // end method log_event

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

	// Method: procedure_status_list
	//
	//	Get list of all procedure statuses in the system
	//	that are currently being used.
	//
	// Returns:
	//
	//	Array of distinct procedure statuses.
	function procedure_status_list ( ) {
		$query = "SELECT DISTINCT(procstatus) AS procstatus ".
			"FROM procrec ORDER BY procstatus";
		$result = $GLOBALS['sql']->query ( $query );
		$return = array ( );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			// Key and value are the same...
			$return[$r['procstatus']] = $r['procstatus'];
		} // end while loop
		return $return;
	} // end method procedure_status_list

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
