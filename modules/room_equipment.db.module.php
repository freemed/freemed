<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

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
		global $reqdatemod;
		$reqdatemod = date("Y-m-d");
		$this->MaintenanceModule();
	} // end constructor RoomEquipmentMaintenance

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
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
    <FORM ACTION=\"".$this->page_name."\" METHOD=\"POST\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
     ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"reqdateadd\" VALUE=\"".prepare($reqdateadd)."\"  >

  ".html_form::form_table ( array (
    __("Name") =>
    html_form::text_widget('reqname', 20, 100),

    __("Description") =>
    html_form::text_widget('reqdescrip', 30)
   ) )."  

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".( ($action=="addform") ? __("Add") :
      __("Modify") )." \"  >
    <INPUT TYPE=RESET  VALUE=\" ".__("Clear")." \">
    </CENTER></FORM>
  ";

  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$page_name\"
     >".__("Abandon ".( ($action=="addform") ? "Addition" : "Modification" )).
      "</A>
    </CENTER>
  ";
	} // end function RoomEquipmentMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".$this->order_field
			),
			$this->page_name,
			array (
				__("Name")		=>	"reqname",
				__("Description")	=>	"reqdescrip"
			),
			array (
				"", __("NO DESCRIPTION")
			)
		);
	} // end function RoomEquipmentMaintenance->view()

} // end class RoomEquipmentMaintenance

register_module ("RoomEquipmentMaintenance");

?>
