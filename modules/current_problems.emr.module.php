<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class CurrentProblemsModule extends EMRModule {

	var $MODULE_NAME = "Current Problems";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Current Problems";
	var $patient_field = "ppatient";

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );
	var $date_field = 'id';
	var $table_name = 'current_problems';
	var $order_fields = 'pdate,problem';
	var $widget_hash = '##pdate## ##problem##';

	function CurrentProblemsModule () {
		$this->table_definition = array (
			'pdate'    => SQL__DATE,
			'problem'  => SQL__VARCHAR(250),
			'ppatient' => SQL__INT_UNSIGNED(0),
			'id'       => SQL__SERIAL
		);

		$this->variables = array (
			'problem',
			'ppatient' => $_REQUEST['patient'],
			'pdate' => date('Y-m-d')
		);
		
		// call parent constructor
		$this->EMRModule();
	} // end constructor CurrentProblemsModule

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		$my_result = $GLOBALS['sql']->query(
			"SELECT *,DATE_FORMAT(pdate, '%m/%d/%Y') AS my_date ".
			"FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY pdate DESC,".$this->order_fields
		);

		// Check to see if it's set (show listings if it is)
		if ($GLOBALS['sql']->results($my_result)) {
			// Show menu bar
			$buffer .= "
			<table BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
			"CELLPADDING=\"2\">
			<tr CLASS=\"menubar_info\">
			<td><b>".__("Reviewed")."</b></td>
			<td><b>".__("Problem")."</b></td>
			<td><b>".__("Action")."</b></td>
			</tr>
			";

			// Loop thru and display problems
			while ($my_r = $GLOBALS['sql']->fetch_array($my_result)) {
				$buffer .= "
				<tr>
				<td ALIGN=\"LEFT\"><small>".prepare($my_r['my_date'])."</small></td>
				<td ALIGN=\"LEFT\"><small>".prepare($my_r['problem'])."</small></td>
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
			} // end looping thru problems

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
/*
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
			".html_form::text_widget("problem", 75)."
			<input TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\" class=\"button\"/>
			</form>
			</div>
			";
*/
		return $buffer;
	} // end method summary

	//function summary_bar() { }

	function add () {
		// Save original values
		$v = array(
			'problem' => $_REQUEST['problem']
		);
		// Loop through all possibles
		for ($i=2; $i<=8; $i++) {
			// Only add if it looks like we have values
			if ($_REQUEST['problem'.$i]) {
				$this->_add(array(
					'problem' => $_REQUEST['problem'.$i]
				));
			}
		}

		// Restore from saved values
		foreach ($v as $key => $value) {
			$_REQUEST[$key] = $GLOBALS[$key] = $value;
		}
		// Call the regular way, so we get good handling...
		$this->_add();
	}
	function _preadd($p) {
		$this->variables = array (
			'problem' => $p['problem'],
			'ppatient' => $_REQUEST['patient'],
			'pdate' => date('Y-m-d')
		);
	}

	function view ($condition = false) {
		global $display_buffer;
		global $patient, $action;

		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE (".$this->patient_field."='".addslashes($patient)."') ".
			freemed::itemlist_conditions(false)." ".
			( $condition ? 'AND '.$condition : '' )." ".
			"ORDER BY ".$this->order_fields;
		$result = $GLOBALS['sql']->query ($query);

		$display_buffer .= freemed_display_itemlist(
			$result,
			$this->page_name,
			array (
				__("Date")        => "pdate",
				__("Problem") => "problem"
			), // array
			array ( "", ""),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_DEL
		);
		$display_buffer .= "\n<p/>\n";
	} // end method view

	function form_table ( ) {
		$a = array (
			__("Problem").' 1' =>
			html_form::text_widget('problem', 128)
		);
		for ($i=2; $i<=8; $i++) {
		$a = array_merge($a,
			array (
			__("Problem")." $i" =>
			html_form::text_widget('problem'.$i, 128)
		)
		);
		}
		return $a;
	} // end method form_table

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->order_fields;
		$res = $GLOBALS['sql']->query($query);

		// Get problems, and extract to an array
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['problem']);
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
			$q = $GLOBALS['sql']->query('SELECT ptcproblems,id FROM patient WHERE LENGTH(ptcproblems) > 3');
			while ($r = $GLOBALS['sql']->fetch_array($q)) {
				$e = sql_expand($r['ptcproblems']);
				if (!is_array($e)) { $e = array ($e); }
				foreach ($e AS $a) {
					$GLOBALS['sql']->query(
						$GLOBALS['sql']->insert_query(
							$this->table_name,
							array(
								'ppatient' => $r['id'],
								'problem' => $a
							)
						)
					); // end query
				} // end foreach $e
			} // end while
		}
	} // end method _update

} // end class CurrentProblemsModule

register_module ("CurrentProblemsModule");

?>
