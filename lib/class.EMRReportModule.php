<?php
 // $Id$
 // desc: module prototype. These modules are used to produce
 // a single report for the patient being viewed
 // lic : GPL, v2

LoadObjectDependency('FreeMED.BaseModule');

class EMRReportModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Electronic Medical Record Report";
	var $CATEGORY_VERSION = "0.2";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;

	// contructor method
	function EMRReportModule () {
		// call parent constructor
		$this->BaseModule();
	} // end function EMRReportModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module, $patient;
		if (!isset($module)) 
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		if ($patient < 1) 
		{
			trigger_error( "No Patient Defined", E_ERROR);
		}
		// check access to patient
		if (!freemed::check_access_for_patient($patient)) 
		{
			trigger_error("User not Authorized for this function", E_USER_ERROR);
		}
		return true;

	} // end function check_vars

		function header($nullvar="") {
		global $display_buffer;
            freemed_open_db();
            // don't display the box top
        }
        function footer($nullvar="") {
		global $display_buffer;
            // dont display the bottom
            return;
        }

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $patient;

		if (!isset($this->this_patient))
			$this->this_patient = CreateObject('FreeMED.Patient', $patient);
		if (!isset($this->this_user))
			$this->this_user    = CreateObject('FreeMED.User');

		// display universal patient box
		// no box used since these need to be printable
		//$display_buffer .= freemed::patient_box($this->this_patient)."<p/>\n";

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

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function add
	// - addition routine
	function add () { $this->_add(); }
	function _add () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;
	
		$display_buffer .= "
			<P><CENTER>
			"._("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { $display_buffer .= "<B>"._("done").".</B>\n"; }
		 else		 { $display_buffer .= "<B>"._("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module&patient=$patient\"
				>"._("back")."</A>
			</CENTER>
		";

	} // end function _add

	// function del
	// - delete function
	function del () { $this->_del(); }
	function _del () {
		global $display_buffer;
		global $id, $sql;
		$display_buffer .= "<P ALIGN=CENTER>".
			_("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= _("done"); }
		 else		 { $display_buffer .= "<FONT COLOR=\"#ff0000\">"._("ERROR")."</FONT>"; }
		$display_buffer .= "</P>\n";
	} // end function _del

	// function mod
	// - modification function
	function mod () { $this->_mod(); }
	function _mod () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;
	
		$display_buffer .= "
			<P><CENTER>
			"._("Modifying")." ...
		";

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id"	=>		$id
				)
			)
		);

		if ($result) { $display_buffer .= "<B>"._("done").".</B>\n"; }
		 else { $display_buffer .= "<B>"._("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module&patient=$patient\"
				>"._("back")."</A>
			</CENTER>
		";

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

} // end class EMRReportModule

?>
