<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

class ShimStation extends SupportModule {

	var $MODULE_NAME = "Shim Station";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "49d646d4-d272-4a48-891a-168847ce7457";
	
	var $PACKAGE_MINIMUM_VERSION = '0.8.6';

	var $table_name = "shimstation";

	var $acl_category = 'emr';
	
	var $variables = array (
		  'name'
		, 'location'
		, 'facility'
		, 'username'
		, 'password'
		, 'url'
		, 'ip'
		, 'dosing_enabled'
		, 'label_enabled'
		, 'signature_enabled'
		, 'vitals_enabled'
		, 'dosing_last_close'
		, 'dosing_open'
		, 'dosing_bottle'
		, 'dosing_lot'
	);	

	public function __construct ( ) {
		// DEBUG TESTING: $this->defeat_acl = true;
		$this->table_join=array('facility' => 'facility');
		$this->additional_fields= array (
			"psrname AS facility"
		);
		// Call parent constructor
		parent::__construct ( );
	} // end constructor PatientModule

	protected function add_pre ( &$data ) {
	} // end method add_pre

        public function GetAll ( ) {
        	$today = date('Y-m-d');
		$q = "SELECT id AS Id, location AS ds_location, name as D_name from shimstation WHERE facility=".((int) HTTP_Session2::get('facility_id'))." AND dsopen='closed' AND dosing_last_close != '".$today."' AND ip='".$_SERVER['REMOTE_ADDR']."';";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll  
	
        public function getStationsByType($type) {
		switch ($type) {
			case 'dosing':
			case 'label':
			case 'signature':
			case 'vitals':
				$c = $type . "_enabled = 1";
				break;

			default:
				return NULL;
				break;
		}
		$q = "SELECT id AS Id, location AS ds_location, name as D_name from shimstation WHERE $c AND facility=".((int)HTTP_Session2::get('facility_id'))." AND ip='".$_SERVER['REMOTE_ADDR']."';";
		syslog(LOG_INFO, $q);
		return $GLOBALS['sql']->queryAll( $q );
	}
	
	public function dateDiff($startDate, $endDate)
	{
	    // Parse dates for conversion
	    $startArry = date_parse($startDate);
	    $endArry = date_parse($endDate);
	
	    // Convert dates to Julian Days
	    $start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
	    $end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);
	
	    // Return difference
	    return round(($end_date - $start_date), 0);
	} 

} // end class ShimStation

register_module('ShimStation');

?>
