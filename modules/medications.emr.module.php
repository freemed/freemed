<?php
	// $Id$
	// lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class MedicationsModule extends EMRModule {

	var $MODULE_NAME = "Medications";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Medications";
	var $table_name = 'medications';
	var $patient_field = 'mpatient';

	function MedicationsModule () {
		$this->table_definition = array (
			'mdrug' => SQL__VARCHAR(150),
			'mdosage' => SQL__VARCHAR(150),
			'mroute' => SQL__VARCHAR(150),
			'mpatient' => SQL__INT_UNSIGNED(0),
			'mdate' => SQL__DATE,
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'mdrug' => html_form::combo_assemble('mdrug'),
			'mdosage' => html_form::combo_assemble('mdosage'),
			'mroute' => html_form::combo_assemble('mroute'),
			'mpatient' => $_REQUEST['patient'],
			'mdate' => date('Y-m-d')
		);

		$this->summary_vars = array (
			__("Drug") => 'mdrug',
			__("Dosage") => 'mdosage'
		);
		$this->summary_options = SUMMARY_DELETE;

		// call parent constructor
		$this->EMRModule();
	} // end constructor MedicationsModule

	function form_table ( ) {
		return array (
			__("Drug") =>
			html_form::combo_widget(
				'mdrug',
				$GLOBALS['sql']->distinct_values($this->table_name,'mdrug')
			),

			__("Dosage") =>
			html_form::combo_widget(
				'mdosage',
				$GLOBALS['sql']->distinct_values($this->table_name,'mdosage')
			),

			__("Method of Intake") =>
			html_form::combo_widget(
				'mroute',
				$GLOBALS['sql']->distinct_values($this->table_name,'mroute')
			)
		);
	} // end method form_table

	function view ( ) {
		global $sql; global $display_buffer; global $patient;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".$this->table_name." ".
				"WHERE mpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY mdrug"),
			$this->page_name,
			array(
				__("Drug") => 'mdrug',
				__("Dosage") => 'mdosage',
				__("Method") => 'mroute'
			),
			array('', __("Not specified")) //blanks
		);
	} // end method view

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		// Version 0.3
		//
		//	Migrated to seperate table ...
		//
		if (!version_check($version, '0.3')) {
			// Create new table
			$sql->query($sql->create_table_query($this->table_name, $this->table_definition, array('id')));
			// Migrate old entries
			$q = $sql->query("SELECT ptquickmeds,id FROM patient WHERE LENGTH(ptquickmeds) > 3");
			if ($sql->results($q)) {
				while ($r = $sql->fetch_array($q)) {
					$e = sql_expand($r['ptquickmeds']);
					foreach ($e AS $a) {
						$sql->query($sql->insert_query(
							$this->table_name,
							array(
								'mdrug' => $a,
								'mdosage' => '',
								'mroute' => '',
								'mpatient' => $r['id']	
							)
						));
					} // end foreach entry
				} // end loop through patient entries
			} // end checking for results
		}	
	} // end method _update

} // end class MedicationsModule

register_module ("MedicationsModule");

?>
