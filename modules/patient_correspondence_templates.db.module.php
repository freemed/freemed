<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class PatientCorrespondenceTemplates extends MaintenanceModule {
	var $MODULE_NAME = 'Patient Correspondence Templates';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $VENDOR = 'FreeMED Software Foundation';

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	// This module is hidden because it doesn't *behave* like other
	// generic modules. It is meant to be run in a popup window.
	var $MODULE_HIDDEN = true;

	var $record_name = "Patient Correspondence Templates";
	var $table_name = "patletters_templates";

	function PatientCorrespondenceTemplates () {
		// Check for, and if not there create, a user object
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Create table definition
		$this->table_definition = array(
			'ltname' => SQL__VARCHAR(150),
			'ltphy' => SQL__INT_UNSIGNED(0),
			'lttext' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'ltname',
			'ltphy' => $this_user->user_phy,
			'lttext'
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor PatientCorrespondenceTemplates

	// function add
	function add () {
		// Set onLoad to reload parent template set
		$GLOBALS['__freemed']['on_load'] = 'process';
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Set this phy to be 'ltphy'
		$result = $GLOBALS['sql']->query(
			$GLOBALS['sql']->insert_query(
				$this->table_name, 
				$this->variables
			)
		);

		// Put out proper JavaScript
		$GLOBALS['display_buffer'] .= "
			<script LANGUAGE=\"JavaScript\">
			function process() {
				opener.document.forms.".prepare($GLOBALS['formname']).".submit()
				window.self.close()
			}
			</script>
			";

		template_display();
	}
		
	// function mod
	function mod () {
		// Set onLoad to reload parent template set
		$GLOBALS['__freemed']['on_load'] = 'process';
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Set this phy to be 'ltphy'
		$result = $GLOBALS['sql']->query(
			$GLOBALS['sql']->update_query(
				$this->table_name, 
				$this->variables,
				array('id' => $GLOBALS['id'])
			)
		);

		// Put out proper JavaScript
		$GLOBALS['display_buffer'] .= "
			<script LANGUAGE=\"JavaScript\">
			function process() {
				opener.document.forms.".prepare($GLOBALS['formname']).".submit()
				window.self.close()
			}
			</script>
			";

		template_display();
	}
		
	// function form
	function form () {
		global $display_buffer, $module, $formname;

		// Get everything if modification
		if ($GLOBALS['action'] == 'modform') {
			$r = freemed::get_link_rec($GLOBALS['id'], $this->table_name);
			if (is_array($r)) {
				foreach ($r AS $k => $v) {
					global ${$k}; ${$k} = $v;
				}
			}
		}

		$GLOBALS['__freemed']['no_template_display'] = true;
		$display_buffer .= "
		<form ACTION=\"".$this->page_name."\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".
			prepare($GLOBALS['id'])."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
			( ($GLOBALS['action']=='addform') ? 'add' : 'mod' )."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"formname\" VALUE=\"".prepare($formname)."\"/>
		".html_form::form_table(array(
		
			__("Template Name") =>
			html_form::text_widget('ltname', 25, 150),

			__("Letter Template") =>
			//html_form::text_area('lttext', 'VIRTUAL', 20, 80)
			freemed::rich_text_area('lttext', 20, 80)
		
		))."
		</div>
		<p/>
		<div ALIGN=\"CENTER\">
			<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".(
				($action=="addform") ? __("Add") : __("Modify") )."\"/>
			<input class=\"button\" TYPE=\"BUTTON\" VALUE=\"".__("Cancel")."\"
			 onClick=\"window.close(); return true;\"/>
		</div>
		</form>
		";
	}

	// function picklist
	// - generates a picklist widget of possible templates
	function picklist ($varname, $formname) {
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ltphy='".$GLOBALS['this_user']->user_phy."' ".
			"ORDER BY ltname";
		$result = $GLOBALS['sql']->query($query);
		
		$add = "<input type=\"BUTTON\" onClick=\"ltPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=addform&formname=".
		urlencode($formname)."', 'ltPopup'); ".
		"ltPopup.opener=self; return true\" VALUE=\"".__("Add")."\" ".
		"class=\"button\"/>\n";

		// Make sure there are templates already
		if (!$GLOBALS['sql']->results($result)) {
			return $add;
		}

		// Add the "edit" button
		$add .= "<input type=\"BUTTON\" onClick=\"ltPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=modform&formname=".
		urlencode($formname)."&id='+document.".$formname.".".$varname.
		".value, 'ltPopup'); ".
		"ltPopup.opener=self; return true\" VALUE=\"".__("Edit")."\" ".
		"class=\"button\"/>\n";

		// Loop them into "options"
		$options = array();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$options[prepare($r['ltname'])] = $r['id'];
		}
		
		return html_form::select_widget(
			$varname,
			$options
		)." ".
		"<input name=\"".$varname."_used\" TYPE=\"SUBMIT\" ".
		"VALUE=\"".__("Use")."\" ".
		"onClick=\"this.form.submit(); ".
		"return true;\" ".
		"class=\"button\"/> ".
		$add;
	} // end method picklist

	// function retrieve
	// - retrieves a template and inserts it locally into proper variables
	function retrieve ($varname) {
		global ${$varname}, ${$varname.'_used'};

		if (${$varname.'_used'} == __("Use")) {
			// Get template
			$t = freemed::get_link_rec(${$varname}, $this->table_name);

			// Loop through values in record
			foreach ($t AS $k => $v) {
				// Check for 'lt' prefix
				if (is_integer(strpos($k, 'lt'))) {
					$k = str_replace('lt', 'letter', $k);
					//print "k = $k, v = $v<br/>\n";
					global ${$k}; ${$k} = $v;
				}
			}

			// Reset
			${$varname.'_used'} = '';
		}
	} // end method retrieve

} // end class PatientCorrespondenceTemplates

register_module('PatientCorrespondenceTemplates');

?>
