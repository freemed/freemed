<?php
 // $Id$
 // note: room database functions
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class RoomMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Room Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Room";
	var $table_name = "room";

	var $variables  = array (
		"roomname",
		"roompos",
		"roomdescrip",
		"roomdefphy",
		"roomsurgery",
		"roombooking",
		"roomipaddr"
	);

	function RoomMaintenance () {
		// Table definition
		$this->table_definition = array (
			'roomname' => SQL_VARCHAR(20),
			'roompos' => SQL_INT_UNSIGNED(0),
			'roomdescrip' => SQL_VARCHAR(40),
			'roomdefphy' => SQL_INT_UNSIGNED,
			'roomsurgery' => SQL_ENUM(array('y', 'n')),
			'roombooking' => SQL_ENUM(array('y', 'n')),
			'roomipaddr' => SQL_VARCHAR(15),
			'id' => SQL_SERIAL
		);

		// Run constructor
		$this->MaintenanceModule();
	} // end constructor RoomMaintenance

	function form () {
		global $display_buffer;
    		global $roomdefphy, $roompos;
		foreach ($GLOBALS as $k => $v) global $$k; 

  switch ($action) { // inner switch
    case "addform":
      // do nothing
     break; // end of addform

    case "modform":
     $r = freemed::get_link_rec ($id, $this->table_name);
     extract ($r);
     break; // end of modform 
  } // end inner switch

  $display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=\"POST\">
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
     ( ($action=="addform") ? "add" : "mod" )."\">
  
  ".html_form::form_table ( array (
    __("Room Name") =>
    "<INPUT TYPE=TEXT NAME=\"roomname\" SIZE=20 MAXLENGTH=20
     VALUE=\"".prepare($roomname)."\">",

    __("Location") =>
    "<SELECT NAME=\"roompos\">
    ".freemed_display_facilities ("roompos", true)."
    </SELECT>",

    __("Description") =>
    "<INPUT TYPE=TEXT NAME=\"roomdescrip\" SIZE=20 MAXLENGTH=40
     VALUE=\"".prepare($roomdescrip)."\">",

    __("Default Provider") =>
    freemed_display_selectbox (
    $sql->query ("SELECT * FROM physician WHERE phyref != 'yes' AND phylname != ''"),
    "#phylname#, #phyfname#",
    "roomdefphy"),

    __("Surgery Equipped") =>
    "<INPUT TYPE=CHECKBOX NAME=\"roomsurgery\" VALUE=\"y\"
     ".( ($roomsurgery=="y") ? "CHECKED" : "" ).">",

    __("Booking Enabled") =>
    "<INPUT TYPE=CHECKBOX NAME=\"roombooking\" VALUE=\"y\" 
     ".( ($roombooking=="y") ? "CHECKED" : "" ).">",

    __("IP Address") =>
    "<INPUT TYPE=TEXT NAME=\"roomipaddr\" SIZE=16 MAXLENGTH=15
     VALUE=\"".prepare($roomipaddr)."\">"

    )
   ); 

		$display_buffer .= "
	<CENTER>
	<INPUT TYPE=\"SUBMIT\" VALUE=\" ".
	( ($action=="addform") ? __("Add") : __("Modify") )." \" CLASS=\"button\"/>
	<INPUT TYPE=\"RESET\" VALUE=\"".__("Clear")."\" CLASS=\"button\"/>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"".__("Cancel")."\" ".
	"CLASS=\"button\"/>
	</CENTER></FORM>
		";
	} // end function RoomMaintenance->form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT roomname,roomdescrip,id ".
				"FROM $this->table_name ORDER BY roomname"),
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
	} // end function RoomMaintenance->view

} // end class RoomMaintenance

register_module ("RoomMaintenance");

?>
