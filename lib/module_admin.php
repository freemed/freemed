<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_ADMIN_PHP__")) {

define ('__MODULE_ADMIN_PHP__', true);

// class freemedAdminModule extends freeMedmodule
class freemedAdminModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Freemed Admin";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;

	// contructor method
	function freemedAdminModule () {
		// call parent constructor
		$this->freemedModule();
	} // end function freemedAdminModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module)) 
		{
			trigger_error("Module not Defined", E_ERROR);
		}
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
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
					freemed_display_box_bottom ();
					freemed_display_html_bottom ();
					die ("");
				}
				$this->modform();
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
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		echo "
			<P><CENTER>
			<$STDFONT_B>"._("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { echo "<B>"._("done").".</B>\n"; }
		 else        { echo "<B>"._("ERROR")."</B>\n"; }

		echo "
			<$STDFONT_E></CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module\"
				><$STDFONT_B>"._("back")."<$STDFONT_E></A>
			</CENTER>
		";
	} // end function _add
	function add () { $this->_add(); }

	// function _del
	// - only override this if you *really* have something weird to do
	function _del () {
		global $STDFONT_B, $STDFONT_E, $id, $sql, $module;
		echo "<P ALIGN=CENTER>".
			"<$STDFONT_B>"._("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { echo _("done"); }
		 else        { echo "<FONT COLOR=\"#ff0000\">"._("ERROR")."</FONT>"; }
		echo "<$STDFONT_E></P>\n";
		echo "<P ALIGN=CENTER><$STDFONT_B><A HREF=\"".$this->page_name.
			"?module=".urlencode($module)."\">"._("back")."</A></P>\n";
	} // end function _del
	function del () { $this->_del(); }

	// function _mod
	// - modification routine (override if neccessary)
	function _mod () {
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		echo "
			<P><CENTER>
			<$STDFONT_B>"._("Modifying")." ...
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

		if ($result) { echo "<B>"._("done").".</B>\n"; }
		 else        { echo "<B>"._("ERROR")."</B>\n"; }

		echo "
			<$STDFONT_E></CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module\"
				><$STDFONT_B>"._("back")."<$STDFONT_E></A>
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
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		echo freemed_display_itemlist (
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
	// - used to initially create SQL table
	function create_table () {
		if (!isset($this->table_definition)) return false;
		$query = $sql->create_table_query(
			$this->table_name,
			$this->table_definition
		);
		$result = $sql->query ($query);
		return !empty($result);
	} // end function create_table

} // end class freemedAdminModule

} // end if not defined

?>
