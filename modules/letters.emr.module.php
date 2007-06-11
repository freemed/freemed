<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class LettersModule extends EMRModule {

	var $MODULE_NAME    = "Letters";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.6";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Letters";
	var $table_name     = "letters";
	var $patient_field  = "letterpatient";
	var $widget_hash    = "##letterdt## ##letterfrom:physician:phylname## to ##letterto:physician:phylname##";

	var $print_template = 'letters';

	function LettersModule () {
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "my_date",
			__("From")   => "letterfrom:physician",
			__("To")   => "letterto:physician",
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
			"letterto",
			"lettercc" => ( is_array($_REQUEST['lettercc']) ?
				join(',', $_REQUEST['lettercc']) :
				$_REQUEST['lettercc'] ),
			"letterenc" => ( is_array($_REQUEST['letterenc']) ?
				join(',', $_REQUEST['letterenc']) :
				$_REQUEST['letterenc'] ),
			"letterencseperately",
			"lettertext",
			"letterpatient" => $patient,
			"lettertypist" => html_form::combo_assemble('lettertypist'),
			"locked" => '0' // needed for when it is added
		);

		// Table definition
		$this->table_definition = array (
			"letterdt" => SQL__DATE,
			"lettereoc" => SQL__TEXT,
			"letterfrom" => SQL__VARCHAR(150),
			"lettersubject" => SQL__VARCHAR(150),
			"letterto" => SQL__VARCHAR(150),
			"lettercc" => SQL__BLOB,
			"letterenc" => SQL__BLOB,
			"letterencseperately" => SQL__INT_UNSIGNED(0),
			"lettertext" => SQL__TEXT,
			"lettersent" => SQL__INT_UNSIGNED(0),
			"letterpatient" => SQL__INT_UNSIGNED(0),
			"lettertypist" => SQL__VARCHAR(50),
			"locked" => SQL__INT_UNSIGNED(0),
			"id" => SQL__SERIAL
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'lettereoc');

		// Run parent constructor
		$this->EMRModule();
	} // end constructor LettersModule

	function additional_summary_icons ( $patient, $id ) {
		$return =
			"\n"."<a onClick=\"printWindow=".
			"window.open('".$this->page_name."?".
			"module=".get_class($this)."&action=print&".
			"print_template=envelope&id=".urlencode($id)."&".
			"patient=".urlencode($patient)."', ".
			"'printWindow', ".
			"'width=400,height=200,menubar=no,titlebar=no'); ".
			"printWindow.opener=self; return true;\" ".
			"><img SRC=\"lib/template/default/img/summary_envelope.png\"
			BORDER=\"0\" ALT=\"".__("Print Envelope")."\"/></a>".
			"\n"."<a onClick=\"printWindow=".
			"window.open('".$this->page_name."?".
			"module=".get_class($this)."&action=print&".
			"print_template=letters_nosignature&id=".urlencode($id)."&".
			"patient=".urlencode($patient)."', ".
			"'printWindow', ".
			"'width=400,height=200,menubar=no,titlebar=no'); ".
			"printWindow.opener=self; return true;\" ".
			"><img SRC=\"lib/template/default/img/summary_nosignature.png\"
			BORDER=\"0\" ALT=\"".__("Print No Signature")."\"/></a>";
		return $return;
	} // end method additional_summary_icons

	function add () {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }
	
		// Check for submit as add, else drop
		switch ($_REQUEST['my_submit']) {
			case __("Send to Provider"):
			include_once(resolve_module('LettersRepository'));
			$l = new LettersRepository();
			return $l->_add();
			break;

			case __("File and Fax Directly"): case __("Add"):
			$fax = true;
			break;

			case __("File Directly"):
			$fax = false;
			break;

			default:
			global $action; $action = "addform";
			return $this->form();
			break;
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
		$new_id = $this->_add($_REQUEST);

		// If we're supposed to, send fax
		if ($fax and $_REQUEST['letterfax']) {
			// Start up TeX renderer
			$TeX = CreateObject('FreeMED.TeX', array (
				'title' => $title,
				'heading' => $heading,
				'physician' => $physician
			));
			$TeX->_buffer = $TeX->RenderFromTemplate(
				$this->print_template,
				freemed::get_link_rec($new_id, $this->table_name)
			);

			// Render to PDF and send
			$file = $TeX->RenderToPDF(true);
			if (!file_exists($file)) {
				trigger_error(__("Letter disappeared before it could be sent!"));
			}
			$fax = CreateObject('_FreeMED.Fax', $file, array (
				'sender' => $this_user->user_descrip,
				'comment' => __("HIPPA Compliance Notice: This transmission contains sensitive medical data.")
			));
			$output = $fax->Send($_REQUEST['letterfax']);
			$this_user->setFaxInQueue(
				$output,
				$_REQUEST['patient'],
				$_REQUEST['fax_number'],
				get_class($this),
				$new_id
				);
			unlink($file);
		}

		// If this is management, refresh properly
		if ($_REQUEST['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
			Header("Location: ".$refresh);
			die();
		}
	} // end method add

	function mod () {
		if ($_REQUEST['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
		$this->_mod();
	} // end method mod

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
		module_function('ProviderModule', 'widget', array('letterfrom')),
		/*
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician WHERE phyref='no' ".
				"ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterfrom"
		),
		*/
		

		__("To") =>
		module_function('ProviderModule', 'widget', array('letterto')),
		/*
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician ORDER BY phylname"),
			"#phylname#, #phyfname#",
			"letterto",
			3
		),
		*/

		__("Subject") =>
		html_form::text_widget("lettersubject", 30, 150),

		__("CC") =>
		$this->cc_widget('lettercc'),

		__("Enclosures") =>
		$this->enc_widget('letterenc')." ".
		html_form::checkbox_widget('letterencseperately', '1', __("Enclosures sent separately")),

		__("Text") =>
		//html_form::text_area("lettertext", 'VIRTUAL', 25, 70),
		freemed::rich_text_area('lettertext', 25, 70),

		__("Typist") =>
		html_form::combo_widget(
			'lettertypist',
			$GLOBALS['sql']->distinct_values(
				$this->table_name,
				'lettertypist'
			)
		),

		( check_module('LettersRepository') ? __("Fax Number") : "" ) =>
			module_function('providermodule',
				'widget',
				array ( 'letterfax', false, 'phyfaxa' )
			),
		//html_form::text_widget( 'letterfax', 16 ),

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
		";

		if (check_module('LettersRepository') and ($action=='addform')) {
			$display_buffer .= "
		<input class=\"button\" name=\"my_submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("Send to Provider")."\"/>
		<input class=\"button\" name=\"my_submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("File Directly")."\"/>
		<input class=\"button\" name=\"my_submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("File and Fax Directly")."\"/>
			";
		} else {
			$display_buffer .= "
		<input class=\"button\" name=\"my_submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".
	         ( ($action=="addform") ? __("Add") : __("Modify"))."\"/>
			";
		}

		$display_buffer .= "
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
		".stripslashes(str_replace("\n", "<br/>", 
			eregi('<[A-Z]*', $record['lettertext']) ?
			$record['lettertext'] :
			htmlentities($record['lettertext'])
			))."
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
				__("Subject") => "lettersubject",
				__("To") => "letterto",
				__("Typist") => "lettertypist"
			),
			array ("", "", ""),
			array (
				"",
				"physician" => "phylname",
				// This is a workaround because it relies on
				// key/value pairs, and it cannot have a
				// duplicate key without being *very* funny.
				"physician " => "phylname"
			), NULL, NULL, 
			ITEMLIST_LOCK | ITEMLIST_MOD | ITEMLIST_DEL
		);
	} // end method view

	/*
		OLD PRINT MAPPING:
		return array (
			'date' => $TeX->_SanitizeText( fm_date_print($r['letterdt'], false) ),
			'patient' => $TeX->_SanitizeText($pt['ptfname'].
				' '. $pt['ptmname'] . ' ' . $pt['ptlname']),
			'dateofbirth' => $TeX->_SanitizeText(fm_date_print($pt['ptdob'])),
			'from' => $TeX->_SanitizeText(
				'Dr. '.$phf['phyfname'].' '.$phf['phylname']
				),
			'to' => $TeX->_SanitizeText($tophyobj->fullName(false)),
			'cc' => $TeX->_SanitizeText($cc),
			'enclosures' => $enc,
			'body' => $TeX->_HTMLToRichText($r['lettertext']),
			'typist' => $TeX->_SanitizeText($r['lettertypist']),
			'practice' => $TeX->_SanitizeText($phf['phypracname']),
			'physician' => $TeX->_SanitizeText($phyobj->fullName()),
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
			'signature' => ( file_exists('/usr/share/freemed/lib/tex/img/'.$phf['id'].'.pdf') ? '\\includegraphics[height=2em]{/usr/share/freemed/lib/tex/img/'.$phf['id'].'.pdf}'."\n" : '\\bigskip'."\n" ),
			'physicianid' => $phf['id'],
			'tophysicianpractice' => $TeX->_SanitizeText($pht['phypracname']),
			'tophysician' => $TeX->_SanitizeText($tophyobj->fullName(true)),
			'tophysicianfirstname' =>$TeX->_SanitizeText($pht['phyfname']),
			'tophysicianaddress' => $TeX->_SanitizeText($pht['phyaddr1a']),
			'tophysiciancitystatezip' => $TeX->_SanitizeText($pht['phycitya'].', '.$pht['phystatea'].' '.$pht['phyzipa'])
		);
	*/

	function cc_widget ( $varname ) {
		global ${$varname};
		$buffer .= "<select NAME=\"".$varname."[]\" SIZE=\"3\" MULTIPLE=\"multiple\">\n";
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

	function enc_widget ( $varname ) {
		global ${$varname};
		$buffer .= "<select NAME=\"".$varname."[]\" SIZE=\"3\" MULTIPLE=\"multiple\">\n";
		$query = "SELECT * FROM enctype ORDER BY enclosure";
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
				prepare($r['enclosure']).
				"</option>\n";
		}
		$buffer .= "</select>\n";
		return $buffer;
	} // end method enc_widget

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

	function fax_widget ( $varname, $id ) {
		global $sql, ${$varname};
		$r = freemed::get_link_rec($id, $this->table_name);
		$phy = freemed::get_link_rec($r['letterto'], 'physician');
		${$varname} = $phy['phyfaxa'];
		return module_function('faxcontacts',
			'widget',
			array ( $varname, false, 'phyfaxa' )
		);
	} // end method fax_widget

	// ----- Internal update

	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.3.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				 'ADD COLUMN lettereoc TEXT AFTER letterdt');
		}

		// Version 0.3.2
		//
		//	Added locking ability to letters module
		//
		if (!version_check($version, '0.3.2')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN locked INT UNSIGNED AFTER letterpatient');
			// Make sure they are all starting with 0
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'locked = \'0\'');
		}

		// Version 0.3.2
		//
		//      Added subject line (lettersubject)
		//
		if (!version_check($version, '0.3.2')) {
		        $sql->query('ALTER TABLE '.$this->table_name.' '.
			        'ADD COLUMN lettersubject VARCHAR(150) AFTER letterfrom');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
			        'lettersubject = \'\'');
		}		

		// Version 0.3.3
		//
		//	Added CC
		//
		if (!version_check($version, '0.3.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN lettercc BLOB AFTER letterto');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'lettercc = \'\'');
		}

		// Version 0.3.4
		//
		//	Added enclosures
		//
		if (!version_check($version, '0.3.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN letterenc BLOB AFTER lettercc');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'letterenc = \'\'');
		}

		// Version 0.3.5
		//
		//	Added typist
		//
		if (!version_check($version, '0.3.5')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN lettertypist VARCHAR(50) AFTER letterpatient');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'lettertypist = \'\'');
		}

		// Version 0.3.6
		//
		//	Added enclosures seperate
		//
		if (!version_check($version, '0.3.6')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN letterencseperately INT UNSIGNED AFTER letterenc');
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'letterencseperately = \'0\'');
		}

	} // end method _update

} // end class LettersModule

register_module ("LettersModule");

?>
