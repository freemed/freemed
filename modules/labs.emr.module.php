<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class LabsModule extends EMRModule {

	var $MODULE_NAME    = "Labs";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Lab reports";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Labs";
	var $table_name     = "labs";
	var $patient_field  = "labpatient";

	function LabsModule () {
		// Table definition
		$this->table_definition = array (
			'labpatient' => SQL__INT_UNSIGNED(0), // PID
			'labfiller' => SQL__TEXT, // ORC 20/21 (decide later)
			'labstatus' => SQL__CHAR(2), // ORC 05
			'labprovider' => SQL__INT_UNSIGNED(0), // ORC 12
			'labordercode' => SQL__VARCHAR(16), // OBR 04-03
			'laborderdescrip' => SQL__VARCHAR(250), // OBR 04-04
			'labcomponentcode' => SQL__VARCHAR(16), // OBR 20-03
			'labcomponentdescrip' => SQL__VARCHAR(250), // OBR 20-04
			'labfillernum' => SQL__VARCHAR(16), // OBR 02
			'labplacernum' => SQL__VARCHAR(16), // OBR 03
			'labtimestamp' => SQL__TIMESTAMP(14), // OBR 07
			'labresultstatus' => SQL__CHAR(1), // OBR 25
			'labnotes' => SQL__TEXT, // NTE
			'id' => SQL__SERIAL
		);
	
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => 'labtimestamp',
			__("Order Code") => 'labordercode',
			__("Status") => 'labresultstatus'
		);

		$this->form_vars = array (
			// TODO - FIXME
		);

		$this->variables = array (
			'labtimestamp' => SQL__NOW,
		);

		$this->acl = array ( 'emr' );

		// Run parent constructor
		$this->EMRModule();
	} // end constructor LabsModule

	function form_table () {
		return array (
			// TODO - FIXME
		);
	} // end method form_table

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				"WHERE (".$this->patient_field."='".addslashes($_REQUEST['patient'])."') ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY ".$this->order_fields
			),
			$this->page_name,
			array (
				__("Date") => 'labtimestamp',
				__("Order Code") => 'labordercode',
				__("Status") => 'labresultstatus'
			),
			array ("")
		);
	} // end method view

} // end class LabsModule

register_module ("LabsModule");

?>
