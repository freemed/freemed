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

class PatientStatus extends SupportModule {

	var $MODULE_NAME = "Patient Status";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "83021f95-1d5a-4ee2-9841-c3088c2307e0";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name 	= "Patient Status";
	var $table_name		= "ptstatus";

	var $variables 		= array (
		"ptstatus",
		"ptstatusdescrip"
	);

	var $widget_hash        = "##ptstatus## - ##ptstatusdescrip##";

	public function __construct ( ) {
		// __("Patient Status")

		$this->list_view = array (
			__("Status")		=>	"ptstatus",
			__("Description")	=>	"ptstatusdescrip"
		);

		// Run parent constructor
		parent::__construct ( );
	} // end constructor PatientStatus

} // end class PatientStatus

register_module ("PatientStatus");

?>
