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

class WorkflowStatusType extends SupportModule {

	var $MODULE_NAME = "Workflow Status Type";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "dc2cd1a1-3272-46d4-ab6e-7d7d2e5714a4";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Workflow Status Type";
	var $table_name  = "workflow_status_type";
	var $order_field = "status_order, status_name";

	var $widget_hash = "##status_name## - ##status_module##";

	var $variables = array (
		"status_name",
		"status_order",
		"status_module",
		"active"
	);

	public function __construct ( ) {
		// __("Workflow Status Type")
	
		$this->list_view = array (
			__("Name") => "status_name",
			__("Order") => "status_order",
			__("Module") => "status_module"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

} // end class WorkflowStatusType

register_module ("WorkflowStatusType");

?>
