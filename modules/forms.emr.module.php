<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class FormsModule extends EMRModule {

	var $MODULE_NAME    = "Forms";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Custom forms module";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Forms";
	var $table_name     = "form_results";
	var $patient_field  = "fr_patient";

	function FormsModule () {
		// __("Forms")

		// Table definition
		$this->table_definition = array (
			'fr_patient' => SQL__INT_UNSIGNED(0),
			'fr_timestamp' => SQL__TIMESTAMP(16),
			'fr_template' => SQL__VARCHAR(50),
			'fr_formname' => SQL__VARCHAR(50),
			'id' => SQL__SERIAL
		);
	
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => '_timestamp',
			__("Form") => 'fr_formname'
		);
		$this->summary_query = array (
			"DATE_FORMAT(fr_timestamp, '%b %d, %Y %H:%i') AS _timestamp"
		);
		$this->summary_options |= SUMMARY_PRINT;

		$this->acl = array ( 'emr' );

		// Run parent constructor
		$this->EMRModule();
	} // end constructor FormsModule

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
				__("Date") => 'fr_timestamp',
				__("Form") => 'fr_formname'
			),
			array ("")
		);
	} // end method view

	function addform ( ) {
		if (!$_REQUEST['formtemplate']) {
			trigger_error(__("You must specify a template."), E_USER_ERROR);
		}

		$_REQUEST['formtemplate'] = ereg_replace('[^A-Za-z0-9_]', '', $_REQUEST['formtemplate']);
		$template = CreateObject('_FreeMED.FormTemplate', $_REQUEST['formtemplate']);
		$controls = $template->GetControls();

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Call the appropriate method
				$widget = call_user_func(
					array(&$this, 'control_'.$v['type']),
					'widget',
					$v
				);

				// Push into form
				$form[$v['name']] = $widget;
			}
		} // end foreach controls

		$GLOBALS['display_buffer'] .= "
		<form method=\"POST\">
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\" />
		<input type=\"hidden\" name=\"return\" value=\"".prepare($_REQUEST['return'])."\" />
		<input type=\"hidden\" name=\"formtemplate\" value=\"".prepare($_REQUEST['formtemplate'])."\" />
		<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"add\" />

		".html_form::form_table($form)."

		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Add")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		</form>
		";
	} // end method addform

	function add ( ) {
		$_REQUEST['formtemplate'] = ereg_replace('[^A-Za-z0-9_]', '', $_REQUEST['formtemplate']);
		
		$template = CreateObject('_FreeMED.FormTemplate', $_REQUEST['formtemplate']);
		$information = $template->GetInformation();
		$controls = $template->GetControls();

		// Create local record
		$fr_query = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array (
				'fr_timestamp' => SQL__NOW,
				'fr_patient' => $_REQUEST['patient'],
				'fr_template' => $_REQUEST['formtemplate'],
				'fr_formname' => $information['name']
			)
		);
		$fr_result = $GLOBALS['sql']->query ( $fr_query );

		// Get id for association
		$fid = $GLOBALS['sql']->last_record ( $fr_result );

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Call the appropriate method
				$value = call_user_func(
					array(&$this, 'control_'.$v['type']),
					'serialize',
					$v
				);

				// Build INSERT query
				$query = $GLOBALS['sql']->insert_query(
					'form_results',
					array (
						'fr_id' => $fid,
						'fr_uuid' => $v['uuid'],
						'fr_name' => $v['name'],
						'fr_value' => $value
					)
				);
				$result = $GLOBALS['sql']->query ( $query );
			}
		} // end foreach controls

		// Return to where we came from:
		if ($_REQUEST['return'] == 'manage') {
			$GLOBALS['refresh'] = "manage.php?id=".urlencode($_REQUEST['patient']);
		} else {
			$GLOBALS['refresh'] = "module_loader.php?module=".urlencode(get_class($this));
		}
	} // end method add

	//----- Print Override ----------------------------------------------

	function print_override ( $id ) {
		// Get actual record text
		$rec = freemed::get_link_rec ( $id, $this->table_name );

		// Render
		$t = CreateObject( '_FreeMED.FormTemplate', $rec['fr_template'] );
		$t->LoadData( $id );
		$data = $t->OutputData();

		// Return file name to the calling function
		return $t->RenderToPDF($data, false);
	} // end method print_override

	//----- Controls ----------------------------------------------------

	function control_boolean ( $action, $data ) {
		if ($action == 'serialize') {
			return $_REQUEST['variable_'.$data['variable']];
		} elseif ($action == 'widget') {
			return html_form::select_widget(
				'variable_'.$data['variable'],
				array (
					__("no")  => 0,
					__("yes") => 1
				)
			);
		}
	} // end method control_boolean

	function control_date ( $action, $data ) {
		if ($action == 'serialize') {
			return fm_date_assemble ( 'variable_'.$data['variable'] );
		} elseif ($action == 'widget') {
			return fm_date_entry( 'variable_'.$data['variable'] );
		}
	} // end method control_date

	function control_select ( $action, $data ) {
		if ($action == 'serialize') {
			return $_REQUEST['variable_'.$data['variable']];
		} elseif ($action == 'widget') {
			return html_form::select_widget(
				'variable_'.$data['variable'],
				explode('|', $data['options'])
			);
		}
	} // end method control_select

	function control_string ( $action, $data ) {
		if ($action == 'serialize') {
			return $_REQUEST['variable_'.$data['variable']];
		} elseif ($action == 'widget') {
			return html_form::text_widget(
				'variable_'.$data['variable'],
				( $data['limits'] ? $data['limits'] : 20 )
			);
		}
	} // end method control_string

	/*
	function control_ ( $action, $data ) {
		if ($action == 'serialize') {
			return $_REQUEST['variable_'.$data['variable']];
		} elseif ($action == 'widget') {
		}
	} // end method control_
	*/

} // end class FormsModule

register_module ("FormsModule");

?>
