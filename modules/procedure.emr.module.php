<?php
 // $Id$
 // desc: procedure database module
 // lic : GPL, v2

if (!defined("__PROCEDURE_MODULE_PHP__")) {

define (__PROCEDURE_MODULE_PHP__, true);

class procedureModule extends freemedEMRModule {

	var $MODULE_NAME = "Procedures";
	var $MODULE_VERSION = "0.1";

	var $table_name  = "procrec";
	var $record_name = "Procedure";
	var $proc_fields = array(
		"procpatient",
		"proceoc",
		"proccpt",
		"proccptmod",
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
		"procclmtp"
	);    


	function procedureModule () {
		// call parent constructor
		$this->freemedEMRModule();

		// Set vars for patient management
		$this->summary_vars = array (
			_("Date")    => "procdt",
			_("Comment") => "proccomment"
		);
	} // end constructor procedureModule

	function addform() {
		global $display_buffer;

		reset ($GLOBALS);
		 while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here) {
			global $procunits, $procdiag1,$procdiag2,$procdiag3,$procdiag4,$procphysician,$procrefdoc;
			global $been_here;

			$procunits = "1.0";        // default value for units
			$this_patient = new Patient($patient);
			$procdiag1      = $this_patient->local_record[ptdiag1];
			$procdiag2      = $this_patient->local_record[ptdiag2];
			$procdiag3      = $this_patient->local_record[ptdiag3];
			$procdiag4      = $this_patient->local_record[ptdiag4];
			$procphysician = $this_patient->local_record[ptdoc];
			$procrefdoc = $this_patient->local_record[ptrefdoc];
			$been_here = 1;
		}

		$phys_query = "SELECT * FROM physician WHERE phyref='no' ".
					  "ORDER BY phylname,phyfname";
		$phys_result = $sql->query($phys_query);

		if (empty ($procdt)) $procdt = $cur_date; // show current date
		$icd_type = freemed_config_value("icd"); // '9' or '10'
		$cptmod_query = "SELECT * FROM cptmod ORDER BY cptmod,cptmoddescrip";
		$cptmod_result = $sql->query($cptmod_query);
		$icd_query = "SELECT * FROM icd9 ORDER BY icd$icd_type"."code";
		$icd_result = $sql->query($icd_query);

		$cert_query = "SELECT id,certdesc FROM certifications WHERE certpatient='$patient'";
		$cert_result = $sql->query($cert_query);
		$clmtype_query = "SELECT id,clmtpname,clmtpdescrip FROM claimtypes";
		$clmtype_result = $sql->query($clmtype_query);


		$auth_r_buffer = $this->GetAuthorizations($patient);

		// Check for eoc
		if (check_module("episodeOfCare")) {
			$related_episode_array = array ( _("Episode of Care") =>
			freemed_multiple_choice ("SELECT * FROM eoc
								  WHERE eocpatient='$patient'
								  ORDER BY eocdtlastsimilar DESC",
								 "eocstartdate:eocdtlastsimilar:eocdescrip",
								 "proceoc",
								 $proceoc,
								 false)
			);
		} else {
			$related_episode_array = array ( "" => "" );
		} // end checking for eoc

		// ************** BUILD THE WIZARD ****************
		$wizard = new wizard ( array ("been_here", "action", "patient", "id",
		"module") );
		$wizard->add_page ("Step One",
			array_merge(array("procphysician", "proceoc", "procrefdoc",
							  "proccpt", "proccptmod", "procunits", 
							  "procdiag1", "procdiag2", "procdiag3", "procdiag4",		
							  "procpos", "procvoucher","proccomment",
								"procauth","proccert","procclmtp"),
							  date_vars("procdt"),date_vars("procrefdt")),
		html_form::form_table ( array_merge ( array (
		  _("Provider") =>
			freemed_display_selectbox ($phys_result, "#phylname#, #phyfname#", "procphysician"),
		  _("Date of Procedure") =>
			fm_date_entry ("procdt"),
		 ),
		 $related_episode_array,
		 array (
		  _("Procedural Code") =>
			freemed_display_selectbox(
			  $sql->query("SELECT * FROM cpt ORDER BY cptcode,cptnameint"),
				"#cptcode# (#cptnameint#)", "proccpt").
			  freemed_display_selectbox(
				$sql->query("SELECT cptmod,cptmoddescrip,id ".
				  "FROM cptmod ORDER BY cptmod,cptmoddescrip"),
				  "#cptmod# (#cptmoddescrip#)", "proccptmod"),
		  _("Units") =>
			"<INPUT TYPE=TEXT NAME=\"procunits\" VALUE=\"".prepare($procunits)."\" ".
			"SIZE=10 MAXLENGTH=9>",
		  _("Diagnosis Code")." 1" =>
			freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
			  "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag1"),
		  _("Diagnosis Code")." 2" =>
			freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
			  "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag2"),
		  _("Diagnosis Code")." 3" =>
			freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
			  "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag3"),
		  _("Diagnosis Code")." 4" =>
			freemed_display_selectbox ($icd_result, (($icd_type=="9") ? 
			  "#icd9code# (#icd9descrip#)" : "#icd10code# (#icd10descrip#)"), "procdiag4"),
		  _("Place of Service") =>
			freemed_display_selectbox(
			  $sql->query("SELECT psrname,psrnote,id FROM facility"),
			  "#psrname# [#psrnote#]", 
			  "procpos"
			),
		  _("Voucher Number") =>
			"<INPUT TYPE=TEXT NAME=\"procvoucher\" VALUE=\"".prepare($procvoucher)."\" ".
			"SIZE=20>\n",
		  _("Authorization") =>
			"<SELECT NAME=\"procauth\">\n".
			"<OPTION VALUE=\"0\" ".
			( ($procauth==0) ? "SELECTED" : "" ).">NONE SELECTED\n".
			$auth_r_buffer.
			"</SELECT>\n",
		  _("Certifications") => freemed_display_selectbox($cert_result,"#certdesc#","proccert"),
		  _("Claim Type") => freemed_display_selectbox($clmtype_result,"#clmtpname# #clmtpdescrip#","procclmtp"),
		  _("Referring Provider") =>
			freemed_display_selectbox (
			  $sql->query("SELECT phylname,phyfname,id FROM physician 
						  WHERE phyref='yes'
						  ORDER BY phylname, phyfname"),
			  "#phylname#, #phyfname#", "procrefdoc"
			),
		  _("Date of Last Visit") =>
			fm_date_entry ("procrefdt"),
		  _("Comment") =>
			"<INPUT TYPE=TEXT NAME=\"proccomment\" VALUE=\"".prepare($proccomment)."\" ".
			"SIZE=30 MAXLENGTH=512>\n"
		) ),
			// verify
			array(
					array ("procdiag1", VERIFY_NONZERO, NULL, _("Must have one diagnosis code")),
					array ("procphysician", VERIFY_NONZERO, NULL, _("Must Specify physician")),
					array ("procdt_y", VERIFY_NONZERO, NULL, _("Must Specify Proc Year")),
					array ("procdt_m", VERIFY_NONZERO, NULL, _("Must Specify proc Month")),
					array ("procdt_d", VERIFY_NONZERO, NULL, _("Must Specify proc Day")),
					array ("procpos", VERIFY_NONZERO, NULL, _("Must Specify Place of Service")),
					array ("procclmtp", VERIFY_NONZERO, NULL, _("Must Specify Type of Claim")),
					array ("proccpt", VERIFY_NONZERO, NULL, _("Must Specify Procedural code"))
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

		$wizard->add_page (_("Step Two: Select Coverage"),
			array("proccurcovid","proccurcovtp","proccov1","proccov2","proccov3","proccov4"),
			html_form::form_table(array (
				_("Primary Coverage") =>  freemed_display_selectbox($prim_result,"#insconame#","proccov1"),
				_("Secondary Coverage") =>  freemed_display_selectbox($sec_result,"#insconame#","proccov2"),
				_("Tertiary Coverage") =>  freemed_display_selectbox($tert_result,"#insconame#","proccov3"),
				_("Work Comp Coverage") =>  freemed_display_selectbox($tert_result,"#insconame#","proccov4")
				))
			); // end coverage page	

		$charge = $this->CalculateCharge($proccov1,$proccpt,$procphysician,$patient);
		$cpt_code = freemed_get_link_rec ($proccpt, "cpt"); // cpt code


		$wizard->add_page (_("Step Three: Confirm"),
		array ("proccomment","procunits", "procbalorig", "procbillable"),
		html_form::form_table ( array (

		 _("Procedural Code") =>
		   prepare($cpt_code["cptcode"]),

		 _("Units") =>
		   prepare($procunits),

		 _("Calculated Accepted Fee") =>
		   $cpt_code_stdfee,

		 _("Calculated Charge") =>
		   "<INPUT TYPE=TEXT NAME=\"procbalorig\" SIZE=10 MAXLENGTH=9 ".
		   "VALUE=\"".prepare($charge)."\">",

		 _("Insurance Billable?") =>
		   "<SELECT NAME=\"procbillable\">
			<OPTION VALUE=\"0\" ".
			 ( ($procbillable == 0) ? "SELECTED" : "" ).">"._("Yes")."
			<OPTION VALUE=\"1\" ".
			 ( ($procbillable != 0) ? "SELECTED" : "" ).">"._("No")."
		   </SELECT>\n",

		 _("Comment") =>
		   prepare($proccomment)
		) ),
			array (
					array ("procbalorig", VERIFY_NONNULL, NULL, _("Must Specify Amount"))
				)
		);

		// required to get the wizard to validate the previous (last) page
		$wizard->add_page(_("Click Finish"),array("dummy"),"");

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

			$display_buffer .= "<P><CENTER>"._("Adding")." ... ";

			$query = $sql->insert_query 
				(
					$this->table_name,
					array (
					"procpatient"   =>  $patient,
					"proceoc",
					"proccpt",
					"proccptmod",
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
					)
				);

				$result = $sql->query ($query);
				if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				if ($result) { $display_buffer .= _("done")."."; }
				else        { $display_buffer .= _("ERROR");    }

				$this_procedure = $sql->last_record ($result);

				// form add query
				$display_buffer .= "
				<BR>
				"._("Committing to ledger")." ... 
				";
				$query = "INSERT INTO payrec VALUES (
					'$cur_date',
					'0000-00-00',
					'$patient',
					'".fm_date_assemble("procdt")."',
					'".PROCEDURE."',
					'$this_procedure',
					'$proccurcovtp',
					'$proccurcovid',
					'0',
					'',
					'$procbalorig',
					'".addslashes($proccomment)."',
					'unlocked',
					NULL )";
				$result = $sql->query ($query);
				if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				if ($result) { $display_buffer .= _("done")."."; }
				else        { $display_buffer .= _("ERROR");    }
				$this_procedure = $sql->last_record ($result, $this->table_name);

				// updating patient diagnoses
				$display_buffer .= "
				<BR>
				"._("Updating patient diagnoses")." ...  ";
				$query = "UPDATE patient SET
					ptdiag1  = '$procdiag1',
					ptdiag2  = '$procdiag2',
					ptdiag3  = '$procdiag3',
					ptdiag4  = '$procdiag4'
					WHERE id = '$patient'";
				$result = $sql->query ($query);
				if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
				if ($result) { $display_buffer .= _("done")."."; }
				else        { $display_buffer .= _("ERROR");    }

				$display_buffer .= "
				</CENTER>
				<P>
				<CENTER>
				 <A HREF=\"manage.php?id=$patient\"
				 >"._("Manage Patient")."</A> <B>|</B>
				 <A HREF=\"$this->page_name?module=PaymentModule&action=addform&patient=$patient\"
				 >"._("Add Payment")."</A> <B>|</B>
				 <A HREF=\"$this->page_name?module=$module&action=addform&procvoucher=$procvoucher".
				  "&patient=$patient&procdt=".fm_date_assemble("procdt").
				  "&proccpt=$proccpt".
				  "&procpos=$procpos".
				  "&procdiag1=$procdiag1".
				  "&procdiag2=$procdiag2".
				  "&procdiag3=$procdiag3".
				  "&procdiag4=$procdiag4".
				  "&procphysician=$procphysician".
				  "\"
				 >"._("Add Another")." "._($record_name)."</A>
				</CENTER>
				<P>
				";
		
		} // end wizard done

		if ($wizard->is_cancelled())
		{
				$display_buffer .= "
				<P>
				<CENTER><B>"._(Cancelled)."</B><BR>
				 <A HREF=\"manage.php?id=$patient\"
				 >"._("Manage Patient")."</A> 
				";

		} // end cancelled

	} // end addform

	function modform() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

		if (!$been_here)
		{
			while(list($k,$v)=each($this->proc_fields))
			{
				global $$v;
			}
			$this_data = freemed_get_link_rec ($id, $this->table_name);
			extract ($this_data); // extract all of this data

			global $been_here;
			$been_here = 1;
		}

		$auth_r_buffer = $this->GetAuthorizations($patient);

		$cert_query = "SELECT id,certdesc FROM certifications WHERE certpatient='$patient'";
		$cert_result = $sql->query($cert_query);
		$clmtype_query = "SELECT id,clmtpname,clmtpdescrip FROM claimtypes";
		$clmtype_result = $sql->query($clmtype_query);

		// ************** BUILD THE WIZARD ****************
		$wizard = new wizard ( array ("been_here", "action", "patient", "id", "module") );

		$wizard->add_page ("Step One",
				array("proceoc", "proccomment", "procauth", "procvoucher", "proccert", "procclmtp"),
			html_form::form_table ( array (
		  _("Episode of Care") =>
			freemed_multiple_choice ("SELECT * FROM eoc
								  WHERE eocpatient='$patient'
								  ORDER BY eocdtlastsimilar DESC",
								 "eocstartdate:eocdtlastsimilar:eocdescrip",
								 "proceoc",
								 $proceoc,
								 false),
		  _("Voucher Number") =>
			"<INPUT TYPE=TEXT NAME=\"procvoucher\" VALUE=\"".prepare($procvoucher)."\" ".
			"SIZE=20>\n",
		  _("Authorization") =>
			"<SELECT NAME=\"procauth\">\n".
			"<OPTION VALUE=\"0\" ".
			( ($procauth==0) ? "SELECTED" : "" ).">NONE SELECTED\n".
			$auth_r_buffer.
			"</SELECT>\n",
		  _("Certifications") => freemed_display_selectbox($cert_result,"#certdesc#","proccert"),
		  _("Claim Type") => freemed_display_selectbox($clmtype_result,"#clmtpname# #clmtpdescrip#","procclmtp"),
		  _("Comment") =>
			"<INPUT TYPE=TEXT NAME=\"proccomment\" VALUE=\"".prepare($proccomment)."\" ".
			"SIZE=30 MAXLENGTH=512>\n"
			) ) 
		); // end of page one

		if (!$wizard->is_done() and !$wizard->is_cancelled()) 
		{
			// display the wizard
			$display_buffer .= "<CENTER>".$wizard->display()."</CENTER>\n";
		}

		if ($wizard->is_done())
		{

			$display_buffer .= "<P><CENTER>"._("Modifying")." ... ";

			$query = "UPDATE $this->table_name SET
			proceoc         = '".addslashes(fm_join_from_array($proceoc))."',
			procvoucher     = '".addslashes($procvoucher).  "',
			proccomment     = '".addslashes($proccomment).  "',
			proccert        = '".addslashes($proccert).  "',
			procclmtp       = '".addslashes($procclmtp).  "',
			procauth        = '".addslashes($procauth).     "'".
			" WHERE id='$id'";
			$result = $sql->query ($query);
			if ($debug) $display_buffer .= " (query = $query, result = $result) <BR>\n";
			if ($result) { $display_buffer .= _("done")."."; }
			else        { $display_buffer .= _("ERROR");    }

			$display_buffer .= "
				<P>
				<CENTER>
			 	<A HREF=\"$this->page_name?module=$module&patient=$patient\"
			 	>"._("back")."</A><BR>
				<A HREF=\"manage.php?id=$patient\"
				>"._("Manage Patient")."</A>
				</CENTER>
				<P>
			";



		} // end wizard is done

		if ($wizard->is_cancelled())
		{
				$display_buffer .= "
				<P>
				<CENTER><B>"._(Cancelled)."</B><BR>
				 <A HREF=\"manage.php?id=$patient\"
				 >"._("Manage Patient")."</A> 
				";

		} // end cancelled

		
		

	} // end modform

	
	function delete () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$display_buffer .= "
		<P><CENTER>
		"._("Deleting")." ...
		";
		$query = "DELETE FROM $this->table_name WHERE id='$id'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= "["._("Procedure")."] "; }
		else        { $display_buffer .= "["._("ERROR")."] ";     }
		$query = "DELETE FROM payrec WHERE payrecproc='".addslashes($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= "["._("Payment Record")."] "; }
		else        { $display_buffer .= "["._("ERROR")."] ";          }
		$display_buffer .= "
		</CENTER>
		<P>
		<CENTER>
		 <A HREF=\"$this->page_name?module=$module&patient=$patient\"
		 >"._("back")."</A> <B>|</B>
		 <A HREF=\"manage.php?id=$patient\"
		 >"._("Manage Patient")."</A>
		</CENTER>
		<P>
		";
					} // end function procedureModule->delete()

	function display() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;
		while(list($k,$v)=each($this->proc_fields))
		{
			global $$v;
		}
		$this_data = freemed_get_link_rec ($id, $this->table_name);
		extract ($this_data); // extract all of this data

		$phyname = freemed_get_link_field($procphysician,"physician","phylname");
		$refphyname = freemed_get_link_field($procrefdoc,"physician","phylname");

		$icd_type = freemed_config_value("icd"); // '9' or '10'
		$icd_code = "icd".$icd_type."code";
		$diag1 = freemed_get_link_field($procdiag1,"icd9",$icd_code);
		$diag2 = freemed_get_link_field($procdiag2,"icd9",$icd_code);
		$diag3 = freemed_get_link_field($procdiag3,"icd9",$icd_code);
		$diag4 = freemed_get_link_field($procdiag4,"icd9",$icd_code);
		$psrname = freemed_get_link_field($procpos,"pos","psrname");
		$authdtbeg = freemed_get_link_field($procauth,"authorizations","authdtbegin");
		$authdtend = freemed_get_link_field($procauth,"authorizations","authdtend");
		$authdt = $authdtbeg.$authdtend;
		$cov1ins = freemed_get_link_field($proccov1,"coverage","covinsco");
		$cov1name = freemed_get_link_field($cov1ins,"insco","insconame");
		$cov2ins = freemed_get_link_field($proccov2,"coverage","covinsco");
		$cov2name = freemed_get_link_field($cov2ins,"insco","insconame");
		$cov3ins = freemed_get_link_field($proccov3,"coverage","covinsco");
		$cov3name = freemed_get_link_field($cov3ins,"insco","insconame");
		$cov4ins = freemed_get_link_field($proccov4,"coverage","covinsco");
		$cov4name = freemed_get_link_field($cov4ins,"insco","insconame");
		$covins = freemed_get_link_field($proccurcovid,"coverage","covinsco");
		$covname = freemed_get_link_field($covins,"insco","insconame");

		$wizard = new wizard ( array ("been_here", "action", "patient", "id",
		"module") );
		$wizard->add_page (_("Part One"),
			array_merge(array("phyname", "proceoc", "refphyname",
							  "procunits", 
							  "diag1", "diag2", "diag3", "diag4",		
							  "psrname", "procvoucher","proccomment",
								"psrname","covname","cov1name","cov2name","cov3name","cov4name"),
							  date_vars("procdt"),date_vars("procrefdt")),
		html_form::form_table ( array (
		  _("Provider") => prepare($phyname),
		  _("Date of Procedure") => prepare($procdt),
		  _("Episode of Care") =>
			freemed_multiple_choice ("SELECT * FROM eoc
								  WHERE eocpatient='$patient'
								  ORDER BY eocdtlastsimilar DESC",
								 "eocstartdate:eocdtlastsimilar:eocdescrip",
								 "proceoc",
								 $proceoc,
								 false),
		  _("Units") => prepare($procunits), 
		  _("Diagnosis Code")." 1" => prepare($diag1),
		  _("Diagnosis Code")." 2" => prepare($diag2),
		  _("Diagnosis Code")." 3" => prepare($diag3),
		  _("Diagnosis Code")." 4" => prepare($diag4),
		  _("Place of Service") => prepare($psrname),
		  _("Voucher Number") => prepare($procvoucher),
		  _("Authorization") => prepare($authdt),
		  _("Referring Provider") => prepare($refphyname),
		  _("Date of Last Visit") => prepare($procrefdt),
		  _("Comment") => prepare($proccomment),
		  _("Current Coverage") => prepare($covname), 
		  _("Primary Coverage") => prepare($cov1name),
		  _("Secondary Coverage") => prepare($cov2name),
		  _("Tertiary Coverage") => prepare($cov3name), 
		  _("Work Comp Coverage") => prepare($cov4name)
		) )
		); // end of page one

		
		if (!$wizard->is_done() and !$wizard->is_cancelled()) 
		{
			// display the wizard
			$display_buffer .= "<CENTER>".$wizard->display()."</CENTER>\n";
		}
		else
		{
			$display_buffer .= "
			<P>
			<CENTER>
			 <A HREF=\"$this->page_name?module=$module&patient=$patient\"
			 >"._("back")."</A>
			</CENTER>
			<P>
			";
		}
		

	}

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$query = "SELECT * FROM $this->table_name
				WHERE procpatient='".addslashes($patient)."'
				ORDER BY procdt DESC";
		$result = $sql->query ($query);
		$display_buffer .= freemed_display_itemlist(
			$result,
			$this->page_name,
			array ( // control
			  _("Date of Procedure")	=> "procdt",
			  _("Procedure Code")	=> "proccpt",
			  _("Modifier")		=> "proccptmod",
			  _("Comment")		=> "proccomment"
			),
			array ( // blanks
			  "",
			  "",
			  "",
			  _("NO COMMENT")
			),
			array ( // xref
			  "",
			  "cpt"    => "cptcode",
			  "cptmod" => "cptmod",
			  ""
			)
		);
	} // end function procedureModule->view()


	function GetAuthorizations($patid) {
		global $display_buffer;
		global $sql;
		
		$auth_r_buffer = "";

		if ($patid==0)
			return $auth_r_buffer;

		$auth_res = $sql->query ("SELECT * FROM authorizations
							  WHERE (authpatient='$patid')");
		if ($auth_res > 0) { // begin if there are authorizations...
		while ($auth_r = $sql->fetch_array ($auth_res)) {
		$auth_r_buffer .= "
		 <OPTION VALUE=\"$auth_r[id]\" ".
		 ( ($auth_r[id]==$procauth) ? "SELECTED" : "" )
		 .">$auth_r[authdtbegin] to $auth_r[authdtend]\n";
		} // end while looping for authorizations
		} // end if there are authorizations

		return $auth_r_buffer;
	}

	function CalculateCharge($covid,$cptid,$phyid,$patid)  {
		global $display_buffer;
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
		$primary = new Coverage($covid);
		$insid = $primary->local_record[covinsco];

		$cpt_code = freemed_get_link_rec ($cptid, "cpt"); // cpt code
		$cpt_code_fees = fm_split_into_array ($cpt_code["cptstdfee"]);
		$cpt_code_stdfee = $cpt_code_fees[$insid]; // grab proper std fee
		if (empty($cpt_code_stdfee) or ($cpt_code_stdfee==0))
		$cpt_code_stdfee = $cpt_code["cptdefstdfee"]; // if none, do default
		$cpt_code_stdfee = bcadd ($cpt_code_stdfee, 0, 2);

		// step two:
		//   grab the relative value from the CPT db
		$relative_value = $cpt_code["cptrelval"];
		if ($debug) $display_buffer .= " (relative_value = \"$relative_value\")\n";

		// step three:
		//   calculate the base value
		$internal_type  = $cpt_code ["cpttype"]; // grab internal type
		if ($debug) 
		$display_buffer .= " (inttype = $internal_type) (procphysician = $procphysician) ";
		$this_physician = freemed_get_link_rec ($physid, "physician");
		$charge_map     = fm_split_into_array($this_physician ["phychargemap"]);
		$base_value     = $charge_map [$internal_type];
		if ($debug) $display_buffer .= "<BR>base value = \"$base_value\"\n";

		// step four:
		//   check for patient discount percentage
		$this_patient = new Patient($patid);
		$percentage = $this_patient->local_record["ptdisc"];
		if ($percentage>0) { $discount = $percentage / 100; }
		else              { $discount = 0;                 }
		if ($debug) $display_buffer .= "<BR>discount = \"$discount\"\n";

		// step five:
		//   calculate formula...
		$charge = ($base_value * $procunits * $relative_value) - $discount; 
		if ($charge == 0)
		$charge = $cpt_code_stdfee;
		if ($debug) $display_buffer .= " (charge = \"$charge\") \n";

		// step six:
		//   adjust values to proper precision
		$charge = bcadd ($charge, 0, 2);
		return $charge;

	}

} // end class procedureModule

register_module ("procedureModule");

} // end if not defined

?>
