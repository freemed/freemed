<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class PatientCoveragesModule extends EMRModule {

	// override variables
	var $MODULE_NAME = "Patient Coverage";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name    = "coverage";
	var $record_name   = "Patient Coverage";
	var $patient_field = "covpatient";

	// contructor method
	function PatientCoveragesModule ($nullvar = "") {
		// call parent constructor
		$this->EMRModule($nullvar);
	} // end function PatientCoveragesModule

	// override check_vars method
	//function check_vars ($nullvar = "") {
	//	global $module;
	//	if (!isset($module)) return false;
	//	return true;
	//} // end function check_vars

	function modform() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		if ($id<=0) {
			$display_buffer .= _("ID not valid");
			template_display();
		}
		//$this->View();
		//$display_buffer .= "<CENTER><P><B>Not Implemented</B></P><BR></CENTER>";

		$book = CreateObject('PHP.notebook', array ("action", "id", "module", "been_here", "patient"),
			NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR);

		if (!$book->been_here())
		{
			global $been_here;
			$been_here = 1;
			// note book ignores globals of 0 (BUG??)
			$row = freemed::get_link_rec($id,$this->table_name);
			if (!$row) {
				$display_buffer .= _("Failed to read coverage table");
				template_display();
			}
			while (list($k,$v)=each($row)) 
			{
				if ( (substr($k,0,3) == "cov") )
				{
					global $$k;
				}
			}
			extract($row);
		}

		$query = "SELECT * FROM covtypes ORDER BY covtpname";
		$covtypes_result = $sql->query($query);
		if (!$covtypes_result) {
			$display_buffer .= _("Failed to get insurance coverage types");
			template_display();
		}

		$book->add_page(_("Supply Coverage Information"),
			array_merge(array("covinstp","covprovasgn","covbenasgn","covrelinfo","covplanname"),
			date_vars("covrelinfodt")),
			html_form::form_table( array (
										_("Coverage Insurance Type") => 
											freemed_display_selectbox($covtypes_result,"#covtpname# #covtpdescrip#","covinstp"),
										_("Provider Accepts Assigment") =>
											html_form::select_widget("covprovasgn",array(
												_("Yes") => "1",
												_("No") => "0")),
										_("Assigment Of Benefits") =>
											html_form::select_widget("covbenasgn",array(
												_("Yes") => "1",
												_("No") => "0")),
										_("Release Of Information") =>
											html_form::select_widget("covrelinfo",array(
												_("Yes") => "1",
												_("No") => "0",
												_("Limited") => "2")),
										_("Release Date Signed") => fm_date_entry("covrelinfodt"),
										_("Group - Plan Name") => 
											"<INPUT TYPE=TEXT NAME=\"covplanname\" SIZE=20 MAXLENGTH=33 ".
                                            "VALUE=\"".prepare($covplanname)."\">"
																))
								);
			$book->add_page("Modify Insurance Information",
								array_merge( array("covpatgrpno", "covpatinsno", "covrel"), 
									  date_vars("coveffdt")),
								html_form::form_table( array (
										"Start Date" => fm_date_entry("coveffdt"),
										"Insurance ID Number" => 
											"<INPUT TYPE=TEXT NAME=\"covpatinsno\" SIZE=20 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($covpatinsno)."\">\n",
										"Insurance Group Number" => 
											"<INPUT TYPE=TEXT NAME=\"covpatgrpno\" SIZE=20 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($covpatgrpno)."\">\n",
										"Relationship to Insured" => html_form::select_widget("covrel", array (
															_("Self")    => "S",
															_("Child")   => "C",
															_("Husband") => "H",
															_("Wife")    => "W",
															_("Child Not Fin") => "D",
															_("Step Child") => "SC",
															_("Foster Child") => "FC",
															_("Ward of Court") => "WC",
															_("HC Dependent") => "HD",
															_("Sponsored Dependent") => "SD",
															_("Medicare Legal Rep") => "LR",
															_("Other")   => "O" ) )
										 					 ) 
													) 
							); // end add page

			if ($covrel != "S")
			{
				$book->add_page("Modify Insureds Information",
								array_merge(array("covlname", "covfname", "covmname", "covaddr1", "covaddr2", "covcity",
											"covstate", "covzip", "covsex"), date_vars("covdob")),
						html_form::form_table ( array (
							_("Last Name") =>
								"<INPUT TYPE=TEXT NAME=\"covlname\" SIZE=25 MAXLENGTH=50 ".
								"VALUE=\"".prepare($covlname)."\">",
					
							_("First Name") =>
								"<INPUT TYPE=TEXT NAME=\"covfname\" SIZE=25 MAXLENGTH=50 ".
								"VALUE=\"".prepare($covfname)."\">",

							_("Middle Name") =>
								"<INPUT TYPE=TEXT NAME=\"covmname\" SIZE=25 MAXLENGTH=50 ".
								"VALUE=\"".prepare($covmname)."\">",

							_("Address Line 1") =>
								"<INPUT TYPE=TEXT NAME=\"covaddr1\" SIZE=25 MAXLENGTH=45 ".
								"VALUE=\"".prepare($covaddr1)."\">",

							_("Address Line 2") =>
								"<INPUT TYPE=TEXT NAME=\"covaddr2\" SIZE=25 MAXLENGTH=45 ".
								"VALUE=\"".prepare($covaddr2)."\">",

							_("City").", "._("State").", "._("Zip") =>
								"<INPUT TYPE=TEXT NAME=\"covcity\" SIZE=10 MAXLENGTH=45 ".
								"VALUE=\"".prepare($covcity)."\">\n".
								"<INPUT TYPE=TEXT NAME=\"covstate\" SIZE=3 MAXLENGTH=2 ".
								"VALUE=\"".prepare($covstate)."\">\n". 
								"<INPUT TYPE=TEXT NAME=\"covzip\" SIZE=10 MAXLENGTH=10 ".
								"VALUE=\"".prepare($covzip)."\">",

							_("Date of Birth") =>
								date_entry("covdob"),
							_("Gender") =>
            					html_form::select_widget("covsex",
                						array (
                     						_("Female")        => "f",
                     						_("Male")          => "m",
                     						_("Transgendered") => "t"
                								)
            							)


						) )
					 );


			}
			
		if ($book->is_cancelled()) {
			Header("Location: ".$this->page_name."?".
				"module=".$this->MODULE_CLASS."&".
				"patient=".urlencode($patient));
			die("");
		}

		if (!$book->is_done())
		{
			$display_buffer .= "<CENTER>".$book->display()."</CENTER>";
			$display_buffer .= "
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?module=$module&patient=$patient\"
				>"._("Abandon Modification").
				"</A>
				</CENTER>
				";
			return;
		}

		$error_msg = $this->EditInsurance();

		if (!empty($error_msg))
		{
			$display_buffer .= "
   				<P>
   				<CENTER>Entry Error found<BR></CENTER>
   				<CENTER>$error_msg<BR></CENTER>
   				<P>
   				<CENTER>
   				<FORM ACTION=\"$this->page_name\" METHOD=POST>
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

		$covstatus=ACTIVE;
		$coveffdt = fm_date_assemble("coveffdt");
		$covdob = fm_date_assemble("covdob");
		$covrelinfodt = fm_date_assemble("covrelinfodt");

		$query = "UPDATE $this->table_name SET coveffdt='".addslashes($coveffdt)."',".
												"covdtmod='".addslashes($cur_date)."',".
												"covlname='".addslashes($covlname)."',".
												"covfname='".addslashes($covfname)."',".
												"covmname='".addslashes($covmname)."',".
												"covdob='".addslashes($covdob)."',".
												"covsex='".addslashes($covsex)."',".
												"covaddr1='".addslashes($covaddr1)."',".
												"covaddr2='".addslashes($covaddr2)."',".
												"covcity='".addslashes($covcity)."',".
												"covstate='".addslashes($covstate)."',".
												"covzip='".addslashes($covzip)."',".
												"covrel='".addslashes($covrel)."',".
												"covpatinsno='".addslashes($covpatinsno)."',".
												"covpatgrpno='".addslashes($covpatgrpno)."',".
												"covinstp='".addslashes($covinstp)."',".
												"covprovasgn='".addslashes($covprovasgn)."',".
												"covbenasgn='".addslashes($covbenasgn)."',".
												"covrelinfo='".addslashes($covrelinfo)."',".
												"covrelinfodt='".addslashes($covrelinfodt)."',".
												"covplanname='".addslashes($covplanname)."'".
				" WHERE id='".addslashes($id)."'";
		$result = $sql->query($query);
		$display_buffer .= "<CENTER>";
		if ($result)
			$display_buffer .= _("done").".";
		else
			$display_buffer .= _("ERROR");
		$display_buffer .= "</CENTER>";
		
		$display_buffer .= "
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?patient=$patient&module=$module\">
			"._("Back")."</A>
			</CENTER>
			<P>
			";
		


	}

	function addform() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		if ($patient<=0) {
			$display_buffer .= _("Must Select a patient");
			template_display();
		}
		// 
		// wizard 
		// step 1 guar or insurance
		// step2/3 select a guar or insurance if a guar then insurance
		// step4 all other data
		$wizard = CreateObject('PHP.wizard', array("been_here", "module", "action", "patient"));

		// Im leaving this in incase we decide later to break it up more
		$wizard->add_page("Select Coverage Type",
						  array("coveragetype"),
						"<CENTER><TABLE ALIGN=CENTER BORDER=0 CELLSPACING=0 CELLPADDING=2>
						<TR>
						<TD ALIGN=RIGHT>
						<INPUT TYPE=RADIO NAME=\"coveragetype\" VALUE=\"0\" CHECKED>
						</TD><TD ALIGN=LEFT>
						"._("Insurance")."
						</TD>
						</TR>
						</TABLE></CENTER>" );

		if ($coveragetype==0)  // Insurance Coverage
		{
			// patient has insurance
			$query = "SELECT * FROM insco ORDER BY insconame";
			$ins_result = $sql->query($query);
			if (!$ins_result) {
				$display_buffer .= _("Failed to get insurance companies");
				template_display();
			}
			$query = "SELECT * FROM covtypes ORDER BY covtpname";
			$covtypes_result = $sql->query($query);
			if (!$covtypes_result) {
				$display_buffer .= _("Failed to get insurance coverage types");
				template_display();
			}
			//insurance coverage
			$wizard->add_page("Select an Insurance Company",
								array("covinsco"),
								html_form::form_table( array(
										_("Insurance Company") => 
										freemed_display_selectbox($ins_result,"#insconame#","covinsco")
										) )
							);

			$wizard->add_page(_("Supply Coverage Information"),
								array_merge(array("covinstp","covprovasgn","covbenasgn","covrelinfo","covplanname"),
											date_vars("covrelinfodt")),
								html_form::form_table( array (
										_("Coverage Insurance Type") => 
											freemed_display_selectbox($covtypes_result,"#covtpname# #covtpdescrip#","covinstp"),
										_("Provider Accepts Assigment") =>
											html_form::select_widget("covprovasgn",array(
												_("Yes") => "1",
												_("No") => "0")),
										_("Assigment Of Benefits") =>
											html_form::select_widget("covbenasgn",array(
												_("Yes") => "1",
												_("No") => "0")),
										_("Release Of Information") =>
											html_form::select_widget("covrelinfo",array(
												_("Yes") => "1",
												_("No") => "0",
												_("Limited") => "2")),
										_("Release Date Signed") => fm_date_entry("covrelinfodt"),
										_("Group - Plan Name") => 
											"<INPUT TYPE=TEXT NAME=\"covplanname\" SIZE=20 MAXLENGTH=33 ".
                                            "VALUE=\"".prepare($covplanname)."\">\n"
																))
								);
										
			$wizard->add_page("Supply Insurance Information",
								array_merge( array("covpatgrpno", "covpatinsno", "covreplace", 
									  "covtype", "covstatus", "covrel"),date_vars("coveffdt")),
								html_form::form_table( array (
										_("Start Date") => fm_date_entry("coveffdt"),
										_("Insurance ID Number") => 
											"<INPUT TYPE=TEXT NAME=\"covpatinsno\" SIZE=30 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($covpatinsno)."\">\n",
										_("Insurance Group Number") => 
											"<INPUT TYPE=TEXT NAME=\"covpatgrpno\" SIZE=30 MAXLENGTH=30 ".
                                            "VALUE=\"".prepare($covpatgrpno)."\">\n",
										_("Insurance Type") => html_form::select_widget("covtype", array (
															_("Primary") => "1",
															_("Secondary") => "2",
															_("Tertiary") => "3",
															_("Work Comp") => "4" )	),
										_("Relationship to Insured") => html_form::select_widget("covrel", array (
															_("Self")    => "S",
															_("Child")   => "C",
															_("Husband") => "H",
															_("Wife")    => "W",
															_("Child Not Fin") => "D",
															_("Step Child") => "SC",
															_("Foster Child") => "FC",
															_("Ward of Court") => "WC",
															_("HC Dependent") => "HD",
															_("Sponsored Dependent") => "SD",
															_("Medicare Legal Rep") => "LR",
															_("Other")   => "O" ) ),
										_("Replace Like Coverage") => html_form::select_widget("covreplace", array (
															_("No") => "0",
															_("Yes") => "1" ) )
										 					 ) 
													) 
							);
			if ($covrel != "S")
			{
			$wizard->add_page("Supply Insureds Info if Not the Patient",
								array_merge(array("covlname", "covfname", "covaddr1", "covaddr2", "covcity",
											"covstate", "covzip", "covsex"), date_vars("covdob")),
						html_form::form_table ( array (
							_("Last Name") =>
								"<INPUT TYPE=TEXT NAME=\"covlname\" SIZE=25 MAXLENGTH=50 ".
								"VALUE=\"".prepare($covlname)."\">",
					
							_("First Name") =>
								"<INPUT TYPE=TEXT NAME=\"covfname\" SIZE=25 MAXLENGTH=50 ".
								"VALUE=\"".prepare($covfname)."\">",

							_("Middle Name") =>
								"<INPUT TYPE=TEXT NAME=\"covmname\" SIZE=25 MAXLENGTH=50 ".
								"VALUE=\"".prepare($covmname)."\">",

							_("Address Line 1") =>
								"<INPUT TYPE=TEXT NAME=\"covaddr1\" SIZE=25 MAXLENGTH=45 ".
								"VALUE=\"".prepare($covaddr1)."\">",

							_("Address Line 2") =>
								"<INPUT TYPE=TEXT NAME=\"covaddr2\" SIZE=25 MAXLENGTH=45 ".
								"VALUE=\"".prepare($covaddr2)."\">",

							_("City").", "._("State").", "._("Zip") =>
								"<INPUT TYPE=TEXT NAME=\"covcity\" SIZE=10 MAXLENGTH=45 ".
								"VALUE=\"".prepare($covcity)."\">\n".
								"<INPUT TYPE=TEXT NAME=\"covstate\" SIZE=3 MAXLENGTH=2 ".
								"VALUE=\"".prepare($covstate)."\">\n". 
								"<INPUT TYPE=TEXT NAME=\"covzip\" SIZE=10 MAXLENGTH=10 ".
								"VALUE=\"".prepare($covzip)."\">",

							_("Date of Birth") =>
								date_entry("covdob"),
							_("Gender") =>
            					html_form::select_widget("covsex",
                						array (
                     						_("Female")        => "f",
                     						_("Male")          => "m",
                     						_("Transgendered") => "t"
                								)
            							)


						) )
					 );


			} // end relation not self
			else
			{
				$wizard->add_page("Press Finish",
								array_merge(array("covlname", "covfname", "covaddr1", "covaddr2", "covcity",
											"covstate", "covzip", "covsex"), date_vars("covdob")),"");

			}
								
		} // end page for Insurance type coverage

		if (!$wizard->is_done() and !$wizard->is_cancelled())
		{
			$display_buffer .= "<div ALIGN=\"CENTER\">".$wizard->display()."</div>";
			return;
		}
		if ($wizard->is_cancelled())
		{
			// if the wizard was cancelled
			global $refresh;
			if ($GLOBALS['return'] == 'manage') {
				$refresh = "manage.php?id=".urlencode($patient);
			} else {
				$refresh = $this->page_name."?module=".
					urlencode($module)."&patient=".
					urlencode($patient);
			}
			return false;
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
				$display_buffer .= "
      				<p/>
      				<div ALIGN=\"CENTER\">Entry Error found</div>
				<br/>
      				<div ALIGN=\"CENTER\">$error_msg</div>
      				<p/>
      				<div ALIGN=\"CENTER\">
      				<form ACTION=\"$this->page_name\" METHOD=\"POST\">
       				<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"addform\"/>
       				<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"$patient\"/>
       				<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"$module\"/>
       				<input TYPE=\"SUBMIT\" VALUE=\"  Try Again  \"/>
      				</form>
      				</div>
				";
					return;
			}
			// we should be good to go

			// start by replacing existing coverages.
			if ($covreplace==1) // replace an existing coverage
			{
				$display_buffer .= _("Removing old coverage")."<br/>\n";
				$query = "UPDATE coverage SET covstatus='".DELETED."' ".
					"WHERE covtype='".addslashes($covtype)."' ".
					"AND covpatient='".addslashes($patient)."'";
				$updres = $sql->query($query);
				if (!$updres) {
					$display_buffer .= _("Error updating coverage status");
					template_display();
				}

			}

			// add the coverage
			$coveffdt = fm_date_assemble("coveffdt");
			$covdob = fm_date_assemble("covdob");
			$covrelinfodt = fm_date_assemble("covrelinfodt");

			$display_buffer .= "<div ALIGN=\"CENTER\">";
			$display_buffer .= _("Adding")." ... \n";
			$covstatus = ACTIVE;  // active
			$query = $sql->insert_query(
				$this->table_name,
				array (
					"covdtadd" => $cur_date,
					"covdtmod" => $cur_date,
					"covlname" => $covlname,
					"covfname" => $covfname,
					"covmname" => $covmname,
					"covaddr1" => $covaddr1,
					"covaddr2" => $covaddr2,
					"covcity" => $covcity,
					"covstate" => $covstate,
					"covzip" => $covzip,
					"covrel" => $covrel,
					"covsex" => $covsex,
					"covdob" => $covdob,
					"covinsco" => $covinsco,
					"coveffdt" => $coveffdt,
					"covpatient" => $patient,
					"covpatgrpno" => $covpatgrpno,
					"covpatinsno" => $covpatinsno,
					"covtype" => $covtype,
					"covstatus" => $covstatus,
					"covinstp" => $covinstp,
					"covprovasgn" => $covprovasgn,
					"covbenasgn" => $covbenasgn,
					"covrelinfo" => $covrelinfo,
					"covplanname" => $covplanname,
					"covrelinfodt" => $covrelinfodt)
				);
			$coverage = $sql->query($query);
			if ($coverage) {
				$display_buffer .= _("done").".";
			} else {
				$display_buffer .= _("ERROR");
			}
			$display_buffer .= "</div>";

		} // end edit for patient insured

		$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\">
			<a HREF=\"$this->page_name?patient=$patient&module=$module\">
			"._("Back")."</a>
			</div>
			<p>
			";

	} // end addform

	function view() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// View brings up the notebook with the correct page first
		// ie insurance if not a guar
		if ($patient <= 0) {
			$display_buffer .= 
				"<div ALIGN=\"CENTER\">\n".
				_("You must select a patient before viewing coverages.").
				"</div>\n";
			template_display();
		}

		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT *,IF(covstatus,\"Deleted\",\"Active\") as covstat,".
				"ELT(covtype,\"Primary\",\"Secondary\",\"Tertiary\",\"WorkComp\") AS covtp ".
				"FROM ".$this->table_name." ".
				"WHERE covpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY covstatus,covtype"
			),
			$this->page_name,
			array(
				"InsCo" => "covinsco",
				"Relation" => "covrel",
				"StartDate" => "coveffdt",
				"Group" => "covpatgrpno",
				"ID"    => "covpatinsno",
				"Status" => "covstat",
				"Type"  => "covtp"
			),
			array("","","","","","",""),
			array(
				"insco" => "insconame",
				"",
				"",
				"",
				"",
				"",
				""
			)
		);
						 
	} // end of view function

		
	function EditInsurance() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$error_msg = "";
		// patient is the insured

		if ($action=="addform")  // cant change these on modform
		{
			if ($covinsco == 0)
				$error_msg .= "You must select an Insurance Company<BR>";

			// see if we alread have an insurer for this type (prim,sec etc...)
			if ($covreplace==0)
			{
				 // if not replacing a like coverage type then verify 		
				 // that we DO NOT already coverage of this type.
		
				$result = fm_verify_patient_coverage($patient,$covtype);
				if ($result > 0)
					$error_msg .= "Patient has active coverage of this type Select Replace to replace<BR>";
			}
		}

		if ($covrel != "S")
		{
			//if ( empty($covaddr1))
			//	$error_msg .= "You must supply The Insureds Address<BR>";
			//if ( (empty($covcity)) OR (empty($covstate)) )
			//	$error_msg .= "You must supply The Insureds Address<BR>";
			//if ( empty($covzip))
			//	$error_msg .= "You must supply The Insureds Address<BR>";
			if ( (empty($covfname)) OR (empty($covlname)) )
				$error_msg .= "You must supply The Insureds Name<BR>";



		}

		// modform only or addform
		$startdt = fm_date_assemble("coveffdt");

		if ($startdt > $cur_date)
			$error_msg .= "Start date cannot be greater than Today $cur_date<BR>";

		//if ( (empty($covpatgrpno)) OR (empty($covpatinsno)) )
		if ( (empty($covpatinsno)) )
			$error_msg .= "You must supply ID numbers<BR>";

		return $error_msg;

	}				 
			


} // end class PatientCoveragesModule

register_module("PatientCoveragesModule");

?>
