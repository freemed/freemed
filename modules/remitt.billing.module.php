<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.BillingModule');

class RemittBillingTransport extends BillingModule {

	var $MODULE_NAME = "Remitt Billing System";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";

	var $MODULE_FILE = __FILE__;

	function RemittBillingTransport ( ) {
		// GettextXML:
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
		$this->BillingModule();
	} // end constructor RemittBillingTransport

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

			case 'statement':
				return $this->statement();
				break;

			case 'status':
				return $this->status();
				break;

			case 'mark':
				return $this->mark();
				break;

			case 'rebill':
				return $this->rebillkey();
				break;

			case 'rebill_menu':
				return $this->rebill_menu();
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
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		if (!$remitt->GetServerStatus()) {
			trigger_error(__("The REMITT Server is not running. Please start it and try again."), E_USER_ERROR);
		}

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

		// Start master form
		$buffer .= "
		<form action=\"".page_name()."\" method=\"post\">
		<input type=\"hidden\" name=\"type\" value=\"".get_class($this)."\" />
		<input type=\"hidden\" name=\"billing_action\" value=\"".$_REQUEST['billing_action']."\" />
		<input type=\"hidden\" name=\"action\" value=\"type\" />
		<input type=\"hidden\" name=\"been_here\" value=\"1\" />
		";

		// Determine list of procedures / patients to bill
		$patients_to_bill = $this->PatientsToBill();

		// If there are none, decide where to go from here
		if (!$patients_to_bill) {
			$buffer .= "
			<div align=\"center\">
			".__("There are no patients to bill.")."
			</div>
			";
			return $buffer;
		}	

		// Show clearinghouse, etc info
		$buffer .= "
		<table width=\"80%\" cellspacing=\"1\" cellpadding=\"0\" border=\"1\">
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
		<table width=\"80%\" cellspacing=\"1\" cellpadding=\"0\" border=\"1\">
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

		include_once(freemed::template_file('ajax.php'));

		// Loop for patients
		foreach ($patients_to_bill AS $__garbage => $p) {
			// Default on first (!been_here) to check patient...
			if (!$_REQUEST['been_here']) {
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
			<table width=\"80%\" cellspacing=\"1\" cellpadding=\"0\" border=\"1\">
			<tbody><tr>
			<td class=\"Data\" width=\"15%\"><a href=\"manage.php?id=".urlencode($p)."\"
				>".prepare($this_patient->fullName())."</a></td>
			<td class=\"Data\" width=\"20%\">".
				ajax_expand_module_html(
					'patient_claims_'.$p,
					get_class($this),
					'ajax_show_patient_claims',
					$p
				)." ".__("expand individual claims")."</td>
			<td class=\"Data\" width=\"10%\">".$number_of_claims." ".__("claims")."</td>
			<td class=\"Data\" width=\"25%\">&nbsp;</td>
			<td class=\"Data\" width=\"20%\"><input type=\"checkbox\" name=\"bill[".$p."]\" ".( $bill[$p]==1 ? 'checked="checked"' : '' )." value=\"1\" />".__("Submit Patient?")."</td>
			</tr>
			</tbody></table>
			";

			// Get claims for this patient
			$these_claims = $this->ProceduresToBill($p);
			//print "these claims = "; print_r($these_claims); print "<br/>\n";

			// ... otherwise set hidden attributes for them,
			// depending on what was passed
			foreach ($these_claims AS $__garbage => $c) {
				// On first load, assume that all claims
				// are being submitted...
				if (!$_REQUEST['been_here']) {
					$claim[$c] = 1;
					$claim_owner[$c] = $p;
					// HACK! Should not always be X12
					$__dummy = $this->PatientCoverages($c, &$curcov);
					$coverage[$c] = $curcov;
					// These default to values used by the default
					$this_coverage = CreateObject('FreeMED.Coverage', $curcov);
					//print "media for $c set to electronic<br/>\n";
					$media[$c] = $this_coverage->covinsco->local_record['inscodefoutput'];
					//$format[$c] = $this_coverage->covinsco->local_record['inscodefformat'];
					//$target[$c] = $this_coverage->covinsco->local_record['inscodeftarget'];
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

			// Add hidden div for ajax stuff
			$buffer .= "<div id=\"patient_claims_$p\"></div>\n";
		} // end looping for patients

		// End master form
		$buffer .= "
		</form>
		";

		return $buffer;
	} // end method billing

	function menu () {
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$buffer .= "
		<div class=\"section\">
		".__("Remitt Billing System")." ( <span ".
		( $remitt->GetServerStatus() ?
		">".__("REMITT Server Running")." [Protocol v".
		$remitt->GetProtocolVersion()."] " :
		"style=\"color: #ff0000;\"><b>".__("REMITT Server Not Running")."</b>"
		)."</span> ) </div>
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">

		<tr>
			<td class=\"DataHead\" width=\"15%\">".__("Function")."</td>
			<td class=\"DataHead\">".__("Description")."</td>
		</tr>

		<tr>
		<td>
		<a href=\"".page_name()."?type=".get_class($this).
			"&billing_action=statement&action=type\"
			>".__("Patient Statements")."</a></td>
		<td>
		".__("Create patient statements for the system.")."
		</td>
		</tr>

		<tr>
		<td>
		<a href=\"".page_name()."?type=".get_class($this).
			"&billing_action=billing&action=type\"
			>".__("Perform Billing")."</a></td>
		<td>
		".__("Perform Remitt billing runs.")."
		</td>
		</tr>

		<tr>
		<td>
		<a href=\"".page_name()."?type=".get_class($this).
			"&billing_action=rebill_menu&action=type\"
			>".__("Rebill")."</a></td>
		<td>
		".__("Select a previous billing to rebill.")."
		</td>
		</tr>

		<tr>
		<td>
		<a href=\"".page_name()."?type=".get_class($this).
			"&billing_action=reports&action=type\"
			>".__("Show Reports")."</a></td>
		<td>
		".__("View output files and logs from Remitt.")."
		</td>
		</tr>
		</table>
		";
		return $buffer;
	} // end method menu

	function rebill_menu ( ) {
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		if (!$remitt->GetServerStatus()) {
			trigger_error(__("The REMITT Server is not running. Please start it and try again."), E_USER_ERROR);
		}

		$buffer = '';

		$query = "SELECT * FROM billkey ORDER BY id DESC LIMIT 50";
		$result = $GLOBALS['sql']->query($query);

		$buffer .= "<div class=\"section\">".__("Rebill Claims")."</div><br/>\n";

		if (!$GLOBALS['sql']->results($result)) {
			$buffer .= "
			<div align=\"center\">
			".__("There are no billing runs to rebill.")."
			</div>
			";
			return $buffer;
		}

		$buffer .= "
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">
		<tr>
			<td class=\"DataHead\">".__("Date Billed")."</td>
			<td class=\"DataHead\">".__("Billkey")."</td>
			<td class=\"DataHead\">".__("Detail")."</td>
			<td class=\"DataHead\">".__("Procedures")."</td>
			<td class=\"DataHead\" colspan=\"2\">".__("Action")."</td>
		</tr>
		";
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$bk = unserialize($r['billkey']);
			$bk_count = count($bk['procedures']);

			$buffer .= "
			<tr>
				<td nowrap>".$r['billkeydate']."</td>
				<td nowrap>#${r['id']}</td>
				<td nowrap>".$bk_count." ".__("claim(s).")."</td>
				<td align=\"right\">";

			// Loop through procedures
			$buffer .= "<select name=\"billkeydetail_".$r['id']."\" multiple size=\"5\" style=\"width: 300px;\">\n";
			foreach ($bk['procedures'] AS $_g => $proc) {
				if ($proc[0] > 0) {
					$_q = "SELECT proc.procdt AS procdt, CONCAT(pt.ptlname, ".
					"', ', pt.ptfname, ' ', pt.ptmname) AS patient_name, ".
					"pt.id AS patient_id ".
					"FROM procrec AS proc, patient AS pt ".
					"WHERE proc.procpatient=pt.id AND ".
					"proc.id='".addslashes($proc[0])."'";
					$_r = $GLOBALS['sql']->query($_q);
					$this_proc = $GLOBALS['sql']->fetch_array($_r);
					$buffer .= "<option>".$this_proc['procdt']." - ".
					prepare($this_proc['patient_name'])."</option>\n";
				}
			}
			$buffer .= "</select>\n";
			
			$buffer .= "
				</td><td>
				<a href=\"".page_name()."?type=".get_class($this).
				"&billing_action=rebill&key=".urlencode($r['id']).
				"&action=".$_REQUEST['action']."\" class=\"button\">".
				__("Rebill")."</a>
				</td><td><a href=\"".page_name()."?".
				"module=".urlencode($_REQUEST['module'])."&".
				"action=".urlencode($_REQUEST['action'])."&".
				"type=".urlencode($_REQUEST['type'])."&".
				"billing_action=mark&".
				"keys=".urlencode(serialize(array($r['id']))).
				"\" class=\"button\">".__("Mark as Billed")."</a>
				</td>
			</tr>
			";
		}
		$buffer .= "</table>";
		return $buffer;
	} // end method rebill_menu

	function reports ( ) {
		$buffer = '';

		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		if (!$remitt->GetServerStatus()) {
			trigger_error(__("The REMITT Server is not running. Please start it and try again."), E_USER_ERROR);
		}
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	
		$reports = $remitt->ListOutputMonths();
		krsort($reports);

		$buffer .= "<div class=\"section\">".__("Remitt Results and Logs")."</div>\n";
		//print "<br/<hr/>reports = "; print_r($reports); print "<hr/>\n";
		include_once(freemed::template_file('ajax.php'));
		$buffer .= "<table border=\"0\" cellspacing=\"0\" ".
			"width=\"75%\" cellpadding=\"2\">\n".
			"<tr>\n".
			"<td class=\"DataHead\">".__("Results")."</td>\n".
			"<td class=\"DataHead\">".__("Logs")."</td>\n".
			"</tr>\n".
			"<tr><td valign=\"top\">\n";
		$buffer .= "<table border=\"0\" cellspacing=\"0\" ".
			"width=\"75%\" cellpadding=\"2\">\n";
		foreach ($reports AS $report_month => $report_count) {
			$s_report_month = str_replace('-', '_', $report_month);
			$t_report_month = explode('-', $report_month);
			$p_report_month = date('M Y', mktime(0,0,0,$t_report_month[1], 1, $t_report_month[0]));
			$buffer .= "<tr class=\"cell_alt\">\n".
				"<td><b>\n".
				ajax_expand_module_html(
					'content_'.$s_report_month,
					get_class($this),
					'ajax_get_month_reports',
					$report_month
				).
				"</b> ".prepare($p_report_month)."</td>\n".
				"<td>".$report_count." report(s)</td></tr>\n";
			// Hidden cell for output
			$buffer .= "<tr><td colspan=\"2\">\n".
				"<div id=\"content_".$s_report_month."\">".
				"</div>\n".
				"</td></tr>\n";
		}
		$buffer .= "</table>\n";
		$buffer .= "</td><td valign=\"top\">\n";
/*
		// Get log years
		$years = $remitt->_call('Remitt.Interface.FileList');
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
				$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
				$remitt->Login(
					freemed::config_value('remitt_user'),
					freemed::config_value('remitt_pass')
				);	
				$logs = $remitt->_call('Remitt.Interface.FileList', array(
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
*/
		$buffer .= "</td></tr></table>\n";
		return $buffer;
	} // end method reports

	function display_report ( ) {
		$buffer = '';

		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	
		switch ($_REQUEST['file_type']) {
			case 'report':
			$param = array (
				CreateObject('PHP.xmlrpcval', 'output', 'string'),
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
		$report = $remitt->_call('Remitt.Interface.GetFile', $param, false);

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
	} // end method display_report

	function ajax_show_patient_claims ( $patient ) {
		$these_claims = $this->ProceduresToBill($patient);
		//print "these claims = "; print_r($these_claims); print "<br/>\n";
		// If expanding, display all procedures
		$buffer .= "
		<table width=\"80%\" cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
		<tbody>
		";

		// Loop through claims
		global $coverage;
		foreach ($these_claims AS $__garbage => $c) {
			$coverages = $this->PatientCoverages($c, &$curcov);
			$coverage[$c] = $curcov;
			$__temp = CreateObject('FreeMED.Coverage', $coverage[$c]);
			$media[$c] = $__temp->covinsco->local_record['inscodefoutput'];
			unset($__temp);

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
			<a href=\"module_loader.php?module=proceduremodule&patient=".urlencode($patient)."&action=modform&id=".urlencode($c)."\"
			>".__("DOS:")." ".$_record['procdt']."</a>
			</td>

			<td class=\"Data\" colspan=\"2\">
			<select name=\"media[".$c."]\">
			";
			// Format selection widget from Remitt server
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

		return $buffer;
	} // end method ajax_show_patient_claims

	function ajax_get_month_reports ( $monthhash ) {
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);
		$reports = $remitt->GetFileList('output', 'month', $monthhash);

		$buffer .= "<table border=\"0\" width=\"100%\">\n".
			"<tr class=\"DataHead\">\n".
			"<td>".__("Report")."</td>\n".
			"<td>".__("Size")."</td>\n".
			"<td>".__("Generated")."</td>\n".
			"<td>".__("Time")."</td>\n".
			"<td>&nbsp;</td>\n".
			"<td>".__("View")."</td>\n".
			"</tr>\n";
		krsort($reports);
		foreach ($reports AS $report => $v) {
			$buffer .= "<tr>\n".
				"<td>".$report."</td>".
				"<td>".$v['filesize']."</td>".
				"<td>".$v['generated']."</td>".
				"<td>".sprintf('%0.2d',$v['time'])."s</td>".
				"<td>".$v['format']."/".$v['transport']."</td>".
				"<td><a href=\"".$this->page_name."?".
				"module=".get_class($this)."&".
				"type=".get_class($this)."&".
				"action=transport&".
				"billing_action=display_report&".
				"file_type=report&".
				"report=".urlencode($report)."\" ".
				"target=\"_view\">".__("View")."</a></td>".
				"</tr>\n";
		}
		$buffer .= "</table>\n<hr/>\n";
		return $buffer;
	} // end method ajax_get_month_reports

	function ajax_get_year_reports ( $year ) {
		include_once(freemed::template_file('ajax.php'));
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);
		$reports = $remitt->ListMonths( $year );

		$buffer .= "<table border=\"0\" cellspacing=\"0\" ".
			"width=\"75%\" cellpadding=\"2\">\n";
		foreach ($reports AS $report_month => $report_count) {
			$s_report_month = str_replace('-', '_', $report_month);
			$buffer .= "<tr class=\"cell_alt\">\n".
				"<td><b>\n".
				ajax_expand_module_html(
					'content_'.$s_report_month,
					get_class($this),
					'ajax_get_month_reports',
					$report_month,
					false
				).
				"</b> ".prepare($report_month)."</td>\n".
				"<td>".sprintf(__("%s reports(s)"), $report_count)."</td></tr>\n";
			// Hidden cell for output
			$buffer .= "<tr><td colspan=\"2\">\n".
				"<div id=\"content_".$s_report_month."\">".
				"</div>\n".
				"</td></tr>\n";
		}
		$buffer .= "</table>\n";
		return $buffer;
	} // end method ajax_get_year_reports

	function statement ( ) {
		$buffer .= __("Submitting data to Remitt server")." ... <br/>\n"; 		
		// Create new Remitt instance
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	

		// Create new ClaimLog instance
		$claimlog = CreateObject ('FreeMED.ClaimLog');

		// Create Bill Key
		extract($_REQUEST);

		// Get all claims in the system
		$q = "Select proc.id AS p ".
			"From patient AS pat, procrec AS proc ".
			"Where proc.procpatient = pat.id and ".
			"proc.procbalcurrent > 0 and proc.proccurcovtp = '0'";
		$res = $GLOBALS['sql']->query($q);
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$procs[] = $r['p'];
		} // end fetch array

		$result = $remitt->ProcessStatement( $procs );

		$buffer .= "<div class=\"section\">".
			__("Remitt Billing Sent")."</div><br/>\n";

		// Refresh to status screen
		global $refresh;
		$refresh = page_name()."?".
			"module=".$_REQUEST['module']."&".
			"type=".$_REQUEST['type']."&".
			"action=".$_REQUEST['action']."&".
			"billing_action=status&".
			"uniques=".urlencode(serialize(array(0 => $result)));

		$buffer .= __("Refreshing")." ... ";

		//print "DEBUG: "; print_r($result); print "<br/>\n";
		//$buffer .= "Should have returned $result.<br/>\n";
		// Add to claimlog
		$result = $claimlog->log_billing (
			$this_billkey,
			'statement', //$my_format,
			'PDF', //$my_target,
			__("Patient statement generated")
		);
		return $buffer;
	} // end method statement

	function process ( $single = NULL ) {
		$buffer .= __("Submitting data to Remitt server")." ... <br/>\n"; 		
		// Create new Remitt instance
		$remitt = CreateObject('FreeMED.Remitt', freemed::config_value('remitt_server'));
		$remitt->Login(
			freemed::config_value('remitt_user'),
			freemed::config_value('remitt_pass')
		);	

		// Create new ClaimLog instance
		$claimlog = CreateObject ('FreeMED.ClaimLog');

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
			"uniques=".urlencode(serialize(array(0 => $result)));

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
