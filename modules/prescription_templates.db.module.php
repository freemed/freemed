<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class PrescriptionTemplates extends MaintenanceModule {
	var $MODULE_NAME = 'Prescription Templates';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.1';
	var $MODULE_FILE = __FILE__;
	var $VENDOR = 'FreeMED Software Foundation';

	var $PACKAGE_MINIMUM_VERSION = '0.7.1';

	// This module is hidden because it doesn't *behave* like other
	// generic modules. It is meant to be run in a popup window.
	var $MODULE_HIDDEN = true;

	var $record_name = "Prescription Templates";
	var $table_name = "rx_templates";

	function PrescriptionTemplates () {
		// Check for, and if not there create, a user object
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Create table definition
		$this->table_definition = array(
			'rtname' => SQL__VARCHAR(150),
			'rtphy' => SQL__INT_UNSIGNED(0),
			'rtdrug' => SQL__VARCHAR(150),
			'rtform' => SQL__ENUM(array(
				'suspension',
				'tablet',
				'capsule',
				'solution',
				'scoops'
			)),
			'rtdosage' => SQL__INT_UNSIGNED(0),
			'rtsize' => SQL__INT_UNSIGNED(0),
			'rtunit' => SQL__ENUM(array(
				'mg',
				'mg/1cc',
				'mg/2cc',
				'mg/3cc',
				'mg/4cc',
				'mg/5cc',
				'g',
				'mcg'
			)),
			'rtinterval' => SQL__ENUM(array(
				'b.i.d.',
				't.i.d.',
				'q.i.d.',
				'q. 3h',
				'q. 4h',
				'q. 5h',
				'q. 6h',
				'q. 8h',
				'q.d.',
				'h.s.',
				'q.h.s.',
				'q.A.M.',
				'q.P.M.',
				'a.c.',
				'p.c.',
				'p.r.n.'
			)),
			'rtsubstitute' => SQL__ENUM(array(
				'may substitute',
				'may not substitute'
			)),
			'rtrefills' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Check for combo widget
		switch (freemed::config_value('drug_widget_type')) {
			case 'combobox':
			$rtdrug_chosen = html_form::combo_assemble('rtdrug');
			break;

			case 'rxlist':
			default:
			$rtdrug_chosen = $GLOBALS['rtdrug'];
			break;
		}

		$this->variables = array (
			'rtname',
			'rtphy' => $this_user->user_phy,
			'rtdrug' => $rtdrug_chosen,
			'rtform',
			'rtdosage',
			'rtsize',
			'rtinterval',
			'rtsubstitute',
			'rtrefills'
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor PrescriptionTemplates

	// function add
	function add () {
		// Set onLoad to reload parent template set
		$GLOBALS['__freemed']['on_load'] = 'process';
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Set this phy to be 'rtphy'
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

		// Set this phy to be 'rtphy'
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
			html_form::text_widget('rtname', 25, 150),

			__("Drug") =>
			freemed::drug_widget('rtdrug', 'myform', '__action'),

			__("Quantity") =>
			html_form::text_widget('rtquantity', 10),

			__("Medicine Units") =>
			html_form::text_widget('rxsize', 10).
			html_form::select_widget(
				'rtunit',
				array(
					'mg' => 'mg',
					'mg/1cc' => 'mg/1cc',
					'mg/2cc' => 'mg/2cc',
					'mg/3cc' => 'mg/3cc',
					'mg/4cc' => 'mg/4cc',
					'mg/5cc' => 'mg/5cc',
					'g' => 'g',
					'mcg' => 'mcg'
				)
			),

			__("Dosage") =>
			html_form::text_widget('rtdosage', 10).
			" ".__("in")." ".
			html_form::select_widget(
				'rtform',
				array(
					'suspension' => 'suspension',
					'tablet' => 'tablet',
					'capsule' => 'capsule',
					'solution' => 'solution',
					'scoops' => 'scoops'
				)
			)." ".
			html_form::select_widget(
				'rtinterval',
				array(
					'q.d.' => 'q.d.',
					'b.i.d.' => 'b.i.d.',
					't.i.d.' => 't.i.d.',
					'q.i.d.' => 'q.i.d.',
					'q. 3h' => 'q. 3h',
					'q. 4h' => 'q. 4h',
					'q. 5h' => 'q. 5h',
					'q. 6h' => 'q. 6h',
					'q. 8h' => 'q. 8h',
					'h.s.' => 'h.s.',
					'q.h.s.' => 'q.h.s.',
					'q.A.M.' => 'q.A.M.',
					'q.P.M.' => 'q.P.M.',
					'a.c.' => 'p.c.',
					'p.r.n.' => 'p.r.n.'
				)
			),

			__("Substitution") =>
			html_form::select_widget(	
				'rtsubstitute',
				array (
					__("may not substitute") => 'may not substitute',
					__("may substitute") => 'may substitute'
				)
			)
		
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
			"WHERE rtphy='".$GLOBALS['this_user']->user_phy."' OR rtphy=0 ".
			"ORDER BY rtname";
		$result = $GLOBALS['sql']->query($query);
		
		$add = "<input type=\"BUTTON\" onClick=\"rtPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=addform&formname=".
		urlencode($formname)."', 'rtPopup'); ".
		"rtPopup.opener=self; return true\" VALUE=\"".__("Add")."\" ".
		"class=\"button\"/>\n";

		// Make sure there are templates already
		if (!$GLOBALS['sql']->results($result)) {
			return $add;
		}

		// Add the "edit" button
		$add .= "<input type=\"BUTTON\" onClick=\"rtPopup=window.open(".
		"'".$this->page_name."?module=".get_class($this)."&varname=".
		urlencode($varname)."&action=modform&formname=".
		urlencode($formname)."&id='+document.".$formname.".".$varname.
		".value, 'rtPopup'); ".
		"rtPopup.opener=self; return true\" VALUE=\"".__("Edit")."\" ".
		"class=\"button\"/>\n";

		// Loop them into "options"
		$options = array();
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$options[prepare($r['rtname'])] = $r['id'];
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
				// Check for 'rt' prefix
				if (is_integer(strpos($k, 'rt'))) {
					$k = str_replace('rt', 'rx', $k);
					//print "k = $k, v = $v<br/>\n";
					global ${$k}; ${$k} = $v;
				}
			}

			// Reset
			${$varname.'_used'} = '';
		}
	} // end method retrieve

} // end class PrescrptionTemplates

register_module('PrescriptionTemplates');

?>
