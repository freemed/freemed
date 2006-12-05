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

class ClaimLogTable extends SupportModule {

	var $MODULE_NAME = "Claim Log";
	var $MODULE_VERSION = "0.7.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "9a7ff944-8b61-4d8f-a58d-a8ddb92e124b";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "claimlog";

	public function __construct ( ) {
		// __("Claim Log")

		// Call parent constructor
		parent::__construct();
	} // end constructor

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );

		// Version 0.7.1
		//
		//	Add ability to track events by payment record (clpayrec)
		//
		if (!version_check($version, '0.7.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN clpayrec INT UNSIGNED AFTER clprocedure');
			// Set to 0 by default (not associated with any payrec)
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET clpayrec=\'0\'');
		}

		// Version 0.7.2
		//
		//	Force table creation, due to messed up older version
		//
		if (!version_check($version, '0.8.2')) {
			$this->create_table();
		}
	} // end method _update

} // end class ClaimLogTable

register_module('ClaimLogTable');

?>
