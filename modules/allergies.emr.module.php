<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class AllergiesModule extends EMRModule {

	var $MODULE_NAME = "Allergies";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Allergies";
	var $table_name = 'allergies';
	var $patient_field = 'patient';
	var $date_field = 'reviewed';
	var $widget_hash = '##allergy## (##severity##)';

	function AllergiesModule () {
		$this->table_definition = array (
			'allergy' => SQL__VARCHAR(150),
			'severity' => SQL__VARCHAR(150),
			'patient' => SQL__INT_UNSIGNED(0),
			'reviewed' => SQL__TIMESTAMP(14),
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'allergy' => html_form::combo_assemble('allergy'),
			'severity' => html_form::combo_assemble('severity'),
			'patient',
			'reviewed' => SQL__NOW
		);

		$this->summary_vars = array (
			__("Allergy") => 'allergy',
			__("Reaction") => 'severity',
			__("Reviewed") => '_reviewed'
		);
		$this->summary_query = array (
			"DATE_FORMAT(reviewed, '%m/%d/%Y') AS _reviewed"
		);
		$this->summary_options = SUMMARY_DELETE;

		// call parent constructor
		$this->EMRModule();
	} // end constructor AllergiesModule

        function summary_bar ($patient) {
                return "
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&return=manage\">".__("View/Manage")."</a> |
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&action=addform&return=manage\">".__("Add")."</a> |
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&subaction=review&return=manage\">".__("Review All")."</a>
		";
	} // end function summary_bar

	function add () {
		// Save original values
		$v = array(
			'allergy' => $_REQUEST['allergy'],
			'allergy_text' => $_REQUEST['allergy_text'],
			'severity' => $_REQUEST['severity'],
			'severity_text' => $_REQUEST['severity_text'],
		);
		// Loop through all possibles
		for ($i=2; $i<=5; $i++) {
			// Only add if it looks like we have values
			if ($_REQUEST['allergy'.$i] or $_REQUEST['allergy'.$i.'_text']) {
				$this->_add(array(
					'allergy' => $_REQUEST['allergy'.$i],
					'allergy_text' => $_REQUEST['allergy'.$i.'_text'],
					'severity' => $_REQUEST['severity'.$i],
					'severity_text' => $_REQUEST['severity'.$i.'_text'],
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
			'allergy' => ( $p['allergy_text'] ? $p['allergy_text'] : $p['allergy'] ),
			'severity' => ( $p['severity_text'] ? $p['severity_text'] : $p['severity'] ),
			'patient' => $_REQUEST['patient'],
			'reviewed' => SQL__NOW
		);
	}

	function form_table ( ) {
		$a = array (
			__("Allergy") =>
			html_form::combo_widget(
				'allergy',
				$GLOBALS['sql']->distinct_values('allergies','allergy')
			),

			__("Reaction") =>
			html_form::combo_widget(
				'severity',
				$GLOBALS['sql']->distinct_values('allergies','severity')
			)
		);
		for ($i=2; $i<=5; $i++) {
		$a = array_merge($a,
			array (
			__("Allergy")." $i" =>
			html_form::combo_widget(
				'allergy'.$i,
				$GLOBALS['sql']->distinct_values('allergies','allergy')
			),

			__("Reaction")." $i" =>
			html_form::combo_widget(
				'severity'.$i,
				$GLOBALS['sql']->distinct_values('allergies','severity')
			)
		)
		);
		}
		return $a;
	} // end method form_table

	function view ( ) {
		if ($_REQUEST['subaction'] == 'review') {
			$this->review_all();
		}
		
		global $sql; global $display_buffer; global $patient;
		$display_buffer .= freemed_display_itemlist (
			$sql->query("SELECT * FROM ".$this->table_name." ".
				"WHERE patient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY allergy"),
			$this->page_name,
			array(
				__("Allergy") => 'allergy',
				__("Reaction") => 'severity'
			),
			array('', __("Not specified")) //blanks
		);
	} // end method view

	function review_all () {
		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array ( 'reviewed' => SQL__NOW ),
			array ( 'patient' => $_REQUEST['patient'] )
		);
		$r = $GLOBALS['sql']->query($q);
	} // end method review_all

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->date_field." DESC";
		$res = $GLOBALS['sql']->query($query);
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['allergy']).' ('.trim($r['severity']).')';
		}
		return @join(', ', $m);
	} // end method recent_text

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		// Version 0.2
		//
		//	Migrated to seperate table ...
		//
		if (!version_check($version, '0.2')) {
			// Create new table
			$sql->query($sql->create_table_query($this->table_name, $this->table_definition, array('id')));
			// Migrate old entries
			$q = $sql->query("SELECT ptallergies,id FROM patient WHERE LENGTH(ptallergies) > 3");
			if ($sql->results($q)) {
				while ($r = $sql->fetch_array($q)) {
					$e = sql_expand($r['ptallergies']);
					foreach ($e AS $a) {
						$sql->query($sql->insert_query(
							$this->table_name,
							array(
								'allergy' => $a,								'severity' => '',
								'patient' => $r['id']	
							)
						));
					} // end foreach entry
				} // end loop through patient entries
			} // end checking for results
		}

		// Version 0.2.1
		//
		//	Add "reviewed" field
		//
		if (!version_check($version, '0.2.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN reviewed TIMESTAMP(14) AFTER patient');
		}
	} // end method _update

} // end class AllergiesModule

register_module ("AllergiesModule");

?>
