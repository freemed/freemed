<?php
 // $Id$
 // $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class EncounterNotesTemplates extends MaintenanceModule {
	var $MODULE_NAME = "Encounter Notes Templates";
	var $MODULE_AUTHOR = "RPL (RPL121@verizon.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	// This module is hidden because it doesn't *behave* like other
	// generic modules. It is meant to be run in a popup window.
	var $MODULE_HIDDEN = true;

	var $record_name = "Encounter Notes Templates";
	var $table_name = "entemplate";

	function EncounterNotesTemplates () {
		// Check for, and if not there create, a user object
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

		// Create table definition
		$this->table_definition = array(
			'pntname' => SQL__VARCHAR(150),
			'pntphy' => SQL__INT_UNSIGNED(0),
			'pnt_S' => SQL__TEXT,
			'pnt_O' => SQL__TEXT,
			'pnt_A' => SQL__TEXT,
			'pnt_P' => SQL__TEXT,
			'pnt_I' => SQL__TEXT,
			'pnt_E' => SQL__TEXT,
			'pnt_R' => SQL__TEXT,
                        'pntgeneral' => SQL__TEXT,
			'pntcc' => SQL__TEXT,
			'pnthpi' => SQL__TEXT,
			'pntroscons' => SQL__TEXT,
			'pntroseyes' => SQL__TEXT,
			'pntrosent' => SQL__TEXT,
			'pntroscv' =>  SQL__TEXT,
			'pntrosresp' =>  SQL__TEXT,
			'pntrosgi' =>  SQL__TEXT,
			'pntrosgu' =>  SQL__TEXT,
			'pntrosms' =>  SQL__TEXT,
			'pntrosskinbreast' =>  SQL__TEXT,
			'pntrosneuro' =>  SQL__TEXT,
			'pntrospsych' =>  SQL__TEXT,
			'pntrosendo' =>  SQL__TEXT,
			'pntroshemelymph' =>  SQL__TEXT,
			'pntrosallergyimmune' =>  SQL__TEXT,
			'pntph' =>  SQL__TEXT,
			'pntfh' =>  SQL__TEXT,
			'pntsh' =>  SQL__TEXT,
			'pntpeeyes' => SQL__TEXT,
			'pntpeent' => SQL__TEXT,
			'pntpeneck' => SQL__TEXT,
			'pntperesp' => SQL__TEXT,
			'pntpecv' => SQL__TEXT,
			'pntpechestbreast' => SQL__TEXT,
			'pntpegiabd' => SQL__TEXT,
			'pntpegu' => SQL__TEXT,
			'pntpelymph' => SQL__TEXT,
			'pntpems' => SQL__TEXT,
			'pntpeskin' => SQL__TEXT,
			'pntpeneuro' => SQL__TEXT,
			'pntpepsych' => SQL__TEXT,
			'pnthandp' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		$this->variables = array (
			'pntname',
			'pntphy' => $this_user->user_phy,
			'pnt_S',
			'pnt_O',
			'pnt_A',
			'pnt_P',
			'pnt_I',
			'pnt_E',
			'pnt_R',
			'pntgeneral',
			'pntcc',
			'pnthpi',
			'pntroscons',
			'pntroseyes',
			'pntrosent',
			'pntroscv',
			'pntrosresp',
			'pntrosgi',
			'pntrosgu',
			'pntrosms',
			'pntrosskinbreast',
			'pntrosneuro',
			'pntrospsych',
			'pntrosendo',
			'pntroshemelymph',
		        'pntrosallergyimmune',
			'pntph',
			'pntfh',
			'pntsh',
			'pntpeeyes',
			'pntpeent',
			'pntpeneck',
			'pntperesp',
			'pntpecv',
			'pntpechestbreast',
			'pntpegiabd',
			'pntpegu',
			'pntpelymph',
			'pntpems',
			'pntpeskin',
		        'pntpeneuro',
			'pntpepsych',
			'pnthandp'
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor EncounterNotesTemplates

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

 //RPL changed 'addform' to 'modform a few lines below

		$GLOBALS['__freemed']['no_template_display'] = true;
		$display_buffer .= "
		<form ACTION=\"".$this->page_name."\" METHOD=\"POST\">
	       	<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".
			prepare($GLOBALS['id'])."\"/>
		<input TYPE=\"TEXT\" NAME=\"action\" VALUE=\"".
			( ($GLOBALS['action']=='modform') ? 'add' : 'mod' )."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"formname\" VALUE=\"".prepare($formname)."\"/>
		".html_form::form_table(array(
		
			__("Template Name") =>
			html_form::text_widget('pntname', 25, 150),

			__("Subjective") =>
			html_form::text_area('pnt_S', 'VIRTUAL', 5, 60, true),
		
			__("Objective") =>
			html_form::text_area('pnt_O', 'VIRTUAL', 5, 60),
		
			__("Assessment") =>
			html_form::text_area('pnt_A', 'VIRTUAL', 5, 60),
		
			__("Plan") =>
			html_form::text_area('pnt_P', 'VIRTUAL', 5, 60),
		
			__("Interval") =>
			html_form::text_area('pnt_I', 'VIRTUAL', 5, 60),
		
			__("Education") =>
			html_form::text_area('pnt_E', 'VIRTUAL', 5, 60),
		
			__("Rx") =>
			html_form::text_area('pnt_R', 'VIRTUAL', 5, 60),
					      
			__("General") =>
		       html_form::text_area('pntgeneral', 'VIRTUAL', 5, 60),
                        
		       __("Chief Complaint") =>
			html_form::text_area('pntcc', 'VIRTUAL', 5, 60),
			
		         __("HPI") =>
			html_form::text_area('pnthpi', 'VIRTUAL', 5, 60),
		
			__("ROS Constitutional") =>
			html_form::text_area('pntroscons', 'VIRTUAL', 5, 60),
		
			__("ROS Eyes") =>
			html_form::text_area('pntroseyes', 'VIRTUAL', 5, 60),
		
			__("ROS ENT") =>
			html_form::text_area('pntrosent', 'VIRTUAL', 5, 60),
		
		       __("ROS CV") =>
			html_form::text_area('pntroscv', 'VIRTUAL', 5, 60),
		
			__("ROS Resp") =>
			html_form::text_area('pntrosresp', 'VIRTUAL', 5, 60),
		
			__("ROS GI") =>
			html_form::text_area('pntrosgi', 'VIRTUAL', 5, 60),
					      
			__("ROS GU") =>
		       html_form::text_area('pntrosgu', 'VIRTUAL', 5, 60),

		     	__("ROS MS") =>
			html_form::text_area('pntrosms', 'VIRTUAL', 5, 60),
		
			__("ROS Skin/breast") =>
			html_form::text_area('pntrosskinbreast', 'VIRTUAL', 5, 60),
		
			__("ROS Neuro") =>
			html_form::text_area('pntrosneuro', 'VIRTUAL', 5, 60),
		
			__("ROS Psych") =>
			html_form::text_area('pntrospsych', 'VIRTUAL', 5, 60),
		
			__("ROS Endocrine") =>
			html_form::text_area('pntrosendo', 'VIRTUAL', 5, 60),
		
			__("ROS Heme/lymph") =>
			html_form::text_area('pntroshemelymph', 'VIRTUAL', 5, 60),
					      
			__("ROS Allergy/Immune") =>
		       html_form::text_area('pntrosallergyimmune', 'VIRTUAL', 5, 60),
		
			__("PH") =>
			html_form::text_area('pntph', 'VIRTUAL', 5, 60),
		
			__("FH") =>
			html_form::text_area('pntfh', 'VIRTUAL', 5, 60),
		
			__("SH") =>
			html_form::text_area('pntsh', 'VIRTUAL', 5, 60),
		
			__("PE Eyes") =>
			html_form::text_area('pntpeeyes', 'VIRTUAL', 5, 60),
		
		       __("PE ENT") =>
			html_form::text_area('pntpeent', 'VIRTUAL', 5, 60),
		
			__("PE Neck") =>
			html_form::text_area('pntpeneck', 'VIRTUAL', 5, 60),
		
			__("PE Resp") =>
			html_form::text_area('pntperesp', 'VIRTUAL', 5, 60),
					      
			__("PE CV") =>
		       html_form::text_area('pntpecv', 'VIRTUAL', 5, 60),
					      
		        __("PE Chest/breast") =>
			html_form::text_area('pntpechestbreast', 'VIRTUAL', 5, 60),
					      
			__("PE GI/abdomen") =>
		       html_form::text_area('pntpegiabd', 'VIRTUAL', 5, 60),
		
			__("PE GU") =>
			html_form::text_area('pntpegu', 'VIRTUAL', 5, 60),
		
			__("PE Lymph") =>
			html_form::text_area('pntpelymph', 'VIRTUAL', 5, 60),
		
			__("PE MS") =>
			html_form::text_area('pntpems', 'VIRTUAL', 5, 60),
		
			__("PE Skin") =>
			html_form::text_area('pntpeskin', 'VIRTUAL', 5, 60),
		
		       __("PE Neuro") =>
			html_form::text_area('pntpeneuro', 'VIRTUAL', 5, 60),
		
			__("PE Psych") =>
			html_form::text_area('pntpepsych', 'VIRTUAL', 5, 60),
		
			__("H&P") =>
			freemed::rich_text_area('pnthandp', 10, 40)
					      
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
//RPL			"WHERE pntphy='".$GLOBALS['this_user']->user_phy."' ".
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
	} // end function EncounterNotesTemplates->picklist

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
					$k = str_replace('pnt', 'pnotes', $k);
					global ${$k}; ${$k} = $v;
				}
			}

			// Reset
			${$varname.'_used'} = 0;
		}
	} // end function EncounterNotesTemplates->picklist

} // end class EncounterNotesTemplates

register_module('EncounterNotesTemplates');

?>

