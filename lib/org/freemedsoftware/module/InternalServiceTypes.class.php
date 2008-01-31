<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

class InternalServiceTypes extends SupportModule {

	var $MODULE_NAME    = "Internal Service Types";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "531424f9-37a5-48d2-b7b9-4f271325d67b";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "intservtype";
	var $record_name    = "Internal Service Type";
	var $order_field    = "intservtype";
	var $widget_hash    = "##intservtype##";
 
	var $variables      = array (
		"intservtype"
	); 

	public function __construct ( ) {
		// For i18n: __("Internal Service Types")

		$this->list_view = array (
			__("Types")	=>	"intservtype"
		);

		// Run parent constructor
		parent::__construct ( );
	} // end constructor InternalServiceTypes

} // end class InternalServiceTypes

register_module ("InternalServiceTypes");

?>
