<?php
 // $Id$
 // note: room equipment database
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class RoomEquipmentMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Room Equipment Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "roomequip";
	var $record_name    = "Room Equipment";
	var $order_field    = "reqname,reqdescrip";
	var $variables      = array (
		"reqname",
		"reqdescrip",
		"reqdateadd",
		"reqdatemod"
	);

	function RoomEquipmentMaintenance () {
		global $cur_date;
		$this->MaintenanceModule();
		$reqdatemod = $cur_date;
	} // end constructor RoomEquipmentMaintenance

	function form () {
		global $display_buffer;
		switch ($action) {
			case "addform":
				break;
			case "modform":
				$r = freemed::get_link_rec ($id, $db_name);
				extract ($r);
				break;
		} // end switch
 
		$display_buffer .= "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
     ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"reqdateadd\" VALUE=\"".prepare($reqdateadd)."\"  >

  ".html_form::form_table ( array (
    _("Name") =>
    "<INPUT TYPE=TEXT NAME=\"reqname\" SIZE=20 MAXLENGTH=100
     VALUE=\"".prepare($reqname)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"reqdescrip\" SIZE=30
     VALUE=\"".prepare($reqdescrip)."\">"
   ) )."  

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".( ($action=="addform") ? _("Add") :
      _("Modify") )." \"  >
    <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER></FORM>
  ";

  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$page_name\"
     >"._("Abandon ".( ($action=="addform") ? "Addition" : "Modification" )).
      "</A>
    </CENTER>
  ";
	} // end function RoomEquipmentMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM $this->table_name ".
				"ORDER BY $this->order_field"),
			$this->page_name,
			array (
				_("Name")			=>	"reqname",
				_("Description")	=>	"reqdescrip"
			),
			array (
				"", _("NO DESCRIPTION")
			)
		);
	} // end function RoomEquipmentMaintenance->view()

} // end class RoomEquipmentMaintenance

register_module ("RoomEquipmentMaintenance");

?>
