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
	var $order_fields = 'odate DESC,operation';
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
			'odate'
		);
	
	        $this->summary_vars = array (
		        __("Date") => '_odate',
			__("Operation") => 'operation'
		);
		$this->summary_query = array (
		        "DATE_FORMAT(odate, '%m/%d/%Y') AS _odate"
		);
		$this->summary_order_by = $this->order_fields;
		$this->summary_options |= SUMMARY_DELETE;

		// call parent constructor
		$this->EMRModule();
	} // end constructor PreviousOperationsModule

	function add () {
		// Save original values
		$v = array(
			'operation' => $_REQUEST['operation'],
			'odate' => fm_date_assemble('odate')
		);
		// Loop through all possibles
		for ($i=2; $i<=8; $i++) {
			// Only add if it looks like we have values
			if ($_REQUEST['operation'.$i]) {
				$this->_add(array(
					'operation' => $_REQUEST['operation'.$i],
					'odate' => fm_date_assemble('odate'.$i)
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
			'operation' => $p['operation'],
			'opatient' => $_REQUEST['patient'],
			'odate' => $p['odate']
		);
	}

/*
	function form_table ( ) {
		return array (
			__("Date") =>
			fm_date_entry('odate'),
			
			__("Operation") =>
			html_form::text_widget('operation', 128)
		);
	} // end method form_table
*/

	function form_table ( ) {
		$a = array (
			__("Date")." 1" => fm_date_entry('odate'),
			__("Operation")." 1" => html_form::text_widget('operation', 128)
		);
		for ($i=2; $i<=8; $i++) {
		$a = array_merge($a,
			array (
			__("Date")." $i" => fm_date_entry('odate'.$i),
			__("Operation")." $i" => html_form::text_widget('operation'.$i, 128)
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

		// Get operations, and extract to an array
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['odate'].' '.$r['operation']);
		}
		return @join(', ', $m);
	} // end method recent_text

	function view ( ) {
		global $display_buffer;
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE (".$this->patient_field."='".addslashes($_REQUEST['patient'])."') ".
			freemed::itemlist_conditions(false)." ".
			( $condition ? 'AND '.$condition : '' )." ".
			"ORDER BY ".$this->order_fields;
		$result = $GLOBALS['sql']->query( $query );
		$display_buffer .= freemed_display_itemlist (
			$result,
			$this->page_name,
			array (
				__("Date") => 'odate',
				__("Operation") => 'operation'
			),
			array ( '', '' ),
			NULL, NULL, NULL,
			ITEMLIST_DEL
		);
	}

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
