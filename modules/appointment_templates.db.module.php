<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class AppointmentTemplates extends MaintenanceModule {

	var $MODULE_NAME    = "Appointment Templates";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Appointment Template";
	var $table_name     = "appttemplate";

	var $widget_hash    = "##atname## (##atduration## min)";

	var $variables = array (
		'atname',
		'atduration',
		'atequipment'
	);

	function AppointmentTemplates () {
		global $display_buffer;

		$this->table_definition = array (
			'atname'	=>	SQL__VARCHAR(50),
			'atduration'	=>	SQL__INT_UNSIGNED(0),
			'atequipment'	=>	SQL__BLOB,
			'id'		=>	SQL__SERIAL
		);

		$this->rpc_field_map = array (
			'name' => 'atname',
			'duration' => 'atduration'
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
		".html_form::form_table ( array (
		__("Template Name") => html_form::text_widget('atname'),
		__("Duration") => html_form::number_pulldown('atduration', 1, 90),
		__("Equipment") => "NOT USED FOR NOW"

		) )."
		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\" ".
		( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input class=\"button\" NAME=\"submit\" TYPE=\"SUBMIT\" ".
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

} // end class AppointmentTemplates

register_module ("AppointmentTemplates");

?>
