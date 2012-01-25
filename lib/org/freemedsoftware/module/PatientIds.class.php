<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

class PatientIds extends EMRModule {

	var $MODULE_NAME    = "Patient IDs";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "26bf56af-0d7b-47bc-91e2-aa827844ff71";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Patient IDs";
	var $table_name     = "patient_ids";
	var $patient_field  = "patient";
	var $order_fields   = "stamp";
	var $widget_hash    = "##foreign_id##";

	public function __construct () {
		// __("PatientIDs")
	
		// Set vars for patient management summary
		$this->list_view = array (
			__("From") => "authdtbegin",
			__("To")   => "authdtend",
			__("Remaining") => "_remaining"
		);
		$this->variables = array (
			"foreign_id",
			"practice",
			"facility",
			"patient",
			"stamp",
			"user"
		);

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

} // end class PatientIds

register_module ("PatientIds");

?>
