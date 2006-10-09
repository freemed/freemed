<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

class CallinTable extends SupportModule {

	var $MODULE_NAME = "Call-in";
	var $MODULE_VERSION = "0.7";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "5f4e5de0-58fa-495e-84a6-9c35a7f7e816";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "callin";

	public function __construct ( ) {
		// __("Call-in Table")

		// Call parent constructor
		parent::__construct();
	} // end constructor CallinTable

	// Use _update to update table definitions with new versions
	function _update () {
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.7
		//
		//	Add ciuser field
		//
		if (!version_check($version, '0.7')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ciuser INT UNSIGNED AFTER ciphysician');
		}
	} // end function _update
}

register_module('CallinTable');

?>
