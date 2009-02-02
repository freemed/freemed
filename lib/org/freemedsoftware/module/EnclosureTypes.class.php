<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

class EnclosureTypes extends SupportModule {

	var $MODULE_NAME    = "Enclosure Types";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "e01829c2-4299-4b4d-9463-74aeb49def49";

	var $PACKAGE_MINIMUM_VERSION = '0.7.2';

	var $table_name     = "enctype";
	var $record_name    = "Enclosure Type";
	var $order_field    = "enclosure";
 
	var $variables      = array (
		"enclosure"
	); 

	public function __construct ( ) {
		// For i18n: __("Enclosure Types")

		$this->list_view = array (
			__("Enclosure") => 'enclosure'
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor EnclosureTypes

} // end class EnclosureTypes

register_module ("EnclosureTypes");

?>
