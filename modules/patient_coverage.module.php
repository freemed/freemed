<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__PATIENTCOVERAGES_MODULE_PHP__")) {

define (__PATIENTCOVERAGES_MODULE_PHP__, true);

// class PatientCoveragesModule extends freemedModule
class PatientCoveragesModule extends freemedEMRModule {

	// override variables
	var $MODULE_NAME = "Patient Coverage";
	var $MODULE_VERSION = "0.1";

	var $payer_table = "payer";
	var $guar_table = "guarantor";
	var $view_coveragetype = "";



	// contructor method
	function PatientCoveragesModule ($nullvar = "") {
		// call parent constructor
		$this->freemedEMRModule($nullvar);
	} // end function PatientCoveragesModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) return false;
		return true;
	} // end function check_vars

	function modform()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if ($id<=0)
			DIE("ID not valid");
		//$this->View();
		//echo "<CENTER><P><B>Not Implemented</B></P><BR></CENTER>";

		$this_patient = new Patient($patient);
		if (!$this_patient)
			DIE("Patient Class failed");

		if (!isset($been_here))
		{
			global $been_here, $coveragetype;
			$been_here = 1;
			if ($this_patient->ptdep == 0)
			{
				// note book ignores globals of 0 (BUG??)
				$coveragetype="I"; // insurance coverage
				$row = freemed_get_link_rec($id,$this->payer_table);
			}
			else
			{
				$coveragetype="G"; // guarantor coverage
				$row = freemed_get_link_rec($id,$this->guar_table);
			}
			if (!$row)
				DIE("Failed to read guar/payer table");
			while (list($k,$v)=each($row)) 
			{
				if ( (substr($k,0,4) == "guar") OR (substr($k,0,5) == "payer") )
				{
					global $$k;
				}
			}
			extract($row);
		}

		$book = new notebook (array ("action", "id", "module", "been_here", "coveragetype", "patient"),
			NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR);
		if ($coveragetype=="I")  // insurance
		{
			$book->add_page("Supply Insurance Information",
								array_merge( array("payerpatientgrp", "payerpatientinsno"), 
									  date_vars("payerstartdt"),date_vars("payerenddt")),
								html_form::form_table( array (
										"Start Date" => fm_date_entry("payerstartdt"),
										"End Date" => fm_date_entry("payerenddt"),
										"Insurance ID Number" => 
											"<INPUT TYPE=TEXT NAME=\"payerpatientinsno\" SIZE=20 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($payerpatientinsno)."\">\n",
										"Insurance Group Number" => 
											"<INPUT TYPE=TEXT NAME=\"payerpatientgrp\" SIZE=20 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($payerpatientgrp)."\">\n"
										 					 ) 
													) 
							); // end add page
			
		} // end incurance coverage
		
		if ($coveragetype=="G") // guar
		{
			$book->add_page("Supply Guarantor Information",
								array_merge(array("guarrel"),date_vars("guarenddt"), date_vars("guarstartdt")),
								html_form::form_table( array (
										"Start Date" => fm_date_entry("guarstartdt"),
										"End Date" => fm_date_entry("guarenddt"),
										"Relationship to Insured" => html_form::select_widget("guarrel", array (
															_("Self")    => "S",
															_("Child")   => "C",
															_("Husband") => "H",
															_("Wife")    => "W",
															_("Other")   => "O" ) )
															)
													)
							);


		}

		if (!$book->is_done())
		{
			echo "<CENTER>".$book->display()."</CENTER>";
			echo "
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
				>"._("Abandon Modification").
				"</A>
				</CENTER>
				";
			return;
		}

		if ($coveragetype=="I")  // patient is insured
		{
			$error_msg = $this->EditInsurance();

			if (!empty($error_msg))
			{
				echo "
      				<P>
      				<CENTER>Entry Error found<BR></CENTER>
      				<CENTER>$error_msg<BR></CENTER>
      				<P>
      				<CENTER>
      				<FORM ACTION=\"$this->page_name\" METHOD=POST>
       				<INPUT TYPE=HIDDEN NAME=\"_auth\"        VALUE=\"$_auth\">
       				<INPUT TYPE=HIDDEN NAME=\"action\"       VALUE=\"modform\">
       				<INPUT TYPE=HIDDEN NAME=\"id\"           VALUE=\"$id\">
       				<INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">
       				<INPUT TYPE=HIDDEN NAME=\"module\"      VALUE=\"$module\">
       				<INPUT TYPE=SUBMIT VALUE=\"  Try Again  \">
      				</FORM>
      				</CENTER>
     				";
					return;
			}

			$startdt = fm_date_assemble("payerstartdt");
			$enddt = fm_date_assemble("payerenddt");
			$query = "UPDATE $this->payer_table SET payerstartdt='".addslashes($startdt)."',".
													"payerenddt='".addslashes($enddt)."',".
													"payerpatientinsno='".addslashes($payerpatientinsno)."',".
													"payerpatientgrp='".addslashes($payerpatientgrp)."'".
					" WHERE id='".addslashes($id)."'";
			$result = $sql->query($query);
			echo "<CENTER>";
			if ($result)
				echo _("done").".";
			else
				echo _("ERROR");
			echo "</CENTER>";

			

		}

		if ($coveragetype=="G")  // guarantor is insured
		{
			$error_msg = $this->EditGuarantor();

			if (!empty($error_msg))
			{
				echo "
      				<P>
      				<CENTER>Entry Error found<BR></CENTER>
      				<CENTER>$error_msg<BR></CENTER>
      				<P>
      				<CENTER>
      				<FORM ACTION=\"$this->page_name\" METHOD=POST>
       				<INPUT TYPE=HIDDEN NAME=\"_auth\"        VALUE=\"$_auth\">
       				<INPUT TYPE=HIDDEN NAME=\"action\"       VALUE=\"modform\">
       				<INPUT TYPE=HIDDEN NAME=\"id\"           VALUE=\"$id\">
       				<INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">
       				<INPUT TYPE=HIDDEN NAME=\"module\"      VALUE=\"$module\">
       				<INPUT TYPE=SUBMIT VALUE=\"  Try Again  \">
      				</FORM>
      				</CENTER>
     				";
					return;
			}

			$startdt = fm_date_assemble("guarstartdt");
			$enddt = fm_date_assemble("guarenddt");
			
			$query = "UPDATE $this->guar_table SET guarenddt='".addslashes($enddt)."',".
					"guarstartdt='".addslashes($startdt)."',guarrel='".addslashes($guarrel)."'".
					" WHERE id='".addslashes($id)."'";
			$result = $sql->query($query);
			echo "<CENTER>";
			if ($result)
				echo _("done").".";
			else
				echo _("ERROR");
			echo "</CENTER>";

		}
		
		echo "
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?_auth=$_auth&patient=$patient&module=$module\">
			<$STDFONT_B>"._("Back")."<$STDFONT_E></A>
			</CENTER>
			<P>
			";
		


	}

	function addform()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if ($patient<=0)
			DIE("Must Select a patient");
		// 
		// wizard 
		// step 1 guar or insurance
		// step2/3 select a guar or insurance if a guar then insurance
		// step4 all other data
		$wizard = new wizard (array("been_here", "module", "action", "patient", "_auth"));
		$wizard->add_page("Select Coverage Type",
						  array("coveragetype"),
						"<CENTER><TABLE ALIGN=CENTER BORDER=0 CELLSPACING=0 CELLPADDING=2>
						<TR>
						<TD ALIGN=RIGHT>
						<INPUT TYPE=RADIO NAME=\"coveragetype\" VALUE=\"0\" CHECKED>
						</TD><TD ALIGN=LEFT>
						<$STDFONT_B>Insurance<$STDFONT_E>
						</TD>
						</TR>
						<TR>
						<TD ALIGN=RIGHT>
						<INPUT TYPE=RADIO NAME=\"coveragetype\" VALUE=\"1\">
						</TD><TD ALIGN=LEFT>
						<$STDFONT_B>Guarantor<$STDFONT_E>
						</TD>
						</TR>
						</TABLE></CENTER>" );
		if ($coveragetype==0)
		{
			// patient has insurance
			$query = "SELECT * FROM insco ORDER BY insconame";
			$ins_result = $sql->query($query);
			if (!$ins_result)
				DIE("Failed to get insurance companies");
			//insurance coverage
			$wizard->add_page("Select an Insurance Company",
								array("payerinsco"),
								html_form::form_table( array(
										"Insurance Company" => 
										freemed_display_selectbox($ins_result,"#insconame#","payerinsco")
										) )
							);
			$wizard->add_page("Supply Insurance Information",
								array_merge( array("payerpatientgrp", "payerpatientinsno", "payerreplace", 
									  "payertype", "payerstatus"),date_vars("payerstartdt"),date_vars("payerenddt")),
								html_form::form_table( array (
										"Start Date" => fm_date_entry("payerstartdt"),
										"End Date" => fm_date_entry("payerenddt"),
										"Insurance ID Number" => 
											"<INPUT TYPE=TEXT NAME=\"payerpatientinsno\" SIZE=30 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($payerpatientinsno)."\">\n",
										"Insurance Group Number" => 
											"<INPUT TYPE=TEXT NAME=\"payerpatientgrp\" SIZE=30 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($payerpatientgrp)."\">\n",
										"Insurance Type" => html_form::select_widget("payertype", array (
															_("Primary") => "0",
															_("Secondary") => "1",
															_("Tertiary") => "2",
															_("Work Comp") => "3" )	),
										"Replace Like Coverage" => html_form::select_widget("payerreplace", array (
															_("No") => "0",
															_("Yes") => "1" ) )
										 					 ) 
													) 
							);
								
		} // end page for patient is insured

		if ($coveragetype==1)
		{
			//patient has a guarantor
			$payer_result = fm_get_all_insured_patients();
			if (!$payer_result)
				DIE("Failed to get insured patients");
			$wizard->add_page("Select a Guarantor",
								array("guarguar"),
								html_form::form_table( array(
										"Guarantor" => 
										freemed_display_selectbox($payer_result,"#ptlname#, #ptfname#","guarguar")
										) )
							);
			$wizard->add_page("Supply Guarantor Information",
								array_merge(array("guarrel", "guarreplace"),date_vars("guarenddt"), date_vars("guarstartdt")),
								html_form::form_table( array (
										"Start Date" => fm_date_entry("guarstartdt"),
										"End Date" => fm_date_entry("guarenddt"),
										"Relationship to Insured" => html_form::select_widget("guarrel", array (
															_("Self")    => "S",
															_("Child")   => "C",
															_("Husband") => "H",
															_("Wife")    => "W",
															_("Other")   => "O" ) ),
										"Replace Like Coverage" => html_form::select_widget("guarreplace", array (
															_("No") => "0",
															_("Yes") => "1" ) )
															)
													)
							);
												
								
			
		} // end wizard page if guarantor

		if (!$wizard->is_done() and !$wizard->is_cancelled())
		{
			echo "<CENTER>".$wizard->display()."</CENTER>";
			return;
		}
		if ($wizard->is_cancelled())
		{
			// if the wizard was cancelled
			echo "<CENTER>CANCELLED<BR></CENTER><BR>\n";
		}
		// wizard must be done


		// here we start editing the input.
		// edit for insurance entry
		//
		if ($coveragetype==0)
		{  
			$error_msg = $this->EditInsurance();

			if (!empty($error_msg))
			{
				echo "
      				<P>
      				<CENTER>Entry Error found<BR></CENTER>
      				<CENTER>$error_msg<BR></CENTER>
      				<P>
      				<CENTER>
      				<FORM ACTION=\"$this->page_name\" METHOD=POST>
       				<INPUT TYPE=HIDDEN NAME=\"_auth\"        VALUE=\"$_auth\">
       				<INPUT TYPE=HIDDEN NAME=\"action\"       VALUE=\"addform\">
       				<INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">
       				<INPUT TYPE=HIDDEN NAME=\"module\"      VALUE=\"$module\">
       				<INPUT TYPE=SUBMIT VALUE=\"  Try Again  \">
      				</FORM>
      				</CENTER>
     				";
					return;
			}
			// we should be good to go

			$startdt = fm_date_assemble("payerstartdt");
			$enddt = fm_date_assemble("payerenddt");

			// start by replacing existing coverages.
			if ($payerreplace==1) // replace an existing coverage
			{
				$result = fm_verify_patient_coverage($patient,$payertype);
				if ($result)
				{
					echo "<$STDFONT_B>Removing Old Coverage<BR><$STDFONT_E>\n";
					while ($row = $sql->fetch_array($result))
					{
						$query = "UPDATE payer SET payerstatus='1' WHERE id='$row[id]'";
						$updres = $sql->query($query);
						if (!$updres)
							DIE("Error updating payer status");
					}
				}	
				echo "<$STDFONT_B>Removing Old Guarantors<BR><$STDFONT_E>\n";
				// since only 1 guar is allowed we just mark em all
				$query = "UPDATE guarantor SET guarstatus='1' WHERE guarpatient='$patient'";
				$updres = $sql->query($query); // hope the best

			}
			// add the payer
			echo "<$STDFONT_B>"._("Adding")." ... <$STDFONT_E>\n";
			$payerstatus = 0;  // active
			$query = $sql->insert_query($this->payer_table,
										array (
										"payerinsco" => $payerinsco,
										"payerstartdt" => $startdt,
										"payerenddt" => $enddt,
										"payerpatient" => $patient,
										"payerpatientgrp" => $payerpatientgrp,
										"payerpatientinsno" => $payerpatientinsno,
										"payertype" => $payertype,
										"payerstatus" => $payerstatus) );
			$payer_result = $sql->query($query);
			if ($payer_result)
				echo _("done").".";
			else
				echo _("ERROR");

		} // end edit for patient insured

		// edit input data
		// start edit for guar data
		//
		if ($coveragetype==1)
		{
			$error_msg = $this->EditGuarantor();

			if (!empty($error_msg))
			{
				echo "
      				<P>
      				<CENTER>Entry Error found<BR></CENTER>
      				<CENTER>$error_msg<BR></CENTER>
      				<P>
      				<CENTER>
      				<FORM ACTION=\"$this->page_name\" METHOD=POST>
       				<INPUT TYPE=HIDDEN NAME=\"_auth\"        VALUE=\"$_auth\">
       				<INPUT TYPE=HIDDEN NAME=\"action\"       VALUE=\"addform\">
       				<INPUT TYPE=HIDDEN NAME=\"patient\"      VALUE=\"$patient\">
       				<INPUT TYPE=HIDDEN NAME=\"module\"      VALUE=\"$module\">
       				<INPUT TYPE=SUBMIT VALUE=\"  Try Again  \">
      				</FORM>
      				</CENTER>
     				";
					return;
			}

			// we should be good to go
			// wipe out old guars if requested

			if ($guarreplace==1) // replace an existing guarantor
			{
				echo "<$STDFONT_B>Removing Old Guarantors<BR><$STDFONT_E>\n";
				// since only 1 guar is allowed we just mark em all
				$query = "UPDATE guarantor SET guarstatus='1' WHERE guarpatient='$patient'";
				$updres = $sql->query($query);
				if (!$updres)
					DIE("Error updating Guarantor status");
				echo "<$STDFONT_B>Removing Old Insurers<BR><$STDFONT_E>\n";
				// since only 1 guar is allowed we just mark em all
				$query = "UPDATE payer SET payerstatus='1' WHERE payerpatient='$patient'";
				$updres = $sql->query($query); // hope for the best

			}

			// add the guarantor
			echo "<$STDFONT_B>"._("Adding")." ... <$STDFONT_E>\n";
			$guarstatus = 0;  // active
			$startdt = fm_date_assemble("guarstartdt");
			$enddt = fm_date_assemble("guarenddt");
			
			$query = $sql->insert_query($this->guar_table, array(
									"guarpatient" => $patient,
									"guarguar" => $guarguar,
									"guarrel" => $guarrel,
									"guarstartdt" => $startdt,
									"guarenddt" => $enddt,
									"guarstatus" => $guarstatus
										) );
	
			$guar_result = $sql->query($query);
			if ($guar_result)
				echo _("done").".";
			else
				echo _("ERROR");

		} // end edit guarantor
		echo "
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?_auth=$_auth&patient=$patient&module=$module\">
			<$STDFONT_B>"._("Back")."<$STDFONT_E></A>
			</CENTER>
			<P>
			";

	} // end addform

	function view()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		//view brings up the notebook with the correct page first
		// ie insurance if not a guar
		if ($patient<=0)
			DIE("Must Select a patient");

		$this_patient = new Patient($patient);
		if (!$this_patient)
			DIE("Patient Class failed");

		if ($this_patient->ptdep == 0)
		{
			// patient is the insured
			$query = "SELECT * FROM $this->payer_table WHERE 
				payerpatient='$patient' ORDER BY payertype, payerstatus";
			$result = $sql->query($query);
			if (!$result)
				DIE("ERROR Failed to read $this->payer_table");

			echo freemed_display_itemlist($result,
									 $this->page_name,
									array("InsCo" => "payerinsco",
										  "StartDate" => "payerstartdt",
										  "EndDate"   => "payerenddt",
										  "Group" => "payerpatientgrp",
										  "ID"    => "payerpatientinsno",
										  "Type"  => "payertype",
										  "Status" => "payerstatus"),
									array("","","","","","",""),
									array("insco" => "insconame",
											"",
											"",
											"",
											"",
											"",
											"")
										);
			$this->view_coveragetype=0;  // payer is insco
						 
			
		} // end patient is insured
		else
		{ 
			// guar holds the insurance
			$query = "SELECT * FROM $this->guar_table WHERE 
				guarpatient='$patient'";
			$result = $sql->query($query);
			if (!$result)
				DIE("ERROR Failed to read $this->guar_table");

			echo freemed_display_itemlist($result,
									 $this->page_name,
									 array("Guarantor" => "guarguar",
										   "Relation" => "guarrel",
										   "StartDate" => "guarstartdt",
										   "EndDate" => "guarenddt",
										   "Status" => "guarstatus"),
									 array("","","","",""),
									 array("patient" => "ptlname",
											"","","","")
									);
			$this->view_coveragetype=1;  // guarantor
		} // end guar

	} // end of view function

		
	// misc functions
	function EditGuarantor()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$error_msg = "";
		if ($action=="addform")
		{
			if ($guarguar == 0)
				$error_msg .= "You must select a Guarantor<BR>";
			// see if we already have a guarantor 
			if ($guarreplace==0)
			{
				 // if not replacing a like coverage type then verify 		
				 // that we DO NOT already coverage of this type.
				 if (fm_get_active_guarids($patient))
					$error_msg .= "Patient has an active Guarantor. Select Replace to replace<BR>";
				 if (fm_get_active_payerids($patient))
					$error_msg .= "Patient has an active Insurers. Select Replace to replace<BR>";
			}
		}

		// modform only or both for addform
		$startdt = fm_date_assemble("guarstartdt");
		$enddt = fm_date_assemble("guarenddt");
		if ($enddt <= $cur_date)
			$error_msg .= "End date must be greater than Today $cur_date<BR>";

		if ($enddt == $startdt)
			$erro_msg .= "Start date and End date are equal<BR>";

		if ($startdt > $enddt)
			$error_msg .= "Start date cannot be greater than End date<BR>";

		return $error_msg;

	}

	function EditInsurance()
	{
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$error_msg = "";
		// patient is the insured

		if ($action=="addform")  // cant change these on modform
		{
			if ($payerinsco == 0)
				$error_msg .= "You must select an Insurance Company<BR>";

			// see if we alread have an insurer for this type (prim,sec etc...)
			if ($payerreplace==0)
			{
				 // if not replacing a like coverage type then verify 		
				 // that we DO NOT already coverage of this type.
		
				$cov_result = fm_verify_patient_coverage($patient,$payertype);
				$result = ($cov_result) ? $sql->num_rows($cov_result) : 0;
				if ($result > 0)
					$error_msg .= "Patient has active coverage of this type Select Replace to replace<BR>";
				 if (fm_get_active_guarids($patient))
					$error_msg .= "Patient has an active Guarantor. Select Replace to replace<BR>";
			}
		}

		// modform only or addform
		$startdt = fm_date_assemble("payerstartdt");
		$enddt = fm_date_assemble("payerenddt");
		if ($enddt <= $cur_date)
			$error_msg .= "End date must be greater than Today $cur_date<BR>";

		if ($enddt == $startdt)
			$erro_msg .= "Start date and End date are equal<BR>";

		if ($startdt > $enddt)
			$error_msg .= "Start date cannot be greater than End date<BR>";

		if ( (empty($payerpatientgrp)) OR (empty($payerpatientinsno)) )
			$error_msg .= "You must supply Group and ID numbers<BR>";

		return $error_msg;

	}				 
			


} // end class PatientCoveragesModule

register_module("PatientCoveragesModule");

} // end if not defined

?>
