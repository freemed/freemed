<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.EMRModule');

class PrescriptionModule extends EMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.4";
	var $MODULE_DESCRIPTION = "
		The prescription module allows prescriptions to be written 
		for patients from any drug in the local formulary or in the 
		Multum drug database (if access to that database is 
		available.";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Prescription";
	var $table_name     = "rx";
	var $patient_field  = "rxpatient";

	var $print_template = 'rx';

	function PrescriptionModule () {
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW |
			SUMMARY_LOCK | SUMMARY_PRINT | SUMMARY_DELETE;

		$this->summary_vars = array (
			__("Date From") => "rxdtfrom",
			__("Drug") => "_drug",
			__("Dosage") => "_dosage",
			__("Dispensed") => "_dispensed",
			__("By")   => "rxphy:physician"
			//"Crypto Key" => "rxmd5"
		);
		// Specialized query bits
		$this->summary_query = array (
			"MD5(id) AS rxmd5",
			"CONCAT(rxdrug, ' ', rxform) AS _drug",
			"CONCAT(rxsize, ' ', rxunit, ' ', rxinterval) AS _dosage",
			"CASE rxform WHEN 'tablet' THEN CONCAT(rxquantity, ' tablets') WHEN 'capsule' THEN CONCAT(rxquantity, ' capsules') ELSE CONCAT(rxquantity, ' ', IF(rxunit LIKE '%cc%', 'cc', rxunit)) END AS _dispensed"
		);

		// Table definition
		$this->table_definition = array (
			'rxdtadd' => SQL__DATE,
			'rxdtmod' => SQL__DATE,
			'rxphy' => SQL__INT_UNSIGNED(0),
			'rxpatient' => SQL__INT_UNSIGNED(0),
			'rxdtfrom' => SQL__DATE,
			'rxdrug' => SQL__VARCHAR(150),
			'rxform' => SQL__ENUM(array(
				"suspension",
				"tablet",
				"capsule",
				"solution"
				)),
			'rxdosage' => SQL__INT_UNSIGNED(0),
			'rxquantity' => SQL__INT_UNSIGNED(0),
			'rxsize' => SQL__INT_UNSIGNED(0),
			'rxunit' => SQL__ENUM(array(
				"mg",
				"mg/1cc",
				"mg/2cc",
				"mg/3cc",
				"mg/4cc",
				"mg/5cc",
				"g"
				)),
			'rxinterval' => SQL__ENUM(array(
				"b.i.d.",
				"t.i.d.",
				"q.i.d.",
				"q. 3h",
				"q. 4h",
				"q. 5h",
				"q. 6h",
				"q. 8h",
				"q.d.",
				"h.s.",
				"q.h.s.",
				"q.A.M.",
				"q.P.M.",
				"a.c.",
				"p.c.",
				"p.r.n."
				)),
			'rxsubstitute' => SQL__ENUM(array(
				"may substitute", "may not substitute"
				)),
			'rxrefills' => SQL__INT_UNSIGNED(0),
			'rxperrefill' => SQL__INT_UNSIGNED(0),
			'rxnote' => SQL__TEXT,
			'locked' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		switch (freemed::config_value('drug_widget_type')) {
			case 'combobox':
			$rxdrug_chosen = html_form::combo_assemble('rxdrug');
			break; // combobox

			case 'rxlist': default:
			$rxdrug_chosen = $GLOBALS['rxdrug'];
			break; // rxlist
		}

		$this->variables = array (
			"rxdtfrom" => fm_date_assemble("rxdtfrom"),
			"rxphy",
			"rxdrug" => $rxdrug_chosen,
			"rxsize",
			"rxform",
			"rxdosage",
			"rxquantity",
			"rxunit",
			"rxinterval",
			"rxpatient",
			"rxsubstitute",
			"rxrefills",
			"rxperrefill",
			"rxnote",
			"locked" => '0'
		);
		$this->EMRModule();
	} // end constructor PrescriptionModule

	function display () {
		global $display_buffer, $sql, $id, $patient;

		// Get all parts of the display
		$r = freemed::get_link_rec($id, $this->table_name);
		foreach ($r AS $k => $v) {
			global ${$k};
			${$k} = $v;
		}

		$display_buffer .= html_form::form_table(array(
			__("Drug") => $rxdrug,
			__("Dosage") => $rxdosage." ".$rxunit." ".$rxinterval
		));
	} // end function PrescriptionModule->display

	function form () {
		global $display_buffer, $sql, $action, $id, $patient,
			$return;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Create new notebook
		$book = CreateObject('PHP.notebook',
			array ("module", "action", "id", "patient", "return"),
			NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH | NOTEBOOK_NOFORM
		);

		// If modify, grab old record
		if (($action=="mod") or ($action=="modform")) {
			if (!$book->been_here()) {
				$r = freemed::get_link_rec($id, $this->table_name);
				foreach ($r AS $k => $v) {
					global ${$k};
					${$k} = $v;
				}
				if (!is_object($this_user)) {
					$this_user = CreateObject('FreeMED.User');
				}
			}
		} else {
			global $this_user;
			if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }
			if ($this_user->isPhysician()) {
				global $rxphy;
				$rxphy = $this_user->user_phy;
			}
		}

		$book->set_submit_name(
			(
				( ($action=="add") or ($action=="addform") ) ?
				__("Add") :
				 __("Modify")
			)
		);

		// Add pages
		$book->add_page(
			__("Prescription"),
			array(
				"rxdtfrom",
				"rxphy",
				"rxdrug",
				"rxsize",
				"rxunit",
				"rxquantity",
				"rxdosage",
				"rxform",
				"rxinterval",
				"rxrefills",
				"rxperrefill",
				"rxsubstitute"
			),
			html_form::form_table(array(
				__("Starting Date") =>
				fm_date_entry("rxdtfrom"),

				__("Physician") =>
				freemed_display_selectbox(
					$sql->query("SELECT * FROM physician WHERE phyref != 'yes' ".
						"ORDER BY phylname, phyfname"),
					"#phylname#, #phyfname# #phymname#",
					"rxphy"
				),

				__("Drug") =>
				freemed::drug_widget("rxdrug", "myform", "__action"),

				__("Quantity") =>
				html_form::text_widget(
					"rxquantity", 10
				),

				__("Medicine Units") =>
				html_form::text_widget(
					"rxsize", 10
				).
				html_form::select_widget(
					"rxunit",
					array(
						"mg" => "mg",
						"mg/1cc" => "mg/1cc",
						"mg/2cc" => "mg/2cc",
						"mg/3cc" => "mg/3cc",
						"mg/4cc" => "mg/4cc",
						"mg/5cc" => "mg/5cc",
						"g" => "g"
					)
				),

				__("Dosage") =>
				html_form::text_widget(
					"rxdosage", 10
				).
				" ".__("in")." ".
				html_form::select_widget(
					"rxform",
					array(
						"suspension" => "suspension",
						"tablet" => "tablet",
						"capsule" => "capsule",
						"solution" => "solution"
					)
				)." ".
				html_form::select_widget(
					"rxinterval",
					array(
						"q.d."   => "q.d.",
						"b.i.d." => "b.i.d.",
						"t.i.d." => "t.i.d.",
						"q.i.d." => "q.i.d.",
						"q. 3h",
						"q. 4h",
						"q. 5h",
						"q. 6h",
						"q. 8h",
						"h.s.",
						"q.h.s.",
						"q.A.M.",
						"q.P.M.",
						"a.c.",
						"p.c.",
						"p.r.n."
					)
				),

				__("Refill") =>
				html_form::number_pulldown(
					"rxrefills", 0, 20
				)." / ".
				html_form::text_widget(
					"rxperrefill", 10
				)." ".__("units"),

				__("Substitution") =>
				html_form::select_widget(
					"rxsubstitute",
					array (
					__("may not substitute") => "may not substitute",
					__("may substitute") => "may substitute"
					)
				)
			))
		);

		$book->add_page(
			__("Sig"),
			array(
				"rxnote"
			),
			"<div ALIGN=\"CENTER\">\n".
			html_form::text_area(
				"rxnote"
			).
			"</div>"
		);

		// Handle cancel
		if ($book->is_cancelled()) {
			if ($return=="manage") {
				Header("Location: manage.php?".
					"id=".urlencode($patient));
			} else {
				Header("Location: module_loader.php?module=".
					urlencode($module)."&".
					"patient=".urlencode($patient));
			}
			die("");
		}

		// If not done, display
		if (!$book->is_done()) {
			$display_buffer .= "<div ALIGN=\"CENTER\">\n";
			$display_buffer .= "<form NAME=\"myform\" ACTION=\"".
				$this->page_name."\" METHOD=\"POST\">\n";
			$display_buffer .= $book->display();
			$display_buffer .= "</form>\n";
			$display_buffer .= "</div>\n";
			return true;
		}

		// Process notebook
		switch ($action) {
			case "add": case "addform":
			$this->prepare();
			$this->add();
			break;

			case "mod": case "modform":
			$this->prepare();
			$this->mod();
			break;
		}

		// Handle return to management
		if ($return=="manage") {
			Header("Location: manage.php?".
				"id=".urlencode($patient));
			die("");
		}
	} // end function PrescriptionModule->form

	function prepare () {
		// Common stuff between add/mod to prepare vars
		global $display_buffer,
			$rxpatient, $patient;
		$rxpatient = $patient;
	} // end function PrescriptionModule->prepare

	function view () {
		global $display_buffer, $patient;
		foreach ($GLOBALS AS $k => $v) global ${$k};
		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT *,".
				"CONCAT(rxquantity,' of ',rxsize,' ',rxunit,' ',".
				"rxinterval) AS _dosage ".
				"FROM ".$this->table_name." ".
				"WHERE rxpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY rxdtfrom DESC"
			),
			$this->page_name,
			array(
				__("Date") => "rxdtfrom",
				__("Drug") => "rxdrug",
				__("Dosage") => "_dosage"
			),
			array("", __("NONE")),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL | ITEMLIST_LOCK
		);
	} // end function PrescriptionModule->view

	function _print_mapping ($TeX, $id) {
		$r = freemed::get_link_rec($id, $this->table_name);
		$pt = freemed::get_link_rec($r[$this->patient_field], 'patient');
		$ph = freemed::get_link_rec($r['rxphy'], 'physician');
		$phyobj = CreateObject('_FreeMED.Physician', $r['rxphy']);
		return array (
			'patient' => $TeX->_SanitizeText($pt['ptlname'].', '.
				$pt['ptfname'].' '.$pt['ptmname'].' ('.
				$pt['ptid'].')'),
			'patientaddress' => $TeX->_SanitizeText($pt['ptaddr1']).' ',
			'patientcitystatezip' => $TeX->_SanitizeText($pt['ptcity'].', '.$pt['ptstate'].' '.$pt['ptzip']),
			'patientdob' => $TeX->_SanitizeText(fm_date_print($pt['ptdob'])),
			'ssn' => $TeX->_SanitizeText( empty($pt['ptssn']) ?
				"NONE PROVIDED" :
				substr($pt['ptssn'], 0, 3).'-'.
				substr($pt['ptssn'], 3, 2).'-'.
				substr($pt['ptssn'], 5, 4) ),
			'practicename' => $TeX->_SanitizeText($ph['phypracname']),
			'physician' => $TeX->_SanitizeText($phyobj->fullName()),
			'physicianaddress' => $TeX->_SanitizeText($ph['phyaddr1a']),
			'physiciancitystatezip' => $TeX->_SanitizeText($ph['phycitya'].', '.$ph['phystatea'].' '.$ph['phyzipa']),
			'physicianphone' => $TeX->_SanitizeText(
				substr($ph['phyphonea'], 0, 3).'-'.
				substr($ph['phyphonea'], 3, 3).'-'.
				substr($ph['phyphonea'], 6, 4) ),
			'physicianfax' => $TeX->_SanitizeText(
				substr($ph['phyfaxa'], 0, 3).'-'.
				substr($ph['phyfaxa'], 3, 3).'-'.
				substr($ph['phyfaxa'], 6, 4) ),
			'physiciandea' => $TeX->_SanitizeText($ph['phydea']),

			// rx specific
			'date' => $TeX->_SanitizeText(fm_date_print($r['rxdtfrom'])),
			'size' => $TeX->_SanitizeText($r['rxsize']),
			'units' => $TeX->_SanitizeText($r['rxunit']),
			'refill' => $TeX->_SanitizeText(($r['rxrefills']+0).' refill(s)'),
			'quantity' => $TeX->_SanitizeText($r['rxquantity']),
			'drug' => $TeX->_SanitizeText($r['rxdrug']),
			'dosage' => $TeX->_SanitizeText($r['rxdosage']),
			'interval' => $TeX->_SanitizeText($r['rxinterval']),
			'form' => $TeX->_SanitizeText($r['rxform']),
			'substitution' => $TeX->_SanitizeText($r['rxsubstitute']),
			'md5' => $TeX->_SanitizeText(md5($r['id']))
		);
	} // end method _print_mapping

	function fax_widget ( $varname, $id ) {
		global $sql, ${$varname};
		$r = freemed::get_link_rec($id, $this->table_name);
		$p = freemed::get_link_rec($r[$this->patient_field], 'patient');
		$pharmacy = freemed::get_link_rec($p['ptpharmacy'], 'pharmacy');
		${$varname} = $pharmacy['phfax'];
		return module_function('pharmacymaintenance',
			'widget',
			array ( $varname, false, 'phfax' )
		);
	} // end method fax_widget

	// Updates
	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN rxphy INT UNSIGNED AFTER rxdtfrom');
		}
		// Version 0.3.3
		//
		//	Add prescription locking
		//
		if (!version_check($version, '0.3.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN locked INT UNSIGNED AFTER rxnote');
			// Patch existing data to be unlocked
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'locked = \'0\'');
		}

		// Version 0.3.4
		//
		//	Add extra intervals
		//
		if (!version_check($version, '0.3.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxinterval rxinterval ENUM (
				"b.i.d.",
				"t.i.d.",
				"q.i.d.",
				"q. 3h",
				"q. 4h",
				"q. 5h",
				"q. 6h",
				"q. 8h",
				"q.d.",
				"h.s.",
				"q.h.s.",
				"q.A.M.",
				"q.P.M.",
				"a.c.",
				"p.c.",
				"p.r.n."
			)');
		}
	} // end function PrescriptionModule->_update

} // end class PrescriptionModule

register_module ("PrescriptionModule");

?>
