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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class UserGroups extends SupportModule {

	var $MODULE_NAME    = "User Groups";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "86dcfdc8-4bb9-4c7c-972c-b60f1d1bb347";

	var $PACKAGE_MINIMUM_VERSION = '0.7.1';

	var $table_name     = "usergroup";
	var $record_name    = "User Group";
	var $order_field    = "usergroupname";

	var $widget_hash    = '##usergroupname##';

	var $variables = array (
		"usergroupname",
		"usergroupfac",
		"usergroupdtadd",
		"usergroupdtmod",
		"usergroup"
	);

	public function __construct ( ) {
		// __("User Group")
		// __("User Groups")
		$this->table_definition = array (
			'usergroupname' => SQL__VARCHAR(100),
			'usergroupfac' => SQL__INT_UNSIGNED(0),
			'usergroupdtadd' => SQL__DATE,
			'usergroupdtmod' => SQL__DATE,
			'usergroup' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		$this->list_view = array (
			__("User Group Name") => "usergroupname",
			__("Facility")     => "usergroupfac"
		);

		// Run constructor
		parent::__construct();
	} // end constructor UserGroups

	protected function add_pre ( &$data ) {
		$data['usergroupdtadd'] = date('Y-m-d');
		$data['usergroupdtmod'] = date('Y-m-d');
	}

	protected function mod_pre ( &$data ) {
		$data['usergroupdtmod'] = date('Y-m-d');
	}

} // end class UserGroups

register_module ("UserGroups");

?>
