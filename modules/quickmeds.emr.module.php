<?php
 // $Id$
 // desc: quickmeds simple medication record
 // lic : GPL, v2

if (!defined("__QUICKMEDS_EMR_MODULE_PHP__")) {

define ('__QUICKMEDS_EMR_MODULE_PHP__', true);

class quickmedsModule extends freemedEMRModule {

	var $MODULE_NAME = "QuickMeds";
	var $MODULE_VERSION = "0.1";

	var $record_name = "QuickMeds";
	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function quickmedsModule () {
		// call parent constructor
		$this->freemedEMRModule();
	} // end constructor quickmedsModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS[this_patient])) {
			global $this_patient;
		} else {
			$this_patient = new Patient ($patient);
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
			<TABLE BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<TR CLASS=\"menubar_info\">
			<TD>"._("Medication")."</TD>
			<TD>"._("Action")."</TD>
			</TR>
			";

			// Loop thru and display quickmeds
			foreach ($my_quickmeds AS $k => $v) {
				$buffer .= "
				<TR>
				<TD ALIGN=\"LEFT\"><SMALL>".prepare($v)."</SMALL></TD>
				<TD ALIGN=\"LEFT\"><A HREF=\"module_loader.php?module=quickmedsModule&action=del&patient=".urlencode($patient)."&return=manage&id=".urlencode($k)."\"".
				"><SMALL>"._("Delete")."</SMALL></A></TD>
				</TR>
				";
			} // end looping thru quickmeds

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
			".html_form::text_widget("med", 20, 50)."
			<INPUT TYPE=\"SUBMIT\" VALUE=\""._("Add")."\">
			</FORM>
			</DIV>
			";
		return $buffer;
	} // end function quickmedsModule->summary

	function add () {
		global $display_buffer, $return, $patient, $med;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global ${$k};

		// Get patient object
		$this_patient = new Patient ($patient);

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
		"._("Adding")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptquickmeds" => $quickmeds ),
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
	} // end function quickmedsModule->add()

	function del() { $this->delete(); }
	function delete () {
		global $display_buffer, $return, $patient, $id;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		// Get patient object
		$this_patient = new Patient ($patient);

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
		"._("Deleting")." ...
		";

		// Update the proper table
		$query = $sql->update_query (
			"patient",
			array ( "ptquickmeds" => $quickmeds ),
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
	} // end function quickmedsModule->delete()

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		$display_buffer .= "TODO: Listing for quickmeds here\n";
	} // end function quickmedsModule->view()

} // end class quickmedsModule

register_module ("quickmedsModule");

} // end if not defined

?>
