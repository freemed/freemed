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

class SystemNotifications extends SupportModule {

	var $MODULE_NAME = "System Notification";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "b26cb12a-39ad-47bf-bd2a-f2b7824c6145";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "System Notifications";
	var $table_name = 'systemnotification';
	var $order_field = 'stamp';

	public function __construct () {
		$this->acl = array ( 'bill', 'emr' );
		$this->_SetHandler( 'Dashboard', get_class($this) );

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
	//	Array of hashes of systemnotifications table with added
	//	'timestamp' field containing current timestamp.
	//
	public function GetFromTimestamp ( $timestamp ) {
		$this_user = freemed::user_cache( );
		$q = "SELECT *, SUBSTRING_INDEX(NOW() + 0, '.', 1) AS 'timestamp' FROM ".$this->table_name." WHERE nuser = ".$GLOBALS['sql']->quote( $this_user->user_number )." AND stamp >= ".$GLOBALS['sql']->quote( $timestamp );
		$res = $GLOBALS['sql']->queryAll( $q );
		return $res;
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
	//	$box - (optional) Organizational box text name.
	//
	// Returns:
	//
	//	Number of items in the organizational box for the current user.
	//
	public function GetSystemTaskInboxCount ( $box = NULL ) {
		$q = "SELECT count FROM systemtaskinboxsummary WHERE user = " . $GLOBALS['sql']->quote( freemed::user_cache()->user_number ).( $box != NULL ? " AND box = " . $GLOBALS['sql']->quote( $box ) : '' );
		return (int)( $GLOBALS['sql']->queryOne( $q ) );
	} // end method GetSystemTaskInboxCount

	// Method: GetSystemTaskPatientInbox
	//
	//	Get system task inbox items for a patient.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$box - (optional) Box to qualify results. Defaults to none.
	//
	// Returns:
	//
	//	Array of hashes
	//	* stamp
	//	* stamp_mdy
	//	* patient
	//	* box
	//	* module
	//	* module_name
	//	* oid
	//	* summary
	//	* id
	//
	public function GetSystemTaskPatientInbox ( $patient, $box ) {
		$q = "SELECT DATE_FORMAT(s.stamp, '%m/%d/%Y') AS stamp_mdy, s.stamp AS stamp, s.patient AS patient, s.box AS box, s.module AS module, m.module_name AS module_name, s.oid AS oid, s.summary AS summary, s.id AS id FROM systemtaskinbox s LEFT OUTER JOIN modules m ON s.module = m.module_class WHERE s.patient = " . $GLOBALS['sql']->quote( $patient ).( $box != NULL ? " AND s.box = " . $GLOBALS['sql']->quote( $box ) : '' );
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetSystemTaskPatientInbox

	// Method: GetSystemTaskUserInbox
	//
	//	Get system task inbox items for a user.
	//
	// Parameters:
	//
	//	$box - (optional) Box to qualify results. Defaults to none.
	//
	// Returns:
	//
	//	Array of hashes
	//	* stamp
	//	* stamp_mdy
	//	* patient
	//	* patient_name
	//	* box
	//	* module
	//	* module_name
	//	* oid
	//	* summary
	//	* id
	//
	public function GetSystemTaskUserInbox ( $box = NULL ) {
		$q = "SELECT DATE_FORMAT(s.stamp, '%m/%d/%Y') AS stamp_mdy, s.stamp AS stamp, s.patient AS patient, s.box AS box, s.module AS module, m.module_name AS module_name, CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname, ' (', p.ptid, ')' ) AS patient_name, s.oid AS oid, s.summary AS summary, s.id AS id FROM systemtaskinbox s LEFT OUTER JOIN modules m ON s.module = m.module_class LEFT OUTER JOIN patient p ON s.patient = p.id WHERE s.user = " . $GLOBALS['sql']->quote( freemed::user_cache()->user_number ).( $box != NULL ? " AND s.box = " . $GLOBALS['sql']->quote( $box ) : '' );
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetSystemTaskUserInbox

	// Method: GetSystemTaskPatientInboxCount
	//
	//	Get number of system task inbox items for a patient.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$box - Organizational box text name.
	//
	// Returns:
	//
	//	Number of items in the organizational box for the current user.
	//
	public function GetSystemTaskPatientInboxCount ( $patient, $box ) {
		$q = "SELECT count FROM systemtaskinboxpatientsummary WHERE patient = " . $GLOBALS['sql']->quote( $patient )." AND box = " . $GLOBALS['sql']->quote( $box );
		return (int)( $GLOBALS['sql']->queryOne( $q ) );
	} // end method GetSystemTaskPatientInboxCount

} // end class SystemNotifications

register_module ("SystemNotifications");

?>
