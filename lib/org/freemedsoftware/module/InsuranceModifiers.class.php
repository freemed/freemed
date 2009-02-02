<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

class InsuranceModifiers extends SupportModule {

	var $MODULE_NAME = "Insurance Modifiers";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "e685b8b7-e3f7-486d-ab05-133172dfbfa2";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "insmod";
	var $record_name = "Insurance Modifiers";
	var $order_field = "insmoddesc";
	var $widget_hash = "##insmod## - ##insmoddesc##";
 
	var $variables = array (
		"insmod",
		"insmoddesc"
	);
 
	public function __construct ( ) {
		// __("Insurance Modifiers")

		$this->list_view = array (
			__("Modifier") => "insmod",
			__("Description") => "insmoddesc"
		);

		// Run parent constructor
		parent::__construct ( );
	} // end constructor InsuranceModifiers

} // end class InsuranceModifiers

register_module("InsuranceModifiers");

?>
