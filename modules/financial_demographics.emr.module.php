<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class FinancialDemographics extends EMRModule {

	var $MODULE_NAME    = "Financial Demographics";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
	Keep track of information for determining sliding fee schedule
	and other income and dependent parties related information.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Financial Demographics";
	var $table_name     = "financialdemographics";
	var $patient_field  = "fdpatient";

	function FinancialDemographics () {
		// __("Financial Demographics")
		// Table definition
		$this->table_definition = array (
			'fdtimestamp' => SQL__TIMESTAMP(16),
			'fdpatient' => SQL__INT_UNSIGNED(0),
			'fdincome' => SQL__INT_UNSIGNED(0),
			'fdidtype' => SQL__VARCHAR(50),
			'fdidissuer' => SQL__VARCHAR(50),
			'fdidnumber' => SQL__VARCHAR(50),
			'fdidexpire' => SQL__VARCHAR(10),
			'fdhousehold' => SQL__INT_UNSIGNED(0),
			'fdspouse' => SQL__INT_UNSIGNED(0),
			'fdchild' => SQL__INT_UNSIGNED(0),
			'fdother' => SQL__INT_UNSIGNED(0),
			'fdfreetext' => SQL__TEXT,
			'fdentry' => SQL__VARCHAR(75),
			'id' => SQL__SERIAL
		);
	
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "_timestamp",
			__("Household Size") => "fdhousehold",
			__("Income") => "fdincome"
		);
		$this->summary_query = array (
			"DATE_FORMAT(fdtimestamp, '%b %d, %Y %H:%i') AS _timestamp"
		);

		$this->form_vars = array (
			'fdincome',
			'fdidtype',
			'fdidissuer',
			'fdidnumber',
			'fdidexpire',
			'fdhousehold',
			'fdspouse',
			'fdchild',
			'fdother',
			'fdfreetext'
		);

		$this->variables = array (
			'fdtimestamp' => SQL__NOW,
			'fdpatient',
			'fdincome',
			'fdidtype',
			'fdidissuer',
			'fdidnumber',
			'fdidexpire',
			'fdhousehold',
			'fdspouse',
			'fdchild',
			'fdother',
			'fdfreetext'
		);

		$this->acl = array ( 'bill', 'emr' );

		// Set configuration variables for sliding fee scale
		$this->_SetMetaInformation('global_config_vars', array (
			'sliding_fee', 'fed_pov_level', 'fed_pov_inc'
		));
		$this->_SetMetaInformation('global_config', array (
			__("Sliding Fee Scale Enabled") =>
			'html_form::select_widget("sliding_fee", array('.
			'"'.__("no").'" => 0, '.
			'"'.__("yes").'" => 1 ))',
			__("Federal Poverty Level") =>
			'html_form::text_widget("fed_pov_level", 20, 50)',
			__("Federal Poverty Increment") =>
			'html_form::text_widget("fed_pov_inc", 20, 50)',
		));

		// Run parent constructor
		$this->EMRModule();
	} // end constructor FinancialDemographics

	function form_table () {
		return array (
			__("Yearly Income") =>
			html_form::text_widget('fdincome', 10),

			__("Identification") =>
			html_form::select_widget(
				'fdidtype',
				array(
					__("driver's license") => 'driver\'s license',
					__("passport") => 'passport',
					__("baptismal certificate") => 'baptismal certificate',
					__("green card") => 'green card',
					__("birth certificate") => 'birth certificate'
				)
			),

			__("Identification Issuer") =>
			html_form::combo_widget(
				'fdidtype',
				$GLOBALS['sql']->distinct_values($this->table_name, 'fdidtype')
			),

			__("Identification Number") =>
			html_form::text_widget('fdidnumber', 50),

			__("Expiration") =>
			html_form::text_widget('fdidnumber', 50),

			__("Size of Household") =>
			html_form::number_pulldown('fdhousehold', 0, 30),

			__("Spouse") =>
			html_form::select_widget(
				'fdspouse',
				array(
					__("no") => 0,
					__("yes") => 1
				)
			),

			__("Dependent Children") =>
			html_form::number_pulldown('fdchild', 0, 30),

			__("Other Dependents") =>
			html_form::number_pulldown('fdother', 0, 30),

			__("Other Information") =>
			html_form::text_area('fdfreetext')
		);
	} // end method form_table

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				"WHERE (".$this->patient_field."='".addslashes($_REQUEST['patient'])."') ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY ".$this->order_fields
			),
			$this->page_name,
			array (
				__("Timestamp") => "fdtimestamp",
			),
			array ("")
		);
	} // end method view

} // end class FinancialDemographics

register_module ("FinancialDemographics");

?>
