<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class PreviousOperationsModule extends EMRModule {

	var $MODULE_NAME = "Previous Operations";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Previous Operations";
	var $patient_field = "opatient";

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );
	var $date_field = 'id';
	var $table_name = 'previous_operations';
	var $order_fields = 'odate,operation';
	var $widget_hash = '##odate## ##operation##';

	function PreviousOperationsModule () {
		$this->table_definition = array (
			'odate'      => SQL__DATE,
			'operation'  => SQL__VARCHAR(250),
			'opatient'   => SQL__INT_UNSIGNED(0),
			'id'         => SQL__SERIAL
		);

		$this->variables = array (
			'operation',
			'opatient' => $_REQUEST['patient'],
			'odate' => fm_date_assemble('odate')
		);
		
		// call parent constructor
		$this->EMRModule();
	} // end constructor PreviousOperationsModule

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
			<td><b>".__("Operation")."</b></td>
			<td><b>".__("Action")."</b></td>
			</tr>
			";

			// Loop thru and display operations
			while ($my_r = $GLOBALS['sql']->fetch_array($my_result)) {
				$buffer .= "
				<tr>
				<td ALIGN=\"LEFT\"><small>".prepare($my_r['odate'])."</small></td>
				<td ALIGN=\"LEFT\"><small>".prepare($my_r['operation'])."</small></td>
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
			} // end looping thru operations

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
			".fm_date_entry('odate')."
			".html_form::text_widget("operation", 40, 150)."
			<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\" class=\"button\"/>
			</form>
			</div>
			";
		return $buffer;
	} // end method summary

	function summary_bar() { }

	function form_table ( ) {
		return array (
			__("Date") =>
			fm_date_entry('odate'),
			
			__("Operation") =>
			html_form::text_widget('operation', 128)
		);
	} // end method form_table

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->order_fields;
		$res = $GLOBALS['sql']->query($query);

		// Get operations, and extract to an array
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['odate'].' '.$r['operation']);
		}
		return @join(', ', $m);
	} // end method recent_text

	function _update ( ) {
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.2
		//
		//	Migrated to separate table
		//
		if (!version_check($version, '0.2')) {
			// Create table
			$GLOBALS['sql']->query($GLOBALS['sql']->create_table_query($this->table_name, $this->table_definition, array('id')));

			// Migrate old entries
			$q = $GLOBALS['sql']->query('SELECT ptops,id FROM patient WHERE LENGTH(ptops) > 3');
			while ($r = $GLOBALS['sql']->fetch_array($q)) {
				$e = sql_expand($r['ptops']);
				if (!is_array($e)) { $e = array ($e); }
				foreach ($e AS $a) {
					$GLOBALS['sql']->query(
						$GLOBALS['sql']->insert_query(
							$this->table_name,
							array(
								'opatient'  => $r['id'],
								'operation' => $a
							)
						)
					); // end query
				} // end foreach $e
			} // end while
		}
	} // end method _update

} // end class PreviousOperationsModule

register_module ("PreviousOperationsModule");

?>
