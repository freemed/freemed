<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class Forms extends EMRModule {

	var $MODULE_NAME    = "Form";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Custom forms module";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "f1a2619e-f54b-4190-b112-7b9cf7ff6212";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Forms";
	var $table_name     = "form_results";
	var $patient_field  = "fr_patient";
	var $widget_hash    = "##fr_timestamp## ##fr_formname##";

	public function __construct ( ) {
		// __("Forms")

		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => '_timestamp',
			__("Form") => 'fr_formname'
		);
		$this->summary_query = array (
			"DATE_FORMAT(fr_timestamp, '%b %d, %Y %H:%i') AS _timestamp"
		);
		$this->summary_options |= SUMMARY_PRINT | SUMMARY_DELETE;

		$this->acl = array ( 'emr' );

		// Run parent constructor
		parent::__construct ( );
	} // end constructor Forms

	function addform ( ) {
		if (!$_REQUEST['formtemplate']) {
			$this->addform_requesttemplate();
			return false;
		}

		$_REQUEST['formtemplate'] = ereg_replace('[^A-Za-z0-9_]', '', $_REQUEST['formtemplate']);
		$template = CreateObject('org.freemedsoftware.core.FormTemplate', $_REQUEST['formtemplate']);
		$controls = $template->GetControls();

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Call the appropriate method
				$widget = call_user_func(
					array($this, 'control_'.$v['type']),
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

	function addform_requesttemplate ( ) {
		$tlist = CreateObject('org.freemedsoftware.core.FormTemplateList');
		$items = $tlist->GetList();
		foreach ($items AS $item => $data) {
			$choices[$data['name']] = $item;
		}

		$GLOBALS['display_buffer'] .= "
		<form method=\"POST\">
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\" />
		<input type=\"hidden\" name=\"return\" value=\"".prepare($_REQUEST['return'])."\" />
		<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"addform\" />

		<div align=\"center\">
		".__("Choose Template")." : 
		".html_form::select_widget('formtemplate', $choices)."
		</div>

		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Choose")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		</form>
		";
	} // end method addform_requesttemplate

	function add ( ) {
		$_REQUEST['formtemplate'] = ereg_replace('[^A-Za-z0-9_]', '', $_REQUEST['formtemplate']);
		
		$template = CreateObject('org.freemedsoftware.core.FormTemplate', $_REQUEST['formtemplate']);
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
					array($this, 'control_'.$v['type']),
					'serialize',
					$v
				);

				// Build INSERT query
				$query = $GLOBALS['sql']->insert_query(
					'form_record',
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

	function modform ( ) {
		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		foreach ($rec AS $k => $v) { $_REQUEST[$k] = $v; }

		$template = CreateObject('org.freemedsoftware.core.FormTemplate', $rec['fr_template']);
		$controls = $template->GetControls();
		$template->LoadData($_REQUEST['id']);

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Set default value
				//$_REQUEST['variable_'.$v['variable']]
				$v['default'] = $template->FetchDataElement($v['variable']);

				// Call the appropriate method
				$widget = call_user_func(
					array($this, 'control_'.$v['type']),
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
		<input type=\"hidden\" name=\"template\" value=\"".prepare($rec['fr_template'])."\" />
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\" />
		<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"mod\" />

		".html_form::form_table($form)."

		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Modify")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		</form>
		";
	} // end method modform

	function mod ( ) {
		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		
		$template = CreateObject('org.freemedsoftware.core.FormTemplate', $rec['fr_template']);
		$information = $template->GetInformation();
		$controls = $template->GetControls();

		// Only update timestamp on master record
		$fr_query = $GLOBALS['sql']->update_query(
			$this->table_name,
			array (
				'fr_timestamp' => SQL__NOW,
			),
			array ( 'id' => $_REQUEST['id'] )
		);
		$fr_result = $GLOBALS['sql']->query ( $fr_query );

		// Get id for association
		$fid = $_REQUEST['id'];

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Call the appropriate method
				$value = call_user_func(
					array($this, 'control_'.$v['type']),
					'serialize',
					$v
				);

				// Build UPDATE query
				$query = "UPDATE form_record SET fr_value = '".addslashes($value)."' WHERE fr_id='".addslashes($fid)."' AND fr_uuid='".$v['uuid']."'";
				$result = $GLOBALS['sql']->query ( $query );
			}
		} // end foreach controls

		// Return to where we came from:
		if ($_REQUEST['return'] == 'manage') {
			$GLOBALS['refresh'] = "manage.php?id=".urlencode($_REQUEST['patient']);
		} else {
			$GLOBALS['refresh'] = "module_loader.php?module=".urlencode(get_class($this));
		}
	} // end method mod

	function del ( $_id = NULL ) {
		$id = $_id ? $_id : $_REQUEST['id'];

		if (!freemed::lock_override()) {
			if ($this->locked($id)) {
				$GLOBALS['display_buffer'] .= __("Record is locked.");
				return false;
			}
		}

		// Delete all attached pieces
		$q = "DELETE FROM form_record WHERE fr_id = '".addslashes($id)."'";
		$GLOBALS['sql']->query($q);

		// Stock deletion routine
		$this->_del();
	} // end method del

	//----- Print Override ----------------------------------------------

	public function print_override ( $id ) {
		// Get actual record text
		$rec = $GLOBALS['sql']->get_link ( $this->table_name, $id );

		// Render
		$t = CreateObject( 'org.freemedsoftware.core.FormTemplate', $rec['fr_template'] );
		$t->LoadData( $id );
		$data = $t->OutputData();

		// Return file name to the calling function
		return $t->RenderToPDF( $data, false );
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
			if (!$_REQUEST['variable_'.$data['variable']]) {
				$GLOBALS['variable_'.$data['variable']] =
				$_REQUEST['variable_'.$data['variable']] =
				$data['default'];
			}
			return fm_date_entry( 'variable_'.$data['variable'] );
		}
	} // end method control_date

	function control_module ( $action, $data ) {
		if ($action == 'serialize') {
			return $_REQUEST['variable_'.$data['variable']];
		} elseif ($action == 'widget') {
			if (!$_REQUEST['variable_'.$data['variable']]) {
				$GLOBALS['variable_'.$data['variable']] =
				$_REQUEST['variable_'.$data['variable']] =
				$data['default'];
			}
			return module_function(
				$data['options'],
				'widget',
				array ( 'variable_'.$data['variable'] )
			);
		}
	} // end method control_module

	function control_multiple ( $action, $data ) {
		if ($action == 'serialize') {
			return join(',', $_REQUEST['variable_'.$data['variable']]);
		} elseif ($action == 'widget') {
			if ($data['default']) {
				$default = explode('|', $data['default']);
			}
			$buffer .= "<select name=\"variable_".$data['variable']."[]\" multiple=\"multiple\" size=\"10\">\n";
			foreach ( explode('|', $data['options']) AS $v) {
				$selected = false;
				foreach ($default AS $d) { if ($d == $v) { $selected = true; } }
				$buffer .= "\t<option value=\"${v}\"".( $selected ? ' SELECTED' : '' ).">${v}</option>\n";
			}
			$buffer .= "</select>\n";
			return $buffer;
		}
	} // end method control_multiple

	function control_phone ( $action, $data ) {
		if ($action == 'serialize') {
			return fm_phone_assemble('variable_'.$data['variable']);
		} elseif ($action == 'widget') {
			if (!$_REQUEST['variable_'.$data['variable']]) {
				$GLOBALS['variable_'.$data['variable']] =
				$_REQUEST['variable_'.$data['variable']] =
				$data['default'];
			}
			$buffer .= fm_phone_entry('variable_'.$data['variable']);
			return $buffer;
		}
	} // end method control_phone

	function control_select ( $action, $data ) {
		if ($action == 'serialize') {
			return $_REQUEST['variable_'.$data['variable']];
		} elseif ($action == 'widget') {
			if (!$_REQUEST['variable_'.$data['variable']]) {
				$GLOBALS['variable_'.$data['variable']] =
				$_REQUEST['variable_'.$data['variable']] =
				$data['default'];
			}
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
			if (!$_REQUEST['variable_'.$data['variable']]) {
				$GLOBALS['variable_'.$data['variable']] =
				$_REQUEST['variable_'.$data['variable']] =
				$data['default'];
			}
			return html_form::text_widget(
				'variable_'.$data['variable'],
				array (
					'length' => ( $data['limits'] ? $data['limits'] : 20 )
				)
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

} // end class Forms

register_module ("Forms");

?>
