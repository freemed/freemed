<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

        var $widget_hash = '##cilname##, ##cifname## ##cimname##';

	var $variables = array (
		'cilname',
		'cifname',
		'cimname',
		'cihphone',
		'ciwphone',
		'cidob',
		'cicomplaint',
		'cifacility',
		'ciphysician',
		'ciuser',
		'citookcall',
		'cipatient'
	);

	public function __construct ( ) {
		// __("Call-in Patients")

		// Call parent constructor
		parent::__construct();
	} // end constructor Callin

	protected function add_pre ( &$data ) {
	} // end method add_pre

	protected function mod_pre ( &$data ) {
	} // end method mod_pre

	// Method: GetAll
	//
	//	Get array of all call-in patient records.
	//
	// Parameters:
	//
	//	$id - Database ID
	//
	// Returns:
	//
	//	Hash.
	public function GetAll () {
		freemed::acl_enforce( 'emr', 'search' );
		$q = "SELECT CONCAT(cilname, ', ', cifname, ' ', cimname) AS name, cicomplaint AS complaint, citookcall AS took_call, cidatestamp AS call_date, DATE_FORMAT(cidatestamp, '%m/%d/%Y') AS call_date_mdy, cihphone AS phone_home, ciwphone AS phone_work, id FROM callin ORDER BY cidatestamp DESC";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll
	
	// Method: GetDetailedRecord
	//
	//	Get detailed record of call-in patient.
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetDetailedRecord( $id) {
		freemed::acl_enforce( 'emr', 'search' );
		$q = "SELECT CONCAT(c.cilname, ', ', c.cifname, ' ', c.cimname) AS name, c.cicomplaint AS complaint, c.citookcall AS took_call, c.cidatestamp AS call_date"
		.", DATE_FORMAT(c.cidatestamp, '%m/%d/%Y') AS call_date_mdy,c.cidob AS dob, c.cihphone AS phone_home, c.ciwphone AS phone_work, c.id ,f.psrname as facility"
		.",CONCAT(ph.phylname, ', ', ph.phyfname, ' ', ph.phymname) AS physician "
		."FROM callin c LEFT OUTER JOIN facility f ON c.cifacility=f.id LEFT OUTER JOIN physician ph ON c.ciphysician=ph.id where c.id=".$id;
		return $GLOBALS['sql']->queryRow( $q );
	} // end method GetDetailedRecord
	

}

register_module('Callin');

?>
