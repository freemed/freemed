<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class PatientLink extends SupportModule {

	var $MODULE_NAME = "Patient Tag";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "28e6a139-9d8d-4532-b054-43fe9d0aae01";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Patient Link";
	var $table_name  = "patientlinks";
	var $order_field = "stamp";
	var $patient_field = "srcpatient";

	//var $widget_hash = "##tag## (##datecreate## - ##dateexpire##)";
	var $widget_hash = "##linktype## ##linkdetails##";

	public function __construct ( ) {
		// __("Patient Link")
	
		$this->list_view = array (
			__("Date") => 'stamp',
			__("Link Type") => 'linktype',
			__("Link Details") => 'linkdetails'
		);

		$this->variables = array (
			"srcpatient",
			"destpatient",
			"linktype",
			"linkdetails",
			"user"
		);

		$this->_SetAssociation('EmrModule');

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		unset( $date['stamp'] );
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		unset( $date['stamp'] );
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class PatientLink

register_module ("PatientLink");

?>
