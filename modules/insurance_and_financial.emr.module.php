<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class InsuranceAndFinancial extends EMRModule {

	var $MODULE_NAME    = "Insurance and Financial";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2.3";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Insurance and Financial";
	var $table_name     = "insfinancial";
	var $patient_field  = "letterpatient";
	var $widget_hash    = "##letterdt## ##letterfrom:physician:phylname##";

	var $print_template = 'insurance_and_financial';

	function InsuranceAndFinancial () {
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "my_date",
			__("From") => "letterfrom:physician",
			__("Subject") => "lettersubject"
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW
			| SUMMARY_PRINT | SUMMARY_LOCK | SUMMARY_DELETE;
		$this->summary_query = array (
			"DATE_FORMAT(letterdt, '%m/%d/%Y') AS my_date"
		);

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
			"lettersubject",
			"lettertext",
			"letterpatient" => $patient,
			"lettertypist" => html_form::combo_assemble('lettertypist'),
			"lettercc" => ( is_array($_REQUEST['lettercc']) ?
				join(',', $_REQUEST['lettercc']) :
				$_REQUEST['lettercc'] ),
			"locked" => '0' // needed for when it is added
		);

		// Table definition
		$this->table_definition = array (
			"letterdt" => SQL__DATE,
			"lettereoc" => SQL__TEXT,
			"letterfrom" => SQL__VARCHAR(150),
			"lettersubject" => SQL__VARCHAR(150),
			"lettertext" => SQL__TEXT,
			"lettersent" => SQL__INT_UNSIGNED(0),
			"letterpatient" => SQL__INT_UNSIGNED(0),
			"lettertypist" => SQL__VARCHAR(50),
			"lettercc" => SQL__BLOB,
			"locked" => SQL__INT_UNSIGNED(0),
			"id" => SQL__SERIAL
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'lettereoc');

		// Run parent constructor
		$this->EMRModule();
	} // end constructor InsuranceAndFinancial

	function additional_summary_icons ( $patient, $id ) {
		return "\n"."<a onClick=\"printWindow=".
			"window.open('".$this->page_name."?".
			"module=".get_class($this)."&action=print&".
			"print_template=insurance_and_financial_envelope&id=".urlencode($id)."&".
			"patient=".urlencode($patient)."', ".
			"'printWindow', ".
			"'width=400,height=200,menubar=no,titlebar=no'); ".
			"printWindow.opener=self; return true;\" ".
			"><img SRC=\"lib/template/default/img/summary_envelope.png\"
			BORDER=\"0\" ALT=\"".__("Print Envelope")."\"/></a>";
	} // end method additional_summary_icons

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
			$lettercc = explode(',', $lettercc);
			$letterenc = explode(',', $letterenc);
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
		<input TYPE=\"HIDDEN\" NAME=\"return\"  VALUE=\"".prepare($_REQUEST['return'])."\"\>
		";

		if (check_module("InsuranceAndFinancialTemplates") and ($action=="addform")) {
			// Create widget
			$lt_array = array (
				__("Template") =>
				module_function(
					'InsuranceAndFinancialTemplates',
					'picklist',
					array('lt', 'my_form')
				)
			);

			// Check for used
			module_function(
				'InsuranceAndFinancialTemplates',
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

		__("Subject") =>
		html_form::text_widget("lettersubject", 30, 150),

		__("CC") =>
		$this->cc_widget('lettercc'),

		__("Typist") =>
		html_form::combo_widget(
			'lettertypist',
			$GLOBALS['sql']->distinct_values(
				$this->table_name,
				'lettertypist'
			)
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

		$title = __("View Insurance and Financial");

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
				__("From") => "letterfrom",
				__("Subject") => "lettersubject"
			),
			array ("", "", "", ""),
			array (
				"",
				"physician" => "phylname",
				""
			), NULL, NULL, 
			ITEMLIST_LOCK | ITEMLIST_MOD | ITEMLIST_DEL | ITEMLIST_PRINT
		);
	} // end method view

	// Figure out prefix
	function _divine_prefix ( $id ) {
		$r = freemed::get_link_rec($id, $this->table_name);
		$pt = freemed::get_link_rec($r[$this->patient_field], 'patient');
		$age_res = $GLOBALS['sql']->query("SELECT ".
			"TO_DAYS(DATE_ADD(ptdob, INTERVAL 18 YEAR)) > TO_DAYS('".date('Y-m-d')."') AS under18 ".
			"FROM patient WHERE id='".addslashes($r[$this->patient_field])."'");
		$age_r = $GLOBALS['sql']->fetch_array($age_res);
		if ($age_r['under18'] and strlen($pt['ptglname'])>2) {
			$g = true;
		} else {
			$g = false;
		}

		if ($g) { $_g = 'ptgsalut'; } else { $_g = 'ptsalut'; }
		if ($pt[$_g]) {
			$prefix = $pt[$_g].'. ';
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
		return $prefix;
	} // end _divine_prefix

	function _divine_provider_address ( $id ) {
		if ($_SESSION['default_facility'] > 0) {
			// Take from session
			$f = freemed::get_link_rec($_SESSION['default_facility']+0, 'facility');
			return $f['psraddr1'];
		} else {
			// Take from record
			$r = freemed::get_link_rec($id, $this->table_name);
			$f = freemed::get_link_rec($r['letterfrom'], 'physician');
			return $f['phyaddr1a'];
		}
	}

	function _divine_provider_csz ( $id ) {
		if ($_SESSION['default_facility'] > 0) {
			// Take from session
			$f = freemed::get_link_rec($_SESSION['default_facility']+0, 'facility');
			return $f['psrcity'].', '.$f['psrstate'].' '.$f['psrzip'];
		} else {
			// Take from record
			$r = freemed::get_link_rec($id, $this->table_name);
			$f = freemed::get_link_rec($r['letterfrom'], 'physician');
			return $f['phycitya'].', '.$f['phystatea'].' '.$f['phyzipa'];
		}
	}

	function _divine_provider_phone ( $id ) {
		if ($_SESSION['default_facility'] > 0) {
			// Take from session
			$f = freemed::get_link_rec($_SESSION['default_facility']+0, 'facility');
			$phone = $f['psrphone'];
		} else {
			// Take from record
			$r = freemed::get_link_rec($id, $this->table_name);
			$f = freemed::get_link_rec($r['letterfrom'], 'physician');
			$phone = $f['phyphonea'];
		}
		return '('.substr($phone,0,3).') '.substr($phone,3,3).'-'.substr($phone,6,4);
	}

	function _divine_provider_fax ( $id ) {
		if ($_SESSION['default_facility'] > 0) {
			// Take from session
			$f = freemed::get_link_rec($_SESSION['default_facility']+0, 'facility');
			$phone = $f['psrfax'];
		} else {
			// Take from record
			$r = freemed::get_link_rec($id, $this->table_name);
			$f = freemed::get_link_rec($r['letterfrom'], 'physician');
			$phone = $f['phyfaxa'];
		}
		return '('.substr($phone,0,3).') '.substr($phone,3,3).'-'.substr($phone,6,4);
	}

	function _divine_firstname ( $id ) {
		$r = freemed::get_link_rec($id, $this->table_name);
		$pt = freemed::get_link_rec($r[$this->patient_field], 'patient');
		$age_res = $GLOBALS['sql']->query("SELECT ".
			"TO_DAYS(DATE_ADD(ptdob, INTERVAL 18 YEAR)) > TO_DAYS('".date('Y-m-d')."') AS under18 ".
			"FROM patient WHERE id='".addslashes($r[$this->patient_field])."'");
		$age_r = $GLOBALS['sql']->fetch_array($age_res);
		if ($age_r['under18'] and strlen($pt['ptglname'])>2) {
			$g = true;
		} else {
			$g = false;
		}
		return $this->uc($pt[( $g ? 'ptgfname' : 'ptfname' )]);
	} // end _divine_firstname

	function _divine_lastname ( $id ) {
		$r = freemed::get_link_rec($id, $this->table_name);
		$pt = freemed::get_link_rec($r[$this->patient_field], 'patient');
		$age_res = $GLOBALS['sql']->query("SELECT ".
			"TO_DAYS(DATE_ADD(ptdob, INTERVAL 18 YEAR)) > TO_DAYS('".date('Y-m-d')."') AS under18 ".
			"FROM patient WHERE id='".addslashes($r[$this->patient_field])."'");
		$age_r = $GLOBALS['sql']->fetch_array($age_res);
		if ($age_r['under18'] and strlen($pt['ptglname'])>2) {
			$g = true;
		} else {
			$g = false;
		}
		return $this->uc($pt[( $g ? 'ptglname' : 'ptlname' )]);
	} // end _divine_lastname

	function uc ( $string ) {
		// Simple substitutions
		$subs = array (
			'Iii' => 'III', // The third
			'Po' => 'PO', // PO Box 
			'St' => 'St.', // Street/Saint
			'Nh' => 'NH', // New Hampshire abbrev
			'Us' => 'US', // United States route abbrev
		);
		$a = explode(' ', $string);
		foreach ($a as $k => $v) {
			$a[$k] = ucfirst(strtolower($v));

			// Handle obvious substitutions
			foreach ($subs AS $s_k => $s_v) {
				if ($a[$k] == $s_k) { $a[$k] = $s_v; }
			}

			// Handle McDonald and kin
			if ((substr($a[$k], 0, 2) == 'Mc') and (strlen($a[$k])>3)) { 
				$a[$k] = 'Mc' . ucfirst(strtolower(substr($a[$k], -(strlen($a[$k])-2) )));
			}

			// Handle rural routes
			if (substr($a[$k], 0, 2) == 'Rr') { 
				$a[$k] = strtoupper($a[$k]);
			}
			
			// Handle things like 212B Baker Street
			if ((substr($a[$k], 0, 1) + 0) > 0) {
				$a[$k] = strtoupper($a[$k]);
			}
		}
		return join(' ', $a);
	}

	function cc_widget ( $varname ) {
		global ${$varname};
		$buffer .= "<select NAME=\"".$varname."[]\" SIZE=\"5\" MULTIPLE=\"multiple\">\n";
		$query = "SELECT * FROM physician ORDER BY phylname,phyfname";
		$res = $GLOBALS['sql']->query($query);
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$selected = false;
			if (is_array(${$varname})) {
				foreach (${$varname} AS $v) {
					if ($v == $r['id']) {
						$selected = true;
					}
				}
			} else {
				if (${$varname} == $r['id']) {
					$selected = true;
				}
			}
			$buffer .= "<option value=\"".prepare($r['id'])."\" ".
				( $selected ? "SELECTED" : "" ).">".
				prepare($r['phylname'].', '.$r['phyfname']).
				"</option>\n";
		}
		$buffer .= "</select>\n";
		return $buffer;
	} // end method cc_widget

	// ----- Internal update

	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
	} // end method _update

} // end class InsuranceAndFinancial

register_module ("InsuranceAndFinancial");

?>
