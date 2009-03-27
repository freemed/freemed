<?php
	// $Id$
	// $Author$
	// lic : GPL, v2

LoadObjectDependency('_FreeMED.EMRModule');

class OncologyReportImages extends EMRModule {

	var $MODULE_NAME = "Oncology Clinic Notes";
	var $MODULE_AUTHOR = "Volker Bradley (volker@srnet.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name   = "Oncology Clinic Notes";
	var $table_name    = "images";
	var $patient_field = "imagepat";
	var $order_by      = "imagedt";
	var $summary_conditional = "imagetype = 'oncology'";

	function OncologyReportImages () {
		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Category")    =>	"imagecat",
			__("Description") =>	"imagedesc",
			__("Reviewed")    =>	'reviewed'
		);
		$this->summary_options |= SUMMARY_VIEW | SUMMARY_PRINT | SUMMARY_DELETE;
		$this->summary_query = array (
			"DATE_FORMAT(imagedt, '%m/%d/%Y') AS my_date",
			"CASE imagereviewed WHEN 0 THEN 'no' ELSE 'yes' END AS reviewed"
		);
		$this->summary_order_by      = "imagedt";

		// Call parent constructor
		$this->EMRModule();
	} // end constructor OncologyReportImages

	function display () { $this->_redirect(); }
	function add () { $this->_redirect(); }
	function mod () { $this->_redirect(); }
	function del () { $this->_redirect(); }
	function form () { $this->_redirect(); }
	function print_override ( $id ) {
		include_once(resolve_module('ScannedDocuments'));
		$s = new ScannedDocuments();
		return $s->print_override ( $id );
	}


	function view ($condition = false) {
		global $display_buffer;
		global $patient, $action;

		$refresh = "module_loader.php?".
			"module=scanneddocuments&".
			"return=".urlencode($_REQUEST['return'])."&".
			"patient=".urlencode($_REQUEST['patient'])."&".
			"action=".urlencode($_REQUEST['action'])."&".
			"id=".urlencode($_REQUEST['id']);
		
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Check for "view" action (actually display)
		if ($action=="view") {
			$this->display();
			return NULL;
		}

		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT * FROM ".$this->table_name." ".
				"WHERE (imagepat='".addslashes($patient)."') ".
				freemed::itemlist_conditions(false)." ".
				( $condition ? 'AND '.$condition : '' )." ".
				"ORDER BY imagedt"
			),
			$this->page_name,
			array (
				"Date"        => "imagedt",
				"Description" => "imagedesc"
			), // array
			array (
				"",
				__("NO DESCRIPTION")
			),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL
		);
		$display_buffer .= "\n<p/>\n";
	} // end method view

	function _redirect () {
		module_function('ScannedDocuments', $_REQUEST['action']);
	}

	function fax_widget ( $a, $b) { return module_function('ScannedDocuments', 'fax_widget', array ($a, $b) ); }

} // end of class OncologyReportImages

register_module ("OncologyReportImages");

?>
