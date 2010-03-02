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

class Letters extends EMRModule {

	var $MODULE_NAME    = "Letter";
	var $MODULE_VERSION = "0.3.5";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID = "791918e6-092a-44ec-9477-f87b50345659";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Letters";
	var $table_name     = "letters";
	var $patient_field  = "letterpatient";
	var $widget_hash    = "##letterdt## ##letterfrom:physician:phylname## to ##letterto:physician:phylname##";

	var $print_template = 'letters';

	var $variables = array (
		"letterdt",
		"lettereoc",
		"letterfrom",
		"letterto",
		"lettercc",
		"letterenc",
		"lettersubject",
		"lettertext",
		"letterpatient",
		"lettertypist",
		"locked" => '0',
		"user"
	);

	public function __construct ( ) {
		// __("Letters")

		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "my_date",
			__("From")   => "letterfrom:physician",
			__("To")   => "letterto:physician"
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW
			| SUMMARY_PRINT | SUMMARY_LOCK | SUMMARY_DELETE;
		$this->summary_query = array (
			"DATE_FORMAT(letterdt, '%m/%d/%Y') AS my_date"
		);

		// Set associations
		$this->_SetAssociation('EmrModule');
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'lettereoc');

		$this->acl = array ( 'bill', 'emr' );

		// Run parent constructor
		parent::__construct ( );
	} // end constructor Letters

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class Letters

register_module ("Letters");

?>
