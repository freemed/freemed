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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class Notifications extends EMRModule {

	var $MODULE_NAME = "Notification";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "a49608f4-6383-43d2-9910-7be07ad36d96";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Notifications";
	var $table_name = 'notification';
	var $patient_field = 'npatient';
	var $order_field = 'ntarget';

	public function __construct () {
		$this->summary_vars = array (
			__("Date") => 'ntarget',
			__("User") => 'nfor:user'
		);
		$this->summary_options = SUMMARY_DELETE;

		$this->acl = array ( 'bill', 'emr' );

		// Set up a tickler, so we can send messages to the user
		$this->_SetHandler('Tickler', 'notify_user');

		// call parent constructor
		parent::__construct( );
	} // end constructor AllergiesModule

	protected function add_pre ( &$data ) {
		$data['noriginal'] = date('Y-m-d');
		$data['nuser'] = $GLOBALS['this_user']->user_number;
		$data['nuser'] = freemed::user_cache()->user_number;
	}

	public function notify_user ( $params = NULL ) {
		// Only do this once a day
		$date = ( $params['date'] ? $params['date'] : date('Y-m-d') );
		if ($params['interval'] == 'daily') {
			$query = "SELECT * FROM ".$this->table_name." ".
				"WHERE ntarget='".addslashes($date)."'";
			$res = $GLOBALS['sql']->queryAll($query);
			if (!count($res)) {
				return "Notifications: nothing to do";
			}
			$m = CreateObject('org.freemedsoftware.api.Messages');
			$count = 0;
			foreach ( $res AS $r ) {
				$count += 1;
				$m->send(array(
					'system' => true, // system message
					'user' => $r['nfor'],
					'patient' => $r['npatient'],
					'subject' => __("Notification"),
					'text' => $r['ndescrip'],
					'urgency' => 4
				));
			}
			return "Notifications: sent $count notifications";
		} // end checking for appropriate interval
		return "Notifications: nothing to do";
	} // end method notify_user

} // end class Notifications

register_module ("Notifications");

?>
