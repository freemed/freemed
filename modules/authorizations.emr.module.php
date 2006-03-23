<?php
	// $Id$
	// lic : GPL, v2

LoadObjectDependency('_FreeMED.EMRModule');

class AuthorizationsModule extends EMRModule {

	var $MODULE_NAME    = "Insurance Authorizations";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.2";
	var $MODULE_DESCRIPTION = "
		Insurance authorizations are used to track whether
		a patient is authorized by his or her insurance
		company for service during a particular period of
		time. If you do not use insurance support in
		FreeMED, this module is not needed.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Authorizations";
	var $table_name     = "authorizations";
	var $patient_field  = "authpatient";
	var $order_fields   = "authdtbegin,authdtend";
	var $widget_hash    = "##authdtbegin##-##authdtend## (##authvisitsremain##/##authvisits##)";

	var $variables = array (
		"authdtmod",
		"authdtbegin",
		"authdtend",
		"authnum",
		"authtype",
		"authprov",
		"authprovid",
		"authinsco",
		"authvisits",
		"authvisitsused",
		"authvisitsremain",
		"authcomment",
		"authpatient",
		"authdtadd"
	);

	function AuthorizationsModule () {
		// __("Insurance Authorizations")
		// Table definition
		$this->table_definition = array (
			'authdtadd' => SQL__DATE,
			'authdtmod' => SQL__DATE,
			'authpatient' => SQL__INT_UNSIGNED(0),
			'authdtbegin' => SQL__DATE,
			'authdtend' => SQL__DATE,
			'authnum' => SQL__VARCHAR(25),
			'authtype' => SQL__INT_UNSIGNED(0),
			'authprov' => SQL__INT_UNSIGNED(0),
			'authprovid' => SQL__VARCHAR(20),
			'authinsco' => SQL__INT_UNSIGNED(0),
			'authvisits' => SQL__INT_UNSIGNED(0),
			'authvisitsused' => SQL__INT_UNSIGNED(0),
			'authvisitsremain' => SQL__INT_UNSIGNED(0),
			'authcomment' => SQL__VARCHAR(100),
			'id' => SQL__SERIAL
		);
	
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("From") => "authdtbegin",
			__("To")   => "authdtend",
			__("Remaining") => "_remaining"
		);
		$this->summary_query = array (
			"IF(authvisits>0,CONCAT(authvisitsremain,'/',authvisits),CONCAT(TO_DAYS(authdtend)-TO_DAYS(NOW()),' days')) AS _remaining"
		);

		$this->form_vars = array (
			"authdtmod",
			"authdtbegin",
			"authdtend",
			"authnum",
			"authtype",
			"authprov",
			"authprovid",
			"authinsco",
			"authvisits",
			"authvisitsused",
			"authvisitsremain",
			"authcomment",
			"authpatient",
			"authdtadd"
		);

		$this->acl = array ( 'bill', 'emr' );

		// Run parent constructor
		$this->EMRModule();
	} // end constructor AuthorizationsModule

	function form_table () {
		global $action, $sql;

		if ($action=="addform") {
			global $authdtbegin;
			$authdtbegin = date("Y-m-d");
		}
		
		return array (
			__("Starting Date") =>
			fm_date_entry("authdtbegin"),

			__("Ending Date") =>
			fm_date_entry("authdtend"),

			__("Authorization Number") =>
			html_form::text_widget("authnum", 25),

			__("Authorization Type") =>
			html_form::select_widget(
				"authtype",
				array(
					__("NONE SELECTED") => "0",
					__("physician") => "1",
					__("insurance company") => "2",
					__("certificate of medical neccessity") => "3",
					__("surgical") => "4",
					__("worker's compensation") => "5",
					__("consulatation") => "6"
				)
			),

			__("Authorizing Provider") =>
			module_function (
				'providermodule',
				'widget',
				array ( 'authprov' )
			),
	
			__("Provider Identifier") =>
			html_form::text_widget("authprovid", 20, 15),
	
			__("Authorizing Insurance Company") =>
			module_function (
				'insurancecompanymodule',
				'widget',
				array ( 'authinsco' )
			),
	
			__("Number of Visits") =>
			fm_number_select ("authvisits", 0, 100),
	
			__("Used Visits") =>
			fm_number_select ("authvisitsused", 0, 100),

			( $action=='addform' ? '' : __("Remaining Visits")) =>
			fm_number_select ("authvisitsremain", 0, 100),

			__("Comment") =>
			html_form::text_widget("authcomment", 30, 100)
		);
	} // end method form_table

	function add () {
		global $authpatient, $authdtbegin, $authdtend, $authdtadd, $patient, $authvisits, $authvisitsremain, $authvisitsused;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend   = fm_date_assemble("authdtend");
		$authdtadd   = date("Y-m-d");
		$authpatient = $patient;
		// All auth visits still remaining by default
		if ($authvisitsused == 0) { $authvisitsremain = $authvisits; }
		$this->_add();
	} // end method add

	function mod () {
		global $authpatient, $authdtbegin, $authdtend, 
			$authdtmod, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend = fm_date_assemble("authdtend");
		$authdtmod = date("Y-m-d");
		$authpatient = $patient;
		$this->_mod();
	} // end method mod

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				"WHERE (authpatient='".addslashes($patient)."') ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY ".$this->order_fields
			),
			$this->page_name,
			array (
				__("Dates") => "authdtbegin",
				"<FONT COLOR=\"#000000\">_</FONT>" => 
					"", // &nbsp; doesn't work, dunno why
				"&nbsp;"  => "authdtend"
			),
			array ("", "/", "")
		);
	} // end method view

} // end class AuthorizationsModule

register_module ("AuthorizationsModule");

?>
