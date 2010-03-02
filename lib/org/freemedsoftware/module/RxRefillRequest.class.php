<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //     Phil Meng <pmeng@freemedsoftware.org>
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

class RxRefillRequest extends EMRModule {

	var $MODULE_NAME = "Prescription Refill";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "24d3b7c8-c683-4185-af0b-0742d3a58161";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Prescription Refills";
	var $table_name = 'rxrefillrequest';
	var $patient_field = 'patient';
	var $order_field = 'stamp';
	
	// 	'stamp' - removed and allowed to set the default 
	//  will set to the current timestamp     
	var $variables = array (
		'patient',
		'provider',
		'rxorig',
		'note',
		'approved',
		'user'
	);

	public function __construct () {
		// call parent constructor
		parent::__construct( );
	} // end constructor

	protected function add_pre ( &$data ) {
		unset($data['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: GetAll
	//
	//	Get all records.
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function GetAll ( ) {
		freemed::acl_enforce( 'emr', 'search' );
		$query = "select a.stamp as stamp, c.username as user, 
          concat(b.ptlname, ' ', b.ptmname, ' ', b.ptfname) as patient, 
          a.provider, a.rxorig as rxorig, a.note as note,a.approved as approved, 
          a.locked as locked, a.id as id 
          from " . $this->table_name . " as a, 
          patient as b, 
          user as c 
          where a.patient = b.id and c.id = a.user ORDER BY stamp DESC";
		
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetAll

} // end class RxRefillRequest

register_module ("RxRefillRequest");

?>
