<?php
 // $Id$
 // desc: allergies
 // lic : GPL, v2

if (!defined("__ALLERGIES_EMR_MODULE_PHP__")) {

define ('__ALLERGIES_EMR_MODULE_PHP__', true);

class allergiesModule extends freemedEMRModule {

	var $MODULE_NAME = "Allergies";
	var $MODULE_VERSION = "0.1";

	var $record_name = "Allergies";
	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function allergiesModule () {
		// call parent constructor
		$this->freemedEMRModule();
	} // end constructor allergiesModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS[this_patient])) {
			global $this_patient;
		} else {
			$this_patient = CreateObject('FreeMED.Patient', $patient);
		}

		// Extract allergies
		$allergies = $this_patient->local_record["ptallergies"];

		// Check to see if it's set (show listings if it is)
		if (strlen($allergies)>3) {
			// Form an array
			$my_allergies = sql_expand($allergies);
			if (!is_array($my_allergies)) {
				$my_allergies = array ($my_allergies);
			}

			// Show menu bar
			$buffer .= "
			<TABLE BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<TR CLASS=\"menubar_info\">
			<TD>"._("Allergy")."</TD>
			<TD>"._("Action")."</TD>
			</TR>
			";

			// Loop thru and display allergies
			foreach ($my_allergies AS $k => $v) {
				$buffer .= "
				<TR>
				<TD ALIGN=\"LEFT\"><SMALL>".prepare($v)."</SMALL></TD>
				<TD ALIGN=\"LEFT\">".
				template::summary_delete_link($this,
				"module_loader.php?module=allergiesModule&action=del&patient=".urlencode($patient)."&return=manage&id=".urlencode($k)).
				"</TD></TR>
				";
			} // end looping thru allergies

			// End table
			$buffer .= "
			</TABLE>
			";
		}

		$buffer .= "
			<DIV ALIGN=\"CENTER\">
			<FORM ACTION=\"module_loader.php\" METHOD=\"POST\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".
			prepare($this->MODULE_CLASS)."\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
			"add\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"return\" VALUE=\"".
			"manage\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".
			prepare($patient)."\">
			".html_form::text_widget("allergy", 20, 50)."
			<INPUT TYPE=\"SUBMIT\" VALUE=\""._("Add")."\">
			</FORM>
			</DIV>
			";
		return $buffer;
	} // end function allergiesModule->summary

	function add () {
		global $display_buffer, $return, $patient, $allergy;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get allergies, and extract to an array
		$allergies = $this_patient->local_record["ptallergies"];
		$my_allergies = sql_expand($allergies);
		if (!is_array($my_allergies)) {
			$my_allergies = array ($my_allergies);
		}

		// Add a new member to the array
		$my_allergies[] = $allergy;

		// Remove empties
		foreach ($my_allergies AS $k => $v) {
			if (empty($v)) unset($my_allergies[$k]);
		}

		// Recombine into a single variable
		$allergies = sql_squash($my_allergies);

		$display_buffer .= "
		<P><CENTER>
		"._("Adding")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptallergies" => $allergies ),
			array ( "id" => $patient )
		);
		$result = $sql->query($query);

		// Check for result, etc
		if ($result) { $display_buffer .= _("done");  }
		 else        { $display_buffer .= _("ERROR"); }
		$display_buffer .= "</CENTER>\n";

		// If we came from patient management (EMR), return there
		if ($return=="manage") {
			Header("Location: manage.php?id=".urlencode($patient));
			die("");
		}
	} // end function allergiesModule->add()

	function del() { $this->delete(); }
	function delete () {
		global $display_buffer, $return, $patient, $id;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get allergies, and extract to an array
		$allergies = $this_patient->local_record["ptallergies"];
		$my_allergies = sql_expand($allergies);
		if (!is_array($my_allergies)) {
			$my_allergies = array ($my_allergies);
		}

		// Unset the proper member of the array
		unset ($my_allergies[$id]);

		// Recombine into a single variable
		$allergies = sql_squash($my_allergies);

		$display_buffer .= "
		<P><CENTER>
		"._("Deleting")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptallergies" => $allergies ),
			array ( "id" => $patient )
		);
		$result = $sql->query($query);

		// Check for result, etc
		if ($result) { $display_buffer .= _("done");  }
		 else        { $display_buffer .= _("ERROR"); }
		$display_buffer .= "</CENTER>\n";

		// If we came from patient management (EMR), return there
		if ($return=="manage") {
			Header("Location: manage.php?id=".urlencode($patient));
			die("");
		}
	} // end function allergiesModule->delete()

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= "TODO: Listing for allergies here\n";
	} // end function allergiesModule->view()

} // end class allergiesModule

register_module ("allergiesModule");

} // end if not defined

?>
