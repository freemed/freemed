<?php
 // $Id$
 // desc: previous operations summary module
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class PreviousOperationsModule extends EMRModule {

	var $MODULE_NAME = "Previous Operations";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Previous Operations";
	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function PreviousOperationsModule () {
		// call parent constructor
		$this->EMRModule();
	} // end constructor PreviousOperationsModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS[this_patient])) {
			global $this_patient;
		} else {
			$this_patient = CreateObject('FreeMED.Patient', $patient);
		}

		// Extract ops
		$ops = $this_patient->local_record["ptops"];

		// Check to see if it's set (show listings if it is)
		if (strlen($ops)>3) {
			// Form an array
			$my_ops = sql_expand($ops);
			if (!is_array($my_ops)) {
				$my_ops = array ($my_ops);
			}

			// Show menu bar
			$buffer .= "
			<TABLE BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<TR CLASS=\"menubar_info\">
			<TD>"._("Operation")."</TD>
			<TD>"._("Action")."</TD>
			</TR>
			";

			// Loop thru and display ops 
			foreach ($my_ops AS $k => $v) {
				$buffer .= "
				<TR>
				<TD ALIGN=\"LEFT\"><SMALL>".prepare($v)."</SMALL></TD>
				<TD ALIGN=\"LEFT\">".
				template::summary_delete_link($this,
				"module_loader.php?module=PreviousOperationsModule&action=del&patient=".urlencode($patient)."&return=manage&id=".urlencode($k)).
				"</TD></TR>
				";
			} // end looping thru ops 

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
			".html_form::text_widget("op", 20, 50)."
			<input TYPE=\"SUBMIT\" VALUE=\""._("Add")."\" class=\"button\"/>
			</form>
			</div>
			";
		return $buffer;
	} // end function PreviousOperationsModule->summary

	function summary_bar() { }

	function add () {
		global $display_buffer, $return, $patient, $op;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get ops, and extract to an array
		$ops = $this_patient->local_record["ptops"];
		$my_ops = sql_expand($ops);
		if (!is_array($my_ops)) {
			$my_ops = array ($my_ops);
		}

		// Add a new member to the array
		$my_ops[] = $op;

		// Remove empties
		foreach ($my_ops AS $k => $v) {
			if (empty($v)) unset($my_ops[$k]);
		}

		// Recombine into a single variable
		$ops = sql_squash($my_ops);

		$display_buffer .= "
		<P><CENTER>
		"._("Adding")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptops" => $ops ),
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
	} // end function PreviousOperationsModule->add()

	function del() { $this->delete(); }
	function delete () {
		global $display_buffer, $return, $patient, $id;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		// Get patient object
		$this_patient = CreateObject('FreeMED.Patient', $patient);

		// Get ops, and extract to an array
		$ops = $this_patient->local_record["ptops"];
		$my_ops = sql_expand($ops);
		if (!is_array($my_ops)) {
			$my_ops = array ($my_ops);
		}

		// Unset the proper member of the array
		unset ($my_ops[$id]);

		// Recombine into a single variable
		$ops = sql_squash($my_ops);

		$display_buffer .= "
		<P><CENTER>
		"._("Deleting")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptops" => $ops ),
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
	} // end function PreviousOperationsModule->delete()

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= "TODO: Listing for operations here\n";
	} // end function PreviousOperationsModule->view()

} // end class PreviousOperationsModule

register_module ("PreviousOperationsModule");

?>
