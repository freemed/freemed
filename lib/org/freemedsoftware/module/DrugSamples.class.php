<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

class DrugSamples extends EMRModule {

	var $MODULE_NAME    = "Drug Samples";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "0e228dfe-c72d-4f82-8610-6c4990fb201c";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Patient IDs";
	var $table_name     = "drugsamples";
	var $patient_field  = "patientid";
	var $order_fields   = "stamp";
	var $widget_hash    = "##drugsampleid##";

	var $variables = array (
		  "drugsampleid"
		, "patientid"
		, "prescriber"
		, "deliveryform"
		, "amount"
		, "instructions"
		, "stamp"
		, "user"
	);

	public function __construct () {
		// __("Drug Samples")
	
		$this->acl = array ( 'emr' );
		$this->_SetAssociation( 'EmrModule' );

		// Run parent constructor
		parent::__construct();
	} // end constructor


	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class DrugSamples

register_module ("DrugSamples");

?>
