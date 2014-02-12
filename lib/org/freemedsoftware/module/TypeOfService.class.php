<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //     Adam Buchbinder <grendelkhan@gmail.com>
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

class TypeOfService extends SupportModule {

	var $MODULE_NAME = "Type of Service";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "cfe3da34-bea9-414a-8491-4d4c543eed00";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Type of Service";
	var $table_name  = "tos";
	var $order_field = "tosname,tosdescrip";

	var $widget_hash = "##tosname## - ##tosdescrip##";

	var $variables = array (
		"tosname",
		"tosdescrip",
		"tosdtadd",
		"tosdtmod"
	);

	public function __construct ( ) {
		// __("Type of Service")
	
		$this->list_view = array (
			__("Code") => "tosname",
			__("Description") => "tosdescrip"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( $data ) {
		$data['tosdtadd'] = date('Y-m-d');
	}

	protected function mod_pre ( $data ) {
		$data['tosdtmod'] = date('Y-m-d');
	}

} // end class TypeOfService

register_module ("TypeOfService");

?>
