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

class AppointmentFollowup extends EMRModule {

	var $MODULE_NAME = "Potential Followup";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "44a78807-4f60-4f2b-bb7a-b13acfe099a9";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Followup";
	var $table_name = 'apptplanning';
	var $patient_field = 'appatient';
	var $order_field = 'apdatetarget';

	var $variables = array (
		'appatient',
		'apdatecreated',
		'apdatetarget',
		'appriority',
		'apreason',
		'apschedulerlink',	
		'approvider',
		'apnotifiedon',
		'user'
	);

	public function __construct () {
		// call parent constructor
		parent::__construct( );
	} // end constructor

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class AppointmentFollowup

register_module ("AppointmentFollowup");

?>
