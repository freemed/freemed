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

// Class: org.freemedsoftware.api.ClaimLog
//
//	Allows access to functions involving the internal FreeMED claim
//	log, which is used by the system to track billing, rebilling,
//	bill keys, et cetera.
//
class ClaimLog {

	// Constructor: ClaimLog
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
	//	* aging - Aging date range. ( 0-30, 31-60, 61-90, 91-120, 120+ )
	//	* billed - Billed or not. ( 0, 1 )
	//	* date - Date of service
	//	* patient - Patient id
	//	* first_name - Textual patient first name substring
	//	* last_name - Textual patient last name substring
	//	* payer - Insurace company id
	//	* payergroup - Insurance company group id
	//	* plan - Plan name
	//	* status - Billing status
	//
	// Returns:
	//
	//	Array of associative arrays containing aging information.
	//
	function AgingReportQualified ( $criteria ) {
		freemed::acl_enforce( 'financial', 'read' );
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );

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

				case 'billed':
				if ($v == '0' or $v == '1') { $q[] = "p.procbilled = '".addslashes($v)."'"; }
				break; // end billed case

				case 'date':
				if ($v && ($criteria['week']=="" || $criteria['week']==NULL)) $q[] = "p.procdt = '".addslashes($s->ImportDate($v))."'";
				break; // end date
				
				case 'week':
				if ($v && ($criteria['date']!="" || $criteria['date']!=NULL)) $q[] = "WEEK(p.procdt) = WEEK('".addslashes($s->ImportDate($criteria['date']))."')";
				break; // end week
				
				case 'provider':
				if ($v) $q[] = "p.procphysician = '".addslashes($v)."'";
				break; // end patient case
				
				case 'facility':
				if ($v) $q[] = "p.procpos = '".addslashes($v)."'";
				break; // end patient case
				
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

				case 'payergroup':
				if ($v) $q[] = "i.inscogroup = '".addslashes($v)."'";
				break; // end payergroup case

				case 'plan':
				if ($v) $q[] = "c.covplanname = '".addslashes($v)."'";
				break;

				case 'status':
				if ($v) $q[] = "p.procstatus = '".addslashes($v)."'";
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

		$query = "SELECT DISTINCT p.id AS Id, CONCAT(pt.ptlname, ', ', pt.ptfname, ".
			"' ', pt.ptmname) AS patient, ".
			"pt.id AS patient_id, ".
			"p.procdt AS date_of, ".
			"DATE_FORMAT(p.procdt,'%m/%d/%Y') AS date_of_mdy, ".
			"p.procstatus AS status, ".
			"p.procbilled AS billed, ".
			"p.procphysician AS provider_id, ".
			"p.proccurcovtp AS proc_cov_type, ".
			"p.id AS claim, ".
			"p.procpos AS pos, ".
			"c.covpatinsno AS insured_id, ".
			"c.covinsco AS payer_id, ".
			"CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate, ')') AS payer, ".
			"i.inscoidmap AS id_map, ".
			"cl.clbillkey AS billkey, ".
			"TRUNCATE(p.procamtpaid, 2) AS paid, ".
			"TRUNCATE(p.procbalcurrent, 2) AS balance ".
			"FROM procrec p ".
				"LEFT OUTER JOIN coverage c ON p.proccurcovid = c.id ".
				"LEFT OUTER JOIN insco i ON c.covinsco = i.id ".
				"LEFT OUTER JOIN patient pt ON p.procpatient = pt.id ".
				"LEFT OUTER JOIN claimlog cl ON cl.clprocedure = p.id AND cl.clbillkey != 0 AND cl.clbillkey=(select max(tcl.clbillkey) from claimlog tcl where tcl.clprocedure=p.id) ".
			"WHERE ";
		if(($criteria['zerobalance']+0)=='0')			
			$query.="p.procbalcurrent > 0 AND ";
		$query.=( is_array($q) ? join(' AND ', $q) : ' ( 1 > 0 ) ' )." ".
			"ORDER BY patient, balance DESC";
		//print "<hr/>query = \"$query\"<hr/>\n";
		$result = $GLOBALS['sql']->queryAll ( $query );
		$return = array ( );
		foreach ( $result AS $r ) {
			// Make sure to deserialize the id map, since
			// we can't actually extract values from it using
			// SQL regex's, or if we could, it would be a
			// huge waste of processor time...
			$pm = CreateObject( 'org.freemedsoftware.module.ProviderModule' );
			$r['provider_name']=$pm->fullName($r['provider_id']);
			if (is_array(@unserialize($r['id_map']))) {
				$id_map = unserialize($r['id_map']);
				$r['id_map'] = $id_map[$r['provider_id']];
			} else {
				$id_map = array ();
			}
			$fac = CreateObject( 'org.freemedsoftware.module.FacilityModule' );
			$r['posname']=$fac->to_text($r['pos']);
			$return[] = $r;
			 // patient, claims, paid, balance, ratio
		} 
		return $return;
	} // end method AgingReportQualified

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
	
	public function aging_summary_formatted(){
		$data=$this->aging_summary_payer_full();
		foreach($data as $key => $val){

			$i=count($result);
			$result[$i]['payer_name']=$key;
			$result[$i]['payer_id']=$val['payer_id'];
			$result[$i]['paid']=$val['paid'];
			$everything=$val['everything'];
			$result[$i]['ev_amount']="".bcadd($everything['amount'],0,2);
			$result[$i]['ev_claims']=$everything['claims'];
			$zero_thirty=$val['0-30'];
			if($zero_thirty!=""){				
				$result[$i]['amount_0_30']="".bcadd($zero_thirty['amount'], 0, 2);
				$result[$i]['claims_0_30']=$zero_thirty['claims'];
			}
			else{
				$result[$i]['amount_0_30']="0.00";
				$result[$i]['claims_0_30']="0";
			}
			$thirty_sixty=$val['31-60'];
			if($thirty_sixty!=""){
				$result[$i]['amount_31_60']="".bcadd($thirty_sixty['amount'],0,2);
				$result[$i]['claims_31_60']=$thirty_sixty['claims'];
			}
			else{
				$result[$i]['amount_31_60']="0.00";
				$result[$i]['claims_31_60']="0";
			}
			$sixty_ninty=$val['61-90'];
			if($sixty_ninty!=""){
				$result[$i]['amount_61_90']="".bcadd($sixty_ninty['amount'],0,2);
				$result[$i]['claims_61_90']=$sixty_ninty['claims'];
			}
			else{
				$result[$i]['amount_61_90']="0.00";
				$result[$i]['claims_61_90']="0";
			}
			$sixty_ninty=$val['61-90'];
			if($sixty_ninty!=""){
				$result[$i]['amount_61_90']="".bcadd($sixty_ninty['amount'],0,2);
				$result[$i]['claims_61_90']=$sixty_ninty['claims'];
			}
			else{
				$result[$i]['amount_61_90']="0.00";
				$result[$i]['claims_61_90']="0";
			}
			$ninty_hundred20=$val['91-120'];
			if($ninty_hundred20!=""){
				$result[$i]['amount_91_120']="".bcadd($ninty_hundred20['amount'],0,2);
				$result[$i]['claims_91_120']=$ninty_hundred20['claims'];
			}
			else{
				$result[$i]['amount_91_120']="0.00";
				$result[$i]['claims_91_120']="0";
			}
			$hundred20plus=$val['120+'];
			if($hundred20plus!=""){
				$result[$i]['amount_120plus']="".bcadd($hundred20plus['amount'],0,2);
				$result[$i]['claims_120plus']=$hundred20plus['claims'];
			}
			else{
				$result[$i]['amount_120plus']="0.00";
				$result[$i]['claims_120plus']="0";
			}
		}
		return $result;
	}
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
		return $GLOBALS['sql']->queryAll ( $query );
	} // end method aging_summary_payer_range

/*
PAYER:

SELECT i.insconame AS payer, COUNT(p.id) AS claims, 
SUM(p.procamtpaid) AS paid, 
SUM(p.procbalcurrent) AS balance, 
1 / (SUM(p.procamtpaid) / SUM(p.procbalcurrent)) AS r_money
FROM procrec AS p, coverage AS c, insco AS i WHERE p.proccurcovid=c.id AND c.covinsco=i.id AND (TO_DAYS(NOW()) - TO_DAYS(p.procdt)) <= 90 GROUP BY i.id;

PATIENT:

SELECT CONCAT(pt.ptlname, ', ',pt.ptfname, ' ', pt.ptmname) AS patient,
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
	CONCAT(pt.ptlname, ', ',pt.ptfname, ' ', pt.ptmname) AS patient,
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
	function aging_insurance_companies ( $provider = 0 ) {
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
		$result = $GLOBALS['sql']->queryAll ( $query );
		foreach ( $result AS $r ) {
			$return[$r['payer']] = $r['payer_id'];
		}
		return $return;
	} // end method aging_insurance_companies

	
	function aging_insurance_plans ( $criteria) {
		$query = "SELECT ".
				"DISTINCT c.id as Id, c.covplanname AS plan ".
			"FROM ".
				"procrec p LEFT OUTER JOIN coverage c ON p.proccurcovid=c.id ".
			"WHERE ".
				"c.covplanname LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%')".
			"ORDER BY plan";
		
		$result = $GLOBALS['sql']->queryAll ( $query );
		foreach ( $result AS $r ) {
			$return[$r['Id']] = $r['plan'];
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
	//	$payrec - (optional) Payment record id key. Gives information
	//	regarding only that payment record.
	//
	// Returns:
	//
	//	Associative array of information about the specified
	//	procedure
	//
	function claim_information ( $proc, $payrec = NULL ) {
		$query = "SELECT ".
			"CONCAT(pt.ptlname, ', ', pt.ptfname, ".
				"' ', pt.ptmname) AS patient, ".
			"pt.ptdob AS patient_dob, ".
			"pt.id AS patient_id, ".
			"c.id AS curr_cov, ".
			"CONCAT(i.insconame, ' (', i.inscocity, ', ', ".
				"i.inscostate) AS payer_name, ".
			"i.inscodefoutput AS default_format, ".
			"d.icd9code AS diagnosis, ".
			"pt.ptssn AS ssn, ".
			"c.covrel AS rp_name, ".
			"IF(c.covrel != 'S', c.covdob, pt.ptdob ) AS rp_dob, ".
			"IF(c.covrel != 'S', c.covssn, pt.ptssn) AS rp_ssn, ".
			"p.proccov1 AS coverage_primary, ".
			"p.proccov2 AS coverage_secondary, ".
			"p.proccov3 AS coverage_tertiary, ".
			"f.psrname AS facility, ".
			"pc.cptcode AS cpt_code, ".
			"p.proccharges AS fee, ".
			"p.procamtpaid AS paid, ".
			"p.procbalcurrent AS balance, ".
			"p.procbilled AS billed, ".
			"p.procdt AS service_date, ".
			"p.procphysician AS provider, ".
			"p.procrefdoc AS referring_provider, ".
			"c1.covcopay AS prim_copay, ".
			"c1.covdeduct AS prim_deduct, ".
			"c2.covcopay AS sec_copay, ".
			"c2.covdeduct AS sec_deduct, ".
			"c3.covcopay AS ter_copay, ".
			"c3.covdeduct AS ter_deduct, ".
			"p.id AS proc ".
			"FROM ".
				"patient AS pt, ".				
				( $payrec ? "payrec AS pa, " : "" ).				
				"procrec AS p ".
			"LEFT OUTER JOIN coverage c1 ON c1.id=p.proccov1 ".
			"LEFT OUTER JOIN coverage c2 ON c2.id=p.proccov2 ".
			"LEFT OUTER JOIN coverage c3 ON c3.id=p.proccov3 ".
			"LEFT OUTER JOIN coverage c ON c.id=p.proccurcovid ".
			"LEFT OUTER JOIN insco i ON i.id=c.covinsco ".
			"LEFT OUTER JOIN icd9 d ON p.procdiag1 = d.id ".
			"LEFT OUTER JOIN cpt pc ON p.proccpt = pc.id ".
			"LEFT OUTER JOIN facility f ON p.procpos = f.id ".
			"WHERE ".				
				"p.procpatient = pt.id AND ".
				( $payrec ? "pa.payrecproc = p.id AND " : "" ).
				( $payrec ? 
					"pa.id = '".addslashes($payrec)."'" :
					"p.id = '".addslashes($proc)."'" 
				);
		//print "query = \"$query\"<br/>\n";
		
		$r = $GLOBALS['sql']->queryRow ( $query );
		//return $query;
		$pm = CreateObject( 'org.freemedsoftware.module.ProviderModule' );
		$r['provider_name']=$pm->fullName($r['provider']);
		$r['ref_provider_name']=$pm->fullName($r['referring_provider']);
		if($r['coverage_primary']!=null && $r['coverage_primary']!='0'){
			$c_primary = CreateObject('org.freemedsoftware.core.Coverage', $r['coverage_primary']);
			//return $c_primary->covinsco->get_name();
			$r['prim_cov']=$c_primary->covinsco->get_name().' ('.$c_primary->covpatinsno.')';
		}
		else{
			$r['prim_cov']="";
		}
		if($r['coverage_secondary']!=null && $r['coverage_secondary']!='0'){
			$c_sec = CreateObject('org.freemedsoftware.core.Coverage', $r['coverage_secondary']);
			$r['sec_cov']=$c_sec->covinsco->get_name().' ('.$c_sec->covpatinsno.')';
		}
		else{
			$r['sec_cov']="";
		}
		if($r['coverage_tertiary']!=null && $r['coverage_tertiary']!='0'){
			$c_ter = CreateObject('org.freemedsoftware.core.Coverage', $r['coverage_tertiary']);
			$r['ter_cov']=$c_ter->covinsco->get_name().' ('.$c_ter->covpatinsno.')';
		}
		else{
			$r['ter_cov']="";
		}
		$hash=freemed::coverage_relationship_picklist();
		$r['rp_name']=$hash[$r['rp_name']];
		//$c_sec = CreateObject('org.freemedsoftware.core.Coverage', $r['coverage_secondary']);
		//$r['sec_cov']=$c_sec->covinsco->get_name().' ('.$c_sec->covpatinsno.')';
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
	//	$payrec - (optional) Payment record id key. Gives only information
	//	regarding that particular payment record.
	//
	// Returns:
	//
	//	Array of associative arrays containing billing event
	//	data from the claimlog table.
	//
	function events_for_procedure ( $proc, $payrec = 0 ) {
		$query = "SELECT ".
			"u.username AS user, ".
			"e.claction AS action, ".
			"DATE_FORMAT(e.cltimestamp, ".
				"'%Y-%m-%d %h:%i%p') AS date, ".
			"e.clcomment AS comment ".
			"FROM ".
				"claimlog AS e, ".
				( $payrec ? "payrec AS p, " : "" ).
				"user AS u ".
			"WHERE ".
				"e.cluser = u.id AND ".
				( $payrec ? "e.clpayrec = p.id AND " : "" ).
				( $payrec ?
				"e.clpayrec = '".addslashes($payrec)."' " :
				"e.clprocedure = '".addslashes($proc)."' "
				).
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
			$this_user = CreateObject('org.freemedsoftware.core.User');
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
	//	* item - Payment record id (optional)
	//	* billkey - Billkey id for billing runs
	//	* action - Textual action description
	//	* format - Billing engine related
	//	* target - Billing engine related
	//	* comment - What else?
	//
	// Returns:
	//
	//	Record id key of new claim log record, or false if
	//	failed.
	//
	function log_event ( $procedure, $param ) {
		global $this_user;
		if (!is_object($this_user)) $this_user = CreateObject('org.freemedsoftware.core.User');
	
		$query = $GLOBALS['sql']->insert_query (
			'claimlog',
			array (
				'cltimestamp' => SQL__NOW,
				'cluser' => $this_user->user_number,
				'clprocedure' => $procedure,
				'clpayrec' => ( $param['item'] ? $param['item'] : 0 ),
				'clbillkey' => $param['billkey'],
				'claction' => $param['action'],
				'clformat' => $param['format'],
				'cltarget' => $param['target'],
				'clcomment' => $param['comment']
			)
		);
		//print "query = ".$query."<hr/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		return $GLOBALS['sql']->lastInsertId ( 'claimlog', 'id' );
	} // end method log_event

	// Method: MarkAsBilled 
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
	function MarkAsBilled ( $billkeys ) {
		$keys = array();
		$_billkeys = is_array($billkeys) ? $billkeys : array($billkeys);
		foreach ($_billkeys AS $something => $billkey) {
			//print "processing $billkey<br/>\n";
			// Get the actual bill key
			$this_billkey = unserialize (
				freemed::get_link_field (
					$billkey,
					'billkey',
					'billkey'
				)
			);
			//$keys = array_merge($keys, $this_billkey['procedures']);
			foreach($this_billkey['procedures'] AS $k => $v) {
				if (is_array($v)) { $v = $v[0]; }
				$keys[$v] = $v;
			}
		}

		// Create procedure set
		$set = join(',', $keys);
		
		// Perform update to procedure table
		$query = 'UPDATE procrec SET '.
			'procbilled = \'1\' '.
			'WHERE FIND_IN_SET(id, \''.$set.'\')';
		//print "query = $query<br/>\n";
		$result = $GLOBALS['sql']->query ( $query );

		return $result;
	} // end method MarkAsBilled

	// Method: MarkClaimsAsBilled
	//
	//	Mark all procedures in an array as being billed. The
	//	billing interface should use this function.
	//
	// Parameters:
	//
	//	$procs - Array of procedures
	//
	// Returns:
	//
	//	Boolean, if successful.
	//
	public function MarkClaimsAsBilled ( $procs ) {
		// Sanitize data
		if (!is_array($procs)) { return false; }

		foreach ( $procs AS $p ) {
			if ( ($p+0) + 0 ) {
				$sanitized[] = (int) $p;
			}
		}
		if ( count($sanitized) < 1 ) { return false; }

		// Create procedure set
		$set = join( ',', $sanitized );
		
		// Perform update to procedure table
		$query = 'UPDATE procrec SET '.
			'procbilled = 1 '.
			'WHERE FIND_IN_SET(id, \''.$set.'\')';
		$result = $GLOBALS['sql']->query ( $query );
		
		return ! ( $result instanceof DB_Error );
	} // end method MarkClaimsAsBilled 

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
		$result = $GLOBALS['sql']->queryAll ( $query );
		$return = array ( );
		foreach ( $result AS $r ) {
			// Key and value are the same...
			$return[$r['procstatus']] = $r['procstatus'];
		} // end while loop
		return $return;
	} // end method procedure_status_list

	// Method: RebillByPayer
	//
	//	Set all procedures to be rebilled which are associated with
	//	a payer id.
	//
	// Parameters:
	//
	//	$insco - Payer id
	//
	public function RebillByPayer ( $insco ) {
		$query = "UPDATE procrec p, coverage c SET p.procbilled=0 WHERE p.proccurcovid=c.id AND p.procbalcurrent>0 AND c.covinsco=". (int)$insco;
		$result = $GLOBALS['sql']->query( $query );
		return ( $result ? true : false );
	} // end method RebillByPayer

	// Method: RebillDistinctPayers
	//
	//	Form picklist of payers who still have unpaid outstanding
	//	items.
	//
	// Returns:
	//
	//	Array.
	//
	public function RebillDistinctPayers ( $criteria = NULL) {
		$query = "SELECT DISTINCT i.id AS Id, CONCAT(i.insconame, ' (', i.inscocity, ', ',i.inscostate, ')') AS payer FROM procrec p LEFT OUTER JOIN coverage c ON p.proccurcovid=c.id LEFT OUTER JOIN insco i ON c.covinsco=i.id WHERE i.insconame LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%') ORDER BY i.insconame";
		$result = $GLOBALS['sql']->queryAll( $query );
		foreach ($result AS $r) {
			$return[$r['Id']] = trim( $r['payer'] );
		}
		return $return;
	} // end method RebillDistinctPayers

	// Method: RebillClaims
	//
	//	Set rebilling status for multiple claims.
	//
	// Parameters:
	//
	//	$claims - Array of claim ids.
	//
	// Returns:
	//
	//	Boolean, whether or not everything is okay.
	//
	public function RebillClaims ( $claims ) {
		if ( !is_array($claims) or count($claims) < 1 ) {
			return false;
		}
		$res = true;
		foreach ( $claims AS $claim ) {
			if ( ($claim + 0) > 0 ) {
				$res &= $this->set_rebill($claim, __("Marked for Rebill"));
			}
		}
		return $res;
	} // end method RebillClaims

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
			$this_user = CreateObject('org.freemedsoftware.core.User');
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

		return ($proc_query and $cl_query);
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
	protected function _procedure_to_patient ( $procedure ) {
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
	protected function _query_to_result_array ( $query, $sequential = false ) {
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
	
	public function getProcInfo($id){
		$query="select  pr.id AS Id, pr.procdt AS proc_date, CONCAT(cpt.cptcode,' ',cptnameint) AS proc_code, ".
		"pr.procphysician as prov_name, pr.procbalorig as proc_obal, ".
		"pr.procamtallowed as proc_allowed, pr.proccharges as proc_charges, pr.procamtpaid as proc_paid, pr.procbalcurrent as proc_currbal, ".
		"pr.procbilled as proc_billed,procdtbilled as proc_billdate from procrec pr LEFT OUTER JOIN cpt ON cpt.id =pr.proccpt where pr.id=".$GLOBALS['sql']->quote( $id );
		$result= $GLOBALS['sql']->queryRow( $query );
		
		$prov  = CreateObject( 'org.freemedsoftware.module.ProviderModule' );		
		$result["prov_name"]=$prov->fullName($result["prov_name"]+0);
		return $result;		
	}
	
	public function getProceduresInfo($proc_ids){
		foreach ( $proc_ids AS $pid ) {			
			$data = $this->claim_information($pid);
			$bal=$data['balance']+0;
			$cov=$data['curr_cov']+0;
			if ($data['patient_id'] && $bal >0 && $cov > 0) {
				$rcount=count($result);
				$result[$rcount]['id']=''.$pid;
				$result[$rcount]['pt_name']=$data['patient'];
				$result[$rcount]['pt_id']=$data['patient_id'];
				$result[$rcount]['clm']=$data['proc'];
				$result[$rcount]['cpt']=$data['cpt_code'];
				$result[$rcount]['ser_date']=$data['service_date'];
				$result[$rcount]['paid']=bcadd($data['paid'],0,2);
				$result[$rcount]['amnt_bill']=bcadd($data['paid'],0,2);
				$result[$rcount]['balance']=bcadd($data['balance'], 0, 2);
				$result[$rcount]['left']=bcadd($data['balance'], 0, 2);				
			}
		}
		return $result;
	}
	
	function post_check ($payer,$checkno,$data) {
		
		// Create ledger
		$l = CreateObject('org.freemedsoftware.api.Ledger');

			
		// Loop through the posted checks
		foreach ($data AS $d) {
			
			foreach($d as $key=>$value){
				$val[$key]="".$value;
			}
			
			// Get original procedure values
			// Get information about this procedure
			$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $val['proc']);
			$this_procedure = $procedure_object->get_procedure( );

			// Check for payment
			$patient=$l->_procedure_to_patient ( $procedure );
			if ($val['pay'] > 0 ) {
				// Post check to ledger
				$l->post_payment_check (
					$val['proc'],
					date('Y-m-d'),
					$checkno,
					"1",
					$val['pay'],
					"Payment from Payer",
					$patient
				);
				
			} // end if pay > 0

			// Check for copay
			if ($val['copay'] > 0) {
				$l->post_payment_check (
					$val['proc'],
					date('Y-m-d'),
					$checkno,
					"1",
					$val['copay'],
					"COPAY",
					$patient,
					"",
					COPAY
				);
			} // end if copay > 0
			
			// Check for adjustment
			if ($val['adj'] != $this_procedure['procbalcurrent']) {
				$l->post_fee_adjustment (
					$val['proc'],
					$this_procedure['proccurcovtp'],
					$val['adj'],
					"Fee Adjustment"
				);
			} // end if adj > 0
			// Determine disallowed amount manually 
			if (($val['pay']+$val['copay']+$val['adj']) > 0) {
				// Where is this going?
				$where = $l->next_coverage($val['proc']);
			
				// Move to the next coverage in the ledger
				$l->move_to_next_coverage (
					$val['proc'],
					0
					// Calculate disallow ... BORKED RIGHT NOW, PASS 0
					//$proc['procbalcurrent'] - (
					//	$_REQUEST['pay'][$k] +
					//	$_REQUEST['copay'][$k]
					//)
				);

				
			} // end if disallowed amount
		} // end foreach post var
		
		return true;
	} // end method post_check
	
	public function rebill($proc_ids){
		$l = CreateObject('org.freemedsoftware.api.Ledger');
		$result=true;
		foreach ( $proc_ids AS $pid ) {
			$procedure_object = CreateObject('org.freemedsoftware.api.Procedure', $pid);
			$this_procedure = $procedure_object->get_procedure( );			
			$return=$l->queue_for_rebill($pid,$this_procedure['proccurcovtp']);
			if($return==false){
				$result=false;
			}
		}
		return $result;
	}
} // end class ClaimLog

?>
