<?php
  // $Id$
  // note: type of service (TOS) database module
  // code: adam b (gdrago23@yahoo.com) -- modified a lot
  // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class TypeOfServiceMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Type of Service Maintenance";
	var $MODULE_AUTHOR = "Adam (gdrago23@yahoo.com)";
	var $MODULE_VERSION = "0.1";
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
		// run constructor
		$this->MaintenanceModule();
		global $tosdtmod;
		$tosdtmod = $GLOBALS["cur_date"];
	} // end constructor TypeOfServiceMaintenance	

	function view () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;

		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT tosname,tosdescrip,id FROM ".$this->table_name.
				" ORDER BY ".prepare($this->order_field)),
			$this->page_name,
			array (
				_("Code") => "tosname",
				_("Description") => "tosdescrip"
			),
			array ("", _("NO DESCRIPTION")), "", "t_page"
		);
	} // end function module->view

	function form () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k, $v)=each($GLOBALS)) global $$k;
  		if ($action=="modform") { 
    		$result = $sql->query("SELECT tosname,tosdescrip FROM $this->table_name
				WHERE ( id = '$id' )");
			$r = $sql->fetch_array($result); // dump into array r[]
			extract ($r);
		} // if loading values

		// display itemlist first
		$this->view ();

		$display_buffer .= "
			<FORM ACTION=\"$this->page_name\" METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=\"tosdtadd\"".prepare($cur_date)."\">
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
			($action=="modform" ? "mod" : "add")."\">";
		if ($action=="modform")
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">";

		$display_buffer .= html_form::form_table(array(
			_("Type of Service") =>
			html_form::text_widget("tosname", 20, 75),

			_("Description") =>
			html_form::text_widget("tosdescrip", 25, 200)
		)).
			"<DIV ALIGN=\"CENTER\">\n".
			"<INPUT TYPE=SUBMIT VALUE=\"".(
			 ($action=="modform") ? _("Modify") : _("Add"))."\">
			 <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
			</DIV></FORM>
		";
		if ($action=="modform") $display_buffer .= "
			<P>
			<CENTER>
			<A HREF=\"$this->page_name?module=$module&action=view\"
			>"._("Abandon Modification")."</A>
			</CENTER>
			";
	} // end function TypeOfServiceMaintenance->form

} // end of class TypeOfServiceMaintenance

register_module ("TypeOfServiceMaintenance");

?>
