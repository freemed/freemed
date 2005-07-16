<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class PatientCoveragesModule extends EMRModule {
	var $MODULE_NAME = "Patient Coverage";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com), Jeff (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.2';

	var $table_name    = "coverage";
	var $record_name   = "Patient Coverage";
	var $patient_field = "covpatient";

	// contructor method
	function PatientCoveragesModule ($nullvar = "") {
		// Table definition
		$this->table_definition = array (
			'covdtadd' => SQL__DATE,
			'covdtmod' => SQL__DATE,
			'covpatient' => SQL__INT_UNSIGNED(0),
			'coveffdt' => SQL__TEXT,
			'covinsco' => SQL__INT_UNSIGNED(0),
			'covpatinsno' => SQL__VARCHAR(50),
			'covpatgrpno' => SQL__VARCHAR(50),
			'covtype' => SQL__INT_UNSIGNED(0),
			'covstatus' => SQL__INT_UNSIGNED(0),
			'covrel' => SQL__CHAR(2),
			'covlname' => SQL__VARCHAR(50),
			'covfname' => SQL__VARCHAR(50),
			'covmname' => SQL__CHAR(1),
			'covaddr1' => SQL__VARCHAR(25),
			'covaddr2' => SQL__VARCHAR(25),
			'covcity' => SQL__VARCHAR(25),
			'covstate' => SQL__CHAR(3),
			'covzip' => SQL__VARCHAR(10),
			'covdob' => SQL__DATE,
			'covsex' => SQL__ENUM(array('m', 'f', 't')),
			'covssn' => SQL__CHAR(9),
			'covinstp' => SQL__INT_UNSIGNED(0),
			'covprovasgn' => SQL__INT_UNSIGNED(0),
			'covbenasgn' => SQL__INT_UNSIGNED(0),
			'covrelinfo' => SQL__INT_UNSIGNED(0),
			'covrelinfodt' => SQL__DATE,
			'covplanname' => SQL__VARCHAR(33),
			'covisassigning' => SQL__INT_UNSIGNED(0),
			'covschool' => SQL__VARCHAR(50),
			'covemployer' => SQL__VARCHAR(50),
			'id' => SQL__SERIAL
		);
	
		$this->summary_vars = array (
			__("Plan") => 'covplanname',
			__("Date") => 'coveffdt'
		);

		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
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
			$display_buffer .= __("ID not valid");
			template_display();
		}

		$book = CreateObject('PHP.notebook', 
			array ("action", "id", "module", "been_here", "patient", "return"),
			NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR);

		// i18n for controls
		$book->set_submit_name = __("Submit");
		$book->set_refresh_name = __("Refresh");
		$book->set_cancel_name = __("Cancel");

		if (!$book->been_here())
		{
			global $been_here;
			$been_here = 1;
			// note book ignores globals of 0 (BUG??)
			$row = freemed::get_link_rec($id,$this->table_name);
			if (!$row) {
				$display_buffer .= __("Failed to read coverage table");
				template_display();
			}
			foreach ($row as $k => $v) {
				if ( (substr($k,0,3) == "cov") )
				{
					global ${$k};
				}
			}
			extract($row);
		}

		$query = "SELECT * FROM covtypes ORDER BY covtpname";
		$covtypes_result = $sql->query($query);
		if (!$covtypes_result) {
			$display_buffer .= __("Failed to get insurance coverage types");
			template_display();
		}

		$book->add_page(__("Supply Coverage Information"),
			array_merge(array("covinstp","covprovasgn","covbenasgn","covrelinfo","covplanname"),
			date_vars("covrelinfodt")),
			html_form::form_table( array (
				__("Coverage Insurance Type") => 
					freemed_display_selectbox($covtypes_result,"#covtpname# #covtpdescrip#","covinstp"),
				__("Provider Accepts Assigment") =>
					html_form::select_widget("covprovasgn",array(
						__("Yes") => "1",
						__("No") => "0")),
				__("Assigment Of Benefits") =>
					html_form::select_widget("covbenasgn",array(
						__("Yes") => "1",
						__("No") => "0")),
				__("Release Of Information") =>
					html_form::select_widget("covrelinfo",array(
						__("Yes") => "1",
						__("No") => "0",
						__("Limited") => "2")
					),
				__("Release Date Signed") =>
					fm_date_entry("covrelinfodt"),
				__("Group - Plan Name") => 
					html_form::text_widget("covplanname", 20, 33)
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
					__("Self")    => "S",
					__("Child")   => "C",
					__("Husband") => "H",
					__("Wife")    => "W",
					__("Child Not Fin") => "D",
					__("Step Child") => "SC",
					__("Foster Child") => "FC",
					__("Ward of Court") => "WC",
					__("HC Dependent") => "HD",
					__("Sponsored Dependent") => "SD",
					__("Medicare Legal Rep") => "LR",
					__("Other")   => "O" ) )
				 ) 
			) 
		); // end add page

		// Add employment assigning and school information
		$book->add_page(__("Miscellaneous Information"),
			array('covisassigning', 'covschool', 'covemployer'),
			html_form::form_table(array(
				__("Is Assigning?") =>
				html_form::select_widget(
					'covisassigning',
					array(
						__("Yes") => '1',
						__("No")  => '0'
					)
				),

				__("School Name for Insured (Leave blank if not a student)") =>
				html_form::text_widget('covschool'),

				__("Employer of Insured (Leave blank if unemployed)") =>
				html_form::text_widget('covemployer')
			))
		);

		if ($covrel != "S")
		{
			$book->add_page("Modify Insureds Information",
				array_merge(array("covlname", "covfname", "covmname", "covaddr1", "covaddr2", "covcity",
					"covstate", "covzip", "covsex", "covssn"), date_vars("covdob")),
				html_form::form_table ( array (
					__("Last Name") =>
						"<INPUT TYPE=TEXT NAME=\"covlname\" SIZE=25 MAXLENGTH=50 ".
						"VALUE=\"".prepare($covlname)."\">",
			
					__("First Name") =>
						"<INPUT TYPE=TEXT NAME=\"covfname\" SIZE=25 MAXLENGTH=50 ".
						"VALUE=\"".prepare($covfname)."\">",
					__("Middle Name") =>
						"<INPUT TYPE=TEXT NAME=\"covmname\" SIZE=25 MAXLENGTH=50 ".
						"VALUE=\"".prepare($covmname)."\">",
					__("Address Line 1") =>
						"<INPUT TYPE=TEXT NAME=\"covaddr1\" SIZE=25 MAXLENGTH=45 ".
						"VALUE=\"".prepare($covaddr1)."\">",
					__("Address Line 2") =>
						"<INPUT TYPE=TEXT NAME=\"covaddr2\" SIZE=25 MAXLENGTH=45 ".
						"VALUE=\"".prepare($covaddr2)."\">",
					__("City").", ".
					__("State").", ".
					__("Zip") =>
						"<INPUT TYPE=TEXT NAME=\"covcity\" SIZE=10 MAXLENGTH=45 ".
						"VALUE=\"".prepare($covcity)."\">\n".
						"<INPUT TYPE=TEXT NAME=\"covstate\" SIZE=3 MAXLENGTH=2 ".
						"VALUE=\"".prepare($covstate)."\">\n". 
						"<INPUT TYPE=TEXT NAME=\"covzip\" SIZE=10 MAXLENGTH=10 ".
						"VALUE=\"".prepare($covzip)."\">",

					__("Date of Birth") =>
						fm_date_entry("covdob"),
					__("Social Security Number") =>
						html_form::text_widget('covssn'),
					__("Gender") =>
            					html_form::select_widget("covsex",
              						array (
                  						__("Female")        => "f",
                    						__("Male")          => "m",
                     						__("Transgendered") => "t"
                								)
          						)


						) )
					 );


			}

		if ($book->is_cancelled()) {
			// Unlock record, if it is locked
			$__lock = CreateObject('_FreeMED.RecordLock', $this->table_name);
			$__lock->UnlockRow ( $_REQUEST['id'] );

			Header("Location: ".$this->page_name."?".
				"module=".$this->MODULE_CLASS."&".
				"patient=".urlencode($patient));
			die("");
		}

		if (!$book->is_done())
		{
			$display_buffer .= "<div align=\"CENTER\">".$book->display()."</div>";
			return;
		}

		$error_msg = $this->EditInsurance();

		if (!empty($error_msg))
		{
			$display_buffer .= "
   				<p/>
   				<center>".__("Entry error found.")."<br/></center>
   				<center>$error_msg<br/></center>
   				<p/>
   				<center>
   				<form ACTION=\"$this->page_name\" METHOD=POST>
   				<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"modform\"/>
   				<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"$id\"/>
   				<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"$patient\"/>
   				<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"$module\"/>
   				<input TYPE=\"SUBMIT\" VALUE=\"  ".__("Try Again")."  \">
   				</form>
   				</center>
   				";
				return true;
		}

		$query = $sql->update_query (
			$this->table_name,
			array (
				'covstatus' => ACTIVE,
				'coveffdt' => fm_date_assemble('coveffdt'),
				'covdtmod' => date("Y-m-d"),
				'covlname' => $covlname,
				'covfname' => $covfname,
				'covmname' => $covmname,
				'covdob' => fm_date_assemble('covdob'),
				'covsex' => $covsex,
				'covaddr1' => $covaddr1,
				'covaddr2' => $covaddr2,
				'covcity' => $covcity,
				'covstate' => $covstate,
				'covzip' => $covzip,
				'covrel' => $covrel,
				'covssn' => $covssn,
				'covpatinsno' => $covpatinsno,
				'covpatgrpno' => $covpatgrpno,
				'covinstp' => $covinstp,
				'covprovasgn' => $covprovasgn,
				'covbenasgn' => $covbenasgn,
				'covrelinfo' => $covrelinfo,
				'covrelinfodt' => fm_date_assemble('covrelinfodt'),
				'covplanname' => $covplanname,
				'covisassigning' => $covisassigning,
				'covschool' => $covschool,
				'covemployer' => $covemployer
			), array ('id' => $id)
		);
		$result = $sql->query($query);
		$display_buffer .= "<center>";
		if ($result) {
			$display_buffer .= __("done").".";
		} else {
			$display_buffer .= __("ERROR");
		}
		$display_buffer .= "</center>";
		
		$display_buffer .= "
			<p/>
			<center>
			<a HREF=\"$this->page_name?patient=$patient&module=$module\" ".
			"class=\"button\">".__("Back")."</a>
			</center>
			<p/>
			";
		


	}

	function addform() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		if ($patient<=0) {
			$display_buffer .= __("Must Select a patient");
			template_display();
		}
		// 
		// wizard 
		// step 1 guar or insurance
		// step2/3 select a guar or insurance if a guar then insurance
		// step4 all other data
		$wizard = CreateObject('PHP.wizard', array("been_here", "module", "action", "patient", "return"));
		$wizard->set_width("100%");
		
		// i18n
		$wizard->set_cancel_name(__("Cancel"));
		$wizard->set_refresh_name(__("Refresh"));
		$wizard->set_finish_name(__("Finish"));
		$wizard->set_next_name(__("Next"));
		$wizard->set_previous_name(__("Previous"));

		// I'm leaving this in in case we decide later to break it up more
		$wizard->add_page(__("Select Coverage Type"),
			  array("coveragetype"),
			"<CENTER><TABLE ALIGN=CENTER BORDER=0 CELLSPACING=0 CELLPADDING=2>
						<TR>
						<TD ALIGN=RIGHT>
						<INPUT TYPE=RADIO NAME=\"coveragetype\" VALUE=\"0\" CHECKED>
						</TD><TD ALIGN=LEFT>
						".__("Insurance")."
						</TD>
						</TR>
						</TABLE></CENTER>" );

		if ($coveragetype==0)  // Insurance Coverage
		{
			// patient has insurance
			$query = "SELECT * FROM insco ORDER BY insconame";
			$ins_result = $sql->query($query);
			if (!$ins_result) {
				$display_buffer .= __("Failed to get insurance companies");
				template_display();
			}
			$query = "SELECT * FROM covtypes ORDER BY covtpname";
			$covtypes_result = $sql->query($query);
			if (!$covtypes_result) {
				$display_buffer .= __("Failed to get insurance coverage types");
				template_display();
			}
			//insurance coverage
			$wizard->add_page("Select an Insurance Company",
				array("covinsco"),
				html_form::form_table( array(
					__("Insurance Company") => 
					freemed_display_selectbox($ins_result,"#insconame#","covinsco")
				) )
			);

			$wizard->add_page(__("Supply Coverage Information"),
				array_merge(array("covinstp","covprovasgn","covbenasgn","covrelinfo","covplanname"),
					date_vars("covrelinfodt")),
				html_form::form_table( array (
					__("Coverage Insurance Type") => 
						freemed_display_selectbox($covtypes_result,"#covtpname# #covtpdescrip#","covinstp"),
					__("Provider Accepts Assigment") =>
						html_form::select_widget("covprovasgn",array(
							__("Yes") => "1",
							__("No") => "0")),
					__("Assigment Of Benefits") =>
						html_form::select_widget("covbenasgn",array(
							__("Yes") => "1",
							__("No") => "0")),
					__("Release Of Information") =>
						html_form::select_widget("covrelinfo",array(
							__("Yes") => "1",
							__("No") => "0",
							__("Limited") => "2")),
					__("Release Date Signed") => fm_date_entry("covrelinfodt"),
					__("Group - Plan Name") => 
						"<input TYPE=\"TEXT\" NAME=\"covplanname\" SIZE=\"20\" MAXLENGTH=\"33\" ".
                                       "VALUE=\"".prepare($covplanname)."\"/>\n"
				))
			);
										
			$wizard->add_page(__("Supply Insurance Information"),
				array_merge( array("covpatgrpno", "covpatinsno", "covreplace", 
				  "covtype", "covstatus", "covrel"),date_vars("coveffdt")),
				html_form::form_table( array (
					__("Start Date") => fm_date_entry("coveffdt"),
					__("Insurance ID Number") => 
						"<INPUT TYPE=TEXT NAME=\"covpatinsno\" SIZE=30 MAXLENGTH=30 ".
                                       "VALUE=\"".prepare($covpatinsno)."\">\n",
					__("Insurance Group Number") => 
						html_form::text_widget('covpatgrpno', '30'),
					__("Insurance Type") => html_form::select_widget("covtype", array (
						__("Primary") => "1",
						__("Secondary") => "2",
						__("Tertiary") => "3",
						__("Work Comp") => "4" )	),
					__("Relationship to Insured") => html_form::select_widget("covrel", array (
						__("Self")    => "S",
						__("Child")   => "C",
						__("Husband") => "H",
						__("Wife")    => "W",
						__("Child Not Fin") => "D",
						__("Step Child") => "SC",
						__("Foster Child") => "FC",
						__("Ward of Court") => "WC",
						__("HC Dependent") => "HD",
						__("Sponsored Dependent") => "SD",
						__("Medicare Legal Rep") => "LR",
						__("Other")   => "O" ) ),
					__("Replace Like Coverage") => html_form::select_widget("covreplace", array (
						__("No") => "0",
						__("Yes") => "1" ) )
					 ) 
					) 
			);
			if ($covrel != "S")
			{
			$wizard->add_page(__("Supply Insureds Info if not the Patient"),
				array_merge(array("covlname", "covfname", "covaddr1", "covaddr2", "covcity",
					"covstate", "covzip", "covsex"), date_vars("covdob")),
				html_form::form_table ( array (
					__("Last Name") =>
						html_form::text_widget('covlname', 25, 50),
			
					__("First Name") =>
						html_form::text_widget('covfname', 25, 50),

					__("Middle Name") =>
						html_form::text_widget('covmname', 25, 50),

					__("Address Line 1") =>
						html_form::text_widget('covaddr1', 25, 45),

					__("Address Line 2") =>
						html_form::text_widget('covaddr2', 25, 45),

					__("City").", ".
					__("State").", ".
					__("Zip") =>
						"<INPUT TYPE=TEXT NAME=\"covcity\" SIZE=10 MAXLENGTH=45 ".
						"VALUE=\"".prepare($covcity)."\">\n".
						"<INPUT TYPE=TEXT NAME=\"covstate\" SIZE=3 MAXLENGTH=2 ".
						"VALUE=\"".prepare($covstate)."\">\n". 
						"<INPUT TYPE=TEXT NAME=\"covzip\" SIZE=10 MAXLENGTH=10 ".
						"VALUE=\"".prepare($covzip)."\">",

					__("Date of Birth") =>
						fm_date_entry("covdob"),
					__("Gender") =>
          					html_form::select_widget("covsex",
              						array (
                   						__("Female")        => "f",
                   						__("Male")          => "m",
                   						__("Transgendered") => "t"
           						)
           					)

					) )
				 );

			} // end relation not self
								
		} // end page for Insurance type coverage

		// Add employment assigning and school information
		$wizard->add_page(__("Miscellaneous Information"),
			array('covisassigning', 'covschool', 'covemployer'),
			html_form::form_table(array(
				__("Is Assigning?") =>
				html_form::select_widget(
					'covisassigning',
					array(
						__("Yes") => '1',
						__("No")  => '0'
					)
				),

				__("School Name for Insured (Leave blank if not a student)") =>
				html_form::text_widget('covschool'),

				__("Employer of Insured (Leave blank if unemployed)") =>
				html_form::text_widget('covemployer')
			))
		);

		if (!$wizard->is_done() and !$wizard->is_cancelled())
		{
			$display_buffer .= "<div ALIGN=\"CENTER\">".$wizard->display()."</div>\n";
			return;
		}
		if ($wizard->is_cancelled())
		{
			// if the wizard was cancelled
			global $refresh;
			if ($_REQUEST['return'] == 'manage') {
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
      				<div ALIGN=\"CENTER\">".__("Entry error found.")."</div>
				<br/>
      				<div ALIGN=\"CENTER\">$error_msg</div>
      				<p/>
      				<div ALIGN=\"CENTER\">
      				<form ACTION=\"$this->page_name\" METHOD=\"POST\">
       				<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"addform\"/>
       				<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"$patient\"/>
       				<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"$module\"/>
       				<input TYPE=\"SUBMIT\" VALUE=\"  ".__("Try Again")."  \"/>
      				</form>
      				</div>
				";
					return;
			}
			// we should be good to go

			// start by replacing existing coverages.
			if ($covreplace==1) // replace an existing coverage
			{
				$display_buffer .= __("Removing old coverage")."<br/>\n";
				$query = "UPDATE coverage SET covstatus='".DELETED."' ".
					"WHERE covtype='".addslashes($covtype)."' ".
					"AND covpatient='".addslashes($patient)."'";
				$updres = $sql->query($query);
				if (!$updres) {
					$display_buffer .= __("Error updating coverage status");
					template_display();
				}

			}

			// add the coverage
			$display_buffer .= "<div ALIGN=\"CENTER\">";
			$display_buffer .= __("Adding")." ... \n";
			$query = $sql->insert_query(
				$this->table_name,
				array (
					'covstatus' => ACTIVE,
					"covdtadd" => date("Y-m-d"),
					"covdtmod" => date("Y-m-d"),
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
					'covssn' => $covssn,
					"covdob" => fm_date_assemble('covdob'),
					"covinsco" => $covinsco,
					"coveffdt" => fm_date_assemble('coveffdt'),
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
					"covrelinfodt" => fm_date_assemble('covrelinfodt'),
					'covisassigning' => $covisassigning,
					'covschool' => $covschool,
					'covemployer' => $covemployer
				)
			);
			$coverage = $sql->query($query);
			if ($coverage) {
				$display_buffer .= __("done").".";
			} else {
				$display_buffer .= __("ERROR");
			}
			$display_buffer .= "</div>";

		} // end edit for patient insured

		$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\">
			<a HREF=\"$this->page_name?patient=$patient&module=$module\">
			".__("Back")."</a>
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
				__("You must select a patient before viewing coverages.").
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
				__("Ins Co") => "covinsco",
				__("Relationship") => "covrel",
				__("Starting Date") => "coveffdt",
				__("Group") => "covpatgrpno",
				__("ID")    => "covpatinsno",
				__("Status") => "covstat",
				__("Type")  => "covtp"
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
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$error_msg = "";
		// patient is the insured

		if ($action=="addform")  // cant change these on modform
		{
			if ($covinsco == 0) {
				$error_msg .= __("You must select an insurance company.")."<br/>\n";
			}

			// see if we alread have an insurer for this type (prim,sec etc...)
			if ($covreplace==0)
			{
				 // if not replacing a like coverage type then verify 		
				 // that we DO NOT already coverage of this type.
		
				$result = fm_verify_patient_coverage($patient,$covtype);
				if ($result > 0)
					$error_msg .= __("Patient has active coverage of this type. Select 'Replace' to replace.")."<br/>\n";
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
			if ( (empty($covfname)) OR (empty($covlname)) ) {
				$error_msg .= __("You must supply the insured party's name.")."<br/>\n";
			}



		}

		// modform only or addform
		$startdt = fm_date_assemble("coveffdt");

		if ($startdt > date("Y-m-d")) {
			$error_msg .= __("Start date cannot be later than today's date.")."<br/>\n";
		}

		//if ( (empty($covpatgrpno)) OR (empty($covpatinsno)) )
		if ( (empty($covpatinsno)) ) {
			$error_msg .= __("You must supply ID numbers.")."<br/>\n";
		}

		return $error_msg;

	} // end method EditInsurance

	function _update( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.3
		//
		//	Add assigning, school name and employer name for
		//		HCFA and X12 forms and billing stuff.
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covisassigning INT UNSIGNED AFTER covplanname');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covschool INT UNSIGNED AFTER covisassigning');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covemployer INT UNSIGNED AFTER covschool');
		}

		// Version 0.3.1
		//
		//	Added covssn, which claims manager was depending on.
		//
		if (!version_check($version, '0.3.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covssn CHAR(9) AFTER covsex');
		}
	} // end method _update

} // end class PatientCoveragesModule

register_module("PatientCoveragesModule");

?>
