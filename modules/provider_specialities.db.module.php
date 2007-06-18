<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class ProviderSpecialties extends SupportModule {

	var $MODULE_NAME    = "Provider Specialties";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "db74f325-0dfa-4e7f-8c7e-82f5a72f7f57";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name     = "specialties";
	var $record_name    = "Specialty";
	var $order_field    = "specname";

	var $variables      = array (
		"specname",
		"specdesc"
	);

	public function __construct () {
		// For i18n: __("Provider Specialties")

		$this->list_view = array (
			__("Specialty") 		=> 	"specname",
			__("Specialty Description") 	=> 	"specdesc"
		);

		// Call parent constructor
		parent::__construct();
	} // end constructor 

	function form () { $this->view(); }

} // end class ProviderSpecialties

register_module ("ProviderSpecialties");

?>
