<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class MultipleProcedureModule extends EMRModule {

	var $MODULE_NAME = "Multiple Procedures";
	var $MODULE_AUTHOR = "nelson@crynwr.com";
				//from procedures by jeff b
				//from multiple_procedures by Robert Meyer
				//but any bugs are mine.
	var $MODULE_VERSION = "0.1.0";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name  = "procrec";
	var $record_name = "Procedure";
	var $patient_field = "procpatient";
	var $proc_fields = array(
		"procpatient",
		"proceoc",
		"proccpt",
		"proccptmod",
		"proccptmod2",
		"proccptmod3",
		"procdiag1",
		"procdiag2",
		"procdiag3",
		"procdiag4",
		"proccharges",      
		"procunits",
		"procvoucher",
		"procphysician",
		"procdt",		
		"procpos",
		"proccomment",
		"procbalorig",
		"procbalcurrent",	
		"procamtpaid",	
		"procbilled",
		"procbillable",
		"procauth",
		"proccert",
		"procrefdoc",
		"procrefdt",			
		"proccurcovid",     
		"proccurcovtp",    
		"proccov1",       
		"proccov2",      
		"proccov3",     
		"proccov4",
		"procclmtp",
		'procmedicaidref',
		'procmedicaidresub',
		'proclabcharges',
		'procslidingscale',
		'proctosoverride'
	);    

	function MultipleProcedureModule () {
		// Set vars for patient management
		$this->summary_vars = array (
			__("Date")    => "procdt",
			__("CPT")     => "proccpt:cpt:cptcode",
			__("Comment") => "proccomment",
			__("Charges") => "_charges"
		);
		$this->summary_query = array (
			"ROUND(procbalorig,2) AS _charges"
		);

		$this->_SetMetaInformation('RuleInterface', "RuleInterface");

		$this->acl = array ( 'emr', 'bill' );

		// Call parent constructor
		$this->EMRModule();
	} // end constructor ProcedureModule

	function addform() {
		global $display_buffer;

		foreach ($GLOBALS AS $k => $v) global ${$k};

		if (!$been_here) {
			global $procunits, $procdiag1,$procdiag2,$procdiag3,$procdiag4,$procphysician,$procrefdoc,$proclabcharges,$procrefdt;
			global $been_here;

			for ($i=1; $i<=6; $i++) {
				$procunits[$i] = "1.0";        // default value for units
			}

			$this_patient = CreateObject('FreeMED.Patient', $patient);
			$procdiag1      = $this_patient->local_record[ptdiag1];
			$procdiag2      = $this_patient->local_record[ptdiag2];
			$procdiag3      = $this_patient->local_record[ptdiag3];
			$procdiag4      = $this_patient->local_record[ptdiag4];
			$procphysician = $this_patient->local_record[ptdoc];
			$procrefdoc = $this_patient->local_record[ptrefdoc];
			$proclabcharges = '0.00';

			// Pull the date of last visit from last performed proc
			$procrefdt = $this_patient->date_of_last_procedure();
			
			$been_here = 1;
		}

		if (empty ($procdt)) $procdt = $cur_date; // show current date
		$icd_type = freemed::config_value("icd"); // '9' or '10'
		if ( (($icd_type+0) != 9) and (($icd_type+0) != 10) ) {
			// Default to 9 if nothing is selected.
			// TODO: Fix this broken behavior
			$icd_type = 9;
		}

		$cert_query = "SELECT id,certdesc FROM certifications WHERE certpatient='$patient'";
		$cert_result = $sql->query($cert_query);
		$clmtype_query = "SELECT id,clmtpname,clmtpdescrip FROM claimtypes";
		$clmtype_result = $sql->query($clmtype_query);


		// Determine if we have EOC support
		$__episode_of_care = $__episode_of_care_widget = '';
		if (check_module('EpisodeOfCare')) {
			$__episode_of_care = __("Episode of Care");
			$__episode_of_care_widget =
			module_function('EpisodeOfCare', 'widget',
				array('proceoc', $patient));
		}

		// ************** BUILD THE WIZARD ****************
		$wizard = CreateObject('PHP.wizard', array ("been_here", "action", 
			"patient", "id", "module", "return") );
		$wizard->set_cancel_name(__("Cancel"));
		$wizard->set_finish_name(__("Finish"));
		$wizard->set_previous_name(__("Previous"));
		$wizard->set_next_name(__("Next"));
		$wizard->set_refresh_name(__("Refresh"));
		$wizard->set_revise_name(__("Revise"));
		$wizard->set_width('100%');

		// Create grid
		$grid = "<table border=\"0\" cellspacing=\"0\">\n".
			"<tr>\n".
			"<td>".__("Procedural Code")."</td>\n".
			"<td>".__("Modifier")."</td>\n".
			"<td>".__("Units")."</td>\n".
			"</tr>\n";
		for ($i=1; $i<=6; $i++) {
			$grid .= "<tr>\n".
				"<td>".freemed_display_selectbox(
					$sql->query("SELECT * FROM cpt ORDER BY cptcode,cptnameint"),
					"#cptcode# (#cptnameint#)",
					"proccpt[".$i."]"
				)."</td>\n".
				"<td>".freemed_display_selectbox(
					$sql->query("SELECT cptmod,cptmoddescrip,id FROM cptmod ORDER BY cptmod,cptmoddescrip"),
					"#cptmod# (#cptmoddescrip#)",
					"proccptmod[".$i."]"
				)."</td>\n".
				"<td>".html_form::text_widget('procunits['.$i.']', 9)."</td>\n".
				"</tr>\n";
		}

		$wizard->add_page (__("Select Procedures"),
			array("proccpt", "proccptmod", "procunits"),
			$grid
		);

		$wizard->add_page (__("Step One"),
			array_merge(array("procphysician", "proceoc", "procrefdoc",
							  "procdiag1", "procdiag2", "procdiag3", "procdiag4",		
							  "procpos", "procvoucher","proccomment",
								"procauth","proccert","procclmtp"),
							  date_vars("procdt"),date_vars("procrefdt")),
		html_form::form_table ( array (
		  __("Provider") =>
			module_function('providermodule', 'widget', array ('procphysician')),
		  __("Date of Procedure") =>
			fm_date_entry ("procdt") . $wizard->generate_refresh(),
		  $__episode_of_care => $__episode_of_care_widget,
		  __("Diagnosis Code")." 1" =>
			module_function('icdmaintenance', 'widget', array ('procdiag1')),
		  __("Diagnosis Code")." 2" =>
			module_function('icdmaintenance', 'widget', array ('procdiag2')),
		  __("Diagnosis Code")." 3" =>
			module_function('icdmaintenance', 'widget', array ('procdiag3')),
		  __("Diagnosis Code")." 4" =>
			module_function('icdmaintenance', 'widget', array ('procdiag4')),
		  __("Place of Service") =>
			module_function('facilitymodule', 'widget', array ('procpos')),
		  __("Voucher Number") =>
		  	html_form::text_widget('procvoucher', 20),
		  __("Authorization") =>
		  	module_function('authorizationsmodule', 'widget', array('procauth', $patient, "( '".addslashes($procdt)."' >= authdtbegin AND '".addslashes($procdt)."' <= authdtend AND authvisitsremain > 0)")),
		  __("Certifications") => freemed_display_selectbox($cert_result,"#certdesc#","proccert"),
		  __("Claim Type") => freemed_display_selectbox($clmtype_result,"#clmtpname# #clmtpdescrip#","procclmtp"),
		  __("Referring Provider") =>
			module_function('providermodule', 'widget', array ('procrefdoc')),
		  __("Date of Last Visit") =>
			fm_date_entry ("procrefdt"),
		  __("Comment") =>
		  	html_form::text_widget('proccomment', 30, 255)
		),
			// verify
			array(
					array ("procdiag1", VERIFY_NONZERO, NULL, __("Must have one diagnosis code")),
					array ("procphysician", VERIFY_NONZERO, NULL, __("Must Specify physician")),
					array ("procdt_y", VERIFY_NONZERO, NULL, __("Must Specify Proc Year")),
					array ("procdt_m", VERIFY_NONZERO, NULL, __("Must Specify proc Month")),
					array ("procdt_d", VERIFY_NONZERO, NULL, __("Must Specify proc Day")),
					array ("procpos", VERIFY_NONZERO, NULL, __("Must Specify Place of Service")),
					array ("procclmtp", VERIFY_NONZERO, NULL, __("Must Specify Type of Claim")),
					array ("proccpt", VERIFY_NONZERO, NULL, __("Must Specify Procedural code"))
				 ) // end of array
			) // end of array_merge
		); // end of page one

		$prim_query = "SELECT a.id,b.insconame, ".
			"CASE a.covstatus ".
				"WHEN 2 THEN '(suspended)' ".
				"ELSE '' ".
			"END AS note ".
			"FROM coverage as a,insco as b WHERE ".
			"FIND_IN_SET(a.covstatus,'0,2') AND a.covtype='".PRIMARY."'".
			" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";
		$sec_query = "SELECT a.id,b.insconame, ".
			"CASE a.covstatus ".
				"WHEN 2 THEN '(suspended)' ".
				"ELSE '' ".
			"END AS note ".
			"FROM coverage as a,insco as b WHERE ".
			"FIND_IN_SET(a.covstatus,'0,2') AND a.covtype='".SECONDARY."'".
			" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";
		$tert_query = "SELECT a.id,b.insconame, ".
			"CASE a.covstatus ".
				"WHEN 2 THEN '(suspended)' ".
				"ELSE '' ".
			"END AS note ".
			"FROM coverage as a,insco as b WHERE ".
			"FIND_IN_SET(a.covstatus,'0,2') AND a.covtype='".TERTIARY."'".
			" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";
		$wc_query = "SELECT a.id,b.insconame, ".
			"CASE a.covstatus ".
				"WHEN 2 THEN '(suspended)' ".
				"ELSE '' ".
			"END AS note ".
			"FROM coverage as a,insco as b WHERE ".
			"FIND_IN_SET(a.covstatus,'0,2') AND a.covtype='".WORKCOMP."'".
			" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";

		$prim_result = $sql->query($prim_query);
		$sec_result  = $sql->query($sec_query);
		$tert_result = $sql->query($tert_query);
		$wc_result   = $sql->query($wc_query);

		// Sliding scale
		$slidingfeescale = CreateObject('FreeMED.SlidingFeeScale', $_REQUEST['patient']);

		$wizard->add_page (__("Step Two: Select Coverage"),
			array("proccurcovid","proccurcovtp","proccov1","proccov2","proccov3","proccov4"),
			html_form::form_table(array (
				__("Primary Coverage") =>  freemed_display_selectbox($prim_result,"#insconame# #note#","proccov1"),
				__("Secondary Coverage") =>  freemed_display_selectbox($sec_result,"#insconame# #note#","proccov2"),
				__("Tertiary Coverage") =>  freemed_display_selectbox($tert_result,"#insconame# #note#","proccov3"),
				__("Work Comp Coverage") =>  freemed_display_selectbox($tert_result,"#insconame# #note#","proccov4"),
				( (freemed::config_value('sliding_fee') and $slidingfeescale->PatientBracket()) ? __("Sliding Fee Scale") : '' ) => html_form::checkbox_widget('procslidingscale', $slidingfeescale->PatientBracket())
				))
			); // end coverage page	

		// foreach ...
		$confirmgrid = "<table border=\"0\" cellspacing=\"0\" width=\"100%\">\n".
			"<tr>\n".
			"<td class=\"heading\">".__("Procedural Code")."</td>\n".
			"<td>".__("Units")."</td>\n".
			"<td>".__("Calculated Accepted Fee")."</td>\n".
			"<td>".__("Calculated Charge")."</td>\n".
			"</tr>\n".
			"<input type=\"HIDDEN\" NAME=\"procbalorig[0]\" value=\"0\" />\n";
		$primary = CreateObject('FreeMED.Coverage', $covid);
		$insid = $primary->local_record[covinsco];
		for ($i=1; $i<=6; $i++) {
			global $proccpt;
			if ($proccpt[$i]) {
				$cpt_code[$i] = freemed::get_link_rec ($proccpt[$i], "cpt"); // cpt code
				$cpt_code_fees[$i] = fm_split_into_array ($cpt_code[$i]["cptstdfee"]);
				$cpt_code_stdfee[$i] = $cpt_code_fees[$i][$insid]; // grab proper std fee
				$charge[$i] = $this->CalculateCharge($proccov1,$procunits[$i], $proccpt[$i],$procphysician,$patient);
				global $proccharges; $proccharges = $charge[$i];

				// Provide transformation by passing globals
				$transformation = module_function('RulesModule', 'interpreter', array ( get_class($this), $GLOBALS ));
				// Then reglobalize
				foreach ($transformation AS $k => $v) { global ${$k}; ${$k} = $v; }
				$charge = $proccharges;

				// Adjust by sliding scale if present
				if ($_REQUEST['procslidingscale']) {
					$charge *= $slidingfeescale->BracketToMultiplier($_REQUEST['procslidingscale']);
				}
				$cpt_code[$i] = freemed::get_link_rec ($proccpt[$i], "cpt"); // cpt code
				// Interface
				$confirmgrid .= "<tr>\n".
					"<td>".prepare($cpt_code[$i]["cptcode"])."</td>\n".
					"<td>".prepare($procunits[$i])."</td>\n".
					"<td>".$cpt_code_stdfee[$i]."</td>\n".
					"<td><input TYPE=\"TEXT\" NAME=\"procbalorig[".$i."]\" SIZE=\"10\" ".
						"MAXLENGTH=\"9\" VALUE=\"".prepare($charge[$i])."\"/></td>\n".
					"</tr>\n";
			}
		} // end for
		$confirmgrid .= "</table><br/>\n";



		$wizard->add_page (__("Step Three: Confirm"),
		array ("proccomment", "procbalorig", "procbillable"),
				   $confirmgrid,
		html_form::form_table ( array (
		 __("Insurance Billable?") =>
		   "<select NAME=\"procbillable\">
			<option VALUE=\"0\" ".
			 ( ($procbillable == 0) ? "SELECTED" : "" ).">".__("Yes")."</option>
			<option VALUE=\"1\" ".
			 ( ($procbillable != 0) ? "SELECTED" : "" ).">".__("No")."</option>
		   </select>\n",

		 __("Comment") =>
		   prepare($proccomment)
		) )
		);

		// required to get the wizard to validate the previous (last) page
		$wizard->add_page(__("Miscellaneous"),
			array('proclabcharges',
				'procmedicaidresub', 'procmedicaidref'),
			html_form::form_table(array(
				__("Outside Lab Charges") =>
				html_form::text_widget(
					'proclabcharges'
				),

				__("Medicaid Original Reference") =>
				html_form::text_widget(
					'procmedicaidref'
				),

				__("Medicaid Resubmission Code") =>
				html_form::text_widget(
					'procmedicaidresub'
				)
			))
		);

		if (!$wizard->is_done() and !$wizard->is_cancelled()) 
		{
			// display the wizard
			$display_buffer .= "<CENTER>".$wizard->display()."</CENTER>\n";
		}

		if ($wizard->is_done())
		{
			foreach ($this->proc_fields AS $k => $v) {
				global ${$k};
			}

			$proccurcovtp = ( ($proccov4) ? WORKCOMP : 0 );
			$proccurcovtp = ( ($proccov3) ? TERTIARY : 0 );
			$proccurcovtp = ( ($proccov2) ? SECONDARY : 0 );
			$proccurcovtp = ( ($proccov1) ? PRIMARY : 0 );
			$proccurcovid = ( ($proccov4) ? $proccov4 : 0 );
			$proccurcovid = ( ($proccov3) ? $proccov3 : 0 );
			$proccurcovid = ( ($proccov2) ? $proccov2 : 0 );
			$proccurcovid = ( ($proccov1) ? $proccov1 : 0 );

			for ($i=1; $i<=6; $i++) {
				if ($proccpt[$i]) {
				  $display_buffer .= "<P><CENTER>".__("Adding")." ... ";

				  $query = $sql->insert_query 
				    (
				     $this->table_name,
				     array (
					    "procpatient"   =>  $patient,
					    "proceoc",
					    "proccpt"	=>  $proccpt[$i],
					    "proccptmod"	=>  $proccptmod[$i],
					    "proccptmod2",
					    "proccptmod3",
					    "procdiag1",
					    "procdiag2",
					    "procdiag3",
					    "procdiag4",
					    "proccharges"       =>  $procbalorig[$i],
					    "procunits"         =>	$procunits[$i],
					    "procvoucher",
					    "procphysician",
					    "procdt"            =>  fm_date_assemble("procdt"),
					    "procpos",
					    "proccomment",
					    "procbalorig"    =>  $procbalorig[$i],
					    "procbalcurrent"    =>  $procbalorig[$i],
					    "procamtpaid"       =>  "0",
					    "procbilled"        =>  "0",
					    "procbillable",
					    "procauth",
					    "proccert",
					    "procrefdoc",
					    "procslidingscale",
					    "procrefdt"         =>  fm_date_assemble("procrefdt"),
					    "proccurcovid"        =>  $proccurcovid,
					    "proccurcovtp"        =>  $proccurcovtp,
					    "proccov1"        =>  $proccov1,
					    "proccov2"        =>  $proccov2,
					    "proccov3"        =>  $proccov3,
					    "proccov4"        =>  $proccov4,
					    "procclmtp"        =>  $procclmtp,
					    'proctosoverride' => $proctosoverride+0
					    )
				     );
				
				  $result = $sql->query ($query);
				  if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				  if ($result) { $display_buffer .= __("done")."."; }
				  else        { $display_buffer .= __("ERROR");    }
				  
				  $this_procedure = $sql->last_record ($result);

				  // Add to Claimlog
				  $claimlog = CreateObject('_FreeMED.ClaimLog');
				  $claimlog->log_event(
						       $this_procedure,
						       array (
							      'action' => __("Create"),
							      'comment' => __("Procedure created")
							      )
						       );
				  
				  // form add query
				  $display_buffer .= "
				<br/>
				".__("Committing to ledger")." ... 
				";
				  $query = $sql->insert_query(
							      'payrec',
							      array(
								    'payrecdtadd' => date('Y-m-d'),
								    'payrecdtmod' => '0000-00-00',
								    'payrecpatient' => $patient,
								    'payrecdt' => fm_date_assemble("procdt"),
								    'payreccat' => PROCEDURE,
								    'payrecproc' => $this_procedure,
								    'payrecsource' => $proccurcovtp,
								    'payreclink' => $proccurcovid,
								    'payrectype' => '0',
								    'payrecnum' => '',
								    'payrecamt' => $procbalorig[$i],
								    'payrecdescrip' => $proccomment,
								    'payreclock' => 'unlocked'
								    )
							      );
				  $result = $sql->query ($query);
				  if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				  if ($result) { $display_buffer .= __("done")."."; }
				  else        { $display_buffer .= __("ERROR");    }
				  $this_procedure = $sql->last_record ($result, $this->table_name);
				  
				} // end if
			} // end for

				  // updating patient diagnoses
				  $display_buffer .= "
				<br/>
				".__("Updating patient diagnoses")." ...  ";
				  $query = $sql->update_query(
							      'patient',
							      array(
								    'ptdiag1' => $procdiag1,
								    'ptdiag2' => $procdiag2,
								    'ptdiag3' => $procdiag3,
								    'ptdiag4' => $procdiag4
								    ), array ('id' => $patient)
							      );
				  $result = $sql->query ($query);
				  if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				  if ($result) { $display_buffer .= __("done")."."; }
				  else        { $display_buffer .= __("ERROR");    }
				  $display_buffer .= "<br/>\n";
				
				  // Deduct from authorization, if there is one
				  // specified
				  if ($_REQUEST['procauth'] > 0) {
				    $a = CreateObject('FreeMED.Authorizations');
				    // Check for valid first
				    if ($a->valid($_REQUEST['procauth'], fm_date_assemble('procdt'))) {
				      if ($a->use_authorization($_REQUEST['procauth'])) {
					$display_buffer .= __("Updated authorization").
					  "<br/>\n";
				      } else {
					$display_buffer .= __("Failed to modify authorization.").
					  "<br/>\n";
				      } // end checking if use auth success
				    } else {
				      // If not valid, display error
				      $display_buffer .= __("Invalid authorization").
					"<br/>\n";
				    } // end checking for valid
				  } // end checking for use auth

				$display_buffer .= "
				</div>
				<p/>
				<div align=\"CENTER\">
				".template::link_bar(array(
					__("Manage Patient") =>
					"manage.php?id=".urlencode($patient),

					__("Add Payment") =>
				 	$this->page_name."?module=PaymentModule&action=addform&patient=".urlencode($patient),

					__("Add Another") =>
				$this->page_name."?module=".urlencode($module).
				"&action=addform".
				  "&procvoucher=".urlencode($_REQUEST['procvoucher']).
				  "&patient=".urlencode($patient).
				  "&procdt=".fm_date_assemble("procdt").
				  "&proccpt=".urlencode($_REQUEST['proccpt']).
				  "&procpos=".urlencode($_REQUEST['procpos']).
				  "&procdiag1=".urlencode($_REQUEST['procdiag1']).
				  "&procdiag2=".urlencode($_REQUEST['procdiag2']).
				  "&procdiag3=".urlencode($_REQUEST['procdiag3']).
				  "&procdiag4=".urlencode($_REQUEST['procdiag4']).
				  "&procauth=".urlencode($_REQUEST['procauth']).
				  "&procrefdoc=".urlencode($_REQUEST['procrefdoc']).
				  "&procphysician=".urlencode($_REQUEST['procphysician'])
				))."
				</div>
				<p/>
				";

			global $refresh;
			if ($GLOBALS['return'] == 'manage') {
			//	$refresh = 'manage.php?id='.urlencode($patient);
			}
		
		} // end wizard done

		if ($wizard->is_cancelled())
		{
			$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\"><b>".__("Cancelled")."</b></div>
			<p/>
			<div ALIGN=\"CENTER\">
			 <a HREF=\"manage.php?id=$patient\"
			 >".__("Manage Patient")."</a> 
			</div>
			";

			global $refresh;
			if ($GLOBALS['return'] == 'manage') {
				$refresh = 'manage.php?id='.urlencode($patient);
			}
		
		} // end cancelled

	} // end addform

	function modform() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		global $been_here;
		if (!$been_here)
		{
			while(list($k,$v)=each($this->proc_fields)) {
				global ${$v};
			}
			$this_data = freemed::get_link_rec ($id, $this->table_name);
			extract ($this_data); // extract all of this data

			$been_here = 1;
		}

		$auth_r_buffer = $this->GetAuthorizations($patient);

		$cert_query = "SELECT id,certdesc FROM certifications WHERE certpatient='$patient'";
		$cert_result = $sql->query($cert_query);
		$clmtype_query = "SELECT id,clmtpname,clmtpdescrip FROM claimtypes";
		$clmtype_result = $sql->query($clmtype_query);

		foreach ($GLOBALS AS $k => $v) global ${$k};

		if (empty ($procdt)) $procdt = $cur_date; // show current date
		$icd_type = freemed::config_value("icd"); // '9' or '10'
		if ( (($icd_type+0) != 9) and (($icd_type+0) != 10) ) {
			// Default to 9 if nothing is selected.
			// TODO: Fix this broken behavior
			$icd_type = 9;
		}
		$icd_query = "SELECT * FROM icd9 ORDER BY icd$icd_type"."code";
		$icd_result = $sql->query($icd_query);

		$cert_query = "SELECT id,certdesc FROM certifications WHERE certpatient='$patient'";
		$cert_result = $sql->query($cert_query);
		$clmtype_query = "SELECT id,clmtpname,clmtpdescrip FROM claimtypes";
		$clmtype_result = $sql->query($clmtype_query);


		$auth_r_buffer = $this->GetAuthorizations($patient);

		// Determine if we have EOC support
		$__episode_of_care = $__episode_of_care_widget = '';
		if (check_module('EpisodeOfCare')) {
			$__episode_of_care = __("Episode of Care");
			$__episode_of_care_widget =
			module_function('EpisodeOfCare', 'widget',
				array('proceoc', $patient));
		}

		// ************** BUILD THE WIZARD ****************
		$wizard = CreateObject('PHP.wizard', array ("been_here", "action", 
			"patient", "id", "module", "return") );
		$wizard->set_cancel_name(__("Cancel"));
		$wizard->set_finish_name(__("Finish"));
		$wizard->set_previous_name(__("Previous"));
		$wizard->set_next_name(__("Next"));
		$wizard->set_refresh_name(__("Refresh"));
		$wizard->set_revise_name(__("Revise"));

		$wizard->add_page (__("Step One"),
			array_merge(array("procphysician", "proceoc", "procrefdoc",
							  "proccpt", "proccptmod", "proccptmod2", "proccptmod3", "procunits", 
							  "procdiag1", "procdiag2", "procdiag3", "procdiag4",		
							  "procpos", "procvoucher","proccomment",
								"procauth","proccert","procclmtp"),
							  date_vars("procdt"),date_vars("procrefdt")),
		html_form::form_table ( array (
		  __("Provider") =>
			module_function('providermodule', 'widget', array ('procphysician')),
		  __("Date of Procedure") =>
			fm_date_entry ("procdt"),
		  $__episode_of_care => $__episode_of_care_widget,
		  __("Procedural Code") =>
			module_function('cptmaintenance', 'widget', array ('proccpt')),
		  __("Modifier") =>
			module_function('cptmodifiersmaintenance', 'widget', array ('proccptmod')),
		  __("Modifier 2") =>
			module_function('cptmodifiersmaintenance', 'widget', array ('proccptmod2')),
		  __("Modifier 3") =>
			module_function('cptmodifiersmaintenance', 'widget', array ('proccptmod3')),
		  __("Units") =>
		  	html_form::text_widget('procunits', 9),
		  __("Diagnosis Code")." 1" =>
			module_function('icdmaintenance', 'widget', array ('procdiag1')),
		  __("Diagnosis Code")." 2" =>
			module_function('icdmaintenance', 'widget', array ('procdiag2')),
		  __("Diagnosis Code")." 3" =>
			module_function('icdmaintenance', 'widget', array ('procdiag3')),
		  __("Diagnosis Code")." 4" =>
			module_function('icdmaintenance', 'widget', array ('procdiag4')),
		  __("Place of Service") =>
			module_function('facilitymodule', 'widget', array ('procpos')),
		  __("Voucher Number") =>
		  	html_form::text_widget('procvoucher', 20),
		  __("Authorization") =>
			"<select NAME=\"procauth\">\n".
			"<option VALUE=\"0\" ".
			( ($procauth==0) ? "SELECTED" : "" ).">".
			__("NONE SELECTED")."</option>\n".
			$auth_r_buffer.
			"</select>\n",
		  __("Certifications") => freemed_display_selectbox($cert_result,"#certdesc#","proccert"),
		  __("Claim Type") => freemed_display_selectbox($clmtype_result,"#clmtpname# #clmtpdescrip#","procclmtp"),
		  __("Referring Provider") =>
			module_function('providermodule', 'widget', array ('procrefdoc')),
		  __("Date of Last Visit") =>
			fm_date_entry ("procrefdt"),
		  __("Comment") =>
		  	html_form::text_widget('proccomment', 30, 255)
		),
			// verify
			array(
					array ("procdiag1", VERIFY_NONZERO, NULL, __("Must have one diagnosis code")),
					array ("procphysician", VERIFY_NONZERO, NULL, __("Must Specify physician")),
					array ("procdt_y", VERIFY_NONZERO, NULL, __("Must Specify Proc Year")),
					array ("procdt_m", VERIFY_NONZERO, NULL, __("Must Specify proc Month")),
					array ("procdt_d", VERIFY_NONZERO, NULL, __("Must Specify proc Day")),
					array ("procpos", VERIFY_NONZERO, NULL, __("Must Specify Place of Service")),
					array ("procclmtp", VERIFY_NONZERO, NULL, __("Must Specify Type of Claim")),
					array ("proccpt", VERIFY_NONZERO, NULL, __("Must Specify Procedural code"))
				 ) // end of array
			) // end of array_merge
		); // end of page one

		$prim_query = "SELECT a.id,b.insconame FROM coverage as a,insco as b WHERE ".
						"a.covstatus='".ACTIVE."' AND a.covtype='".PRIMARY."'".
						" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";
		$sec_query = "SELECT a.id,b.insconame FROM coverage as a,insco as b WHERE ".
						"a.covstatus='".ACTIVE."' AND a.covtype='".SECONDARY."'".
						" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";
		$tert_query = "SELECT a.id,b.insconame FROM coverage as a,insco as b WHERE ".
						"a.covstatus='".ACTIVE."' AND a.covtype='".TERTIARY."'".
						" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";
		$wc_query = "SELECT a.id,b.insconame FROM coverage as a,insco as b WHERE ".
						"a.covstatus='".ACTIVE."' AND a.covtype='".WORKCOMP."'".
						" AND covpatient='".prepare($patient)."' AND a.covinsco=b.id";

		$prim_result = $sql->query($prim_query);
		$sec_result  = $sql->query($sec_query);
		$tert_result = $sql->query($tert_query);
		$wc_result   = $sql->query($wc_query);

		$wizard->add_page (__("Step Two: Select Coverage"),
			array("proccurcovid","proccurcovtp","proccov1","proccov2","proccov3","proccov4"),
			html_form::form_table(array (
				__("Primary Coverage") =>  freemed_display_selectbox($prim_result,"#insconame#","proccov1"),
				__("Secondary Coverage") =>  freemed_display_selectbox($sec_result,"#insconame#","proccov2"),
				__("Tertiary Coverage") =>  freemed_display_selectbox($tert_result,"#insconame#","proccov3"),
				__("Work Comp Coverage") =>  freemed_display_selectbox($tert_result,"#insconame#","proccov4")
				))
			); // end coverage page	

		$charge = $this->CalculateCharge($proccov1,$procunits,$proccpt,$procphysician,$patient);
		$cpt_code = freemed::get_link_rec ($proccpt, "cpt"); // cpt code


		$wizard->add_page (__("Step Three: Confirm"),
		array ("proccomment","procunits", "procbalorig", "procbillable"),
		html_form::form_table ( array (

		 __("Procedural Code") =>
		   prepare($cpt_code["cptcode"]),

		 __("Units") =>
		   prepare($procunits),

		 __("Calculated Accepted Fee") =>
		   $cpt_code_stdfee,

		 __("Calculated Charge") =>
		   "<input TYPE=\"TEXT\" NAME=\"procbalorig\" SIZE=\"10\" ".
		   "MAXLENGTH=\"9\" VALUE=\"".prepare($charge)."\"/>",

		 __("Insurance Billable?") =>
		   "<select NAME=\"procbillable\">
			<option VALUE=\"0\" ".
			 ( ($procbillable == 0) ? "SELECTED" : "" ).">".__("Yes")."</option>
			<option VALUE=\"1\" ".
			 ( ($procbillable != 0) ? "SELECTED" : "" ).">".__("No")."</option>
		   </select>\n",

		 __("Comment") =>
		   prepare($proccomment)
		) ),
		array (
			array ("procbalorig", VERIFY_NONNULL, NULL, __("Must Specify Amount"))
			)
		);

		// required to get the wizard to validate the previous (last) page
		//$wizard->add_page(__("Click Finish"),array("dummy"),"");

		if (!$wizard->is_done() and !$wizard->is_cancelled()) 
		{
			// display the wizard
			$display_buffer .= "<CENTER>".$wizard->display()."</CENTER>\n";
		}

		if ($wizard->is_done())
		{
			$proccurcovtp = ( ($proccov4) ? WORKCOMP : 0 );
			$proccurcovtp = ( ($proccov3) ? TERTIARY : 0 );
			$proccurcovtp = ( ($proccov2) ? SECONDARY : 0 );
			$proccurcovtp = ( ($proccov1) ? PRIMARY : 0 );
			$proccurcovid = ( ($proccov4) ? $proccov4 : 0 );
			$proccurcovid = ( ($proccov3) ? $proccov3 : 0 );
			$proccurcovid = ( ($proccov2) ? $proccov2 : 0 );
			$proccurcovid = ( ($proccov1) ? $proccov1 : 0 );

			$display_buffer .= "<P><CENTER>".__("Modifying")." ... ";

			// Save old record for authorization update
			$_p = freemed::get_link_rec($_REQUEST['id'], 'procrec');

			$query = $sql->update_query 
				(
					$this->table_name,
					array (
					"procpatient"   =>  $patient,
					"proceoc",
					"proccpt",
					"proccptmod",
					"proccptmod2",
					"proccptmod3",
					"procdiag1",
					"procdiag2",
					"procdiag3",
					"procdiag4",
					"proccharges"       =>  $procbalorig,
					"procunits",
					"procvoucher",
					"procphysician",
					"procdt"            =>  fm_date_assemble("procdt"),
					"procpos",
					"proccomment",
					"procbalorig",
					"procbalcurrent"    =>  $procbalorig,
					"procamtpaid"       =>  "0",
					"procbilled"        =>  "0",
					"procbillable",
					"procauth",
					"proccert",
					"procrefdoc",
					"procrefdt"         =>  fm_date_assemble("procrefdt"),
					"proccurcovid"        =>  $proccurcovid,
					"proccurcovtp"        =>  $proccurcovtp,
					"proccov1"        =>  $proccov1,
					"proccov2"        =>  $proccov2,
					"proccov3"        =>  $proccov3,
					"proccov4"        =>  $proccov4,
					"procclmtp"        =>  $procclmtp
					),
					array ('id' => $id)
				);

				$result = $sql->query ($query);
				if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				if ($result) { $display_buffer .= __("done")."."; }
				else        { $display_buffer .= __("ERROR");    }

				// form mod query
				$display_buffer .= "
				<br/>
				".__("Committing to ledger")." ... 
				";
				$query = $sql->update_query(
					'payrec',
					array(
						'payrecdtmod' => date('Y-m-d'),
						'payrecdt' => fm_date_assemble("procdt"),
						'payrecsource' => $proccurcovtp,
						'payreclink' => $proccurcovid,
						'payrectype' => '0',
						'payrecnum' => '',
						'payrecamt' => $procbalorig,
						'payrecdescrip' => $proccomment,
						'payreclock' => 'unlocked'
					),
					array (
						'payrecproc' => $id,
						'payreccat' => PROCEDURE,
						'payrectype' => '0'
					)
				);
				$result = $sql->query ($query);
				if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				if ($result) { $display_buffer .= __("done")."."; }
				else        { $display_buffer .= __("ERROR");    }

				// updating patient diagnoses
				$display_buffer .= "
				<BR>
				".__("Updating patient diagnoses")." ...  ";
				$query = $sql->update_query(
					'patient',
					array(
						'ptdiag1' => $procdiag1,
						'ptdiag2' => $procdiag2,
						'ptdiag3' => $procdiag3,
						'ptdiag4' => $procdiag4
					), array ('id' => $patient)
				);
				$result = $sql->query ($query);
				if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				if ($result) { $display_buffer .= __("done")."."; }
				else        { $display_buffer .= __("ERROR");    }
				$display_buffer .= "<br/>\n";

				// Check if authorization changed
				if ($_REQUEST['procauth'] != $_p['procauth']) {
					$a = CreateObject('FreeMED.Authorizations');
					// Try to remove old authorization
					if ($_p['procauth'] > 0) {
						$a->replace_authorization($_p['procauth']);
						$display_buffer .= __("Removed old authorization")."<br/>\n";
					}
					if ($_REQUEST['procauth'] > 0) {
						if ($a->valid($_REQUEST['procauth'], fm_date_assemble('procdt'))) {
							if ($a->use_authorization($_REQUEST['procauth'])) {
								$display_buffer .= __("Updated authorization").
									"<br/>\n";
							} else {
								$display_buffer .= __("Failed to modify authorization.").
									"<br/>\n";
							} // end checking if use auth success
						} else {
							// If not valid, display error
							$display_buffer .= __("Invalid authorization").
								"<br/>\n";
						} // end checking for valid
					} // end seeing if something should be added
				} // end checking for updated authorization


				$display_buffer .= "
				</div>
				<p/>
				<div align=\"CENTER\">
				".template::link_bar(array(
					__("Manage Patient") =>
					"manage.php?id=".urlencode($patient),

					__("Add Payment") =>
				 	$this->page_name."?module=PaymentModule&action=addform&patient=".urlencode($patient),

					__("Add Another") =>
				$this->page_name."?module=".urlencode($module).
				"&action=addform".
				  "&procvoucher=".urlencode($procvoucher).
				  "&patient=".urlencode($patient).
				  "&procdt=".fm_date_assemble("procdt").
				  "&proccpt=$proccpt".
				  "&procpos=$procpos".
				  "&procdiag1=$procdiag1".
				  "&procdiag2=$procdiag2".
				  "&procdiag3=$procdiag3".
				  "&procdiag4=$procdiag4".
				  "&procphysician=".urlencode($procphysician)
				))."
				</div>
				<p/>
				";

			global $refresh;
			if ($GLOBALS['return'] == 'manage') {
				$refresh = 'manage.php?id='.urlencode($patient);
			}
		
		} // end wizard done

		if ($wizard->is_cancelled())
		{
			// Unlock record, if it is locked
			$__lock = CreateObject('_FreeMED.RecordLock', $this->table_name);
			$__lock->UnlockRow ( $_REQUEST['id'] );

			$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\"><b>".__("Cancelled")."</b></div>
			<p/>
			<div ALIGN=\"CENTER\">
			 <a HREF=\"manage.php?id=$patient\"
			 >".__("Manage Patient")."</a> 
			</div>
			";

			global $refresh;
			if ($GLOBALS['return'] == 'manage') {
				$refresh = 'manage.php?id='.urlencode($patient);
			}
		
		} // end cancelled

	} // end modform
	
	function delete () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= "
		<p><div align=\"CENTER\">
		".__("Deleting")." ...
		";
		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= "[".__("Procedure")."] "; }
		else        { $display_buffer .= "[".__("ERROR")."] ";     }
		$query = "DELETE FROM payrec WHERE payrecproc='".addslashes($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= "[".__("Payment Record")."] "; }
		else        { $display_buffer .= "[".__("ERROR")."] ";          }
		$display_buffer .= "
		</div>
		<p/>
		".template::link_bar(array(
		 __("back") =>
		 $this->page_name."?module=$module&patient=$patient",
		 __("Manage Patient") =>
		 "manage.php?id=".urlencode($patient)
		))."
		</div>
		<p/>
		";
	} // end function MultipleProcedureModule->delete()

	function display() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;
		while(list($k,$v)=each($this->proc_fields))
		{
			global $$v;
		}
		$this_data = freemed::get_link_rec ($id, $this->table_name);
		extract ($this_data); // extract all of this data

		$phyname = freemed::get_link_field($procphysician,"physician","phylname");
		$refphyname = freemed::get_link_field($procrefdoc,"physician","phylname");

		$icd_type = freemed::config_value("icd"); // '9' or '10'
		$icd_code = "icd".$icd_type."code";
		$diag1 = freemed::get_link_field($procdiag1,"icd9",$icd_code);
		$diag2 = freemed::get_link_field($procdiag2,"icd9",$icd_code);
		$diag3 = freemed::get_link_field($procdiag3,"icd9",$icd_code);
		$diag4 = freemed::get_link_field($procdiag4,"icd9",$icd_code);
		$psrname = freemed::get_link_field($procpos,"pos","psrname");
		$authdtbeg = freemed::get_link_field($procauth,"authorizations","authdtbegin");
		$authdtend = freemed::get_link_field($procauth,"authorizations","authdtend");
		$authdt = $authdtbeg.$authdtend;
		$cov1ins = freemed::get_link_field($proccov1,"coverage","covinsco");
		$cov1name = freemed::get_link_field($cov1ins,"insco","insconame");
		$cov2ins = freemed::get_link_field($proccov2,"coverage","covinsco");
		$cov2name = freemed::get_link_field($cov2ins,"insco","insconame");
		$cov3ins = freemed::get_link_field($proccov3,"coverage","covinsco");
		$cov3name = freemed::get_link_field($cov3ins,"insco","insconame");
		$cov4ins = freemed::get_link_field($proccov4,"coverage","covinsco");
		$cov4name = freemed::get_link_field($cov4ins,"insco","insconame");
		$covins = freemed::get_link_field($proccurcovid,"coverage","covinsco");
		$covname = freemed::get_link_field($covins,"insco","insconame");

		$wizard = CreateObject('PHP.wizard', array ("been_here", "action", "patient", "id",
		"module") );
		$wizard->set_cancel_name(__("Cancel"));
		$wizard->set_finish_name(__("Finish"));
		$wizard->set_previous_name(__("Previous"));
		$wizard->set_next_name(__("Next"));
		$wizard->set_refresh_name(__("Refresh"));
		$wizard->set_revise_name(__("Revise"));

		// Determine if we have EOC support
		$__episode_of_care = $__episode_of_care_widget = '';
		if (check_module('EpisodeOfCare')) {
			$__episode_of_care = __("Episode of Care");
			$__episode_of_care_widget =
			module_function('EpisodeOfCare', 'widget',
				array('proceoc', $patient));
		}

		$wizard->add_page (__("Part One"),
			array_merge(array("phyname", "proceoc", "refphyname",
							  "procunits", 
							  "diag1", "diag2", "diag3", "diag4",		
							  "psrname", "procvoucher","proccomment",
								"psrname","covname","cov1name","cov2name","cov3name","cov4name"),
							  date_vars("procdt"),date_vars("procrefdt")),
		html_form::form_table ( array (
		  __("Provider") => prepare($phyname),
		  __("Date of Procedure") => prepare($procdt),
		  $__episode_of_care => $__episode_of_care_widget,
		  __("Units") => prepare($procunits), 
		  __("Diagnosis Code")." 1" => prepare($diag1),
		  __("Diagnosis Code")." 2" => prepare($diag2),
		  __("Diagnosis Code")." 3" => prepare($diag3),
		  __("Diagnosis Code")." 4" => prepare($diag4),
		  __("Place of Service") => prepare($psrname),
		  __("Voucher Number") => prepare($procvoucher),
		  __("Authorization") => prepare($authdt),
		  __("Referring Provider") => prepare($refphyname),
		  __("Date of Last Visit") => prepare($procrefdt),
		  __("Comment") => prepare($proccomment),
		  __("Current Coverage") => prepare($covname), 
		  __("Primary Coverage") => prepare($cov1name),
		  __("Secondary Coverage") => prepare($cov2name),
		  __("Tertiary Coverage") => prepare($cov3name), 
		  __("Work Comp Coverage") => prepare($cov4name)
		) )
		); // end of page one

		
		if (!$wizard->is_done() and !$wizard->is_cancelled()) 
		{
			// display the wizard
			$display_buffer .= "<div align=\"CENTER\">".$wizard->display()."</div>\n";
		} else {
			$display_buffer .= "
			<p/>
			<div align=\"CENTER\">
			 <a class=\"button\"
			 HREF=\"$this->page_name?module=$module&patient=$patient\"
			 >".__("back")."</a>
			</div>
			<p/>
			";
		}
	}

	function view ($condition = false) {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT * FROM ".$this->table_name." ".
				"WHERE procpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				( $condition ? 'AND '.$condition : '' )." ".
				"ORDER BY procdt DESC"
			),
			$this->page_name,
			array ( // control
			  __("Date of Procedure") => "procdt",
			  __("Procedure Code")	=> "proccpt",
			  __("Modifier") => "proccptmod",
			  __("Comment") => "proccomment"
			),
			array ( // blanks
			  "",
			  "",
			  "",
			  __("NO COMMENT")
			),
			array ( // xref
			  "",
			  "cpt"    => "cptcode",
			  "cptmod" => "cptmod",
			  ""
			)
		);
	} // end method view

	// Method: GetAuthorizations
	//
	//	Create authorizations select widget for the specified patient ID
	//
	// Parameters:
	//
	//	$patid - Patient ID number
	//
	// Returns:
	//
	//	HTML widget
	//
	function GetAuthorizations($patid) {
		global $display_buffer;
		global $sql;
		
		$auth_r_buffer = "";
		if ($patid == 0) return $auth_r_buffer;

		$auth_res = $sql->query ("SELECT * FROM authorizations ".
			"WHERE (authpatient='".addslashes($patid)."')");
		if ($auth_res > 0) { // begin if there are authorizations...
			while ($auth_r = $sql->fetch_array ($auth_res)) {
				$auth_r_buffer .= "
				 <option VALUE=\"".prepare($auth_r['id'])."\" ".
				 ( ($auth_r[id]==$procauth) ? "SELECTED" : "" )
				 .">".prepare($auth_r['authdtbegin'])." ".__("to")." ".
				 prepare($auth_r['authdtend'])." (".
				 prepare($auth_r['authvisitsremain'].' of '.$auth_r['authvisits'])." left)</option>\n";
			} // end while looping for authorizations
		} // end if there are authorizations

		return $auth_r_buffer;
	} // end method GetAuthorizations

	function CalculateCharge($covid,$procunits,$cptid,$phyid,$patid)  {
		global $display_buffer;

		syslog(LOG_INFO, "CalculateCharge ( $covid, $procunits, $cptid, $phyid, $patid )");

		// id of coverage record, cpt record, physician record
		// and patient record

		// charge calculation routine lies here
		//   charge = units * relative_value(cpt) * 
		//            base_value(physician/provider)
		//   standard_fee = standard_fee [insurance co] unless 0 then
		//                = default_standard_fee
		//  (we display "standard fee" as what the bastards (insurance companies)
		//   are actually going to pay -- be sure to check for divide by zeros...)

		// step one:
		//   calculate the standard fee
		//if ($covid==0)
		//		return 0;
		$primary = CreateObject('FreeMED.Coverage', $covid);
		$insid = $primary->local_record[covinsco];
		syslog(LOG_INFO, "CalculateCharge | insid = $insid");

		$cpt_code = freemed::get_link_rec ($cptid, "cpt"); // cpt code
		$cpt_code_fees = unserialize($cpt_code["cptstdfee"]);
		$cpt_code_stdfee = $cpt_code_fees[$insid]; // grab proper std fee
		if (empty($cpt_code_stdfee) or ($cpt_code_stdfee==0))
		$cpt_code_stdfee = $cpt_code["cptdefstdfee"]; // if none, do default
		$cpt_code_stdfee = bcadd ($cpt_code_stdfee, 0, 2);
		syslog(LOG_INFO, "CalculateCharge | cptcodestdfee = $cpt_code_stdfee");

		// step two:
		//   grab the relative value from the CPT db
		$relative_value = $cpt_code["cptrelval"];
		syslog(LOG_INFO, "CalculateCharge | relative value = $relative_value");
		if ($debug) $display_buffer .= " (relative_value = \"$relative_value\")\n";

		// step three:
		//   calculate the base value
		$internal_type  = $cpt_code ["cpttype"]; // grab internal type
		if ($debug) 
		$display_buffer .= " (inttype = $internal_type) (procphysician = $procphysician) ";
		$this_physician = freemed::get_link_rec ($physid, "physician");
		$charge_map     = fm_split_into_array($this_physician ["phychargemap"]);
		$base_value     = $charge_map [$internal_type];
		syslog(LOG_INFO, "CalculateCharge | base value = $base_value");
		if ($debug) $display_buffer .= "<BR>base value = \"$base_value\"\n";

		// step four:
		//   check for patient discount percentage
		$this_patient = CreateObject('FreeMED.Patient', $patid);
		$percentage = $this_patient->local_record["ptdisc"];
		if ($percentage>0) { $discount = $percentage / 100; }
		else              { $discount = 0;                 }
		if ($debug) $display_buffer .= "<BR>discount = \"$discount\"\n";

		// step five:
		//   calculate formula...
		$charge = ($base_value * $procunits * $relative_value) - $discount; 
		syslog(LOG_INFO, "CalculateCharge | procunits = $procunits, charge = $charge");
		if ($charge == 0)
		$charge = $cpt_code_stdfee * $procunits;
		if ($debug) $display_buffer .= " (charge = \"$charge\") \n";

		// step six:
		//   adjust values to proper precision
		$charge = bcadd ($charge, 0, 2);
		return $charge;
	} // end method CalculateCharge

	// Method: RuleInterface
	//
	//	Associated method to provide interface for billing rules
	//
	// Parameters:
	//
	//	$clause - 'if' or 'then'
	//
	// Returns:
	//
	//	Array with the following array type as each element:
	//	* [0] - field name
	//	* [1] - equivalence / assignment choices (array)
	//	* [2] - widget
	//
	function RuleInterface ( $type ) {
		switch ( $type ) {
			case 'if':
			$if[] = array (
				'procpos',
				array ( '=', '!=' ),
				module_function('FacilityModule', 'widget', array('procpos')),
				__("Facility")
			);
			$if[] = array (
				'proccpt',
				array ( '=', '!=' ),
				module_function('CptMaintenance', 'widget', array('proccpt')),
				__("CPT Code")
			);
			$if[] = array (
				'proccptmod',
				array ( '=', '!=' ),
				module_function('CptModifiersMaintenance', 'widget', array('proccptmod')),
				__("CPT Modifier")
			);
			$if[] = array (
				'proccptmod',
				array ( '=', '!=' ),
				module_function('CptModifiersMaintenance', 'widget', array('proccptmod2')),
				__("CPT Modifier")." 2"
			);
			$if[] = array (
				'proccptmod',
				array ( '=', '!=' ),
				module_function('CptModifiersMaintenance', 'widget', array('proccptmod3')),
				__("CPT Modifier")." 3"
			);
			return $if;
			break;

			case 'then':
			$then[] = array (
				'proccharges',
				array ( '=' ),
				html_form::text_widget('proccharges', 20),
				__("Charges")
			);
			$then[] = array (
				'proctosoverride',
				array ( '=' ),
				module_function('TypeOfServiceMaintenance', 'widget', array('proctosoverride')),
				__("Type of Service")
			);
			return $then;
			break;
		}
	} // end method RuleInterface

} // end class MultipleProcedureModule

register_module ("MultipleProcedureModule");

?>
