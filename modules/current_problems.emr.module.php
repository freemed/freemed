<?php
 // $Id$
 // desc: current list of problems
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class CurrentProblemsModule extends EMRModule {

	var $MODULE_NAME = "Current Problems";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Current Problems";
	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function CurrentProblemsModule () {
		// call parent constructor
		$this->EMRModule();
	} // end constructor CurrentProblemsModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS[this_patient])) {
			global $this_patient;
		} else {
			$this_patient = CreateObject('FreeMED.Patient', $patient);
		}

		// Extract problems
		$problems = $this_patient->local_record["ptproblems"];

		// Check to see if it's set (show listings if it is)
		if (strlen($problems)>3) {
			// Form an array
			$my_problems = sql_expand($problems);
			if (!is_array($my_problems)) {
				$my_problems = array ($my_problems);
			}

			// Show menu bar
			$buffer .= "
			<TABLE BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<TR CLASS=\"menubar_info\">
			<TD>"._("Problem")."</TD>
			<TD>"._("Action")."</TD>
			</TR>
			";

			// Loop thru and display problems
			foreach ($my_problems AS $k => $v) {
				$buffer .= "
				<TR>
				<TD ALIGN=\"LEFT\"><SMALL>".prepare($v)."</SMALL></TD>
				<TD ALIGN=\"LEFT\">".
				template::summary_delete_link($this,
				"module_loader.php?".
				"module=CurrentProblemsModule&".
				"action=del&patient=".urlencode($patient).
				"&return=manage&id=".urlencode($k))."</TD>
				</TR>
				";
			} // end looping thru problems

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
			".html_form::text_widget("problem", 20, 50)."
			<INPUT TYPE=\"SUBMIT\" VALUE=\""._("Add")."\">
			</FORM>
			</DIV>
			";
		return $buffer;
	} // end function CurrentProblemsModule->summary

	function add () {
		global $display_buffer, $return, $patient, $problem;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get problems, and extract to an array
		$problems = $this_patient->local_record["ptproblems"];
		$my_problems = sql_expand($problems);
		if (!is_array($my_problems)) {
			$my_problems = array ($my_problems);
		}

		// Add a new member to the array
		$my_problems[] = $problem;

		// Remove empties
		foreach ($my_problems AS $k => $v) {
			if (empty($v)) unset($my_problems[$k]);
		}

		// Recombine into a single variable
		$problems = sql_squash($my_problems);

		$display_buffer .= "
		<P><CENTER>
		"._("Adding")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptproblems" => $problems ),
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
	} // end function CurrentProblemsModule->add()

	function del() { $this->delete(); }
	function delete () {
		global $display_buffer, $return, $patient, $id;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get problems, and extract to an array
		$problems = $this_patient->local_record["ptproblems"];
		$my_problems = sql_expand($problems);
		if (!is_array($my_problems)) {
			$my_problems = array ($my_problems);
		}

		// Unset the proper member of the array
		unset ($my_problems[$id]);

		// Recombine into a single variable
		$problems = sql_squash($my_problems);

		$display_buffer .= "
		<P><CENTER>
		"._("Deleting")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptproblems" => $problems ),
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
	} // end function CurrentProblemsModule->delete()

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= "TODO: Listing for problems here\n";
	} // end function CurrentProblemsModule->view()

} // end class CurrentProblemsModule

register_module ("CurrentProblemsModule");

?>
