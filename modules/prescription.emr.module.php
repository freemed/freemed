<?php
 // $Id$
 // note: prescription db/module functions
 // lic : GPL

LoadObjectDependency('FreeMED.EMRModule');

class PrescriptionModule extends EMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_DESCRIPTION = "
		The prescription module allows prescriptions to be written 
		for patients from any drug in the local formulary or in the 
		Multum drug database (if access to that database is 
		available.";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Prescription";
	var $table_name     = "rx";
	var $patient_field  = "rxpatient";

	function PrescriptionModule () {
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW;

		$this->summary_vars = array (
			"Date From" => "rxdtfrom",
			"Drug" => "_drug",
			"Dosage" => "_dosage",
			"Dispensed" => "_dispensed"
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
			'rxdtadd' => SQL_DATE,
			'rxdtmod' => SQL_DATE,
			'rxpatient' => SQL_INT_UNSIGNED(0),
			'rxdtfrom' => SQL_DATE,
			'rxdrug' => SQL_VARCHAR(150),
			'rxform' => SQL_ENUM(array(
				"suspension",
				"tablet",
				"capsule",
				"solution"
				)),
			'rxdosage' => SQL_INT_UNSIGNED(0),
			'rxquantity' => SQL_INT_UNSIGNED(0),
			'rxsize' => SQL_INT_UNSIGNED(0),
			'rxunit' => SQL_ENUM(array(
				"mg",
				"mg/1cc",
				"mg/2cc",
				"mg/3cc",
				"mg/4cc",
				"mg/5cc",
				"g"
				)),
			'rxinterval' => SQL_ENUM(array(
				"b.i.d.",
				"t.i.d.",
				"q.i.d.",
				"q. 3h",
				"q. 4h",
				"q. 5h",
				"q. 6h",
				"q. 8h",
				"q.d."
				)),
			'rxsubstitute' => SQL_ENUM(array(
				"may substitute", "may not substitute"
				)),
			'rxrefills' => SQL_INT_UNSIGNED(0),
			'rxperrefill' => SQL_INT_UNSIGNED(0),
			'rxnote' => SQL_TEXT,
			'id' => SQL_NOT_NULL(SQL_AUTO_INCREMENT(SQL_INT(0)))
		);

		$this->variables = array (
			"rxdtfrom" => date_assemble("rxdtfrom"),
			"rxdrug",
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
			"rxnote"
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
			_("Drug") => $rxdrug,
			_("Dosage") => $rxdosage." ".$rxunit." ".$rxinterval
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
			}
		}

		$book->set_submit_name(
			(
				( ($action=="add") or ($action=="addform") ) ?
				_("Add") :
				 _("Modify")
			)
		);

		// Add pages
		$book->add_page(
			_("Prescription"),
			array(
				"rxdtfrom",
				"rxdrug",
				"rxsize",
				"rxunit",
				"rxdosage",
				"rxform",
				"rxinterval",
				"rxrefills",
				"rxperrefill",
				"rxsubstitute"
			),
			html_form::form_table(array(
				_("Starting Date") =>
				date_entry("rxdtfrom"),

				_("Drug") =>
				freemed::drug_widget("rxdrug", "myform", "__action"),

				_("Quantity") =>
				html_form::text_widget(
					"rxquantity", 10
				),

				_("Medicine Units") =>
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

				_("Dosage") =>
				html_form::text_widget(
					"rxdosage", 10
				).
				" "._("in")." ".
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
						"q. 8h"
					)
				),

				_("Refill") =>
				html_form::number_pulldown(
					"rxrefills", 0, 20
				)." / ".
				html_form::text_widget(
					"rxperrefill", 10
				)." "._("units"),

				_("Substitution") =>
				html_form::select_widget(
					"rxsubstitute",
					array (
					_("may not substitute") => "may not substitute",
					_("may substitute") => "may substitute"
					)
				)
			))
		);

		$book->add_page(
			_("Notes"),
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
				_("Date") => "rxdtfrom",
				_("Drug") => "rxdrug",
				_("Dosage") => "_dosage"
			),
			array("", _("NONE")),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL
		);
	} // end function PrescriptionModule->view

} // end class PrescriptionModule

register_module ("PrescriptionModule");

?>
