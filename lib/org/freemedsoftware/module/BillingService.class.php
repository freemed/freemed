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

class BillingService extends SupportModule {

	var $MODULE_NAME = "Billing Service";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "0ee1be79-33f8-46ca-8415-1d8d218a0e2d";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.1';

	var $widget_hash = "##bsname## (##bscity##, ##bsstate##)";

	var $table_name = "bservice";

	var $variables = array (
	    'bsname',
	    'bsaddr',
	    'bscity',
	    'bsstate',
	    'bszip',
	    'bsphone',
	    'bsetin',
	    'bstin',
	    'user'
	    
	);

	public function __construct ( ) {

		// Call parent constructor
		parent::__construct();
	} // end constructor Codes

	protected function add_pre ( &$data ) {
		unset($data['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	} // end method add_pre

	
}

register_module('BillingService');

?>
