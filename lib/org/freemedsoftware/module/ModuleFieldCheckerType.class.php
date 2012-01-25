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

class ModuleFieldCheckerType extends SupportModule {

	var $MODULE_NAME = "Module Field Checker Type";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "fd3ae979-33d4-4a16-b54d-cc8760afe922";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Module Field Checker Type";
	var $table_name  = "module_field_checker_type";
	
	var $widget_hash = "##name## (##module##)";

	var $variables = array (
		"name",
		"module",
		"fields",
		"active"
	);

	public function __construct ( ) {
	
		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		if (!ereg("^[[:alpha:]]+$", $data['module'] )) {
			$data['module'] = '';
		}
	}

	protected function mod_pre ( &$data ) {
		if (!ereg("^[[:alpha:]]+$", $data['module'] )) {
			$data['module'] = '';
		}
	}
	
	public function getModuleInfo($module){
 		$query = "SELECT * FROM ".$this->table_name." WHERE module='".$module."'";
		$result = $GLOBALS['sql']->queryRow( $query );
		return $result;
	}

} // end class ModuleFieldCheckerType

register_module ("ModuleFieldCheckerType");

?>
