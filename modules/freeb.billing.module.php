<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class FreeBBillingTransport extends BillingModule {

	var $MODULE_NAME = "FreeB Billing System";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";

	var $MODULE_FILE = __FILE__;

	function FreeBBillingTransport ( ) {
		// GettextXML:
		//	__("FreeB Billing System")

		// Set configuration variables (username/password)
		$this->_SetMetaInformation('global_config_vars', array (
			'freeb_server', 'freeb_port', 'freeb_protocol',
			'freeb_path'
		));
		$this->_SetMetaInformation('global_config', array (
			__("FreeB Server Hostname") =>
			'html_form::text_widget("freeb_server", 20, 50)',
			__("FreeB Server Port") =>
			'html_form::text_widget("freeb_port", 6)',
			__("FreeB Server Port") =>
			'html_form::text_widget("freeb_path", 20, 50)',
			__("Transport Protocol") =>
			'html_form::select_widget("freeb_protocol", array ( '.
				'"http" => "http", '.
				'"https" => "https" ))'
		));

		// Set appropriate associations
		$this->_SetHandler('BillingFunctions', 'transport');
		$this->_SetMetaInformation('BillingFunctionName', __("FreeB Billing System"));
		$this->_SetMetaInformation('BillingFunctionDescription', __("Use the FreeB formatting and transport system to send insurance claims electronically or generate paper claims."));

		// Call parent constructor
		$this->BillingModule();
	} // end constructor FreeBBillingTransport

	function transport () {
		global $display_buffer, $billing_action;
		
		// Handle "Cancel" submit action by returning to previous
		// menu through refresh.
		if ($__submit == __("Cancel")) {
			global $refresh;
			$refresh = "billing_functions.php";
			return '';
		}

		// Act as a switchboard for everything
		switch ($billing_action) {
			case 'billing':
				return $this->billing();
				break;

			case 'mark':
				return $this->mark();
				break;

			case 'rebill':
				return $this->rebillkey();
				break;

			case 'reports':
				return $this->reports();
				break;

			case 'display_report':
				return $this->display_report();
				break;

			// By default, we show the form with information
			// regarding what is going on.
			default:
				return $this->menu();
				break;
		}
	} // end method transport

	function billing ( ) {
		global $type;
		extract ($_REQUEST);

		//print "__submit = ".$__submit."<br/>\n";

		// Handle super submit
		if ($__submit == __("Submit Claims")) {
			//print "__submit is Submit Claims<br/>\n";
			return $this->process();
		}

		// Handle single claim submit ... huge hack since the interface
		// calls for a button, and we can't hide button information
		// without a seperate form... so we try an array. Cross your
		// fingers on this one.
		if (is_array($__submit_alone)) {
			foreach ($__submit_alone as $k => $v) {
				//print "__submit_alone[$k] = $v<br/>\n";
				// Ensure that the actual button was pressed.
				if ($v == __("Submit Alone")) {
					//print "executing process for $k<br/>\n";
					return $this->process($k);
				}
			}
		}

		// Get FreeB formats and information
		$freeb = CreateObject('FreeMED.FreeB_v1');
		//$freeb_formats = $freeb->FormatList();
		//$freeb_targets = $freeb->TargetList();

		// Start master form
		$buffer .= "
		<form action=\"billing_functions.php\" method=\"post\">
		<input type=\"hidden\" name=\"type\" value=\"".get_class($this)."\" />
		<input type=\"hidden\" name=\"billing_action\" value=\"".$_REQUEST['billing_action']."\" />
		<input type=\"hidden\" name=\"action\" value=\"type\" />
		<input type=\"hidden\" name=\"been_here\" value=\"1\" />
		";

		// Determine list of procedures / patients to bill
		$patients_to_bill = $this->PatientsToBill();

		// If there are none, decide where to go from here
		if (count($patients_to_bill) < 1) {
			$buffer .= "
			<div align=\"center\">
			".__("There are no patients to bill.")."
			</div>
			";
			return false;
		}	

		// Show clearinghouse, etc info
		$buffer .= "
		<table width=\"740\" cellspacing=\"1\" cellpadding=\"0\" border=\"1\">
		<thead><tr>
			<td class=\"DataHead\" width=\"34%\">".__("Clearinghouse")."</td>
			<td class=\"DataHead\" width=\"33%\">".__("Billing Service")."</td>
			<td class=\"DataHead\" width=\"33%\">".__("Billing Contact")."</td>
		</tr></thead>
		<tbody><tr>
			<td class=\"Data\">".module_function('BillingClearinghouse', 'widget', 'clearinghouse')."</td>
			<td class=\"Data\">".module_function('BillingService', 'widget', 'service')."</td>
			<td class=\"Data\">".module_function('BillingContact', 'widget', 'contact')."</td>
		</tr></tbody>
		</table>
		";

		// Show billing table header
		$buffer .= "
		<table width=\"740\" cellspacing=\"1\" cellpadding=\"0\" border=\"1\">
		<thead><tr>
			<td class=\"DataHead\" width=\"15%\">".__("Patient")."</td>
			<td class=\"DataHead\" width=\"35%\">&nbsp;</td>
			<td class=\"DataHead\" width=\"15%\" align=\"center\">
				<!-- <input type=\"checkbox\" onClick=\"alert('FIXME!'); return true;\"/>".__("Select None")." -->&nbsp;</td>
			<td class=\"DataHead\" width=\"15%\" align=\"center\">
				<!-- <input type=\"checkbox\" onClick=\"alert('FIXME!'); return true;\"/>".__("Select All")." -->&nbsp;</td>
			<td class=\"DataHead\" width=\"20%\" align=\"center\">
				<input type=\"submit\" name=\"__submit\" value=\"".__("Submit Claims")."\" /></td>
		</td></thead>
		</table>
		";

		// Loop for patients
		foreach ($patients_to_bill AS $__garbage => $p) {
			// Default on first (!been_here) to check patient...
			if (!$been_here) {
				$patients[$p] = 1; // not sure what this does
				$bill[$p] = 1;
			}

			// Hide patient for billing and pass value depending
			// on whether or not it is passed already
			$buffer .= "<input type=\"hidden\" ".
				"name=\"patients[".$p."]\" value=\"".
				( isset($patients[$p]) ? $patients[$p] : '1' ).
				"\" />\n";

			// Get patient object
			unset($this_patient);
			$this_patient = CreateObject('FreeMED.Patient', $p);

			// Determine number of claims from global table, set
			// by PatientsToBill (clever, but hacky)
			$number_of_claims = $this->number_of_claims[$p];

			// Create master table
			$buffer .= "
			<table width=\"740\" cellspacing=\"1\" cellpadding=\"0\" border=\"1\">
			<tbody><tr>
			<td class=\"Data\" width=\"15%\"><a href=\"manage.php?id=".urlencode($p)."\"
				>".prepare($this_patient->fullName())."</a></td>
			<td class=\"Data\" width=\"20%\"><input type=\"checkbox\" name=\"expand[".$p."]\" ".( $expand[$p]==1 ? 'checked="checked"' : '' )." value=\"1\" onChange=\"this.form.submit(); return true;\" />".__("expand individual claims")."</td>
			<td class=\"Data\" width=\"10%\">".$number_of_claims." ".__("claims")."</td>
			<td class=\"Data\" width=\"25%\">&nbsp;</td>
			<td class=\"Data\" width=\"20%\"><input type=\"checkbox\" name=\"bill[".$p."]\" ".( $bill[$p]==1 ? 'checked="checked"' : '' )." value=\"1\" />".__("Submit Patient?")."</td>
			</tr>
			</tbody></table>
			";

			// Get claims for this patient
			$these_claims = $this->ProceduresToBill($p);
			//print "these claims = "; print_r($these_claims); print "<br/>\n";

			// Decide if we're expanding
			if ($expand[$p]) {
				// If expanding, display all procedures
				$buffer .= "
				<table width=\"740\" cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
				<tbody>
				";

				// Loop through claims
				foreach ($these_claims AS $__garbage => $c) {
					// Get claim record information from procrec
					$_record = freemed::get_link_rec($c, 'procrec');

					// Display
					//print "processing claim $c<br/>\n";
					$buffer .= "
					<tr>
					<td class=\"Data\" width=\"15%\" align=\"right\">
					<input type=\"checkbox\" name=\"claim[".$c."]\" ".( $claim[$c]==1 ? 'checked="checked"' : '' )." value=\"1\" />".__("Submit Claim?")."
					</td>
					<td class=\"Data\" width=\"25%\">
					<a href=\"module_loader.php?module=proceduremodule&patient=".urlencode($p)."&action=modform&id=".urlencode($c)."\"
					>".__("DOS:")." ".$_record['procdt']."</a>
					</td>

					<td class=\"Data\" colspan=\"2\">
					<select name=\"media[".$c."]\">
					";
					// Format selection widget from FreeB server
					$buffer .= $this->MediaWidgetOptions($media, $c);

					$buffer .= "
					</select>
					</td>
					<td class=\"Data\" width=\"35%\">
					<select name=\"coverage[".$c."]\">
					";

					// Depending on coverage from procedure,
					// we put together a list of everything
					$coverages = $this->PatientCoverages($c, &$curcov);
					//print "coverages ($c) = "; print_r($coverages); print "<br/>\n";
					foreach ($coverages AS $__garbage => $cov) {
						$this_coverage = CreateObject('FreeMED.Coverage', $cov);
						$buffer .= "<option value=\"".prepare($cov)."\" ".( $cov == $curcov ? 'selected' : '' ).">".$this_coverage->covinsco->insconame." ".
						( $this_coverage->covtype==1 ? __("(Primary)") : '' ).
						( $this_coverage->covtype==2 ? __("(Secondary)") : '' ).
						"</option>\n";
					}
					
					$buffer .= "
					</select>
					<input type=\"submit\" name=\"__submit_alone[".prepare($c)."]\" value=\"".__("Submit Alone")."\" />
					</td>
					</tr>
					";
				}
	
				// End display table			
				$buffer .= "
				</tbody></table>
				";
			} else {
				// ... otherwise set hidden attributes for them,
				// depending on what was passed
				foreach ($these_claims AS $__garbage => $c) {
					// On first load, assume that all claims
					// are being submitted...
					if (!$been_here) {
						$claim[$c] = 1;
						$claim_owner[$c] = $p;
						// HACK! Should not always be X12
						$__dummy = $this->PatientCoverages($c, &$curcov);
						$coverage[$c] = $curcov;
						// These default to values used by the default
						$this_coverage = CreateObject('FreeMED.Coverage', $curcov);
						$media[$c] = 'electronic';
						//$format[$c] = $this_coverage->covinsco->local_record['inscodefformat'];
						//$target[$c] = $this_coverage->covinsco->local_record['inscodeftarget'];
						// Look 'em up, if not, x12/txt is the default for now
						/*
						if (!$format[$c]) {
							$format[$c] = 'x12';
						}
						if (!$target[$c]) {
							$target[$c] = 'txt';
						}
						*/
					}

					// ... and notebook-style hide their
					// data so it can be expanded upon
					$buffer .= "
					<input type=\"hidden\" name=\"claim[".$c."]\" value=\"".prepare($claim[$c])."\" />
					<input type=\"hidden\" name=\"claim_owner[".$c."]\" value=\"".prepare($claim_owner[$c])."\" />
					<input type=\"hidden\" name=\"media[".$c."]\" value=\"".prepare($media[$c])."\" />
					<input type=\"hidden\" name=\"coverage[".$c."]\" value=\"".prepare($coverage[$c])."\" />
					";
				}
			}
		} // end looping for patients

		// End master form
		$buffer .= "
		</form>
		";

		return $buffer;
	} // end method billing

	function menu () {
		$buffer .= "
		<div class=\"section\">
		".__("FreeB Billing System")."
		</div>
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">

		<tr>
			<td class=\"DataHead\" width=\"15%\">".__("Function")."</td>
			<td class=\"DataHead\">".__("Description")."</td>
		</tr>

		<tr>
		<td>
		<a href=\"billing_functions.php?type=".get_class($this).
			"&billing_action=billing&action=type\"
			>".__("Perform Billing")."</a></td>
		<td>
		".__("Perform FreeB billing runs.")."
		</td>
		</tr>

		<tr>
		<td>
		<a href=\"billing_functions.php?type=".get_class($this).
			"&billing_action=reports&action=type\"
			>".__("Show Reports")."</a></td>
		<td>
		".__("View output files and logs from FreeB.")."
		</td>
		</tr>
		</table>
		";
		return $buffer;
	} // end method menu

	function reports ( ) {
		$buffer = '';

		$freeb = CreateObject('FreeMED.FreeB_v1');
		$reports = $freeb->_call('FreeB.Report.list', array(
			CreateObject('PHP.xmlrpcval', 'report', 'string')
			), false);

		$buffer .= "<div class=\"section\">".__("FreeB Results and Logs")."</div>\n";
		//print "<br/<hr/>reports = "; print_r($reports); print "<hr/>\n";
		$buffer .= "<table border=\"0\" cellspacing=\"0\" ".
			"width=\"75%\" cellpadding=\"2\">\n".
			"<tr>\n".
			"<td class=\"DataHead\">".__("Results")."</td>\n".
			"<td class=\"DataHead\">".__("Logs")."</td>\n".
			"</tr>\n".
			"<tr><td valign=\"top\">\n";
		foreach ($reports AS $report) {
			$buffer .= "<a href=\"".page_name()."?".
				"module=".$_REQUEST['module']."&".
				"type=".$_REQUEST['type']."&".
				"action=".$_REQUEST['action']."&".
				"billing_action=display_report&".
				"file_type=report&".
				"report=".urlencode($report) . "\" ".
				"target=\"_view\">".
				prepare($report) . "</a><br/>\n";
		}
		$buffer .= "</td><td valign=\"top\">\n";

		// Get log years
		$years = $freeb->_call('FreeB.Report.log_years');
		//print "<br/><hr/>"; print_r($years); print "<hr/>";
		$selected_year = ( isset($_REQUEST['year']) ?
			$_REQUEST['year'] : date('Y') );
		foreach ($years AS $this_year) {
			$buffer .= "<a href=\"".page_name()."?".
				"module=".$_REQUEST['module']."&".
				"type=".$_REQUEST['type']."&".
				"action=".$_REQUEST['action']."&".
				"billing_action=reports&".
				"year=".urlencode($this_year) . "\" ".
				">".prepare($this_year)."</a><br/>\n";
			if ($this_year == $selected_year) {
				$freeb = CreateObject('FreeMED.FreeB_v1');
				$logs = $freeb->_call('FreeB.Report.list', array(
					CreateObject('PHP.xmlrpcval', 'log', 'string'),
					CreateObject('PHP.xmlrpcval', $this_year, 'string')
					), false);
				foreach ($logs AS $log) {
					$buffer .= "&nbsp;&nbsp; - ".
						"<a href=\"".page_name()."?".
						"module=".$_REQUEST['module']."&".
						"type=".$_REQUEST['type']."&".
						"action=".$_REQUEST['action']."&".
						"billing_action=display_report&".
						"report=".urlencode($log)."&".
						"file_type=log&".
						"year=".urlencode($this_year)."\" ".
						"target=\"_view\">".
						prepare($log)."</a><br/>\n";
				}
			} // is selected year
		} // end looping through years
		$buffer .= "</td></tr></table>\n";
		return $buffer;
	} // end method reports

	function display_report ( ) {
		$buffer = '';

		$freeb = CreateObject('FreeMED.FreeB_v1');
		switch ($_REQUEST['file_type']) {
			case 'report':
			$param = array (
				CreateObject('PHP.xmlrpcval', 'report', 'string'),
				CreateObject('PHP.xmlrpcval', $_REQUEST['report'], 'string')
				);
			break; // report

			case 'log':
			$param = array (
				CreateObject('PHP.xmlrpcval', 'log', 'string'),
				CreateObject('PHP.xmlrpcval', $_REQUEST['year'], 'string'),
				CreateObject('PHP.xmlrpcval', $_REQUEST['report'], 'string')
				);
			break; // log
		}
		//print "param = "; print_r($param); print "<br/>\n";
		$report = $freeb->_call('FreeB.Report.get', $param, false);

		//Header('Content-type: text/plain');
		if (eregi('\%PDF\-1', $report)) {
			Header('Content-type: application/x-pdf');
		} elseif (eregi('<html', $report)) {
			// nothing
		} else {
			Header('Content-type: text/plain');
		}
		print $report;
		die();
	} // end method display_report

	function process ( $single = NULL ) {
		$buffer .= __("Submitting data to FreeB server")." ... <br/>\n"; 		
		// Create new FreeB instance
		$freeb = CreateObject('FreeMED.FreeB_v1');

		// Create new ClaimLog instance
		$claimlog = CreateObject ('FreeMED.ClaimLog');

		// Create Bill Key
		extract($_REQUEST);

		// Make sure we don't have someone passing us bupkus
		unset($billkey);

		// If we're doing a single claim, handle seperately
		if ($single != NULL) {
			// We always pass these ...
			print "<br/><br/><hr/>processing single = $single<br/>\n";
			$billkey['contact'] = $_REQUEST['contact'];
			$billkey['clearinghouse'] = $_REQUEST['clearinghouse'];
			$billkey['service'] = $_REQUEST['service'];

			// Create single array of stuff
			$billkey['procedures'][] = $single;
			$this_billkey = $freeb->StoreBillKey( $billkey );

			// Add to list to be marked
			$__billkeys[] = $this_billkey;

			// Get format and target from default
			list ($my_format, $my_target) =
				$this->MediaToFormatTarget($single, $media[$single]);
			$result = $freeb->ProcessBill(
				$this_billkey,
				$my_format,
				$my_target,
				'SingleProcedure_'.$single
			);

			$buffer .= "<div class=\"section\">".
				__("FreeB Billing Sent")."</div><br/>\n";
			$buffer .= __("Result file returned was")." ".
				$result."<br/>\n";
			//print "DEBUG: format = ".$format[$single]." target = ".$target[$single]."<br/>\n";
			//print "DEBUG: "; print_r($result); print "<br/>\n";
			$buffer .= "Should have returned $result.<br/>\n";
			// Add to claimlog
			$result = $claimlog->log_billing (
				$this_billkey,
				$my_format,
				$my_target,
				__("FreeB billing run sent")
			);
			/*
			$mark = $claimlog->mark_billed ( $this_billkey );
			*/
			return $buffer;
		}

		// Create master hash to work with for all procedures, etc
		$bill_hash = array ();
		foreach ($claim AS $my_claim => $to_bill) {
			// First, form hash key
			list ($my_format, $my_target) =
				$this->MediaToFormatTarget($my_claim, $media[$single]);
			$hash_key = $my_format.'__'.$my_target;
			print "hash key = $hash_key<br/>\n";

			// Only process if the claim is to be billed
			if ($to_bill == 1) {
				// And the patient is supposed to be billed
		//TODO Jeff, this check does not belong here. It should not display a claim that is 
		// not set to be billed in the interface.
			//	if ($bill[$claim_owner[$my_claim]] == 1) {
					// Add the procedure to that hash
					$bill_hash[$hash_key]['procedures'][] = $my_claim;
				}
			//}
		}

		// Once we have created these hashes, we loop through the list
		// of them and process each one
		$buffer .= "<div class=\"section\">".
			__("FreeB Billing Sent")."</div><br/>\n";
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
			$key = $freeb->StoreBillKey($billkey);

			// ... and send it to FreeB, to see what we get for a result
			$result = $freeb->ProcessBill($key, $my_format, $my_target);
			$buffer = __("Result file returned was")." ".
				$result."<br/>\n";
			// Add to claimlog
			$result = $claimlog->log_billing (
				$key,
				my_format,
				$my_target,
				__("FreeB billing run sent")
			);

			// Add to the list of bill keys
			$__billkeys[] = $key;

			/*
			$mark = $claimlog->mark_billed ( $this_billkey );
			*/

			// DEBUG: Show what we got
		//	print "DEBUG: "; print_r($result); print "<br/>\n";

			} // end verifying format and target
		}


		// Show something
		if (!is_array($__billkeys)) {
			$__billkeys = array ($__billkeys);
		}

		$buffer .= __("If you are satisfied with your bills, mark them as sent.")."<br/>".
			"<a href=\"".page_name()."?".
			"module=".urlencode($_REQUEST['module'])."&".
			"action=".urlencode($_REQUEST['action'])."&".
			"type=".urlencode($_REQUEST['type'])."&".
			"keys=".urlencode(serialize($__billkeys)).
			"\" class=\"button\">".__("Mark as Billed")."</a>\n";

		return $buffer;
	} // end method process

	function mark ( ) {
		$billkeys = unserialize($_REQUEST['keys']);
		foreach ($billkeys AS $key) {
			$mark &= $claimlog->mark_billed ( $key );
		}
		if ($mark) {
			$buffer .=  __("Bills were successfully marked as billed.");
		} else {
			$buffer .=  __("Bills were not able to be marked as billed.");
		}
		return $buffer;
	} // end method rebillkey

	function rebillkey ( ) {
		$billkey = $_REQUEST['key'];

		$freeb = CreateObject('FreeMED.FreeB_v1');
		$this_billkey = $freeb->StoreBillKey( $billkey );
		$result = $freeb->ProcessBill(
			$key,
			$format[$single],
			$target[$single]
		);
		return $result;
	} // end method rebillkey

	//--------------------------------------------------------------------
	// Helper and other internal functions
	//--------------------------------------------------------------------

	function MediaToFormatTarget ( $claim, $media ) {
		global $coverage;
		$this_coverage = CreateObject('FreeMED.Coverage', $coverage[$claim]);
		switch ($media) {
			case 'paper':
			$format = $this_coverage->covinsco->local_record['inscodefformat'];
			$target = $this_coverage->covinsco->local_record['inscodeftarget'];
			break;

			case 'electronic': default:
			$format = $this_coverage->covinsco->local_record['inscodefformate'];
			$target = $this_coverage->covinsco->local_record['inscodeftargete'];
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
			// (Not sure) Needs not to be billed already
			"a.procbilled = '0' ".
			// Last little bit of the global claim count hack
			"GROUP BY a.procpatient ".
			// Here's the ordering magic, using patient table:
			"ORDER BY b.ptlname,b.ptfname,b.ptmname,b.ptdob";

		$result = $GLOBALS['sql']->query($query);
		
		// Simple hack to make sure that no results return no answers
		if (!$GLOBALS['sql']->results($result)) {
			return array ();
		}

		$return = array ();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$return[] = $r['patient'];
			$this->number_of_claims[$r['patient']] = $r['claims'];
		}

		return $return;
	} // end method PatientsToBill

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

} // end class FreeBBillingTransport

register_module('FreeBBillingTransport');

?>
