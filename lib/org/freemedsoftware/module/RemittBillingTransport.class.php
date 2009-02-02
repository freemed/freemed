<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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
			'remitt_server', 'remitt_port', 'remitt_protocol',
			'remitt_user', 'remitt_pass'
		));
		$this->_SetMetaInformation('global_config', array (
			__("Remitt Server Hostname") =>
			'html_form::text_widget("remitt_server", 20, 50)',
			__("Remitt Server Port") =>
			'html_form::text_widget("remitt_port", 6)',
			__("Transport Protocol") =>
			'html_form::select_widget("remitt_protocol", array ( '.
				'"http" => "http", '.
				'"https" => "https" ))',
			__("Remitt Username") =>
			'html_form::text_widget("remitt_user", 16)',
			__("Remitt Password") =>
			'html_form::password_widget("remitt_pass", 16)'
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

	public function GetReport ( $file_type, $report ) {
		$remitt = CreateObject( 'org.freemedsoftware.core.Remitt', freemed::config_value('remitt_server') );
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	
		switch ( $file_type ) {
			case 'report':
			$param = array (
				CreateObject('org.freemedsoftware.core.xmlrpcval', 'output', 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $report, 'string')
				);
			break; // report

			case 'log':
			$param = array (
				CreateObject('org.freemedsoftware.core.xmlrpcval', 'log', 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $_REQUEST['year'], 'string'),
				CreateObject('org.freemedsoftware.core.xmlrpcval', $_REQUEST['report'], 'string')
				);
			break; // log
		}
		//print "param = "; print_r($param); print "<br/>\n";
		$report = $remitt->_call('Remitt.Interface.GetFile', $param, false);

		/*
		//Header('Content-type: text/plain');
		if (eregi('\%PDF\-1', $report)) {
			Header('Content-type: application/x-pdf');
		} elseif (eregi('<html', $report)) {
			// nothing
		} else {
			Header('Content-type: text/plain');
		}

		// Make sure to pass the actual filename ...
		Header("Content-Disposition: inline; filename=\"".$_REQUEST['report']."\"");

		print $report;
		die();
		*/

		return $report;
	} // end method GetReport

	public function ProcessStatement ( $procs = NULL ) {
		// Create new Remitt instance
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	

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
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_server'));

		// Handle invalid uniques
		if (!is_array($uniques)) {
			die (__("Invalid keys passed to status function!"));
		}

		foreach ($uniques AS $b => $u) {
			// Get individual status
			$status[$u] = $remitt->GetStatus( $u );
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
		if ( !is_array( $billkeys ) ) { return $claimlog->mark_billed( $billkeys ); }
		$mark = true;
		foreach ( $billkeys AS $key ) {
			$mark &= $claimlog->mark_billed ( $key );
		}
		return $mark;
	} // end method MarkAsBilled

	function rebillkey ( ) {
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	
		$claimlog = CreateObject('org.freemedsoftware.api.ClaimLog');
	
		// Get format and target from claimlog
		$query = "SELECT * FROM claimlog WHERE ".
			"clbillkey='".addslashes($_REQUEST['key'])."'";
		$result = $GLOBALS['sql']->query($query);
		$r = $GLOBALS['sql']->fetch_array($result);

		//print "<br/><br/><br/><hr/>\n";
		//print "key = ".$_REQUEST['key']."<br/>\n";
		//print "clformat = ".$r['clformat']."<br/>\n";
		//print "cltarget = ".$r['cltarget']."<br/>\n";
		$result = $remitt->ProcessBill(
			$_REQUEST['key'],
			$r['clformat'],
			$r['cltarget']
		);
		
		$buffer .= "<div class=\"section\">".
			__("Remitt Billing Sent")."</div><br/>\n";

		// Refresh to status screen
		global $refresh;
		$refresh = page_name()."?".
			"module=".$_REQUEST['module']."&".
			"type=".$_REQUEST['type']."&".
			"action=".$_REQUEST['action']."&".
			"billing_action=status&".
			"uniques=".urlencode(serialize(array($_REQUEST['key'] => $result)));

		$buffer .= __("Refreshing")." ... ";

		// Add to claimlog
		$result = $claimlog->log_billing (
			$_REQUEST['key'],
			$r['clformat'],
			$r['cltarget'],
			__("Remitt billing run sent")
		);
		/* $mark = $claimlog->mark_billed ( $this_billkey ); */
		return $buffer;
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

		// Present 'claims' as an array
		foreach ( $res AS $k => $v) {
			$res[$k]['claims'] = explode(',', $v['claims']);	
		}
		return $res;
	} // end method PatientsToBill

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
	//	$overrides (optional) - Array of media changes and other exceptions (TODO)
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* Billkey number
	//	* Number of claims in billkey
	//
	public function ProcessClaims ( $patients, $claims, $overrides=NULL ) {
		// Boiler plate for dealing with REMITT
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	

		// Create new ClaimLog instance
		$claimlog = CreateObject ('org.freemedsoftware.api.ClaimLog');

		$claim_limit = 500;
		$q = "SELECT p.id AS claim, i.inscodefoutput AS output_format, i.inscodefformat AS paper_format, i.inscodeftarget AS paper_target, i.inscodefformate AS electronic_format, i.inscodeftargete AS electronic_target FROM procrec p LEFT OUTER JOIN coverage c ON p.proccurcovid=c.id LEFT OUTER JOIN insco i ON c.covinsco=i.id WHERE FIND_IN_SET(p.procpatient, ".$GLOBALS['sql']->quote(join(',', $patients)).") AND FIND_IN_SET(p.id, ".$GLOBALS['sql']->quote(join(',', $claims)).")";
		$res = $GLOBALS['sql']->queryAll( $q );

		foreach ( $res AS $r ) {
			// TODO: handle overrides

			switch ($r['output_format']) {
				case 'paper':
				$s[ $r['paper_format'] . '/' . $r['paper_target'] . '/0' ][] = $r['claim'];
				break;

				case 'electronic':
				$s[ $r['electronic_format'] . '/' . $r['electronic_target'] . '/0' ][] = $r['claim'];
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
				list ( $_format, $_target, $_count ) = explode ( '/', $k );
				$pos = 0; $stack = 0;
				for ($i=0; $i<count($temp); $i++) {
					$cur_key = $_format . '/' . $_target . '/' . $stack;
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

			// TODO : extract this information from the system
			//$billkey['contact'] = $_REQUEST['contact'];
			//$billkey['clearinghouse'] = $_REQUEST['clearinghouse'];
			//$billkey['service'] = $_REQUEST['service'];

			// Create single array of stuff
			$billkey['procedures'] = $v;
			$this_billkey = $remitt->StoreBillKey( $billkey );

			// Add to list to be marked
			//$billkeys[] = $this_billkey;

			// Get format and target from default
			list ( $my_format, $my_target, $batch_id ) = explode ( '/', $k );
			$results[] = array (
				'result' => $remitt->ProcessBill( $this_billkey, $my_format, $my_target ),
				'billkey' => $this_billkey,
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

} // end class RemittBillingTransport

register_module('RemittBillingTransport');

?>
