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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class ClinicRegistration extends SupportModule {

	var $MODULE_NAME = "Clinic Registration";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "ec46f8f5-ea7d-4d95-bdc7-1f301405bc4c";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "clinicregistration";

        var $widget_hash = '##lastname## ##lastname2##, ##firstname##';

	var $archive_field = "archive";

	var $archive_check = "1";

	var $variables = array (
		  "lastname"
		, "lastname2"
		, "firstname"
		, "dob"
		, "gender"
		, "age"
		, "notes"
	);

	public function __construct ( ) {
		// __("Clinic Registration")

		// Call parent constructor
		parent::__construct();
		if($this->archive_field) {
			$this->archive_check = "(".$this->archive_field." IS NULL OR ".$this->archive_field."=0)";
		}
	} // end constructor Callin

	protected function add_pre ( $data ) {
		$data['user'] = freemed::user_cache()->user_number;
		$data['facility'] = HTTP_Session2::get( 'facility_id' );
	} // end method add_pre

	protected function mod_pre ( $data ) {
		$data['user'] = freemed::user_cache()->user_number;
		$data['facility'] = HTTP_Session2::get( 'facility_id' );
	} // end method mod_pre

	// Method: GetAll
	//
	//	Get array of all active clinic registration records.
	//
	// Returns:
	//
	//	Hash.
	//
	public function GetAll () {
		freemed::acl_enforce( 'emr', 'read' );
		$q = "SELECT *, CONCAT(lastname, ' ', lastname2) AS fulllastname FROM ".$this->table_name." WHERE processed = FALSE ORDER BY dateof DESC";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll

	public function createPatient( $crid ) {
		$query = "CALL clinicregistration_CreatePatient ( " . $GLOBALS['sql']->quote(freemed::user_cache()->user_number) . ", " . $GLOBALS['sql']->quote($crid) . " );";
		return $GLOBALS['sql']->queryOneStoredProc( $query );
	} // end method createPatient

	public function migrateToPatient( $crid, $pid ) {
		$query = "CALL clinicregistration_MigrateToPatient ( " . $GLOBALS['sql']->quote(freemed::user_cache()->user_number) . ", " . $GLOBALS['sql']->quote($crid) . ", " . $GLOBALS['sql']->quote($pid) . " );";
		$GLOBALS['sql']->queryOneStoredProc( $query );
		return true;
	} // end method migrateToPatient

} // end class ClinicRegistration
	
register_module('ClinicRegistration');

?>
