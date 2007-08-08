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

class SystemNotifications extends SupportModule {

	var $MODULE_NAME = "System Notification";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "b26cb12a-39ad-47bf-bd2a-f2b7824c6145";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "System Notifications";
	var $table_name = 'systemnotification';
	var $order_field = 'stamp';

	public function __construct () {
		$this->acl = array ( 'bill', 'emr' );

		// call parent constructor
		parent::__construct( );
	} // end constructor

	// Method: GetFromTimestamp
	//
	//	Get notifications since a particular time based on the
	//	timestamp passed.
	//
	// Parameters:
	//
	//	$timestamp - Timestamp in format of <GetTimestamp> method.
	//
	// Returns:
	//
	//	Hash containing:
	//	* timestamp: New timestamp, current.
	//	* count: Number of items found.
	//	* items: Notifications. Array of hashes.
	//
	public function GetFromTimestamp ( $timestamp ) {
		$this_user = freemed::user_cache( );
		$q = "SELECT * FROM ".$this->table_name." WHERE nuser = ".$GLOBALS['sql']->quote( $this_user->user_number )." AND stamp >= ".$GLOBALS['sql']->quote( $timestamp );
		$res = $GLOBALS['sql']->queryAll( $q );
		return array (
			'timestamp' => $this->GetTimestamp( ),
			'count' => count( $res ),
			'items' => $res
		);
	} // end method GetFromTimestamp

	// Method: GetTimestamp
	//
	//	Retrieve current timestamp from the SQL database.
	//
	// Returns:
	//
	//	Integer SQL timestamp, as a string so that no floating point
	//	conversion is performed.
	//
	public function GetTimestamp ( ) {
		$q = "SELECT ".$GLOBALS['sql']->now()." + 0";
		list ( $x, $y ) = explode( '.', $GLOBALS['sql']->queryOne( $q ));
		return $x;
	} // end method GetTimestamp

	// Method: GetSystemTaskInboxCount
	//
	//	Get number of system task inbox items.
	//
	// Parameters:
	//
	//	$box - Organizational box text name.
	//
	// Returns:
	//
	//	Number of items in the organizational box for the current user.
	//
	public function GetSystemTaskInboxCount ( $box ) {
		$q = "SELECT count FROM systemtaskinboxsummary WHERE user = " . $GLOBALS['sql']->quote( freemed::user_cache()->user_number )." AND box = " . $GLOBALS['sql']->quote( $box );
		return (int)( $GLOBALS['sql']->queryOne( $q ) );
	} // end method GetSystemTaskInboxCount

} // end class SystemNotifications

register_module ("SystemNotifications");

?>
