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

class PharmacyMaintenance extends SupportModule {

	var $MODULE_NAME    = "Pharmacy Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "47941b4e-cf68-431d-881a-79c5c63885e2";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Pharmacies";
	var $table_name     = "pharmacy";

	var $widget_hash    = "##phname## (##phcity##, ##phstpr##)";

	var $variables = array (
		'phname',
		'phaddr1',
		'phaddr2',
		'phcity',
		'phstpr',
		'phzip',
		'phfax',
		'phmethod'
	);

	function PharmacyMaintenance () {
		// For i18n: __("Pharmacies")
		// TODO: add ability to have additional query pieces ...
		$this->list_view = array (
			__("Name") => "phname",
			__("City, State") => "citystate"
		);

		parent::__construct();
	} // end constructor Pharmacies

} // end class PharmacyMaintenance

register_module ("PharmacyMaintenance");

?>
