<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_EMR_PHP__")) {

define ('__MODULE_EMR_PHP__', true);

// class freemedEMRModule
class freemedEMRModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Electronic Medical Record";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;
	var $patient_field; // the field that links to the patient ID

	// contructor method
	function freemedEMRModule () {
		// call parent constructor
		$this->freemedModule();
	} // end function freemedEMRModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module, $patient;
		if (!isset($module)) {
			trigger_error("No Module Defined", E_ERROR);
		}
		if ($patient < 1) {
			trigger_error( "No Patient Defined", E_ERROR);
		}
		// check access to patient
		if (!freemed_check_access_for_patient($patient)) {
			trigger_error("User not Authorized for this function", E_USER_ERROR);
		}
		return true;

	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $patient, $submit;

		if (!isset($this->this_patient))
			$this->this_patient = new Patient ($patient);
		if (!isset($this->this_user))
			$this->this_user    = new User ();

		// display universal patient box
		$display_buffer .= freemed_patient_box($this->this_patient)."<P>\n";

		// Handle cancel action from submit
		if ($submit==_("Cancel")) {
			Header("Location: ".$this->page_name.
				"?module=".urlencode($this->MODULE_CLASS).
				"&patient=".urlencode($patient));
			die("");
		}

		switch ($action) {
			case "add":
				$this->add();
				break;

			case "addform":
				$this->addform();
				break;

			case "del":
			case "delete":
				$this->del();
				break;

			case "mod":
			case "modify":
				$this->mod();
				break;

			case "modform":
				$this->modform();
				break;

			case "display";
				$this->display();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main
	
	function display_message () {
		global $display_buffer;
		if (isset($this->message)) {
			$display_buffer .= "
			<P>
			<CENTER>
			<B>".prepare($this->message)."</B>
			</CENTER>
			";
		}
	} // end function display_message

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function add
	// - addition routine
	function add () { $this->_add(); }
	function _add () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) $this->message = _("Record added successfully.");
		 else $this->message = _("Record addition failed.");
		$this->view(); $this->display_message();
	} // end function _add

	// function del
	// - delete function
	function del () { $this->_del(); }
	function _del () {
		global $display_buffer;
		global $id, $sql;
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) $this->message = _("Record deleted successfully.");
		 else $this->message = _("Record deletion failed.");
		$this->view(); $this->display_message();
	} // end function _del

	// function mod
	// - modification function
	function mod () { $this->_mod(); }
	function _mod () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;
		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id" => $id
				)
			)
		);
		if ($result) $this->message = _("Record modified successfully.");
		 else $this->message = _("Record modification failed.");
		$this->view(); $this->display_message();
	} // end function _mod

	// function add/modform
	// - wrappers for form
	function addform () { $this->form(); }
	function modform () { $this->form(); }

	// function display
	// by default, a wrapper for view
	function display () { $this->view(); }

	// function form
	// - add/mod form stub
	function form () {
		global $display_buffer;
		global $action, $id, $sql;

		if (is_array($this->form_vars)) {
			reset ($this->form_vars);
			while (list ($k, $v) = each ($this->form_vars)) global $$v;
		} // end if is array

		switch ($action) {
			case "addform":
				break;

			case "modform":
				$result = $sql->query ("SELECT * FROM ".$this->table_name.
					" WHERE ( id = '".prepare($id)."' )");
				$r = $sql->fetch_array ($result);
				extract ($r);
				break;
		} // end of switch action
		
	} // end function form

	// function summary
	// - show summary view of last few items
	function summary ($patient, $items) {
		global $sql, $display_buffer, $patient;

		// get last $items results
		$query = "SELECT *".
			( (count($this->summary_query)>0) ? 
			",".join(",", $this->summary_query)." " : " " ).
			"FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY id DESC LIMIT ".addslashes($items);
		$result = $sql->query($query);

		// Check to see if there *are* any...
		if ($sql->num_rows($result) < 1) {
			// If not, let the world know
			$buffer .= "<B>"._("NONE")."</B>\n";
		} else { // checking for results
			// Or loop and display
			$buffer .= "
			<TABLE WIDTH=\"100%\" CELLSPACING=0
			 CELLPADDING=2 BORDER=0>
			<TR>
			";
			foreach ($this->summary_vars AS $k => $v) {
				$buffer .= "
				<TD VALIGN=MIDDLE CLASS=\"menubar_info\">
				<B>".prepare($k)."</B>
				</TD>
				";
			} // end foreach summary_vars
			$buffer .= "
				<TD VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<B>"._("Action")."</B>
				</TD>
			</TR>
			";
			while ($r = $sql->fetch_array($result)) {
				// Pull out all variables
				extract ($r);

				// Use $this->summary_vars
				$buffer .= "
				<TR VALIGN=\"MIDDLE\">
				";
				foreach ($this->summary_vars AS $k => $v) {
					$buffer .= "
					<TD VALIGN=\"MIDDLE\">
					<SMALL>".prepare(${$v})."</SMALL>
					</TD>
					";
				} // end looping through summary vars
				$buffer .= "
				<TD VALIGN=\"MIDDLE\">
				<A HREF=\"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=modform&id=$r[id]\"
				><SMALL>"._("Modify")."</SMALL></A>
				".( $this->summary_view_link ?
				"| <A HREF=\"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=display&id=$r[id]\"
				><SMALL>"._("View")."</SMALL></A>" : "" )."
				</TD>
				</TR>
				";
			} // end of loop and display
			$buffer .= "</TABLE>\n";
		} // checking if there are any results

		// Send back the buffer
		return $buffer;
	} // end function summary

	// function view
	// - view stub
	function view () {
		global $display_buffer;
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		$display_buffer .= freemed_display_itemlist (
			$result,
			"module_loader.php",
			$this->form_vars,
			array ("", _("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

	// override _setup with create_table
	function _setup () {
		if (!$this->create_table()) return false;
		return freemed_import_stock_data ($this->record_name);
	} // end function _setup

	// function create_table
	// - used to initally create SQL table
	function create_table () {
		if (!isset($this->table_definition)) return false;
		$query = $sql->create_table_query(
			$this->table_name,
			$this->table_definition
		);
		$result = $sql->query($query);
		return !empty($query);
	} // end function create_table

	// this function exports XML for the entire patient record
	function xml_export () {
		global $display_buffer;
		global $patient;

		if (!isset($this->this_patient))
			$this->this_patient = new Patient ($patient);

		return $this->xml_generate($this->this_patient);
	} // end function freemedEMRModule->xml_export

	function xml_generate ($patient) { return ""; } // stub 

} // end class freemedEMRModule

} // end if not defined

?>
