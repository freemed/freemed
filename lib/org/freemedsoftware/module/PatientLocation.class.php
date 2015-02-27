<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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

class PatientLocation extends EMRModule {

	var $MODULE_NAME    = "Patient Location";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "ff6bae36-cc9b-44e0-8482-11f59f092c84";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Patient Location";
	var $table_name     = "patientlocation";
	var $patient_field  = "patient";
	var $order_fields   = "stamp";
	var $widget_hash    = "##note##";

	public function __construct () {
		$this->variables = array (
			  "patient"
			, "stamp"
			, "lat"
			, "lon"
			, "note"
			, "geosource"
			, "user"
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

} // end class PatientLocation

register_module ("PatientLocation");

?>
