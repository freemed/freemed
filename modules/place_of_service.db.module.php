<?php
  // $Id$
  // note: place of service (TOS) database module
  // code: adam b (gdrago23@yahoo.com) -- modified a lot
  // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class PlaceOfServiceMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Place of Service Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Place of Service";
	var $table_name  = "pos";
	var $order_field = "posname,posdescrip";

	var $variables = array (
			"posname",
			"posdescrip",
			"posdtadd",
			"posdtmod"
	);

	function PlaceOfServiceMaintenance () {
		// run constructor
		$this->MaintenanceModule();
		global $posdtmod, $posdtadd;
		$posdtmod = $posdtadd = date("Y-m-d");
	} // end constructor PlaceOfServiceMaintenance	

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) global ${$k};

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT posname,posdescrip,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".prepare($this->order_field)
			),
			$this->page_name,
			array (
				_("Code") => "posname",
				_("Description") => "posdescrip"
			),
			array ("", _("NO DESCRIPTION"))
		);
	} // end function module->view

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) global ${$k};
  		if ($action=="modform") { 
    			$result = $sql->query("SELECT * FROM ".$this->table_name." ".
				"WHERE ( id = '$id' )");
			$r = $sql->fetch_array($result); // dump into array r[]
			extract ($r);
		} // if loading values
		if ($action=="addform") {
			$GLOBALS['posdtadd'] = date("Y-m-d");
		}

		// display itemlist first
		$this->view ();

		$display_buffer .= "
			<form ACTION=\"$this->page_name\" METHOD=\"POST\">
			<input TYPE=\"HIDDEN\" NAME=\"posdtadd\"".prepare($posdtadd)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
			($action=="modform" ? "mod" : "add")."\"/>\n";
		if ($action=="modform")
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>\n";

		$display_buffer .= html_form::form_table(array(
			_("Place of Service") =>
			html_form::text_widget('posname', 20, 75),

			_("Description") =>
			html_form::text_widget('posname', 25, 200),
		));

		$display_buffer .= "
			<div ALIGN=\"CENTER\">
			<input TYPE=\"SUBMIT\" VALUE=\"".(
			 ($action=="modform") ? _("Modify") : _("Add"))."\"/>
			<input TYPE=\"RESET\" VALUE=\""._("Remove Changes")."\"/>
			</div>
			</form>
		";
		if ($action=="modform") $display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\">
			<a HREF=\"$this->page_name?module=$module&action=view\"
			>"._("Abandon Modification")."</a>
			</div>
			";
	} // end function PlaceOfServiceMaintenance->form

} // end of class PlaceOfServiceMaintenance

register_module ("PlaceOfServiceMaintenance");

?>
