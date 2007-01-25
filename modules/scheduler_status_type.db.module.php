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

class SchedulerStatusType extends SupportModule {

	var $MODULE_NAME    = "Scheduler Status Type";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "e80e6d88-ebc1-4ccc-a432-8a9ee69404d1";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Scheduler Status Type";
	var $table_name     = "schedulerstatustype";

	var $widget_hash    = "##sname## (##sdescrip##)";

	var $variables = array (
		'sname',
		'sdescrip',
		'scolor',
		'sage'
	);

	public function __construct ( ) {
		// For i18n: __("Scheduler Status")

		$this->rpc_field_map = array (
			'name' => 'sname',
			'description' => 'sdescrip',	
			'color' => 'scolor',
			'age' => 'sage'
		);

		$this->list_view = array (
			__("Name") => 'sname',
			__("Description") => 'sdescrip'
		);

			// Run constructor
		parent::__construct();
	} // end constructor

} // end class SchedulerStatusType

register_module ("SchedulerStatusType");

?>
