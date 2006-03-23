<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class PatientCorrespondence extends EMRModule {

	var $MODULE_NAME    = "Patient Correspondence";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Correspondence";
	var $table_name     = "patletter";
	var $patient_field  = "letterpatient";
	var $widget_hash    = "##letterdt## ##letterfrom:physician:phylname##";

	var $print_template = 'patient_correspondence';

	function PatientCorrespondence () {
		// __("Patient Correspondence")

		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "letterdt",
			__("From")   => "letterfrom:physician"
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW
			| SUMMARY_PRINT | SUMMARY_LOCK | SUMMARY_DELETE;

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
			"lettertext",
			"letterpatient" => $patient,
			"locked" => '0' // needed for when it is added
		);

		// Table definition
		$this->table_definition = array (
			"letterdt" => SQL__DATE,
			"lettereoc" => SQL__TEXT,
			"letterfrom" => SQL__VARCHAR(150),
			"lettertext" => SQL__TEXT,
			"lettersent" => SQL__INT_UNSIGNED(0),
			"letterpatient" => SQL__INT_UNSIGNED(0),
			"locked" => SQL__INT_UNSIGNED(0),
			"id" => SQL__SERIAL
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'lettereoc');

		// Set ACL for billers + EMR access
		$this->acl = array ( 'bill', 'emr' );

		// Run parent constructor
		$this->EMRModule();
	} // end constructor PatientCorrespondence

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
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
	} // end method add

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

		if (check_module("PatientCorrespondenceTemplates") and ($action=="addform")) {
			// Create widget
			$lt_array = array (
				__("Letters Template") =>
				module_function(
					'PatientCorrespondenceTemplates',
					'picklist',
					array('lt', 'my_form')
				)
			);

			// Check for used
			module_function(
				'PatientCorrespondenceTemplates',
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
	} // end method form

	function display () {
		global $display_buffer, $patient, $action, $id, $title,
			$return;
		global $this_patient;

		$GLOBALS['__freemed']['no_template_display'] = true;

		$title = __("View Correspondence");

		// Get link record
		$record = freemed::get_link_rec($id, $this->table_name);

		// Resolve docs
		$from = CreateObject('FreeMED.Physician', $record[letterfrom]);

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
		</table>
		</div>
		<div ALIGN=\"LEFT\" CLASS=\"letterbox\">
		".stripslashes(str_replace("\n", "<br/>", 
			eregi('<[A-Z]*', $record['lettertext']) ?
			$record['lettertext'] :
			htmlentities($record['lettertext'])
			))."
		</div>
		";
	} // end method display

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
				__("From") => "letterfrom"
			),
			array ("", "", ""),
			array (
				"",
				"physician" => "phylname"
			), NULL, NULL, 
			ITEMLIST_LOCK | ITEMLIST_MOD | ITEMLIST_DEL | ITEMLIST_PRINT
		);
	} // end method view

	function _print_mapping ($TeX, $id) {
		$r = freemed::get_link_rec($id, $this->table_name);
		$pt = freemed::get_link_rec($r[$this->patient_field], 'patient');
		$phyobj = CreateObject('_FreeMED.Physician', $r['letterfrom']);
		$phf = freemed::get_link_rec($r['letterfrom'], 'physician');

		// Figure out prefix
		if ($pt['ptsalut']) {
			$prefix = $pt['ptsalut'].'. ';
		} else {
			if ($pt['ptsex'] != 'f') {
				$prefix = 'Mr. ';
			} else {
				switch ($pt['ptmarital']) {
					case 'married':
					case 'widowed':
					case 'separated':
					case 'divorced':
						$prefix = 'Mrs. ';
						break;

					case 'single':
					default:
						$prefix = 'Ms. ';
						break;
				}
			}
		} // end creating name prefix for patient
		
		return array (
			'date' => $TeX->_SanitizeText( fm_date_print($r['letterdt'], true) ),
			'patient' => $TeX->_SanitizeText(
				$this->uc(
				$prefix . $pt['ptfname'] .  ' ' . 
				$pt['ptmname'] . ' ' . $pt['ptlname'])),
			'patientqualifier' => $TeX->_SanitizeText( $prefix ),
			'patientlastname' => $TeX->_SanitizeText(
				ucfirst ( strtolower ( $pt['ptlname'] ) ) ),
			'patientaddress' => $TeX->_SanitizeText($this->uc($pt['ptaddr1'])),
			'patientcitystatezip' => $TeX->_SanitizeText(ucfirst(strtolower($pt['ptcity'])).', '.$pt['ptstate'].' '.$pt['ptzip']),
			'dateofbirth' => $TeX->_SanitizeText(fm_date_print($pt['ptdob'])),
			'from' => $TeX->_SanitizeText(
				'Dr '.$phf['phyfname'].' '.$phf['phylname']
				),
			'body' => $TeX->_HTMLToRichText($r['lettertext']),
			'physician' => $TeX->_SanitizeText($phyobj->fullName()),
			'practice' => $TeX->_SanitizeText($phf['phypracname']),
			'physicianaddress' => $TeX->_SanitizeText($phf['phyaddr1a']),
			'physiciancitystatezip' => $TeX->_SanitizeText($phf['phycitya'].', '.$phf['phystatea'].' '.$phf['phyzipa']),
			'physicianphone' => $TeX->_SanitizeText(
				substr($phf['phyphonea'], 0, 3).'-'.
				substr($phf['phyphonea'], 3, 3).'-'.
				substr($phf['phyphonea'], 6, 4) ),
			'physicianfax' => $TeX->_SanitizeText(
				substr($phf['phyfaxa'], 0, 3).'-'.
				substr($phf['phyfaxa'], 3, 3).'-'.
				substr($phf['phyfaxa'], 6, 4) ),
		);
	} // end method _print_mapping

	function uc ( $string ) {
		$a = explode(' ', $string);
		foreach ($a as $k => $v) {
			$a[$k] = ucfirst(strtolower($v));
		}
		return join(' ', $a);
	}

	// ----- Internal update

	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		// Version xxxx
		//
		/*
		if (!version_check($version, '0.2')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN locked INT UNSIGNED AFTER letterpatient');
		}
		*/
	} // end method _update

} // end class PatientCorrespondence

register_module ("PatientCorrespondence");

?>
