<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //	Fred Forester <fforest@netcarrier.com>
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

class PatientCoverages extends EMRModule {

	var $MODULE_NAME = "Patient Coverage";
	var $MODULE_VERSION = "0.3.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "bfb88968-fd81-41c7-b3ad-93098c3bc6be";

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name    = "coverage";
	var $record_name   = "Patient Coverage";
	var $patient_field = "covpatient";

	// contructor method
	public function __construct ( ) {
		// __("Patient Coverage")

		$this->summary_vars = array (
			__("Plan") => 'insconame',
			__("Date") => 'coveffdt'
		);
		$this->summary_query = array (
			"IF ( covstatus, 'Deleted', 'Active' ) AS covstat",
			"ELT ( covtype, 'Primary', 'Secondary', 'Tertiary', 'WorkComp' ) AS covtp"
		);
		$this->summary_query_link = array (
			'covinsco' => 'insco'
		);

		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
		parent::__construct ( );
	} // end function PatientCoverages

	protected function add_pre ( &$data ) {
		$data['covstatus'] = ACTIVE;
		$data['covdtadd'] = date('Y-m-d');
		$data['covdtmod'] = date('Y-m-d');
	}

	protected function mod_pre ( &$data ) {
		$data['covstatus'] = ACTIVE;
		$data['covdtmod'] = date('Y-m-d');
	}

	// Method: RemoveOldCoverage
	//
	//	Move old coverage to deleted status.
	//
	// Parameters:
	//
	//	$patient - Patient record id
	//
	//	$covtype - Type of coverage to remove.
	//
	// Returns:
	//
	//	Boolean, if successful.
	//
	public function RemoveOldCoverage ( $patient, $covtype ) {
		$query = "UPDATE coverage SET covstatus='".DELETED."' ".
			"WHERE covtype='".addslashes($covtype)."' ".
			"AND covpatient='".addslashes($patient)."'";
		$result = $GLOBALS['sql']->query( $query );
		return ( $result ? true : false );
	} // end method RemoveOldCoverage

	function _update( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.3
		//
		//	Add assigning, school name and employer name for
		//		HCFA and X12 forms and billing stuff.
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covisassigning INT UNSIGNED AFTER covplanname');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covschool INT UNSIGNED AFTER covisassigning');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covemployer INT UNSIGNED AFTER covschool');
		}

		// Version 0.3.1
		//
		//	Added covssn, which claims manager was depending on.
		//
		if (!version_check($version, '0.3.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covssn CHAR(9) AFTER covsex');
		}

		// Version 0.3.2
		//
		//	Added covcopay and covdeduct to track copay and
		//	deductable per coverage
		//
		if (!version_check($version, '0.3.2')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covcopay REAL AFTER covemployer');
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN covdeduct REAL AFTER covcopay');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET covcopay=0, covdeduct=0 WHERE id>0');
		}
	} // end method _update

} // end class PatientCoverages

register_module("PatientCoverages");

?>
