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

class Practices extends SupportModule {

	var $MODULE_NAME    = "Practice";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "acfb0536-6884-4ffc-91b1-9503fc78d7a3";

	var $PACKAGE_MINIMUM_VERSION = '0.8.7';

	var $record_name    = "Practice";
	var $table_name     = "practice";
	var $archive_field = "pracarchive";
	
	var $variables      = array (
		"pracname",
		"ein",
		"addr1a",
		"addr2a",
		"citya",
		"statea",
		"zipa",
		"countrya",
		"phonea",
		"faxa",
		"addr1b",
		"addr2b",
		"cityb",
		"stateb",
		"zipb",
		"phoneb",
		"faxb",
		"countryb",
		"email",
		"cellular",
		"pager",
		"pracnpi",
	); // end of variables list
	var $order_field = 'pracname, citya, statea';

	var $widget_hash = '##pracname## (##citya##, ##statea##)';

	public function __construct () {
		// For i18n: __("Practice")

		$this->list_view = array (
			__("Practice Name") => "pracname",
			__("City") => "citya",
			__("State / Province") => "statea",
			__("NPI") => "pracnpi"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
	}

	protected function mod_pre ( &$data ) {
	}

} // end class Practices

register_module ("Practices");

?>
