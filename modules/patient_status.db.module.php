<?php
 // $Id$
 // note: patient status functions
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class patientStatusMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Patient Status Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name 	= "Patient Status";
	var $table_name		= "ptstatus";

	var $variables 		= array (
		"ptstatus",
		"ptstatusdescrip"
	);

	function PatientStatusMaintenance () {
		// Table definition
		$this->table_definition = array (
			'ptstatus' => SQL_CHAR(3),
			'ptstatusdescrip' => SQL_VARCHAR(30),
			'id' => SQL_SERIAL
		);

		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor PatientStatusMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		 // grab record number "id"
		$r = freemed::get_link_rec($id, $this->table_name);
		extract($r);

		$display_buffer .= "
		<p/>
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"mod\"/> 
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/> 
		<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>

		".html_form::form_table ( array (

		__("Status") => 
		html_form::text_widget('ptstatus', 2),

		__("Description") =>
		html_form::text_widget('ptstatusdescrip', 20, 30),

		) )."
		<p/>
		<div ALIGN=\"CENTER\">
		 <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\" ".__("Modify")." \"/>
		 <input class=\"button\" TYPE=\"RESET\" VALUE=\"".__("Clear")."\"/>
		 <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Cancel")."\"/>
		</div>

		</form>
		";
	} // end function PatientStatusMaintenance->modform()

	function view () {
		global $display_buffer;
		global $sql;
 		$display_buffer .= freemed_display_itemlist (
 			$sql->query (
				"SELECT ptstatusdescrip,ptstatus,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ptstatusdescrip,ptstatus"
			),
			$this->page_name,
			array (
				__("Status")		=>	"ptstatus",
				__("Description")	=>	"ptstatusdescrip"
			),
			array (
				"", __("NO DESCRIPTION")
			)
		);  
		$this->_addform();
	} // end function PatientStatusMaintenance->view()

	function _addform () {
		global $display_buffer;
		global $module;
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"add\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		".html_form::form_table ( array (
			__("Status") =>
				html_form::text_widget ("ptstatus", 2),
			__("Description") =>
				html_form::text_widget ("ptstatusdescrip", 20)
		) )."
		<br/>	
		<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\"/>
		</form>
		</div>
		";
	} // end function PatientStatusMaintenance->addform()

} // end class PatientStatusMaintenance

register_module ("PatientStatusMaintenance");

?>
