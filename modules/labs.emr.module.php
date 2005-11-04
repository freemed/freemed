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
			'labfiller' => SQL__TEXT, // OBR 21-01
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
			__("Date") => '_timestamp',
			__("Lab") => 'labfiller',
			__("Order Code") => 'labordercode',
			__("Status") => 'labresultstatus'
		);
		$this->summary_query = array (
			"DATE_FORMAT(labtimestamp, '%b %d, %Y %H:%i') AS _timestamp"
		);
		$this->summary_options |= SUMMARY_VIEW;

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

	function display () {
		global $display_buffer;
		if (!$_REQUEST['id']) { trigger_error(__("You must provide an id!"), E_USER_ERROR); }

		// Header
		$display_buffer .= "<table cellpadding=\"5\">\n";

		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		$display_buffer .= "<tr><td coslpan=\"4\">".
			__("Date").': '.$rec['labtimestamp']."<br/>\n".
			__("Order Code").': '.$rec['labordercode']."<br/>\n".
			__("Status").': '.$rec['labresultstatus']."<br/>\n".
			"</td></tr>\n";
	
		$q = "SELECT * FROM labresults ".
			"WHERE labid='".addslashes($_REQUEST['id'])."' ";
		$result = $GLOBALS['sql']->query($q);
		$display_buffer .= "<tr>\n".
			"<th>".__("Observation")."</th>\n".
			"<th>".__("Value")."</th>\n".
			"<th>".__("Range")."</th>\n".
			"<th>".__("Normal")."</th>\n".
			"<th>".__("Abnormal")."</th>\n".
			"</tr>\n";
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$display_buffer .= "<tr>\n";
			$display_buffer .= "<td>".$r['labobscode']."</td>\n";
			$display_buffer .= "<td>".$r['labobsvalue']." ".$r['labobsunit']."</td>\n";
			$display_buffer .= "<td>".$r['labobsrange']."</td>\n";
			$display_buffer .= "<td>".$r['labobsnormal']."</td>\n";
			$display_buffer .= "<td>".$r['labobsabnormal']."</td>\n";
			$display_buffer .= "</tr>\n";
		} // end foreach
	
		// Footer
		$display_buffer .= "</table>\n";
	} // end method display

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		if ($_REQUEST['action'] == 'display') { $this->display(); }

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
