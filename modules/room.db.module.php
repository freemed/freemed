<?php
	// $Id$
	// $Author$
 
LoadObjectDependency('_FreeMED.MaintenanceModule');

class RoomMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Room Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Room";
	var $table_name = "room";
	var $order_field = 'roomname';

	var $variables  = array (
		"roomname",
		"roompos",
		"roomdescrip",
		"roomequipment",
		"roomdefphy",
		"roomsurgery",
		"roombooking",
		"roomipaddr"
	);

	function RoomMaintenance () {
		// Table definition
		$this->table_definition = array (
			'roomname' => SQL__VARCHAR(20),
			'roompos' => SQL__INT_UNSIGNED(0),
			'roomdescrip' => SQL__VARCHAR(40),
			'roomdefphy' => SQL__INT_UNSIGNED(0),
			'roomequipment' => SQL__BLOB,
			'roomsurgery' => SQL__ENUM(array('y', 'n')),
			'roombooking' => SQL__ENUM(array('y', 'n')),
			'roomipaddr' => SQL__VARCHAR(15),
			'id' => SQL__SERIAL
		);

		// Run constructor
		$this->MaintenanceModule();
	} // end constructor RoomMaintenance

	function generate_form () {
		return array (
			__("Room Name") =>
			html_form::text_widget('roomname', array('length'=>20)),

			__("Location") =>
			"<SELECT NAME=\"roompos\">".
			freemed_display_facilities ("roompos", true).
			"</SELECT>",

			__("Description") =>
			html_form::text_widget('roomdescrip', array('length'=>40)),

			__("Default Provider") =>
			freemed_display_selectbox (
			$GLOBALS['sql']->query ("SELECT * FROM physician WHERE phyref != 'yes' AND phylname != ''"),
			"#phylname#, #phyfname#",
			"roomdefphy"),

			__("Equipment") =>
			module_function(
				'RoomEquipment',
				'widget',
				array (
					'roomequipment',
					false,
					'id',
					array ('multiple' => 5)
				)
			),

			//__("Surgery Equipped") =>
			//"<INPUT TYPE=CHECKBOX NAME=\"roomsurgery\" VALUE=\"y\"
			//".( ($roomsurgery=="y") ? "CHECKED" : "" ).">",

			__("Booking Enabled") =>
			"<INPUT TYPE=CHECKBOX NAME=\"roombooking\" VALUE=\"y\" 
			".( ($roombooking!='n') ? "CHECKED" : "" ).">",

			__("IP Address") =>
			html_form::text_widget('roomipaddr', array('length'=>16))
		); 
	} // end method generate_form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT roomname,roomdescrip,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".$this->order_field
			),
			$this->page_name,
			array (
				__("Name")		=>	"roomname",
				__("Description")	=>	"roomdescrip"
			),
			array (
				"",
				__("NO DESCRIPTION")
			)
		);
	} // end method view

	function _update ( ) {
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.2
		//
		//	Added room equipment (roomequipment)
		//
		if (!version_check($version, '0.2')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN roomequipment BLOB AFTER roomdefphy');
		}
	} // end method _update

} // end class RoomMaintenance

register_module ("RoomMaintenance");

?>
