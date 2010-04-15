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

class BillingContact extends SupportModule {

	var $MODULE_NAME = "Billing Contact";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "9418e256-e97c-4a72-8d93-0e9dd5b96121";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.1';

	var $widget_hash = "##bclname##, ##bcfname## ##bclname## (##bccity##, ##bcstate##)";

	var $table_name = "bcontact";

	var $variables = array (
	    'bcfname',
	    'bcmname',
	    'bclname',
	    'bcaddr',
	    'bccity',
	    'bcstate',
	    'bczip',
	    'bcphone',
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

register_module('BillingContact');

?>
