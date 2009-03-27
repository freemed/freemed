<?php
 // $Id$
 // $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ProgressNotesTemplates extends MaintenanceModule {
	var $MODULE_NAME = "Progress Notes Templates";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	// This module is hidden because it doesn't *behave* like other
	// generic modules. It is meant to be run in a popup window.
	var $MODULE_HIDDEN = true;

	var $record_name = "Progress Notes Templates";
	var $table_name = "pntemplate";

	function ProgressNotesTemplates () {
		// Check for, and if not there create, a user object
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Create table definition
		$this->table_definition = array(
			'pntname' => SQL__VARCHAR(150),
			'pntphy' => SQL__INT_UNSIGNED(0),
			'pntS' => SQL__TEXT,
			'pntO' => SQL__TEXT,
			'pntA' => SQL__TEXT,
			'pntP' => SQL__TEXT,
			'pntI' => SQL__TEXT,
			'pntE' => SQL__TEXT,
			'pntR' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'pntname',
			'pntphy' => $this_user->user_phy,
			'pntS',
			'pntO',
			'pntA',
			'pntP',
			'pntI',
			'pntE',
			'pntR'
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor ProgressNotesTemplates

	// function add
	function add () {
		// Set onLoad to reload parent template set
		$GLOBALS['__freemed']['on_load'] = 'process';
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Set this phy to be 'pntphy'
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

		// Set this phy to be 'pntphy'
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
			html_form::text_widget('pntname', 25, 150),

			__("Subjective") =>
			freemed::rich_text_area('pntS', 10, 40, true),
		
			__("Objective") =>
			freemed::rich_text_area('pntO', 10, 40),
		
			__("Assessment") =>
			freemed::rich_text_area('pntA', 10, 40),
		
			__("Plan") =>
			freemed::rich_text_area('pntP', 10, 40),
		
			__("Interval") =>
			//freemed::rich_text_area('pntI', 10, 40),
			html_form::text_area('pntI', 'VIRTUAL', 10, 40),
		
			__("Education") =>
			freemed::rich_text_area('pntE', 10, 40),
		
			__("Rx") =>
			freemed::rich_text_area('pntR', 10, 40)
		
		))."
		</div>
		<p/>
		<div ALIGN=\"CENTER\">
			<input TYPE=\"SUBMIT\" VALUE=\"".(
				($action=="addform") ? __("Add") : __("Modify") )."\"/>
			<input TYPE=\"BUTTON\" VALUE=\"".__("Cancel")."\"
			 onClick=\"window.close(); return true;\"/>
		</div>
		</form>
		";
	}

	// function picklist
	// - generates a picklist widget of possible templates
	function picklist ($varname, $formname) {
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE pntphy='".$GLOBALS['this_user']->user_phy."' OR pntphy=0 ".
			"ORDER BY pntname";
		$result = $GLOBALS['sql']->query($query);
		
		$add = "<input type=\"BUTTON\" onClick=\"pntPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=addform&formname=".
		urlencode($formname)."', 'pntPopup'); ".
		"pntPopup.opener=self; return true\" VALUE=\"".__("Add")."\"/>\n";

		// Make sure there are templates already
		if (!$GLOBALS['sql']->results($result)) {
			return $add;
		}

		// Add the "edit" button
		$add .= "<input type=\"BUTTON\" onClick=\"pntPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=modform&formname=".
		urlencode($formname)."&id='+document.".$formname.".".$varname.
		".value, 'pntPopup'); ".
		"pntPopup.opener=self; return true\" VALUE=\"".__("Edit")."\"/>\n";

		// Loop them into "options"
		$options = array();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$options[prepare($r['pntname'])] = $r['id'];
		}
		
		return html_form::select_widget(
			$varname,
			$options
		)." ".
		"<input TYPE=\"SUBMIT\" VALUE=\"".__("Use")."\" ".
		"onClick=\"this.form.".$varname."_used.value = '1'; this.form.submit(); ".
		"return true;\"/> ".
		$add;
	} // end function ProgressNotesTemplates->picklist

	// function retrieve
	// - retrieves a template and inserts it locally into proper variables
	function retrieve ($varname) {
		global ${$varname}, ${$varname.'_used'};

		if (${$varname.'_used'} == 1) {
			// Get template
			$t = freemed::get_link_rec(${$varname}, $this->table_name);

			// Loop through values in record
			foreach ($t AS $k => $v) {
				// Check for 'pnt' prefix
				if (is_integer(strpos($k, 'pnt'))) {
					$k = str_replace('pnt', 'pnotes_', $k);
					global ${$k}; ${$k} = $v;
				}
				global $pnotesdescrip; $pnotesdescrip = $t['pntname'];
			}

			// Reset
			${$varname.'_used'} = 0;
		}
	} // end function ProgressNotesTemplates->picklist

} // end class ProgressNotesTemplates

register_module('ProgressNotesTemplates');

?>
