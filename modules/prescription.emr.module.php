<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class PrescriptionModule extends EMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.5";
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
	var $date_field	    = "rxdtfrom";
	var $widget_hash    = "##rxdtfrom## ##rxdrug## ##rxform##";

	var $print_template = 'rx';

	function PrescriptionModule () {
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW |
			SUMMARY_LOCK | SUMMARY_PRINT | SUMMARY_DELETE;

		$this->summary_vars = array (
			__("Date From") => "rxdtfrom",
			__("Drug") => "_drug",
			//__("Dosage") => "_dosage",
			__("Disp") => "_dispensed",
			//__("Dispensed") => "_dispensed",
			__("Sig")  => "rxdosage",
			__("By")   => "rxphy:physician"
			//"Crypto Key" => "rxmd5"
		);
		// Specialized query bits
		$this->summary_query = array (
			"MD5(id) AS rxmd5",
			"CASE rxsize WHEN 0 THEN CONCAT(rxform, ' ', rxdrug) ELSE CASE rxform WHEN 'Spray' THEN CONCAT(rxform, ' ', rxdrug) WHEN 'Unit' THEN rxdrug ELSE CONCAT(rxform, ' ', rxdrug, ' ', rxsize, ' ', rxunit) END END AS _drug",
			"CONCAT(rxsize, ' ', rxunit, ' ', rxinterval) AS _dosage",
			//"CASE rxform WHEN 'Unit' THEN rxquantity WHEN 'Tablets' THEN CONCAT(rxquantity, ' tablets') WHEN 'Spray' THEN CONCAT(rxquantity, ' ', rxunit) WHEN 'Capsules' THEN CONCAT(rxquantity, ' capsules') WHEN 'Container' THEN CONCAT(rxquantity, ' container') WHEN 'Cannister' THEN CONCAT(rxquantity, ' cannister') WHEN 'Bottle' THEN CONCAT(rxquantity, ' bottle') WHEN 'Tube' THEN CONCAT(rxquantity, ' tube') ELSE CONCAT(rxquantity, ' ', IF(rxunit LIKE '%cc%', 'cc', rxunit)) END AS _dispensed",
			"CONCAT(rxquantity, ' ', LCASE(rxform)) AS _dispensed",
			"CASE rxrefills WHEN 99 THEN 'p.r.n' ELSE rxrefills END AS _refills"
		);

		// Table definition
		$this->table_definition = array (
			'rxdtadd' => SQL__DATE,
			'rxdtmod' => SQL__DATE,
			'rxphy' => SQL__INT_UNSIGNED(0),
			'rxpatient' => SQL__INT_UNSIGNED(0),
			'rxdtfrom' => SQL__DATE,
			'rxdrug' => SQL__VARCHAR(150),
			'rxform' => SQL__VARCHAR(32),
			'rxdosage' => SQL__VARCHAR(128),
			'rxquantity' => SQL__REAL,
			'rxsize' => SQL__VARCHAR(32),
			'rxunit' => SQL__VARCHAR(32),
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
			"rxsize" => html_form::combo_assemble('rxsize'),
			"rxform" => html_form::combo_assemble('rxform'),
			"rxdosage" => html_form::combo_assemble('rxdosage'),
			"rxquantity" => html_form::combo_assemble('rxquantity'),
			"rxunit" => html_form::combo_assemble('rxunit'),
			"rxinterval",
			"rxpatient",
			"rxsubstitute",
			"rxrefills",
			"rxperrefill",
			"rxnote",
			"locked" => '0'
		);
		$this->acl = array ('emr');
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
	} // end method display

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
					"rxdtfrom_m",
					"rxdtfrom_y",
					"rxdtfrom_d",
				"rxphy",
				"rxdrug",
				"rxsize", "rxsize_text",
				"rxunit", "rxunit_text",
				"rxquantity",
				"rxdosage", "rxdosage_text",
				"rxform", "rxform_text",
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
				html_form::combo_widget(
					"rxform",
					array_merge(
						$GLOBALS['sql']->distinct_values($this->table_name, 'rxform'),
						array(
							"Suspension" => "Suspension",
							"Tablets" => "Tablets",
							"Capsules" => "Capsules",
							"Solution" => "Solution",
							"Spray" => "Spray",
							"Tube" => "Tube",
							"Unit" => "Unit"
						)
					)
				)." ".
				freemed::drug_widget("rxdrug", "myform", "__action")." ".
				"<table border=\"0\"><tr><td>".
				html_form::text_widget('rxsize', 10)." ".
				"</td><td>".
				//html_form::combo_widget(
				html_form::select_widget(
					"rxunit",
					//array_merge(
						//$GLOBALS['sql']->distinct_values($this->table_name, 'rxunit'),
						array(
							" " => " ",
							"cc" => "cc",
							"g" => "g",
							"mg" => "mg",
							"mg/1cc" => "mg/1cc",
							"mg/2cc" => "mg/2cc",
							"mg/3cc" => "mg/3cc",
							"mg/4cc" => "mg/4cc",
							"mg/5cc" => "mg/5cc",
							"ml" => "ml",
							"quart" => "quart",
							"microgram" => "microgram",
							"mcg" => "mcg",
							"unit" => "unit",
							"cannister" => "cannister",
						)
					//)
				).
				"</td></tr></table>",

				__("Disp") =>
				html_form::combo_widget(
					'rxquantity',
					$GLOBALS['sql']->distinct_values($this->table_name, 'rxquantity')
				),

				__("Sig") =>
				html_form::combo_widget(
					'rxdosage',
					$GLOBALS['sql']->distinct_values($this->table_name, 'rxdosage')
				),

				__("Refill") =>
				html_form::select_widget(
					'rxrefills',
					array (
						'0' => 0,
						'1' => 1,
						'2' => 2,
						'3' => 3,
						'4' => 4,
						'5' => 5,
						'6' => 6,
						'7' => 7,
						'8' => 8,
						'9' => 9,
						'10' => 10,
						'11' => 11,
						'12' => 12,
						'p.r.n.' => 99
					)
				),
				//." / ".
				//html_form::text_widget(
				//	"rxperrefill", 10
				//)." ".__("units"),

				__("Substitution") =>
				html_form::select_widget(
					"rxsubstitute",
					array (
					__("may substitute") => "may substitute",
					__("may not substitute") => "may not substitute",
					)
				)
			))
		);

		$book->add_page(
			__("Note"),
			array(
				"rxnote"
			),
			"<div ALIGN=\"CENTER\">\n".
			html_form::text_area(
				"rxnote"
			).
			"</div>"
		);
		if ($book->get_current_page == __("Note")) {
			$GLOBALS['__freemed']['on_load'] = "document.getElementById('rxnote').focus";
		}

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
	} // end method form

	function prepare () {
		// Common stuff between add/mod to prepare vars
		global $display_buffer,
			$rxpatient, $patient;
		$rxpatient = $patient;

		if ($_REQUEST['action'] == 'addform') {
			global $rxdtadd; $rxdtadd = date('Y-m-d');
		}
		global $rxdtmod; $rxdtmod = date('Y-m-d');
	} // end method prepare

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
	} // end method view

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

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->date_field." DESC";
		$res = $GLOBALS['sql']->query($query);
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$m[] = trim($r['rxdrug'].' '.$r['rxdosage'].' '.$r['rxroute']);
		}
		return @join(', ', $m);
	} // end method recent_text

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

		// Version 0.3.5
		//
		//	Change prescription format
		//
		if (!version_check($version, '0.3.5')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxform rxform VARCHAR(32)');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxunit rxunit VARCHAR(32)');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxsize rxsize REAL');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxdosage rxdosage VARCHAR(128)');
			$sql->query('UPDATE '.$this->table_name.' '.
				'SET rxdosage = concat(rxdosage, \' \', rxinterval)');
		}
	} // end method _update

} // end class PrescriptionModule

register_module ("PrescriptionModule");

?>
