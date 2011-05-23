<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class FinancialDemographics extends EMRModule {

	var $MODULE_NAME    = "Financial Demographics";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Keep track of information for determining sliding fee schedule and other income and dependent parties related information.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "1571145e-50f0-4b6d-87ce-37c519c8dfed";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Financial Demographics";
	var $table_name     = "financialdemographics";
	var $patient_field  = "fdpatient";

	public function __construct ( ) {
		// __("Financial Demographics")
		// __("Keep track of information for determining sliding fee schedule and other income and dependent parties related information.")

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
			'fdfreetext',
			'user'
		);

		$this->acl = array ( 'bill', 'emr' );

		$this->_SetAssociation('EmrModule');

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
		parent::__construct();
	} // end constructor FinancialDemographics

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class FinancialDemographics

register_module ("FinancialDemographics");

?>
