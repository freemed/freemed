<?php
 // $Id$
 // desc: allergies
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class AllergiesModule extends EMRModule {

	var $MODULE_NAME = "Allergies";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Allergies";
	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function AllergiesModule () {
		// call parent constructor
		$this->EMRModule();
	} // end constructor AllergiesModule

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
			<TD>".__("Allergy")."</TD>
			<TD>".__("Action")."</TD>
			</TR>
			";

			// Loop thru and display allergies
			foreach ($my_allergies AS $k => $v) {
				$buffer .= "
				<TR>
				<TD ALIGN=\"LEFT\"><SMALL>".prepare($v)."</SMALL></TD>
				<TD ALIGN=\"LEFT\">".
				template::summary_delete_link($this,
				"module_loader.php?module=AllergiesModule&action=del&patient=".urlencode($patient)."&return=manage&id=".urlencode($k)).
				"</TD></TR>
				";
			} // end looping thru allergies

			// End table
			$buffer .= "
			</TABLE>
			";
		}

		$buffer .= "
			<div ALIGN=\"CENTER\">
			<form ACTION=\"module_loader.php\" METHOD=\"POST\">
			<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".
			prepare($this->MODULE_CLASS)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
			"add\"/>
			<input TYPE=\"HIDDEN\" NAME=\"return\" VALUE=\"".
			"manage\"/>
			<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".
			prepare($patient)."\"/>
			".html_form::text_widget("allergy", 20, 50)."
			<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\" class=\"button\"/>
			</form>
			</div>
			";
		return $buffer;
	} // end function AllergiesModule->summary

	function summary_bar () { }

	function add () {
		global $display_buffer, $return, $patient, $allergy;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

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
		".__("Adding")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptallergies" => $allergies ),
			array ( "id" => $patient )
		);
		$result = $sql->query($query);

		// Check for result, etc
		if ($result) { $display_buffer .= __("done");  }
		 else        { $display_buffer .= __("ERROR"); }
		$display_buffer .= "</CENTER>\n";

		// If we came from patient management (EMR), return there
		if ($return=="manage") {
			Header("Location: manage.php?id=".urlencode($patient));
			die("");
		}
	} // end function AllergiesModule->add()

	function del() { $this->delete(); }
	function delete () {
		global $display_buffer, $return, $patient, $id;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

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
		".__("Deleting")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptallergies" => $allergies ),
			array ( "id" => $patient )
		);
		$result = $sql->query($query);

		// Check for result, etc
		if ($result) { $display_buffer .= __("done");  }
		 else        { $display_buffer .= __("ERROR"); }
		$display_buffer .= "</CENTER>\n";

		// If we came from patient management (EMR), return there
		if ($return=="manage") {
			Header("Location: manage.php?id=".urlencode($patient));
			die("");
		}
	} // end function AllergiesModule->delete()

	function view() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		$display_buffer .= "TODO: Listing for allergies here\n";
	} // end function AllergiesModule->view()

} // end class AllergiesModule

register_module ("AllergiesModule");

?>
