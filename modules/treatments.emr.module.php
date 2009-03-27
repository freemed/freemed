<?php
	// $treatments.emr.module.php,v 1.14 2005/07/30 15:05:13 rufustfirefly Exp $
	// $Author$
	// $modified by RPL121@verizon.net on 2005/08/03 $

LoadObjectDependency('_FreeMED.EMRModule');

class TreatmentsModule extends EMRModule {

	var $MODULE_NAME = "Treatments";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net) modified RPL121@verizon.net";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Treatments";
	var $patient_field = "tpatient";

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );
	var $date_field = 'id';
	var $table_name = 'treatments';
	var $order_fields = 'tdate, treatment';

	function TreatmentsModule () {
		$this->table_definition = array (
			'tdate'    => SQL__DATE,
			'treatment'  => SQL__VARCHAR(250),
			'tpatient' => SQL__INT_UNSIGNED(0),
			'id'       => SQL__SERIAL
		);

		$this->variables = array (
			'treatment',
			'tpatient' => $_REQUEST['patient'],
			'tdate' => date('Y-m-d')
		);
		
		// call parent constructor
		$this->EMRModule();
	} // end constructor TreatmentsModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		$my_result = $GLOBALS['sql']->query(
			"SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->order_fields
		);

		// Check to see if it's set (show listings if it is)
		if ($GLOBALS['sql']->results($my_result)) {
			// Show menu bar
			$buffer .= "
			<table BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<tr CLASS=\"menubar_info\">
			<td><b>".__("Date")."</b></td>
			<td><b>".__("Treatment")."</b></td>
			<td><b>".__("Action")."</b></td>
			</tr>
			";

			// Loop thru and display treatments
			while ($my_r = $GLOBALS['sql']->fetch_array($my_result)) {
				$buffer .= "
				<tr>
				<td ALIGN=\"LEFT\"><small>".prepare($my_r['tdate'])."</small></td>
				<td ALIGN=\"LEFT\"><small>".prepare($my_r['treatment'])."</small></td>
				<td ALIGN=\"LEFT\">".
				template::summary_modify_link($this,
				"module_loader.php?".
				"module=".get_class($this)."&".
				"action=modform&patient=".urlencode($patient).
				"&return=manage&id=".urlencode($my_r['id'])).
				template::summary_delete_link($this,
				"module_loader.php?".
				"module=".get_class($this)."&".
				"action=del&patient=".urlencode($patient).
				"&return=manage&id=".urlencode($my_r['id']))."</td>
				</tr>
				";
			} // end looping thru treatments

			// End table
			$buffer .= "
			</table>
			";
		} else {
			$buffer .= "
			<div ALIGN=\"CENTER\">
			<b>".__("No data entered.")."</b>
			</div>
			";
		}

		$buffer .= "
			<div ALIGN=\"CENTER\">
			<form ACTION=\"module_loader.php\" METHOD=\"POST\">
			<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".
			prepare($this->MODULE_CLASS)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
			"add\"/>
			<input TYPE=\"HIDDEN\" NAME=\"return\" VALUE=\"".
			"manage\"/>
			<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".
			prepare($patient)."\"/>
			".html_form::text_widget("treatment", 75)."
			<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\" class=\"button\"/>
			</form>
			</div>
			";
		return $buffer;
	} // end method summary

	function summary_bar() { }

	function form_table ( ) {
		return array (
			__("Treatment") =>
			html_form::text_widget('treatment', 128)
		);
	} // end method form_table

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->order_fields;
		$res = $GLOBALS['sql']->query($query);

		// Get treatments, and extract to an array
		$m[] = "\n\nTREATMENTS:";
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['junktdate'].' '.$r['treatment']);
		}
		return @join("\n", $m);
	} // end method recent_text

} // end class TreatmentsModule

register_module ("TreatmentsModule");

?>

