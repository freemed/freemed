<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BaseModule');

class UtilityModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Billing";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;
	var $patient_forms;  // array of patient id's that we processed
	var $patient_procs;  // 2d array [patient][ids of procs processed]

	// contructor method
	function UtilityModule () {
		// call parent constructor
		$this->BaseModule();
	} // end function UtilityModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action;

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
				global $id;
				if (empty($id) or ($id<1)) {
					template_display();
				}
				$this->modform();
				break;

			case "transport":
				return $this->transport();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function _add
	// - addition routine (can be overridden if need be)
	function _add () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$display_buffer .= "
			<P><CENTER>
			".__("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { $display_buffer .= "<B>".__("done").".</B>\n"; }
		 else        { $display_buffer .= "<B>".__("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>".__("back")."</A>
			</CENTER>
		";
	} // end function _add
	function add () { $this->_add(); }

	// function _del
	// - only override this if you *really* have something weird to do
	function _del () {
		global $display_buffer;
		global $id, $sql;
		$display_buffer .= "<P ALIGN=CENTER>".
			__("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= __("done"); }
		 else        { $display_buffer .= "<FONT COLOR=\"#ff0000\">".__("ERROR")."</FONT>"; }
		$display_buffer .= "</P>\n";
	} // end function _del
	function del () { $this->_del(); }

	// function _mod
	// - modification routine (override if neccessary)
	function _mod () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$display_buffer .= "
			<P><CENTER>
			".__("Modifying")." ...
		";

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id"	=>	$id
				)
			)
		);

		if ($result) { $display_buffer .= "<B>".__("done").".</B>\n"; }
		 else        { $display_buffer .= "<B>".__("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module\"
				>".__("back")."</A>
			</CENTER>
		";
	} // end function _mod
	function mod() { $this->_mod(); }

	// function add/modform
	// - wrappers for form
	function addform () { $this->form(); }
	function modform () { $this->form(); }

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

} // end class BillingModule

?>
