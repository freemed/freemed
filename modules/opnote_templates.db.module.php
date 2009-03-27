<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class OpnoteTemplates extends MaintenanceModule {
	var $MODULE_NAME = 'Operative Note Templates';
	var $MODULE_AUTHOR = 'wadenaziri waden1@earthlink.net';
	var $MODULE_VERSION = '0.4.1';
	var $MODULE_FILE = __FILE__;
	var $VENDOR = 'FreeMED Software Foundation';
	
	var $DEPENDENCY = array (
		'FreeMED_Package' => '0.6.0',
		'OpnoteModule' => '0.3'
	);

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	// This module is hidden because it doesn't *behave* like other
	// generic modules. It is meant to be run in a popup window.
	var $MODULE_HIDDEN = true;

	var $record_name = "Operative Note Templates";
	var $table_name = "optemplate";

	function OpnoteTemplates () {
		// Check for, and if not there create, a user object
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Create table definition
		$this->table_definition = array(
			'optname' => SQL__VARCHAR(150),
			'optdoc' => SQL__INT_UNSIGNED(0),
			'optdescrip' => SQL__TEXT,
			'optpreopdx' => SQL__TEXT,
			'optpostopdx' => SQL__TEXT,
			'optprocedure' => SQL__TEXT,
			'optassistant' => SQL__TEXT,
			'optEBL' => SQL__TEXT,
			'optdrains' => SQL__TEXT,
			'optcomplications' => SQL__TEXT,
			'optfindings' => SQL__TEXT,
			'optindications' => SQL__TEXT,
			'opttext' => SQL__TEXT,
			'optfinalcount' => SQL__TEXT,
			'optanesthesia' => SQL__TEXT,
			'optlocalanesthesia' => SQL__TEXT,
			'optanesthprov' => SQL__VARCHAR(150),
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'optname',
			'optdoc' => $this_user->user_phy,
			'optdescrip',
			'optpreopdx',
			'optpostopdx',
			'optprocedure',
			'optassistant',
			'optEBL',
			'optdrains',
			'optcomplications',
			'optfindings',
			'optindications',
			'opttext',
			'optfinalcount',
			'optanesthesia',
			'optlocalanesthesia',
			'optanesthprov'
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor HistoryPhysicalTemplates

	// function add
	function add () {
		// Set onLoad to reload parent template set
		$GLOBALS['__freemed']['on_load'] = 'process';
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Set this phy to be 'optdoc'
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

		// Set this phy to be 'optdoc'
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
			html_form::text_widget('optname', 25, 150),
			
			__("Description") =>
			html_form::text_area('optdescrip', 'VIRTUAL', 2, 40),
			
			__("Surgeon") =>
			html_form::text_widget('optdoc', 25, 150),
		
			__("Assistant") =>
			html_form::text_widget('optassistant', 25, 150),
			
			__("Preoperative Diagnosis") =>
			html_form::text_area('optpreopdx', 'VIRTUAL', 2, 40),
			
			__("Postoperative Diagnosis") =>
			html_form::text_area('optpostopdx', 'VIRTUAL', 2, 40),
		
			__("Procedure") =>
			html_form::text_area('optprocedure', 'VIRTUAL', 2, 40),
			
			__("Anesthesia") =>
			html_form::select_widget(
				'optanesthesia',
				array(
					__("NONE") => "",
					__("General Anesthesia") => "General Endotracheal",
					__("General and Local") => "General and Local Anesthesia",
					__("Local with Sedations") => "Local with Sedations",
					__("MAC with local") => "MAC with local anesthesia",
					__("MAC with topical") => "MAC with topical anesthesia",
					__("Regional") => "Regional Anesthesia",
					__("Spinal") => "Spinal Anesthesia",
					__("Local") => "Local Anesthesia"
				)
			),

			__("Anesthesia Provider") =>
			html_form::text_widget('optanesthprov', 150),
			
			__("Local Anesthesia") =>
			html_form::text_widget('optlocalanesthesia', 150),
			
			__("Final Count") =>
			html_form::select_widget(
				'optfinalcount',
				array(
					__("Not Noted") => "",
					__("Correct") => "Correct",
					__("Incorrect") => "Incorrect"
				)
			),
			
			__("Estimated Blood Loss") =>
			html_form::text_widget('optEBL', 25, 150),
		
			__("Drains") =>
			html_form::text_widget('optdrains', 25, 150),
			
			__("Complications") =>
			html_form::text_widget('optcomplications', 25, 150),
			
			__("Findings") =>
			html_form::text_area('optfindings', 'VIRTUAL', 2, 40),
			
			__("Indications") =>
			html_form::text_area('optindications', 'VIRTUAL', 2, 40),
			
			__("Operation") =>
			html_form::text_area('opttext', 'VIRTUAL', 4, 40),
			
			)
		)."
		</div>
		<p/>
		<div ALIGN=\"CENTER\">
			<input class=\"button\" ".
				"TYPE=\"SUBMIT\" VALUE=\"".(
				($action=="addform") ? __("Add") : __("Modify") )."\"/>
			<input class=\"button\" ".
				"TYPE=\"BUTTON\" VALUE=\"".__("Cancel")."\"
			 onClick=\"window.close(); return true;\"/>
		</div>
		</form>
		";
	}

	// function picklist
	// - generates a picklist widget of possible templates
	function picklist ($varname, $formname) {
		$query = "SELECT *,LCASE(optname) AS lc ".
			"FROM ".$this->table_name." ".
			"WHERE optdoc='".$GLOBALS['this_user']->user_phy."' OR optdoc=0 ".
			"ORDER BY optdoc, lc";
		$result = $GLOBALS['sql']->query($query);
		
		$add = "<input type=\"BUTTON\" onClick=\"optPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=addform&formname=".
		urlencode($formname)."', 'optPopup'); ".
		"optPopup.opener=self; return true\" VALUE=\"".__("Add")."\" ".
		"class=\"button\"/>\n";

		// Make sure there are templates already
		if (!$GLOBALS['sql']->results($result)) {
			return $add;
		}

		// Add the "edit" button
		$add .= "<input type=\"BUTTON\" onClick=\"optPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=modform&formname=".
		urlencode($formname)."&id='+document.".$formname.".".$varname.
		".value, 'optPopup'); ".
		"optPopup.opener=self; return true\" VALUE=\"".__("Edit")."\" ".
		"class=\"button\"/>\n";

		// Loop them into "options"
		$options = array();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$options[prepare($r['optname'])] = $r['id'];
		}
		
		return html_form::select_widget(
			$varname,
			$options
		)." ".
		"<input TYPE=\"SUBMIT\" VALUE=\"".__("Use")."\" ".
		"onClick=\"this.form.".$varname."_used.value = '1'; this.form.submit(); ".
		"return true;\" ".
		"class=\"button\"/> ".
		$add;
	} // end function HistoryPhysicalTemplates->picklist

	// function retrieve
	// - retrieves a template and inserts it locally into proper variables
	function retrieve ($varname) {
		global ${$varname}, ${$varname.'_used'};

		if (${$varname.'_used'} == 1) {
			// Get template
			$t = freemed::get_link_rec(${$varname}, $this->table_name);

			// Loop through values in record
			foreach ($t AS $k => $v) {
				// Check for 'opt' prefix
				if (is_integer(strpos($k, 'opt'))) {
					$k = str_replace('opt', 'opnote', $k);
					global ${$k}; ${$k} = $v;
				}
			}

			// Reset
			${$varname.'_used'} = 0;
		}
	} // end function HistoryPhysicalTemplates->picklist

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Version 0.4
		//
		//	Add additional fields for anesth prov and local anesth
		//
		if (!version_check($version, '0.4')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN optlocalanesthesia TEXT AFTER optanesthesia');
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN optanesthprov VARCHAR(150) AFTER optlocalanesthesia');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET optlocalanesthesia=\'\', optanesthprov=\'\' WHERE id>0');
		}

		// Version 0.4.1
		//
		//	optfinalcout -> optfinalcount
		//
		if (!version_check($version, '0.4.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN optfinalcout optfinalcount TEXT');
		}
	} // end method _update

} // end class HistoryPhysicalTemplates

register_module("OpnoteTemplates");

?>
