<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

	var $MODULE_NAME    = "Labs";
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

		// Table definition
		$this->table_definition = array (
			'labpatient' => SQL__INT_UNSIGNED(0), // PID
			'labfiller' => SQL__TEXT, // OBR 21-01
			'labstatus' => SQL__CHAR(2), // ORC 05
			'labprovider' => SQL__INT_UNSIGNED(0), // ORC 12
			'labordercode' => SQL__VARCHAR(16), // OBR 04-03
			'laborderdescrip' => SQL__VARCHAR(250), // OBR 04-04
			'labcomponentcode' => SQL__VARCHAR(16), // OBR 20-03
			'labcomponentdescrip' => SQL__VARCHAR(250), // OBR 20-04
			'labfillernum' => SQL__VARCHAR(16), // OBR 02
			'labplacernum' => SQL__VARCHAR(16), // OBR 03
			'labtimestamp' => SQL__TIMESTAMP(14), // OBR 07
			'labresultstatus' => SQL__CHAR(1), // OBR 25
			'labnotes' => SQL__TEXT, // NTE
			'id' => SQL__SERIAL
		);
	
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

} // end class LabsModule

register_module ("LabsModule");

?>
