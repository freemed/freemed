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
			'freeb_server', 'freeb_port', 'freeb_protocol'
		));
		$this->_SetMetaInformation('global_config', array (
			__("FreeB Server Hostname") =>
			'html_form::text_widget("freeb_server", 20, 50)',
			__("FreeB Server Port") =>
			'html_form::password_widget("freeb_port", 6)',
			__("Transport Protocol") =>
			'html_form::select_widget("freeb_protocol", array ( '.
				'"http" => "http", '.
				'"https" => "https" ))'
		));

		// Set appropriate associations
		$this->_SetHandler('BillingFunctions', 'transport');
		$this->_SetMetaInformation('BillingFunctionName', __("FreeB Billing System"));

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

			case 'rebill':
				return $this->rebillkey();
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
		$freeb_formats = $freeb->FormatList();
		$freeb_targets = $freeb->TargetList();

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
				<input type=\"checkbox\" onClick=\"alert('FIXME!'); return true;\"/>".__("Select None")."</td>
			<td class=\"DataHead\" width=\"15%\" align=\"center\">
				<input type=\"checkbox\" onClick=\"alert('FIXME!'); return true;\"/>".__("Select All")."</td>
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

					<td class=\"Data\" width=\"25%\">
					<select name=\"format[".$c."]\">
					";
					// Format selection widget from FreeB server
					foreach ($freeb_formats AS $k => $v) {
						$buffer .= "<option value=\"".prepare($v)."\" ".( $format[$c]==$v ? 'selected' : '' ).">".prepare($v)."</option>\n";
					}

					$buffer .= "
					</select>
					<select name=\"target[".$c."]\">
					";
					// Target selection widget from FreeB server
					foreach ($freeb_targets AS $k => $v) {
						$buffer .= "<option value=\"".prepare($v)."\" ".( $target[$c]==$v ? 'selected' : '' ).">".prepare($v)."</option>\n";
					}

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
						$format[$c] = $this_coverage->covinsco->local_record['inscodefformat'];
						$target[$c] = $this_coverage->covinsco->local_record['inscodeftarget'];
						// Look 'em up, if not, x12/txt is the default for now
						if (!$format[$c]) {
							$format[$c] = 'x12';
						}
						if (!$target[$c]) {
							$target[$c] = 'txt';
						}
					}

					// ... and notebook-style hide their
					// data so it can be expanded upon
					$buffer .= "
					<input type=\"hidden\" name=\"claim[".$c."]\" value=\"".prepare($claim[$c])."\" />
					<input type=\"hidden\" name=\"claim_owner[".$c."]\" value=\"".prepare($claim_owner[$c])."\" />
					<input type=\"hidden\" name=\"format[".$c."]\" value=\"".prepare($format[$c])."\" />
					<input type=\"hidden\" name=\"target[".$c."]\" value=\"".prepare($target[$c])."\" />
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
		<table border=\"0\">

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
		Perform FreeB billing runs.
		</td>
		</tr>
		</table>
		";
		return $buffer;
	} // end method menu

	function process ( $single = NULL ) {
		$buffer .= __("Submitting data to FreeB server")." ... <br/>\n"; 		
		// Create new FreeB instance
		$freeb = CreateObject('FreeMED.FreeB_v1');

		// Create Bill Key
		extract($_REQUEST);

		// Make sure we don't have someone passing us bupkus
		unset($billkey);


		// If we're doing a single claim, handle seperately
		if ($single != NULL) {
			// We always pass these ...
			$billkey['contact'] = $_REQUEST['contact'];
			$billkey['clearinghouse'] = $_REQUEST['clearinghouse'];
			$billkey['service'] = $_REQUEST['service'];

			// Create single array of stuff
			$billkey['procedures'][] = $single;
			$this_billkey = $freeb->StoreBillKey( $billkey );
			$result = $freeb->ProcessBill(
				$this_billkey,
				$format[$single],
				$target[$single],
				'SingleProcedure_'.$single
			);
			print "DEBUG: format = ".$format[$single]." target = ".$target[$single]."<br/>\n";
			print "DEBUG: "; print_r($result); print "<br/>\n";
			$buffer .= "Should have returned $result.<br/>\n";
			return $buffer;
		}

		// Create master hash to work with for all procedures, etc
		$bill_hash = array ();
		foreach ($claim AS $claim => $to_bill) {
			// First, form hash key
			$hash_key = $format[$claim].'__'.$target[$claim];
			print "hash key = $hash_key<br/>\n";

			// Only process if the claim is to be billed
			if ($to_bill == 1) {
				// And the patient is supposed to be billed
				if ($bill[$claim_owner[$claim]] == 1) {
					// Add the procedure to that hash
					$bill_hash[$hash_key]['procedures'][] = $claim;
				}
			}
		}

		// Once we have created these hashes, we loop through the list
		// of them and process each one
		foreach ($bill_hash AS $my_key => $billkey) {
			// Get format and target
			list ($my_format, $my_target) = explode ('__', $my_key);
			print "processing key = $my_key<br/>\n";

			// Add contact, clearinghouse, and service info
			$billkey['contact'] = $_REQUEST['contact'];
			$billkey['clearinghouse'] = $_REQUEST['clearinghouse'];
			$billkey['service'] = $_REQUEST['service'];

			// Lastly, we serialize the bill key
			$key = $freeb->StoreBillKey($billkey);

			// ... and send it to FreeB, to see what we get for a result
			$result = $freeb->ProcessBill($key, $my_format, $my_target);

			// DEBUG: Show what we got
			print "DEBUG: "; print_r($result); print "<br/>\n";
		}

		$buffer .= "This should show something here eventually.<br/>\n";

		return $buffer;
	} // end method process

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
