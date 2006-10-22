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

class PatientTag extends SupportModule {

	var $MODULE_NAME = "Patient Tag";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "1c34f308-1503-4478-9179-896248067fb4";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Patient Tag";
	var $table_name  = "patienttag";
	var $order_field = "datecreate,dateexpire";

	var $widget_hash = "##tag## (##datecreate## - ##dateexpire##)";

	var $variables = array (
		"tag",
		"patient",
		"datecreate",
		"dateexpire"
	);

	public function __construct ( ) {
		// __("Patient Tag")
	
		$this->list_view = array (
			__("Tag") => 'tag',
			__("Date Created") => 'datecreate',
			__("Date Expires") => 'dateexpire'
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$date ) {
		$date['datecreate'] = '';
	}

} // end class PatientTag

register_module ("PatientTag");

?>
