<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class AppointmentTemplates extends MaintenanceModule {

	var $MODULE_NAME    = "Appointment Templates";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Appointment Template";
	var $table_name     = "appttemplate";

	var $widget_hash    = "##atname## (##atduration## min)";

	var $variables = array (
		'atname',
		'atduration',
		'atequipment',
		'atcolor'
	);

	function AppointmentTemplates () {
		// For i18n: __("Appointment Templates")

		global $display_buffer;

		$this->table_definition = array (
			'atname'	=>	SQL__VARCHAR(50),
			'atduration'	=>	SQL__INT_UNSIGNED(0),
			'atequipment'	=>	SQL__BLOB,
			'atcolor'	=>	SQL__CHAR(7),
			'id'		=>	SQL__SERIAL
		);

		$this->rpc_field_map = array (
			'name' => 'atname',
			'duration' => 'atduration',	
			'color' => 'atcolor'
		);

			// Run constructor
		$this->MaintenanceModule();
	} // end constructor AppointmentTemplates

	function form () {
		global $display_buffer, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		switch ($action) { // inner switch
			case "addform":
			break;

			case "modform":
			if ($id<1) trigger_error ("NO ID", E_USER_ERROR);
			$r = freemed::get_link_rec ($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = $v;
			}
			break;
		} // end inner switch

		$display_buffer .= "
		<p/>
		<form ACTION=\"".$this->page_name."\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
		( ($action=="addform") ? "add" : "mod" )."\"/> 
		<input TYPE=\"HIDDEN\" NAME=\"id\"   VALUE=\"".prepare($id)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\"   VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"return\"   VALUE=\"".prepare($_REQUEST['return'])."\"/>
		".html_form::form_table ( array (
		__("Template Name") => html_form::text_widget('atname', array('length'=>50)),
		__("Duration") => html_form::number_pulldown('atduration', 1, 90),
		__("Required Equipment") => module_function(
			'RoomEquipment',
			'widget',
			array(
				'atequipment',
				false,
				'id',
				array ( 'multiple' => 5 )
			)
		),
		__("Color") => html_form::color_widget('atcolor')

		) )."
		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\" ".
		( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input class=\"button\" NAME=\"__submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("Cancel")."\"/>
		</div></form>
		";
	} // end method form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT atname, atduration, id ".
				"FROM ".addslashes($this->table_name)." ".
				freemed::itemlist_conditions().
                		"ORDER BY atname"
			),
			$this->page_name,
			array (
				__("Template") => 'atname',
				__("Duration") => 'atduration'
			),
			array ("", __("NO DESCRIPTION"))
		);
	} // end method view

	// Method: get_description
	//
	//	Get the description of the specified appointment template.
	//
	// Parameters:
	//
	//	$id - Record id field for template
	//
	// Returns:
	//
	//	Description of specified template.
	//
	function get_description ( $id ) {
		$r = freemed::get_link_rec ( $id, $this->table_name );
		return $r['atname'];
	} // end method get_description

	// Method: get_duration
	//
	//	Get the duration of the specified appointment template.
	//
	// Parameters:
	//
	//	$id - Record id field for template
	//
	// Returns:
	//
	//	Duration of specified template.
	//
	function get_duration ( $id ) {
		$r = freemed::get_link_rec ( $id, $this->table_name );
		return $r['atduration'] + 0;
	} // end method get_duration

	// Method: get_rooms
	//
	//	Get rooms which are acceptable with the current
	//	template requirements.
	//
	// Parameters:
	//
	//	$id - Record id field for template
	//
	// Returns:
	//
	//	Array of acceptable ids, or false if there is no
	//	limit on rooms.
	//
	function get_rooms ( $id ) {
		$t = freemed::get_link_rec($id, $this->table_name);
		if (!$t['atequipment']) { return false; }

		// Cache rooms
		$res = $GLOBALS['sql']->query("SELECT * FROM room");
		while ($my_r = $GLOBALS['sql']->fetch_array($res)) {
			$rooms[$my_r['id']] = explode(',', $my_r['roomequipment']);
		}
		
		$e = explode(',', $t['atequipment']);
		foreach ($e AS $this_e) {
			if (is_array($rooms)) {
				foreach ($rooms AS $k => $this_room) {
					$found = false;
					if ($this_room == $this_e) {
						$found = true;
					}
					foreach ($this_room AS $check) {
						if ($check == $this_e) {
							$found = true;
						}
					}
					if (!$found) { unset($rooms[$k]); }
				}
			} else {
				// What in the hell do you do when there is
				// nothing that matches the criteria??!?!?!?
				// FIXME FIXME FIXME HACK FIXME FIXME FIXME
				return false;
			}
		}
		if (is_array($rooms)) {
			foreach ($rooms AS $k => $v) {
				$return[] = $k;
			}
			return $return;
		} else {
			// Ridiculous restrictions?
			return false;
		}
	} // end method get_rooms

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Version 0.1.1
		//
		//	Add colors
		//
		if (!version_check($version, '0.1.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN atcolor CHAR(7) AFTER atequipment');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET atcolor=\'\' WHERE id>0');
		} // end version 0.1.1
	} // end method _update

} // end class AppointmentTemplates

register_module ("AppointmentTemplates");

?>
