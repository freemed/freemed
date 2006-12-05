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

class ProviderStatus extends SupportModule {

	var $MODULE_NAME    = "Provider Status";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "4ef660c8-89a0-4f0d-bbb4-67072139168c";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Provider Status";
	var $table_name     = "phystatus";

	var $variables = array ( "phystatus" );

	public function __construct ( ) {
		// For i18n: __("Provider Status")

		$this->list_view = array (
			__("Status") => "phystatus" 
		);

		// Call parent constructor
		parent::__construct();
	} // end contructor

} // end class ProviderStatus

register_module ("ProviderStatus");

?>
