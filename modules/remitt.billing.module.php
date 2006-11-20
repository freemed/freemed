<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

	public function GetPatientClaims ( $patient ) {
		$these_claims = $this->ProceduresToBill($patient);
		//print "these claims = "; print_r($these_claims); print "<br/>\n";
		// Loop through claims
		global $coverage;
		foreach ($these_claims AS $__garbage => $c) {
			$coverages = $this->PatientCoverages($c, &$curcov);
			$coverage[$c] = $curcov;
			$__temp = CreateObject( 'org.freemedsoftware.core.Coverage', $coverage[$c] );
			$media[$c] = $__temp->covinsco->local_record['inscodefoutput'];
			unset($__temp);

			// Get claim record information from procrec
			$_record = $GLOBALS['sql']->get_link ( 'procrec', $c );

			$buffer .= $this->MediaWidgetOptions($media, $c);

			$buffer .= "
			</select>
			</td>
			<td class=\"Data\" width=\"35%\">
			<select name=\"coverage[".$c."]\">
			";

			// Depending on coverage from procedure,
			// we put together a list of everything
			foreach ($coverages AS $__garbage => $cov) {
				$this_coverage = CreateObject('org.freemedsoftware.core.Coverage', $cov);
				$covoptions[] = $this_coverage->covinsco->insconame." ".
				( $this_coverage->covtype==1 ? __("(Primary)") : '' ).
				( $this_coverage->covtype==2 ? __("(Secondary)") : '' );
			}
		}

		// TODO: FIXME FIXME FIXME	
	} // end method GetPatientClaims

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

	function process ( $single = NULL ) {
		// Create new Remitt instance
		$remitt = CreateObject('org.freemedsoftware.api.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	

		// Create new ClaimLog instance
		$claimlog = CreateObject ('org.freemedsoftware.api.ClaimLog');

		// Create Bill Key
		extract($_REQUEST);

		// Make sure we don't have someone passing us bupkus
		unset($billkey);

		// If we're doing a single claim, handle seperately
		if ($single != NULL) {
			// We always pass these ...
			//print "<br/><br/><hr/>processing single = $single<br/>\n";
			$billkey['contact'] = $_REQUEST['contact'];
			$billkey['clearinghouse'] = $_REQUEST['clearinghouse'];
			$billkey['service'] = $_REQUEST['service'];

			// Create single array of stuff
			$billkey['procedures'][] = $single;
			$this_billkey = $remitt->StoreBillKey( $billkey );

			// Add to list to be marked
			$__billkeys[] = $this_billkey;

			// Get format and target from default
			list ($my_format, $my_target) =
				$this->MediaToFormatTarget($single, $_REQUEST['media'][$single]);
			//print_r($this->MediaToFormatTarget($single, $_REQUEST['media'][$single]));
				
			$result = $remitt->ProcessBill( $this_billkey, $my_format, $my_target );

			$buffer .= "<div class=\"section\">".
				__("Remitt Billing Sent")."</div><br/>\n";

			// Refresh to status screen
			global $refresh;
			$refresh = page_name()."?".
				"module=".$_REQUEST['module']."&".
				"type=".$_REQUEST['type']."&".
				"action=".$_REQUEST['action']."&".
				"billing_action=status&".
				"uniques=".urlencode(serialize(array($this_billkey => $result)));

			$buffer .= __("Refreshing")." ... ";

			//print "DEBUG: format = ".$format[$single]." target = ".$target[$single]."<br/>\n";
			//print "DEBUG: "; print_r($result); print "<br/>\n";
			//$buffer .= "Should have returned $result.<br/>\n";
			// Add to claimlog
			$result = $claimlog->log_billing (
				$this_billkey,
				$my_format,
				$my_target,
				__("Remitt billing run sent")
			);
			/* $mark = $claimlog->mark_billed ( $this_billkey ); */
			$buffer .= __("If you are satisfied with your bills, mark them as sent.")."<br/>".
			"<a href=\"".page_name()."?".
			"module=".urlencode($_REQUEST['module'])."&".
			"action=".urlencode($_REQUEST['action'])."&".
			"type=".urlencode($_REQUEST['type'])."&".
			"billing_action=mark&".
			"keys=".urlencode(serialize($__billkeys)).
			"\" class=\"button\">".__("Mark All Batches as Billed")."</a>\n";

			return $buffer;
		}

		// Create master hash to work with for all procedures, etc
		$bill_hash = array ();
		foreach ($claim AS $my_claim => $to_bill) {
			// First, form hash key
			list ($my_format, $my_target) =
				$this->MediaToFormatTarget($my_claim, $_REQUEST['media'][$my_claim]);
			$hash_key = $my_format.'__'.$my_target;
			//print "hash key = $hash_key<br/>\n";

			// Only process if the claim is to be billed
			// (also check for null hash key)
			if (($to_bill == 1) and ($hash_key != '__')) {
				// And the patient is supposed to be billed
		//TODO Jeff, this check does not belong here. It should not display a claim that is 
		// not set to be billed in the interface.
			//	if ($bill[$claim_owner[$my_claim]] == 1) {
					// Add the procedure to that hash
					$bill_hash[$hash_key]['procedures'][] = is_array($my_claim) ? $my_claim : array($my_claim);
				}
			//}
		}

		// Once we have created these hashes, we loop through the list
		// of them and process each one
		$buffer .= "<div class=\"section\">".
			__("Remitt Billing Sent")."</div><br/>\n";
		foreach ($bill_hash AS $my_key => $billkey) {
			// Get format and target
			list ($my_format, $my_target) = explode ('__', $my_key);
			//print "processing key = $my_key<br/>\n";
			if ($my_format and $my_target) {

			// Add contact, clearinghouse, and service info
			$billkey['contact'] = $_REQUEST['contact'];
			$billkey['clearinghouse'] = $_REQUEST['clearinghouse'];
			$billkey['service'] = $_REQUEST['service'];

			// Lastly, we serialize the bill key
			$key = $remitt->StoreBillKey($billkey);

			// ... and send it to Remitt, to see what we get for a result
			$unique_key = $remitt->ProcessBill($key, $my_format, $my_target);
			// Add to claimlog
			$result = $claimlog->log_billing (
				$key,
				$my_format,
				$my_target,
				__("Remitt billing run sent")
			);

			// Add to the list of bill keys
			$__billkeys[] = $key;
			$uniques[$key] = $unique_key;

			/*
			$mark = $claimlog->mark_billed ( $this_billkey );
			*/

			// DEBUG: Show what we got
			//print "DEBUG: "; print_r($result); print "<br/>\n";

			} // end verifying format and target
		}


		// Show something
		if (!is_array($__billkeys)) {
			$__billkeys = array ($__billkeys);
			//$uniques = array ( $__billkeys => $result );
		}

		// Refresh to status screen
		global $refresh;
		$refresh = page_name()."?".
			"module=".$_REQUEST['module']."&".
			"type=".$_REQUEST['type']."&".
			"action=".$_REQUEST['action']."&".
			"billing_action=status&".
			"uniques=".urlencode(serialize($uniques));

		$buffer .= __("Refreshing")." ... ";

/*
		$buffer .= __("If you are satisfied with your bills, mark them as sent.")."<br/>".
			"<a href=\"".page_name()."?".
			"module=".urlencode($_REQUEST['module'])."&".
			"action=".urlencode($_REQUEST['action'])."&".
			"type=".urlencode($_REQUEST['type'])."&".
			"billing_action=mark&".
			"keys=".urlencode(serialize($__billkeys)).
			"\" class=\"button\">".__("Mark as Billed")."</a>\n";
*/

		return $buffer;
	} // end method process

	function status ( $_uniques = NULL ) {
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));

		// Use optional parameter for first display only
		if ($_uniques != NULL) {
			$uniques = $_uniques;
		} else {
			// If passed by POST/GET, they are serialized
			$uniques = unserialize(stripslashes($_REQUEST['uniques']));
		}

		// Handle invalid uniques
		if (!is_array($uniques)) {
			return __("Invalid keys passed to status function!")."<br/>\n";
		}

		$buffer .= "
		<table border=\"0\">
		<tr>
		<td class=\"DataHead\">".__("Identifier")."</td>
		<td class=\"DataHead\">".__("Status")."</td>
		<td class=\"DataHead\">".__("Report")."</td>
		<td class=\"DataHead\">".__("Action")."</td>
		</tr>
		";
		$alldone = true;
		foreach ($uniques AS $b => $u) {
			// If we have an error from REMITT, show as such
			if (is_array($u)) {
				$buffer .= "
				<tr>
				<td>".prepare($b)."</td>
				<td>".__("ERROR")."</td>
				<td>".prepare($u['faultString'])."</td>
				<td>&nbsp;</td>
				</tr>
				";
			} else {
				// Add to $billkeys
				$billkeys[] = $b;

				// Get individual status
				$s = $remitt->GetStatus($u);
				if (empty($s)) { $alldone = false; }
				$buffer .= "
				<tr>
				<td>".prepare($b)." (".prepare($u).")</td>
				<td>".( empty($s) ?
					__("Processing") :
					__("Completed") )."</td>
				<td>".( empty($s) ? "&nbsp;" :
					"<a href=\"".page_name()."?".
					"module=".urlencode( $_REQUEST['module'] )."&".
					"type=".urlencode( $_REQUEST['type'] )."&".
					"action=".urlencode( $_REQUEST['action'] )."&".
					"billing_action=display_report&".
					"file_type=report&".
					"report=".urlencode( basename($s) ) . "\" ".
					"target=\"_view\">".prepare($s)."</a>" )."</td>
				<td>".( empty($s) ? "&nbsp;" :
					( $_SESSION['mark_as_billed'][$b] ?
					__("Marked as Billed") :
					"<a href=\"".page_name()."?".
					"module=".urlencode( $_REQUEST['module'] )."&".
					"action=".urlencode( $_REQUEST['action'] )."&".
					"type=".urlencode( $_REQUEST['type'] )."&".
					"billing_action=mark&".
					"return=".urlencode($_SERVER['REQUEST_URI'])."&".
					"keys=".urlencode(serialize(array( $b ))).
					"\" class=\"button\">".__("Mark as Billed")."</a>" ))."</td>
				</tr>
				";
			}
		} // end foreach uniques
		$buffer .= "</table>\n";

		// Handle refreshing
		if (!$alldone) {
			global $refresh;
			$GLOBALS['__freemed']['automatic_refresh'] = '15';
		} else {
			// Show mark as billed with reformed billkeys array
			$buffer .= "<p/>".
			__("If you are satisfied with your bills, mark them as sent.")."<br/>".
			"<a href=\"".page_name()."?".
			"module=".urlencode($_REQUEST['module'])."&".
			"action=".urlencode($_REQUEST['action'])."&".
			"type=".urlencode($_REQUEST['type'])."&".
			"billing_action=mark&".
			"keys=".urlencode(serialize($billkeys)).
			"\" class=\"button\">".__("Mark All Batches as Billed")."</a>\n";
		}
		
		return $buffer;
	} // end method status

	function mark ( ) {
		$claimlog = CreateObject ('FreeMED.ClaimLog');
		$billkeys = unserialize(stripslashes($_REQUEST['keys']));
		$mark = true;
		foreach ($billkeys AS $key) {
			//print "marking key $key<br/>\n";
			$mark &= $claimlog->mark_billed ( $key );
			$_SESSION['mark_as_billed'][$key] = 1;
		}
		if ($mark) {
			$buffer .=  __("Bills were successfully marked as billed.");
		} else {
			$buffer .=  __("Bills were not able to be marked as billed.");
		}

		if ($_REQUEST['return']) {
			global $refresh ; $refresh = $_REQUEST['return'];
		}
		return $buffer;
	} // end method mark

	function rebillkey ( ) {
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	
		$claimlog = CreateObject('FreeMED.ClaimLog');
	
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

	//--------------------------------------------------------------------
	// Helper and other internal functions
	//--------------------------------------------------------------------

	function MediaToFormatTarget ( $claim, $media ) {
		global $coverage;
		$cov = $_REQUEST['coverage'][$claim] ? $_REQUEST['coverage'][$claim] :
			$coverage[$claim];
		$this_coverage = CreateObject('FreeMED.Coverage', $cov);
		switch ($media) {
			case 'paper':
			$format = $this_coverage->covinsco->local_record['inscodefformat'];
			$target = $this_coverage->covinsco->local_record['inscodeftarget'];
			break;

			case 'electronic':
			$format = $this_coverage->covinsco->local_record['inscodefformate'];
			$target = $this_coverage->covinsco->local_record['inscodeftargete'];
			break;

			default:
			// This should *not* happen
			break;
		}
		return array ($format, $target);
	}

	function MediaWidgetOptions ( $media, $index ) {
		global $coverage;
		$this_coverage = CreateObject('FreeMED.Coverage', $coverage[$index]);
		$l = $this_coverage->covinsco->local_record;
		return "<option value=\"electronic\" ".
			( ( $media[$index] == 'electronic' ) ? 'SELECTED' : '' ).
			">".__("Electronic")." - ".
			$l['inscodefformate'].'/'.$l['inscodeftargete'].
			"</option>\n".
			"<option value=\"paper\" ".
			( ( $media[$index] == 'paper' ) ? 'SELECTED' : '' ).
			">".__("Paper")." - ".
			$l['inscodefformat'].'/'.$l['inscodeftarget'].
			"</option>\n";
	}

	function PatientCoverages ( $claim, $default_coverage ) {
		// Get all patient coverages associated with a procedure
		$rec = freemed::get_link_rec($claim, 'procrec');
		for ($i=1; $i<=4; $i++) {
			if ($rec['proccov'.$i] > 0) {
				$coverage[] = $rec['proccov'.$i];
			}
		}

		// Get the "default" coverage, and set $default_coverage to it
		$default_coverage = $rec['proccurcovid'];
		//print "default_coverage = $default_coverage<br/>\n";

		// Return coverages
		return $coverage;
	} // end method PatientCoverages

	function PatientsToBill ( ) {
		// This is a *huge* select hack so that the query returns
		// patients in a quasi-alphabetical order. Any better
		// suggestions are welcomed.  - Jeff
		$query = "SELECT DISTINCT(a.procpatient) AS patient, ".
			// Hack to get global claim counts
			"COUNT(a.id) AS claims ".
			"FROM procrec AS a, patient AS b ".
			// Needs to have a balance of over 0
			"WHERE a.procbalcurrent > '0' AND ".
			// Make sure the association is made
			"a.procpatient = b.id AND ".
			// Needs to be billable (at all) -- examine this one!
			//"a.procbillable = '0' AND ".
			// Must be *insurance* billable, otherwise we
			// shouldn't be billing this with REMITT
			"a.proccurcovtp > 0 AND ".
			// (Not sure) Needs not to be billed already
			"a.procbilled = '0' ".
			// Last little bit of the global claim count hack
			"GROUP BY a.procpatient ".
			// Here's the ordering magic, using patient table:
			"ORDER BY b.ptlname,b.ptfname,b.ptmname,b.ptdob";

		$result = $GLOBALS['sql']->query($query);
		
		// Simple hack to make sure that no results return no answers
		if (!$GLOBALS['sql']->results($result)) {
			return false;
		}

		$return = array ();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$return[] = $r['patient'];
			$this->number_of_claims[$r['patient']] = $r['claims'];
		}

		return $return;
	} // end method PatientsToBill

	function NumberOfClaimsForPatient ( $p ) {
		$x = $this->PatientsToBill();
		return $this->number_of_claims[$p];
	}

	function MediaForProcedure ( $p ) {
		$this_coverage = CreateObject('org.freemedsoftware.core.Coverage', $curcov);
		$media = $this_coverage->covinsco->local_record['inscodefoutput'];
	}

	function ProceduresToBill ( $patient ) {
		$query = "SELECT id FROM procrec ".
			// Needs to have a balance of over 0
			"WHERE procbalcurrent > '0' AND ".
			// And patient must jive
			"procpatient = '".addslashes($patient)."' AND ".
			// Needs to be billable (at all) -- examine this one!
			//"procbillable = '0' AND ".
			// (Not sure) Needs not to be billed already
			"procbilled = '0' ".
			// Order by date of procedure
			"ORDER BY procdt";

		$result = $GLOBALS['sql']->query($query);
		
		// Simple hack to make sure that no results return no answers
		if (!$GLOBALS['sql']->results($result)) {
			return array ();
		}

		$return = array ();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$return[] = $r['id'];
		}

		return $return;
	} // end method ProceduresToBill

} // end class RemittBillingTransport

register_module('RemittBillingTransport');

?>
