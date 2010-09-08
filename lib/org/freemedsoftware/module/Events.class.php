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

class Events extends SupportModule {

	var $MODULE_NAME = "Events";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "450c4cb4-55a8-4090-a9d0-74432acd1ca7";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name  = "events";
	var $order_field = "event_time";

	var $variables = array (
		'user'
		,'event_type'
		,'source_id'
		,'event_action'
		,'event_note'
	);

	public function __construct ( ) {
		// __("Events")

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		unset($date['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		unset($date['stamp']);
		$data['user'] = freemed::user_cache()->user_number;
	}
	
	public function GetEvents($event_type,$source_id){
		$q = "SELECT ev.*,CONCAT( u.userlname, ', ', u.userfname, ' ', u.usermname ) as user_name FROM ".$this->table_name." ev LEFT JOIN user u ON u.id=ev.user WHERE event_type=".$GLOBALS['sql']->quote($event_type)." AND source_id=".$GLOBALS['sql']->quote($source_id);
		return $GLOBALS['sql']->queryAll($q);
	}
	
} // end class Events

register_module ("Events");

?>
