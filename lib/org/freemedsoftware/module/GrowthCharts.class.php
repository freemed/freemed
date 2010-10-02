<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

class GrowthCharts extends SupportModule {
	var $MODULE_NAME    = "Growth Charts";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;
	var $MODULE_UID = "cac3fa90-7f3a-4a6d-a854-fc414f794961";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Growth Charts";
	var $table_name     = "growthcharts";

	public function __construct () {
		// For i18n: __("Growth Charts")
		parent::__construct();
	} // end constructor

} // end class GrowthCharts

register_module ("GrowthCharts");

?>
