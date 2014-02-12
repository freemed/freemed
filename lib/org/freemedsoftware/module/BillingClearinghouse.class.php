<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

class BillingClearinghouse extends SupportModule {

	var $MODULE_NAME = "Billing Clearinghouse";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "93d2e4ee-3453-4410-ad66-e4d8e3d98aa3";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.1';

	var $widget_hash = "##chname## (##chcity##, ##chstate##)";

	var $table_name = "clearinghouse";

	var $variables = array (
	    'chname',
	    'chaddr',
	    'chcity',
	    'chstate',
	    'chzip',
	    'chphone',
	    'chetin',
	    'chx12gssender',
	    'chx12gsreceiver',
	    'user'
	    
	);

	public function __construct ( ) {

		// Call parent constructor
		parent::__construct();
	} // end constructor Codes

	protected function add_pre ( $data ) {
		unset($data['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	} // end method add_pre

	
}

register_module('BillingClearinghouse');

?>
