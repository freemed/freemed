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

class ModuleFieldChecker extends SupportModule {

	var $MODULE_NAME = "Module Field Checker";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "04a34310-9737-4aee-9944-47a37d8b3cd7";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Module Field Checker";
	var $table_name  = "module_field_checker";
	var $order_field = "stamp";

	var $variables = array (
		"stamp",
		"patient",
		"user",
		"module_type",
		"module_fields",
		"module_record",
		"status_completed"
	);

	public function __construct ( ) {
		// __("Module Field Checker")

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$date['stamp'] = '';
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: getUncompletedItems
	//
	//	evaluates uncompleted items 
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function getUncompletedItems($patient = NULL) {
		
		$u = freemed::user_cache();
		$q = "select mfc.id,p.id as patient_id,date(mfc.stamp) as stamp,mfct.name as status_name,mfct.module as status_module,'Incomplete Form' as summary,'form_incomplete' as type,CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname ) AS patient_name from ".$this->table_name." mfc left join patient p on p.id = mfc.patient left join module_field_checker_type mfct on mfct.id = mfc.module_type where status_completed=0 and user = ".$u->user_number.($patient?" and mfc.patient=".$GLOBALS['sql']->quote($patient):" ");
		return $GLOBALS['sql']->queryAll( $q );
	} // end method getUncompletedItems
	
	// Method: getUncompletedItemsCount
	//
	//	evaluates uncompleted items  count
	//
	//
	// Returns:
	//
	//	integer
	//
	public function getUncompletedItemsCount($patient = NULL) {
		
		$u = freemed::user_cache();
		$q = "select count(*) as count from ".$this->table_name." mfc where mfc.status_completed=0 and mfc.user = ".$u->user_number.($patient?" and mfc.patient=".$GLOBALS['sql']->quote($patient):" ");
		$return = $GLOBALS['sql']->queryRow( $q );
		return $return['count'];
	} // end method getUncompletedItemsCount	
	
} // end class ModuleFieldChecker

register_module ("ModuleFieldChecker");

?>
