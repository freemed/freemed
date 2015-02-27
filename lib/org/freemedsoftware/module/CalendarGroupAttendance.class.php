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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class CalendarGroupAttendance extends SupportModule {

	var $MODULE_NAME = "Calendar Group Attendance";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "ed245f1f-f724-4741-9bc3-3f8fc0ce81b6";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "calgroupattend";

	var $variables = array (
		'calgroupid',
		'calid',
		'patientid',
		'calstatus',
	);

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
	} // end constructor Callin

	protected function add_pre ( &$data ) {
	} // end method add_pre

	protected function del_pre ( &$data ) {
	} // end method del_pre

}

register_module('CalendarGroupAttendance');

?>
