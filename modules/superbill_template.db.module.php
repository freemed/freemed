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

class SuperbillTemplate extends SupportModule {

	var $MODULE_NAME = "Superbill Template";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "9a0a51dd-f900-42aa-ae8e-75569c52db65";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name 	 = 'superbill_template';
	var $record_name	 = "Superbill Template";
	var $order_field	 = "id";
	var $widget_hash	 = "##st_name##";

	var $variables = array (
		'st_name',
		'st_created',
		'st_user',
		'st_dx',
		'st_px'
	);

	public function __construct () {
		$this->list_view = array (
			__("Name")        => 'st_name'
		);

		parent::__construct( );
	} // end constructor

} // end class SuperbillTemplate

register_module ("SuperbillTemplate");

?>
