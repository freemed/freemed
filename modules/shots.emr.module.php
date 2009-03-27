<?php
	// $Id$
	// lic : GPL, v2

LoadObjectDependency('_FreeMED.EMRModule');

class ShotsModule extends EMRModule {

	var $MODULE_NAME = "Shots";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net), modified RPL (rpl121@verizon.net)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Shots";
	var $table_name = 'shots';
	var $patient_field = 'spatient';
	var $date_field = 'sdate';

	function ShotsModule () {
		$this->table_definition = array (
			'sshot' => SQL__VARCHAR(150),
			'slotno' => SQL__VARCHAR(150),
			'sroute' => SQL__VARCHAR(150),
			'spatient' => SQL__INT_UNSIGNED(0),
			'sdate' => SQL__DATE,
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'sshot' => html_form::combo_assemble('sshot'),
			'slotno' => html_form::combo_assemble('slotno'),
			'sroute' => html_form::combo_assemble('sroute'),
			'spatient' => $_REQUEST['patient'],
			'sdate' => fm_date_assemble('sdate') 
		);

		$this->summary_vars = array (
			__("Date") => 'sdate',		     
			__("Shot") => 'sshot'
		);
		$this->summary_options = SUMMARY_DELETE;
		$this->summary_order_by = 'sshot';

		// call parent constructor
		$this->EMRModule();
	} // end constructor ShotsModule

	function form_table ( ) {
		return array (
			__("Shot") =>
			html_form::combo_widget(
				'sshot',
				$GLOBALS['sql']->distinct_values($this->table_name,'sshot')
			),

			__("Mfg lot number") =>
			html_form::combo_widget(
				'slotno',
				$GLOBALS['sql']->distinct_values($this->table_name,'slotno')
			),

			__("Route of administration") =>
			html_form::combo_widget(
				'sroute',
				$GLOBALS['sql']->distinct_values($this->table_name,'sroute')
			),
			__("Date of administration") =>
			      fm_date_entry("sdate")
		);
	} // end method form_table

	function view ( ) {
		global $sql; global $display_buffer; global $patient;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".$this->table_name." ".
				"WHERE spatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY sshot"),
			$this->page_name,
			array(
				__("Shot") => 'sshot',
				__("Mfg Lot Number") => 'slotno',
				__("Route of adm.") => 'sroute',
			        __("Date of adm.") => 'sdate'
			),
			array('', __("Not specified")) //blanks
		);
	} // end method view

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->date_field." DESC";
		$res = $GLOBALS['sql']->query($query);
	        $m[] = "\n\nIMMUNIZATIONS:\n";
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['sdate'].' '.$r['sshot']);
		}
		return @join("\n", $m);
	} // end method recent_text
    
} // end class ShotsModule

register_module ("ShotsModule");

?>

