<?php
 // $Id$
 // note: letters of referral, etc
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class LettersModule extends EMRModule {

	var $MODULE_NAME    = "Letters";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Letters";
	var $table_name     = "letters";
	var $patient_field  = "letterpatient";

	function LettersModule () {
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "letterdt",
			__("To")   => "letterto:physician"
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW;

		// For display action, disable patient box for print
		// but only if we're the correct module
		global $action, $module;
		if (($action=="display") and (strtolower($module)==get_class($this))) {
			$this->disable_patient_box = true;
		}

		// Variables for add/mod
		global $patient;
		$this->variables = array (
			"letterdt" => fm_date_assemble("letterdt"),
			"lettereoc",
			"letterfrom",
			"letterto",
			"lettertext",
			"letterpatient" => $patient
		);

		// Table definition
		$this->table_definition = array (
			"letterdt" => SQL__DATE,
			"lettereoc" => SQL__TEXT,
			"letterfrom" => SQL__VARCHAR(150),
			"letterto" => SQL__VARCHAR(150),
			"lettertext" => SQL__TEXT,
			"lettersent" => SQL__INT_UNSIGNED(0),
			"letterpatient" => SQL__INT_UNSIGNED(0),
			"id" => SQL__SERIAL
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'lettereoc');

		// Run parent constructor
		$this->EMRModule();
	} // end constructor LettersModule

	function add () {
		// Check for submit as add, else drop
		if ($_REQUEST['my_submit'] != __("Add")) {
			global $action; $action = "addform";
			return $this->form();
		}

		// Check for uploaded msworddoc
		if (!empty($_FILES["msworddoc"]["tmp_name"]) and file_exists($_FILES["msworddoc"]["tmp_name"])) {
			$doc = $_FILES["msworddoc"]["tmp_name"];

			// Convert to the temporary file
			$__command = "/usr/bin/wvWare -x /usr/share/wv/wvText.xml \"$doc\"";
			$output = `$__command`;

			// Read temporary file into lettertext
			global $lettertext;
			$lettertext = $output;

			// Remove uploaded document
			unlink($doc);
		} // end checking for uploaded msworddoc

		// Call wrapped function
		$this->_add();

		// If this is management, refresh properly
		/*
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
		*/
	} // end function LettersModule->add

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		switch ($action) { // internal action switch
			case "addform":
				// Check for this_user object
				global $this_user;
				if (!is_object($this_user)) {
					$this_user = CreateObject('FreeMED.User');
				}

				// If we're a physician, use us
				if ($this_user->isPhysician()) {
					global $letterfrom;
					$letterfrom = $this_user->user_phy;
				}
			break; // end internal addform
			case "modform":
			if (($patient<1) OR (empty($patient))) {
				$display_buffer .= __("You must select a patient.")."\n";
				template_display ();
			}
			$r = freemed::get_link_rec ($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = stripslashes($v);
			}
			extract ($r);
			break; // end internal modform

			default:
			print "BAD!<br/>\n";
			break;
		} // end internal action switch

		$display_buffer .= "
		<p/>
		<form ACTION=\"$this->page_name\" METHOD=\"POST\" ".
		"ENCTYPE=\"multipart/form-data\" name=\"my_form\">
		<input TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" ".
		"VALUE=\"1000000\">
		<input TYPE=\"HIDDEN\" NAME=\"action\"  VALUE=\"".
			( ($action=="addform") ? "add" : "mod" )."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"id\"      VALUE=\"".prepare($id)."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"module\"  VALUE=\"".prepare($module)."\"\>
		<input TYPE=\"HIDDEN\" NAME=\"return\"  VALUE=\"".prepare($return)."\"\>
		";

		if (check_module("LettersTemplates") and ($action=="addform")) {
			// Create widget
			$lt_array = array (
				__("Letters Template") =>
				module_function(
					'LettersTemplates',
					'picklist',
					array('lt', 'my_form')
				)
			);

			// Check for used
			module_function(
				'LettersTemplates',
				'retrieve',
				array('lt')
			);
		} else {
			$lt_array = array ('' => '');
		}

		if (check_module("EpisodeOfCare")) {
			$eoc_array = array (
				__("Episode of Care") =>
				module_function(
					'EpisodeOfCare',
					'widget',
					array('lettereoc', $patient)
				)
			);
		} else {
			$eoc_array = array ('' => '');
		}

		$display_buffer .= html_form::form_table(array_merge(
		$lt_array, $eoc_array, array(
		__("Date") =>
		fm_date_entry("letterdt"),

		__("From") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician WHERE phyref='no' ".
				"ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterfrom"
		),
		

		__("To") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterto"
		),

		__("Text") =>
		//html_form::text_area("lettertext", 'VIRTUAL', 25, 70),
		freemed::rich_text_area('lettertext', 25, 70)

		)));

		// Check for Word document attachment ...
		if (($action=="add") or ($action=="addform")) {
			$display_buffer .= "
			<div ALIGN=\"CENTER\">
			<input TYPE=\"FILE\" NAME=\"msworddoc\"/>
			</div>
			";
		}
 
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<input class=\"button\" name=\"my_submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".
	         ( ($action=="addform") ? __("Add") : __("Modify"))."\"/>
		<input class=\"button\" TYPE=\"RESET\" VALUE=\" ".__("Clear")." \"/>
		<input class=\"button\" TYPE=\"SUBMIT\" NAME=\"__submit\" VALUE=\"Cancel\"/>
		</div>
		</form>
		";
	} // end function LettersModule->form

	function display () {
		global $display_buffer, $patient, $action, $id, $title,
			$return;
		global $this_patient;

		$GLOBALS['__freemed']['no_template_display'] = true;

		$title = __("View Letter");

		// Get link record
		$record = freemed::get_link_rec($id, $this->table_name);

		// Resolve docs
		$from = CreateObject('FreeMED.Physician', $record[letterfrom]);
		$to   = CreateObject('FreeMED.Physician', $record[letterto]  );

		// Create date, address, etc, header
		$display_buffer .= "
		<!-- padding for letterhead -->
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		&nbsp;<br/>
		<table width=\"100%\" border=\"0\" cellspacing=\"0\"
		 cellpadding=\"2\" valign=\"top\">
		<tr>
				<!-- date header -->
			<td width=\"50%\">&nbsp;</td>
			<td width=\"50%\" align=\"left\">".fm_date_print($record[letterdt])."</td>
		</tr>
		<tr>
				<!-- padding -->
			<td colspan=\"2\"> &nbsp; </td>
		</tr>
		<tr>
			<td align=\"left\">
				<!-- physician information -->
				
			</td>
			<td align=\"left\">
					<!-- patient information -->
				<u>Re: ".$this->this_patient->fullName()."</u><br/>
				<u>DOB: ".$this->this_patient->dateOfBirth()."</u>
			</td>
		</tr>
		</table>
		";

		$display_buffer .= "
		<div ALIGN=\"CENTER\" CLASS=\"infobox\">
		<table BORDER=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\" ".
				"CELLPADDING=\"2\">
		<tr>
			<td ALIGN=\"RIGHT\" WIDTH=\"25%\">".__("Date")."</td>
			<td ALIGN=\"LEFT\" WIDTH=\"75%\">".$record[letterdt]."</td>
		</tr>
		<tr>
			<TD ALIGN=\"RIGHT\">".__("From")."</TD>
			<TD ALIGN=\"LEFT\">".$from->fullName()."</TD>
		</tr>
		<tr>
			<td ALIGN=\"RIGHT\">".__("To")."</td>
			<td ALIGN=\"LEFT\">".$to->fullName()."</td>
		</tr>
		</table>
		</div>
		<div ALIGN=\"LEFT\" CLASS=\"letterbox\">
		".stripslashes(str_replace("\n", "<br/>", htmlentities($record[lettertext])))."
		</div>
		";
	} // end function LettersModule->display

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * FROM ".$this->table_name." ".
				"WHERE (".$this->patient_field.
						"='".addslashes($patient)."') ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY letterdt"
			),
			$this->page_name,
			array (
				__("Date") => "letterdt",
				__("From") => "letterfrom",
				__("To") => "letterto"
			),
			array ("", "", ""),
			array (
				"",
				"physician" => "phylname",
				// This is a workaround because it relies on
				// key/value pairs, and it cannot have a
				// duplicate key without being *very* funny.
				"physician " => "phylname"
			)
		);
	} // end function LettersModule->view()

	// ----- Internal update

	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.3.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				 'ADD COLUMN lettereoc TEXT AFTER letterdt');
		}
	} // end method _update

} // end class LettersModule

register_module ("LettersModule");

?>
