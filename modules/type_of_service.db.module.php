<?php
  // $Id$
  // note: type of service (TOS) database module
  // code: adam b (gdrago23@yahoo.com) -- modified a lot
  // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class TypeOfServiceMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Type of Service Maintenance";
	var $MODULE_AUTHOR = "Adam (gdrago23@yahoo.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Type of Service";
	var $table_name  = "tos";
	var $order_field = "tosname,tosdescrip";

	var $variables = array (
			"tosname",
			"tosdescrip",
			"tosdtadd",
			"tosdtmod"
	);

	function TypeOfServiceMaintenance () {
		$this->table_definition = array (
			'tosname' => SQL__VARCHAR(75),
			'tosdescrip' => SQL__VARCHAR(200),
			'tosdtadd' => SQL__DATE,
			'tosdtmod' => SQL__DATE,
			'id' => SQL__SERIAL
		);
	
		global $tosdtmod; $tosdtmod = date("Y-m-d");

		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor TypeOfServiceMaintenance	

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT tosname,tosdescrip,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions().
				"ORDER BY ".prepare($this->order_field)),
			$this->page_name,
			array (
				__("Code") => "tosname",
				__("Description") => "tosdescrip"
			),
			array ("", __("NO DESCRIPTION")), "", "t_page"
		);
	} // end function module->view

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
  		if ($action=="modform") { 
    		$result = $sql->query("SELECT tosname,tosdescrip FROM $this->table_name
				WHERE ( id = '$id' )");
			$r = $sql->fetch_array($result); // dump into array r[]
			extract ($r);
		} // if loading values

		// display itemlist first
		$this->view ();

		$display_buffer .= "
			<form ACTION=\"$this->page_name\" METHOD=\"POST\">
			<input TYPE=\"HIDDEN\" NAME=\"tosdtadd\"".date('Y-m-d')."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
			($action=="modform" ? "mod" : "add")."\"/>\n";
		if ($action=="modform")
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>\n";

		$display_buffer .= html_form::form_table(array(
			__("Type of Service") =>
			html_form::text_widget("tosname", 20, 75),

			__("Description") =>
			html_form::text_widget("tosdescrip", 25, 200)
		)).
			"<div ALIGN=\"CENTER\">\n".
			"<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".(
			 ($action=="modform") ? __("Modify") : __("Add"))."\"/>
			 <input TYPE=\"RESET\" VALUE=\"".__("Remove Changes")."\" ".
			 "class=\"button\"/>
			</div></form>
		";
	} // end function TypeOfServiceMaintenance->form

} // end of class TypeOfServiceMaintenance

register_module ("TypeOfServiceMaintenance");

?>
