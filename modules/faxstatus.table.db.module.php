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

class FaxStatus extends SupportModule {

	var $MODULE_NAME = "Fax Status";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "03b49f2f-c3ed-4e63-9d3c-c58d8f61cd88";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "faxstatus";

	public function __construct ( ) {
		// __("Fax Status")
		$this->table_definition = array (
			'fsid' => SQL__VARCHAR(16),
			'fsmodule' => SQL__VARCHAR(50),
			'fsrecord' => SQL__INT_UNSIGNED(0),
			'fsuser' => SQL__INT_UNSIGNED(0),
			'fspatient' => SQL__INT_UNSIGNED(0),
			'fsdestination' => SQL__VARCHAR(50),
			'fsstatus' => SQL__VARCHAR(250),
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		parent::__construct( );
	} // end constructor

} // end module FaxStatus

register_module('FaxStatus');

?>
