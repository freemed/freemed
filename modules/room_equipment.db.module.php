<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class RoomEquipment extends MaintenanceModule {

	var $MODULE_NAME    = "Room Equipment";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $table_name     = "roomequip";
	var $record_name    = "Room Equipment";
	var $order_field    = "reqname,reqdescrip";

	function RoomEquipment () {
		$this->table_definition = array (
			'reqname' => SQL__VARCHAR(50),
			'reqdescrip' => SQL__VARCHAR(150),
			'reqmovable' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			"reqname",
			"reqdescrip"
		);

		$this->MaintenanceModule();
	} // end constructor

	function generate_form ( ) {
		return array (
			__("Name") =>
			html_form::text_widget('reqname', 20, 100),

			__("Description") =>
			html_form::text_widget('reqdescrip', 30)
		);
	} // end method generate_form

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
	} // end method view

} // end class RoomEquipment

register_module ("RoomEquipment");

?>
