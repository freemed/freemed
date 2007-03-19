<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class Callin extends SupportModule {

	var $MODULE_NAME = "Call-in";
	var $MODULE_VERSION = "0.7";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "5f4e5de0-58fa-495e-84a6-9c35a7f7e816";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "callin";

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
	} // end constructor Callin

	// Method: GetAll
	//
	//	Get array of all call-in patient records.
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetAll ( ) {
		$q = "SELECT CONCAT(cilname, ', ', cifname, ' ', cimname) AS name, cicomplaint AS complaint, citookcall AS took_call, cidatestamp AS call_date, DATE_FORMAT(cidatestamp, '%m/%d/%Y') AS call_date_mdy, cihphone AS phone_home, ciwphone AS phone_work, id FROM callin ORDER BY cidatestamp DESC";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll

}

register_module('Callin');

?>
