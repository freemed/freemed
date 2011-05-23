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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class ReportingPrintLog extends SupportModule {

	var $MODULE_NAME = "Reporting Print Log";
	var $MODULE_VERSION = "0.8.4";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "b0f7d606-0b7c-4ffe-8105-a68c6f099ee2";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "reporting_print_log";

	var $variables = array (
		'report_name',
		'report_params',
		'report_format',
		'user'
	);

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
	} // end constructor Callin

	protected function add_pre ( &$data ) {
		unset($data['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	} // end method add_pre
	
	public function GetAllRecords(){
		$user = freemed::user_cache()->user_number;
		$q = "SELECT * from ".$this->table_name." WHERE user =" . $GLOBALS['sql']->quote( $user );
		return $GLOBALS['sql']->queryAll( $q );
	}
	
}

register_module('ReportingPrintLog');

?>
