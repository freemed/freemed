<?php
 // $Id$
 // note: patient dmepos certifications 
 // code: Fred Forester (fforest@netcarrier.com)
 // lic : GPL, v2

LoadObjectDependency('FreeMED.CertModule');

class DmeposcertsModule extends CertModule {

	var $MODULE_NAME    = "DMEPOS Certification";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR  = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_DESCRIPTION = "
		Insurance certifications are required by insurance
		companies for payment of DMEPOS supplies
		If you are not a DME supply this module is not needed.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Certifications";
	var $table_name     = "certifications";

	var $variables = array (
		"certpatient",
		"certtype",
		"certdesc",
		"certformnum",
		"certformdata"
	);

	function DmeposcertsModule () {
		$this->CertModule();
	} // end constructor DmeposcertsModule

	function form () {
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

	} // end function DmeposcertsModule->form()

	function addform()
	{
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;
		$certtype = DMEPOS;
		$certpatient = $patient;
		$wizard = CreateObject('PHP.wizard', array("certpatient","certtype","been_here", "module", "action", "patient"));

		$wizard->add_page(__("Form"),
						  array_merge(array("certformnum","certdesc","certstatus","certlenneed","certrental"),
									date_vars("certinitdate"),date_vars("certrevisedate"),date_vars("certdatesigned") ),
						  html_form::form_table( array(
									__("Description") => "<INPUT TYPE=TEXT NAME=\"certdesc\" VALUE=\"".prepare($certdesc)."\"",
									__("Form Number") => "<SELECT NAME=\"certformnum\">".
														"<OPTION VALUE=\"".F0602."\">".__("TENS"),
									__("Cert Status") => "<SELECT NAME=\"certstatus\">".
                                                        "<OPTION VALUE=\"1\">".__("Initial").
                                                        "<OPTION VALUE=\"2\">".__("Revision").
                                                        "<OPTION VALUE=\"3\">".__("Recertification").
                                                        "</SELECT>",
									__("Date Signed") => fm_date_entry("certdatesigned"),
									__("Intial date") => fm_date_entry("certinitdate"),
									__("Revise date") => fm_date_entry("certrevisedate"),
									__("Length of Need") => fm_number_select("certlenneed",1,99,1),
									__("Rental?") => "<SELECT NAME=\"certrental\">".
                                                        "<OPTION VALUE=\"1\">".__("Yes").
                                                        "<OPTION VALUE=\"0\">".__("No").
                                                        "<OPTION VALUE=\"0\">".__("Does Not Apply").
														"</SELECT>"
													  )
												),
							array (
									array ("certdesc", VERIFY_NONNULL, NULL, __("Must Specify Description"))
								  )
							);

		if ($certformnum == F0602)
		{
			// TENS form
			$wizard->add_page( __("Form 06.02 TENS Part1"),
						  array("question3","question4","question5","question6"),
						  html_form::form_table( array(
									__("Chronic, Intractable Pain?") => "<SELECT NAME=\"question3\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
														"</SELECT>",
									__("Months patient had intractable pain?") => fm_number_select("question4",1,99,1),
									__("Prescribed Conditions?") => "<SELECT NAME=\"question5\">".
                                                        "<OPTION VALUE=\"1\">".__("Headache").
                                                        "<OPTION VALUE=\"2\">".__("Visceral abdominal pain").
                                                        "<OPTION VALUE=\"3\">".__("Pelvic pain").
                                                        "<OPTION VALUE=\"4\">".__("Temporomandibular joint (TMJ) pain").
                                                        "<OPTION VALUE=\"5\">".__("None of the above").
                                                        "</SELECT>", 
									__("Documentation of other failed treaments?") => "<SELECT NAME=\"question6\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
                                                        "</SELECT>"
													)
												) // end form_table
							); // end add_page

			if ($certrental > 0)
			{
				$wizard->add_page( __("Form 06.02 TENS Rental"),
								array_merge(array("question1"),date_vars("question2")),
						  html_form::form_table( array(
									__("Acute Post-Operative Pain?") => "<SELECT NAME=\"question1\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
														"</SELECT>",
									__("Date of Surgery") => fm_date_entry ("question2")
													)
												) // end form_table
								); // end add_page

			}
			else // purchase
			{
				$wizard->add_page( __("Form 06.02 TENS Purchase"),
						  array_merge (array("question7","question10","question11","question12"),
										date_vars("question8a"), date_vars("question8b"),
										date_vars("question9") ),
						  html_form::form_table( array(
									__("patient received a TENS trial?") => "<SELECT NAME=\"question7\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
                                                        "</SELECT>",
									__("TENS Trial Start Date") => fm_date_entry ("question8a"),
									__("TENS Trial End Date") => fm_date_entry ("question8b"),
									__("Reevaluated after TENS Trial") => fm_date_entry ("question9"),
									__("Usage") => "<SELECT NAME=\"question10\">".
                                                        "<OPTION VALUE=\"1\">".__("Daily").
                                                        "<OPTION VALUE=\"2\">".__("3 or More days per Week").
                                                        "<OPTION VALUE=\"3\">".__("2 days or less per Week").
                                                        "</SELECT>",
									__("Waranted long term use?") => "<SELECT NAME=\"question11\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
                                                        "</SELECT>",
									__("Number of TENS Leads") => "<SELECT NAME=\"question12\">".
                                                        "<OPTION VALUE=\"2\">".__("2 Leads").
                                                        "<OPTION VALUE=\"4\">".__("4 Leads").
                                                        "</SELECT>"
													  )
												) // end form_table

								); // end add_page


			}
/*
			$wizard->add_page( __("Form 06.02 TENS"),
						  array_merge (array("question1","question3","question4","question5","question6","question7",
											 "question10","question11","question12"),
										date_vars("question2"), date_vars("question8a"), date_vars("question8b"),
										date_vars("question9") ),
						  html_form::form_table( array(
									__("Acute Post-Operative Pain?") => "<SELECT NAME=\"question1\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
														"</SELECT>",
									__("Date of Surgery") => fm_date_entry ("question2"),
									__("Chronic, Intractable Pain?") => "<SELECT NAME=\"question3\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
														"</SELECT>",
									__("Months patient had intractable pain?") => fm_number_select("question4",1,99,1),
									__("Prescribed Conditions?") => "<SELECT NAME=\"question5\">".
                                                        "<OPTION VALUE=\"1\">".__("Headache").
                                                        "<OPTION VALUE=\"2\">".__("Visceral abdominal pain").
                                                        "<OPTION VALUE=\"3\">".__("Pelvic pain").
                                                        "<OPTION VALUE=\"4\">".__("Temporomandibular joint (TMJ) pain").
                                                        "<OPTION VALUE=\"5\">".__("None of the above").
                                                        "</SELECT>", 
									__("Documentation of other failed treaments?") => "<SELECT NAME=\"question6\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
                                                        "</SELECT>",
									__("patient received a TENS trial?") => "<SELECT NAME=\"question7\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
                                                        "</SELECT>",
									__("TENS Trial Start Date") => fm_date_entry ("question8a"),
									__("TENS Trial End Date") => fm_date_entry ("question8b"),
									__("Reevaluated after TENS Trial") => fm_date_entry ("question9"),
									__("Usage") => "<SELECT NAME=\"question10\">".
                                                        "<OPTION VALUE=\"1\">".__("Daily").
                                                        "<OPTION VALUE=\"2\">".__("3 or More days per Week").
                                                        "<OPTION VALUE=\"3\">".__("2 days or less per Week").
                                                        "</SELECT>",
									__("Waranted long term use?") => "<SELECT NAME=\"question11\">".
                                                        "<OPTION VALUE=\"Y\">".__("Yes").
                                                        "<OPTION VALUE=\"N\">".__("No").
                                                        "<OPTION VALUE=\"D\">".__("Does Not Apply").
                                                        "</SELECT>",
									__("Number of TENS Leads") => "<SELECT NAME=\"question12\">".
                                                        "<OPTION VALUE=\"2\">".__("2 Leads").
                                                        "<OPTION VALUE=\"4\">".__("4 Leads").
                                                        "</SELECT>"
													  ) // end array
												) // end form_table
							); // end add page
*/

		} // end TENS form

		if ($certformnum == 0)
		{
			//add dummy page.
			$wizard->add_page(__("Dummy"),array("dummy"),"");

		}

		if (!$wizard->is_done() and !$wizard->is_cancelled())
		{
			$display_buffer .= "<CENTER>".$wizard->display()."</CENTER>";
			return;
		}

		if ($wizard->is_done())
		{
			global $certformdata;
			$certonfile = "Y";

			// all forms
			$certinitdate = fm_date_assemble("certinitdate");
			$certrevisedate = fm_date_assemble("certrevisedate");
			$certdatesigned = fm_date_assemble("certdatesigned");

			$certformdata = "0:".
							$certstatus.":".
							$certinitdate.":".
							$certrevisedate.":".
							$certlenneed.":".
							$certdatesigned.":".
							$certonfile.":".
							$certrental.":"; // offset 7
			
			if ($certformnum == F0602)
			{
				$question2 = fm_date_assemble("question2");
				$question8a = fm_date_assemble("question8a");
				$question8b = fm_date_assemble("question8b");
				$question9 = fm_date_assemble("question9");

				//$display_buffer .= "desc $certdesc<BR>";
				//$display_buffer .= "form $certformnum<BR>";

				// questions start at offset 8
				$certformdata .= $question1.":".$question2.":".$question3.":".$question4.":".
							$question5.":".$question6.":".$question7.":".$question8a.":".$question8b.
							":".$question9.":".$question10.":".$question11.":".$question12;
			}

			//$display_buffer .= "$query<BR>";

			//$display_buffer .= "data $certformdata<BR>";

			$query = $sql->insert_query($this->table_name,
							   $this->variables);

			$result = $sql->query($query);
			if ($result) { $display_buffer .= __("done")."."; }
			else        { $display_buffer .= __("ERROR");    }

			$display_buffer .= "
				</CENTER>
				<P>
				<CENTER>
				<A HREF=\"manage.php?id=$patient\"
				>".__("Manage Patient")."</A> <B>|</B>
				<A HREF=\"$this->page_name?module=$module&action=addform".
				"&patient=$patient".
				"\"
				>".__("Add Another")." "._($record_name)."</A>
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
			 >".__("Manage Patient")."</A>
			";
		} // end cancelled


		
	
		
	}

	function view () {
		global $display_buffer;
		global $sql,$patient;

		$query = "SELECT * FROM certifications WHERE certpatient='".prepare($patient)."'";
		//$display_buffer .= "$query<BR>";
		$result = $sql->query($query);

		$display_buffer .= freemed_display_itemlist($result,
								 $this->page_name,
								 array(__("Desc") => "certdesc",
									   __("Type") => "certtype",
									   __("Form") => "certformnum"),
								 array(__("None"),"","")
								);
									
	} // end function DmeposcertsModule->view()

} // end class DmeposcertsModule

register_module ("DmeposcertsModule");

?>
