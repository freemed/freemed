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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class LabsModule extends EMRModule {

	var $MODULE_NAME    = "Lab";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Lab reports";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "c91c2113-4750-48c5-9c71-2dfe81435a07";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Labs";
	var $table_name     = "labs";
	var $patient_field  = "labpatient";

	public function __construct ( ) {
		// __("Labs")

		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => '_timestamp',
			__("Lab") => 'labfiller',
			__("Order Code") => 'labordercode',
			__("Status") => 'labresultstatus'
		);
		$this->summary_query = array (
			"DATE_FORMAT(labtimestamp, '%b %d, %Y %H:%i') AS _timestamp"
		);
		$this->summary_options |= SUMMARY_VIEW;

		$this->form_vars = array (
			// TODO - FIXME
		);

		$this->variables = array (
			'labtimestamp' => SQL__NOW,
			'labpatient',
			'labfiller',
			'labstatus',
			'labprovider',
			'labordercode',
			'laborderdescrip',
			'labcomponentcode',
			'labcomponentdescrip',
			'labfillernum',
			'labplacernum',
			'labresultstatus',
			'labnotes',
			'user'
		);

		$this->acl = array ( 'emr' );

		// Run parent constructor
		parent::__construct ( );
	} // end constructor LabsModule

	// Method: GetLabValues
	//
	//	Retrieve lab values for a particular lab request.
	//
	// Parameters:
	//
	//	$id - Record id for the lab request
	//
	// Returns:
	//
	//	Array of hashes containing the following hash keys:
	//	* labobscode - Observation
	//	* labobsvalue - Value of observation
	//	* labobsunit - Unit of observation value
	//	* labobsrange - Range
	//	* labobsnormal - Normal
	//	* labobsabnormal - Abnormal
	//
	public function GetLabValues ( $id ) {
		$query = "SELECT * FROM labresults WHERE labid='".addslashes( $id )."' ";
		$result = $GLOBALS['sql']->queryAll($query);
		return $result;
	} // end method GetLabValues

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class LabsModule

register_module ("LabsModule");

?>
