<?php
	// $Id$
	// lic : GPL, v2
	// modified by RPL  RPL121@verizon.net 2005/08/05 and 2005/10/02

LoadObjectDependency('_FreeMED.EMRModule');

class LabvaluesModule extends EMRModule {

	var $MODULE_NAME = "Lab Values";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net), modified RPL (rpl121@verizon.net)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Lab Values";
	var $table_name = 'labvalues';
	var $patient_field = 'lpatient';
	var $date_field = 'ldate';
    
	function LabvaluesModule () {
		$this->table_definition = array (
			'labtest' => SQL__VARCHAR(150),
			'labresult' => SQL__VARCHAR(150),
			'lpatient' => SQL__INT_UNSIGNED(0),
			'ldate' => SQL__DATE,
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'labtest' => html_form::combo_assemble('labtest'),
			'labresult',
		        'lpatient' => $_REQUEST['patient'],
			'ldate' => fm_date_assemble('ldate')
		);

		$this->summary_vars = array (
			__("Date") => 'ldate',
			__("Test") => 'labtest',
			__("Result") => 'labresult'		     
			
		);
//		$this->summary_options = SUMMARY_DELETE | SUMMARY_PRINT;
                $this->summary_options = SUMMARY_DELETE;
	        $this->summary_order_by = 'ldate';

		// call parent constructor
		$this->EMRModule();
	} // end constructor LabvaluesModule

	function form_table ( ) {
		return array (
			__("Test") =>
			html_form::combo_widget(
				'labtest',
				$GLOBALS['sql']->distinct_values($this->table_name,'labtest')
			),

			__("Result") =>	
//			      html_form::text_widget('labresult', 20, 150
                              html_form::text_area('labresult', 'VIRTUAL', 1, 30
			      ),

			__("Date") =>
			      fm_date_entry("ldate")
		);
	} // end method form_table

	function view ( ) {
		global $sql; global $display_buffer; global $patient;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".$this->table_name." ".
				"WHERE lpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY ldate"),
			$this->page_name,
			array(
				__("Test") => 'labtest',
				__("Result") => 'labresult',
			        __("Date") => 'ldate'
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
	        $m[] = "\n\nLABS:\n";
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['ldate'].'  '.$r['labtest'].' '.$r['labresult']);
		}
		return @join("\n", $m);
	} // end method recent_text
    
} // end class LabvaluesModule

register_module ("LabvaluesModule");

?>

