<?php
 // $Id$
 // desc: quickmeds simple medication record
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class QuickmedsModule extends EMRModule {

	var $MODULE_NAME = "Medications";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Medications";
	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function QuickmedsModule () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor QuickmedsModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS[this_patient])) {
			global $this_patient;
		} else {
			$this_patient = CreateObject('FreeMED.Patient', $patient);
		}

		// Extract quickmeds
		$quickmeds = $this_patient->local_record["ptquickmeds"];

		// Check to see if it's set (show listings if it is)
		if (strlen($quickmeds)>3) {
			// Form an array
			$my_quickmeds = sql_expand($quickmeds);
			if (!is_array($my_quickmeds)) {
				$my_quickmeds = array ($my_quickmeds);
			}

			// Show menu bar
			$buffer .= "
			<table BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<tr CLASS=\"menubar_info\">
			<td><b>".__("Medication")."</b></td>
			<td><b>".__("Action")."</b></td>
			</tr>
			";

			// Loop thru and display quickmeds
			foreach ($my_quickmeds AS $k => $v) {
				$buffer .= "
				<tr>
				<td ALIGN=\"LEFT\"><small>".prepare($v)."</small></td>
				<td ALIGN=\"LEFT\">".
				template::summary_delete_link($this,
				"module_loader.php?module=QuickmedsModule&".
				"action=del&patient=".urlencode($patient)."&".
				"return=manage&id=".urlencode($k))."
				</td>
				</tr>
				";
			} // end looping thru quickmeds

			// End table
			$buffer .= "
			</table>
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
			".html_form::text_widget("med", 20, 50)."
			<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\" class=\"button\"/>
			</form>
			</div>
			";
		return $buffer;
	} // end function QuickmedsModule->summary

	function summary_bar() { }

	function add () {
		global $display_buffer, $return, $patient, $med;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get quickmeds, and extract to an array
		$quickmeds = $this_patient->local_record["ptquickmeds"];
		$my_quickmeds = sql_expand($quickmeds);
		if (!is_array($my_quickmeds)) {
			$my_quickmeds = array ($my_quickmeds);
		}

		// Add a new member to the array
		$my_quickmeds[] = $med;

		// Remove empties
		foreach ($my_quickmeds AS $k => $v) {
			if (empty($v)) unset($my_quickmeds[$k]);
		}

		// Recombine into a single variable
		$quickmeds = sql_squash($my_quickmeds);

		$display_buffer .= "
		<P><CENTER>
		".__("Adding")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptquickmeds" => $quickmeds ),
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
	} // end function QuickmedsModule->add()

	function del() { $this->delete(); }
	function delete () {
		global $display_buffer, $return, $patient, $id;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get quickmeds, and extract to an array
		$quickmeds = $this_patient->local_record["ptquickmeds"];
		$my_quickmeds = sql_expand($quickmeds);
		if (!is_array($my_quickmeds)) {
			$my_quickmeds = array ($my_quickmeds);
		}

		// Unset the proper member of the array
		unset ($my_quickmeds[$id]);

		// Recombine into a single variable
		$quickmeds = sql_squash($my_quickmeds);

		$display_buffer .= "
		<P><CENTER>
		".__("Deleting")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptquickmeds" => $quickmeds ),
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
	} // end function QuickmedsModule->delete()

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= "TODO: Listing for quickmeds here\n";
	} // end function QuickmedsModule->view()

} // end class QuickmedsModule

register_module ("QuickmedsModule");

?>
