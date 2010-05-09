<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.BillingModule');

class RemittBillingTransport extends BillingModule {

	var $MODULE_NAME = "Remitt Billing System";
	var $MODULE_VERSION = "0.1";

	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "5ed9be92-fd93-40b4-8f4d-1858be548e02";

	public function __construct ( ) {
		//	__("Remitt Billing System")

		// Set configuration variables (username/password)
		$this->_SetMetaInformation('global_config_vars', array (
			'remitt_url', 'remitt_user', 'remitt_pass'
		));

		// Set appropriate associations
		$this->_SetHandler('BillingFunctions', 'transport');
		$this->_SetMetaInformation('BillingFunctionName', __("Remitt Billing System"));
		$this->_SetMetaInformation('BillingFunctionDescription', __("Use the Remitt formatting and transport system to send insurance claims electronically or generate paper claims."));

		// Call parent constructor
		parent::__construct ( );
	} // end constructor RemittBillingTransport

	public function GetRebillList ( ) {
		$query = "SELECT * FROM billkey ORDER BY id DESC LIMIT 50";
		$result = $GLOBALS['sql']->queryAll( $query );

		foreach ( $result AS $r ) {
			$bk = unserialize($r['billkey']);
			$bk_count = count($bk['procedures']);

			// Loop through procedures
			foreach ($bk['procedures'] AS $_g => $proc) {
				if ($proc[0] > 0) {
					$_q = "SELECT proc.procdt AS procdt, CONCAT(pt.ptlname, ".
					"', ', pt.ptfname, ' ', pt.ptmname) AS patient_name, ".
					"pt.id AS patient_id ".
					"FROM procrec AS proc, patient AS pt ".
					"WHERE proc.procpatient=pt.id AND ".
					"proc.id='".addslashes($proc[0])."'";
					$procs[] = $GLOBALS['sql']->queryRow( $_q );
				}
			}
			
			$return[] = array (
				'date' => $r['billkeydate'],
				'billkey' => $r['id'],
				'claims' => $bk_count,
				'procedures' => $procs
			);
		}
		return $return;
	}

	public function ProcessStatement ( $procs = NULL ) {
		// Create new Remitt instance
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_url'));

		// Create new ClaimLog instance
		$claimlog = CreateObject ('org.freemedsoftware.api.ClaimLog');

		// Get all claims in the system
		if (is_array($procs)) {
			$_procs = $procs;
		} else {
			$q = "Select proc.id AS p ".
				"From patient AS pat, procrec AS proc ".
				"Where proc.procpatient = pat.id and ".
				"proc.procbalcurrent > 0 and proc.proccurcovtp = '0'";
			$_procs = $GLOBALS['sql']->queryCol( $q );
		} // end fetch array

		$result = $remitt->ProcessStatement( $_procs );

		//print "DEBUG: "; print_r($result); print "<br/>\n";
		//$buffer .= "Should have returned $result.<br/>\n";
		// Add to claimlog
		$clresult = $claimlog->log_billing (
			$this_billkey,
			'statement', //$my_format,
			'PDF', //$my_target,
			__("Patient statement generated")
		);
		return $result;
	} // end method ProcessStatement

	// Method: GetStatus
	//
	//	Get current status by REMITT unique identifiers
	//
	// Parameters:
	//
	//	$uniques - Array of REMITT unique identifiers
	//
	// Returns:
	//
	//	Hash with key being the unique identifier and value being the REMITT
	//	return code.
	//
	public function GetStatus ( $uniques = NULL ) {
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_url'));

		// Handle invalid uniques
		if (!is_array($uniques)) {
			die (__("Invalid keys passed to status function!"));
		}

		foreach ($uniques AS $b => $u) {
			// Get individual status
			$status["".$u] = "".$remitt->GetStatus( $u );
		} // end foreach uniques

		return $status;
	} // end method GetStatus

	// Method: MarkAsBilled
	//
	//	Mark a list of billkeys as being billed.
	//
	// Parameters:
	//
	//	$billkeys - Array of billkeys
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function MarkAsBilled ( $billkeys ) {
		$claimlog = CreateObject ('org.freemedsoftware.api.ClaimLog');
		if ( !is_array( $billkeys ) ) { return $claimlog->MarkAsBilled( $billkeys ); }
		$mark = true;
		foreach ( $billkeys AS $key ) {
			$mark &= $claimlog->MarkAsBilled ( $key );
		}
		return (boolean) $mark;
	} // end method MarkAsBilled

	public function rebillkeys ($billkeys ) {
		syslog(LOG_INFO, "rebillkeys for ".count($billkeys)." billkeys");
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_url'));
		$claimlog = CreateObject('org.freemedsoftware.api.ClaimLog');
		syslog(LOG_INFO, "rebillkeys: looping");
		foreach ($billkeys AS $billkey){
			syslog(LOG_INFO, "rebillkeys: processing billkey $billkey");
			// Get format and target from claimlog
			$query = "SELECT DISTINCT clformat as format, cltarget as target,clprocedure as proc FROM claimlog WHERE ".
				"clbillkey=".$GLOBALS['sql']->quote( $billkey );
			syslog(LOG_INFO, "rebillkeys: $query");
			$r = $GLOBALS['sql']->queryAll($query);
			$q = "SELECT i.inscodefoutput AS output_format, i.inscodefformat AS paper_format, i.inscodeftarget AS paper_target, i.inscodeftargetopt AS paper_target_option, i.inscodefformate AS electronic_format, i.inscodeftargete AS electronic_target, i.inscodeftargetopte AS electronic_target_option FROM procrec p LEFT OUTER JOIN coverage c ON p.proccurcovid=c.id LEFT OUTER JOIN insco i ON c.covinsco=i.id WHERE p.id=".$GLOBALS['sql']->quote($r[0]['proc']);
			syslog(LOG_INFO, "rebillkeys: $q");
			$temp=$GLOBALS['sql']->queryRow($q);
			$target_opt="";
			switch ($temp['output_format']) {
				case 'paper':
				$target_opt= $temp['paper_target_option'];
				break;

				case 'electronic':
				$target_opt= $temp['electronic_target_option'];
				break;

				default: /* do nothing */ break;
			}
			
			syslog(LOG_INFO, "rebillkeys: ProcessBill( $billkey, ".$r[0]['format'].",".$r[0]['target'].",$target_opt )");
			$result = $remitt->ProcessBill(
				$billkey,
				$r[0]['format'],
				$r[0]['target'],
				$target_opt
			);
			$return[] = array (
				'result' => "".$result,
				'billkey' => "".$billkey,
				'format' => $r[0]['format'],
				'target' => $r[0]['target']
			);
			// Add to claimlog
			$result = $claimlog->log_billing (
				$billkey,
				$r[0]['format'],
				$r[0]['target'],
				__("Remitt billing run sent")
			);
		}
		return $return;
		/* $mark = $claimlog->mark_billed ( $this_billkey ); */
	} // end method rebillkey

	// Method: PatientsToBill
	//
	//	Get list of all patients to bill with claims.
	//
	// Returns:
	//
	//	Hash containing:
	//	* patient_id - Internal FreeMED record ID for this patient
	//	* claim_count - Number of claims for this patient
	//	* claims - Array of claim ids for this patient
	//	* patient - Human readable patient name
	//	* date_of_birth - Date of birth in YYYY-MM-DD format
	//	* date_of_birth_mdy - Date of birth in MM/DD/YYYY format
	//
	public function PatientsToBill ( ) {
		// This is a *huge* select hack so that the query returns
		// patients in a quasi-alphabetical order. Any better
		// suggestions are welcomed.  - Jeff
		$query = "SELECT DISTINCT(a.procpatient) AS patient_id, COUNT(a.id) AS claim_count, GROUP_CONCAT(a.id) AS claims, CONCAT(b.ptlname, ', ', b.ptfname, ' ', b.ptmname, ' [', b.ptid, ']') AS patient, b.ptdob AS date_of_birth, DATE_FORMAT(b.ptdob, '%m/%d/%Y') AS date_of_birth_mdy FROM procrec a LEFT OUTER JOIN patient b ON a.procpatient=b.id WHERE a.procbalcurrent > '0' AND a.proccurcovtp > 0 AND a.procbilled = '0' GROUP BY a.procpatient ORDER BY b.ptlname,b.ptfname,b.ptmname,b.ptdob";

		$res = $GLOBALS['sql']->queryAll($query);

		/*
		// Present 'claims' as an array
		foreach ( $res AS $k => $v) {
			$res[$k]['claims'] = explode(',', $v['claims']);	
		}
		*/
		return $res;
	} // end method PatientsToBill

	// Method: ProceduresToBill
	//
	//	Get list of all procedures to bill for a specified patient.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	// Returns:
	//
	//	Array containing list of procedures to bill for this patient.
	//
	public function ProceduresToBill ( $patient ) {
		$query = "SELECT id FROM procrec ".
			// Needs to have a balance of over 0
			"WHERE procbalcurrent > '0' AND ".
			// And patient must jive
			"procpatient = '".addslashes($patient)."' AND ".
			// Needs to be billable (at all) -- examine this one!
			//"procbillable = '0' AND ".
			// No patient responsibility bills
			"proccurcovtp > 0 AND ".
			// (Not sure) Needs not to be billed already
			"procbilled = '0' ".
			// Order by date of procedure
			"ORDER BY procdt";

		$return = $GLOBALS['sql']->queryCol( $query );
		return $return;
	} // end method ProceduresToBill

	// Method: GetClaimInformation
	//
	//	Resolve additional bill information for a list of claim ids.
	//
	// Parameters:
	//
	//	$claims - Array of claim ids
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* claim
	//	* claim_date
	//	* claim_date_mdy
	//	* output_format
	//	* paper_format
	//	* paper_target
	//	* electronic_format
	//	* electronic_target
	//	* cpt_code
	//	* cpt_description
	//
	public function GetClaimInformation ( $claims ) {
		$q = "SELECT p.id AS claim, p.procdt AS claim_date, DATE_FORMAT(p.procdt, '%m/%d/%Y') AS claim_date_mdy, i.inscodefoutput AS output_format, i.inscodefformat AS paper_format, i.inscodeftarget AS paper_target, i.inscodefformate AS electronic_format, i.inscodeftargete AS electronic_target, a.cptcode AS cpt_code, a.cptnameint AS cpt_description FROM procrec p LEFT OUTER JOIN coverage c ON p.proccurcovid=c.id LEFT OUTER JOIN insco i ON c.covinsco=i.id LEFT OUTER JOIN cpt a ON p.proccpt=a.id WHERE FIND_IN_SET(p.id, ".$GLOBALS['sql']->quote(join(',', $claims)).")";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetClaimInformation

	// Method: ProcessClaims
	//
	//	Input jobs into the claims queue for REMITT.
	//
	// Parameters:
	//
	//	$patients - Array of patients for whom billing is enabled.
	//
	//	$claims - Array of claims for which we are billing
	//
	//	$overrides (optional) - Array of media changes and other exceptions
	//	$clearinghouse (optional) - 
	//
	//	$contact (optional) - 
	//
	//	$service (optional) - 
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* Billkey number
	//	* Number of claims in billkey
	//
	public function ProcessClaims ( $patients, $claims, $overrides=NULL, $clearinghouse = 1, $contact = 1, $service = 1 ) {
		// Boiler plate for dealing with REMITT
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_url'));

		// Create new ClaimLog instance
		$claimlog = CreateObject ('org.freemedsoftware.api.ClaimLog');

		$claim_limit = 500;
		$q = "SELECT p.id AS claim, i.inscodefoutput AS output_format, i.inscodefformat AS paper_format, i.inscodeftarget AS paper_target, i.inscodeftargetopt AS paper_target_option, i.inscodefformate AS electronic_format, i.inscodeftargete AS electronic_target, i.inscodeftargetopte AS electronic_target_option FROM procrec p LEFT OUTER JOIN coverage c ON p.proccurcovid=c.id LEFT OUTER JOIN insco i ON c.covinsco=i.id WHERE FIND_IN_SET(p.procpatient, ".$GLOBALS['sql']->quote(join(',', $patients)).") AND FIND_IN_SET(p.id, ".$GLOBALS['sql']->quote(join(',', $claims)).")";
		$res = $GLOBALS['sql']->queryAll( $q );

		foreach ( $res AS $r ) {
			// TODO: handle overrides

			switch ($r['output_format']) {
				case 'paper':
				$s[ $r['paper_format'] . '/' . $r['paper_target'] . '/' . $r['paper_target_option'] . '/0' ][] = $r['claim'];
				break;

				case 'electronic':
				$s[ $r['electronic_format'] . '/' . $r['electronic_target'] . '/' . $r['electronic_target_option'] . '/0' ][] = $r['claim'];
				break;

				default: /* do nothing */ break;
			}
		}

		// Now, postprocessing to make sure that we split anything above a certain number of claims
		foreach ( $s AS $k => $t ) {
			if ( count($t) > $claim_limit ) {
				// Transfer into a temporary array, then kill the original
				$temp = $t;
				unset( $s[$k ]);

				// Pull out the components to make more
				list ( $_format, $_target, $_targetopt, $_count ) = explode ( '/', $k );
				$pos = 0; $stack = 0;
				for ($i=0; $i<count($temp); $i++) {
					$cur_key = $_format . '/' . $_target . '/' . $_targetopt . '/' . $stack;
					$s[$cur_key][] = $temp[$i];
					if ( count($s[$cur_key]) >= $claim_limit ) { $stack++; }
				}

				// Clean up to avoid unsightly leakage
				unset ( $temp ); unset ( $cur_key );
			}
		}

		// Everything is done, pass on
		foreach ( $s AS $k => $v) {
			// Create new billkey
			$billkey = array ( );

			$billkey['contact'] = $contact;
			$billkey['clearinghouse'] = $clearinghouse;
			$billkey['service'] = $service;

			// Create single array of stuff
			$billkey['procedures'] = $v;
			$this_billkey = $remitt->StoreBillKey( $billkey );

			// Add to list to be marked
			//$billkeys[] = $this_billkey;

			// Get format and target from default
			list ( $my_format, $my_target, $my_targetopt, $batch_id ) = explode ( '/', $k );
			$results[] = array (
				'result' => "".($remitt->ProcessBill( $this_billkey, $my_format, $my_target, $my_targetopt )),
				'billkey' => "".$this_billkey,
				'format' => $my_format,
				'target' => $my_target
			);

			// Log to the claim log
			$claimlog->log_billing (
				$this_billkey,
				$my_format,
				$my_target,
				__("Remitt billing run sent")
			);
		}

		// Return what we have
		return $results;
	} // end method ProcessClaims
	
	 public function getMonthsInfo(){
                $remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_url'));
                $yearsresult=$remitt->ListOutputYears();
                foreach($yearsresult as $key => $val){
                        $monthresult=$remitt->ListOutputMonths($val[0]);
                        if(!is_array($monthresult)){
                        	$month=date("M Y",strtotime($monthresult));
	                        $data[]=array( "month" => $month);
                	}
                	else{
	                        for($i=0;$i<count($monthresult);$i++){
	                                $month=date("M Y",strtotime($monthresult[$i]));
	                                $data[]=array( "month" => $month);
	                        }
	                }
                }
                return $data;
        }
        
	// Method: getMonthlyReportsDetails
	//
	//	get the report delatils for a particular month
	//
	// Parameters:
	//
	//	$month - month for which we want to get the report details
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* filename
	//	* filesize
	//
        public function getMonthlyReportsDetails($month){
                $remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_url'));
                $result= $remitt->GetFileList("output","month",date("Y-m",strtotime($month)));
                for($i=0;$i<count($result);$i++){
                        $index=count($data);
                        foreach($result[$i] as $key => $val){
                                $data[$index][$key]="".$val;
                        }
                }
                return $data;
        }

} // end class RemittBillingTransport

register_module('RemittBillingTransport');

?>
