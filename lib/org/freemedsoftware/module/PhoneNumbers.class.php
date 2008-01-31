<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

class PhoneNumbers extends SupportModule {

	var $MODULE_NAME = "Phone Numbers";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "deec5f0b-d651-4e11-923f-15c1718d1e3f";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name = "phone";

	public function __construct () {
		// __("Phone Numbers")

		$this->variables = array (
			'phonelink',
			'phonetype',
			'phonecategory',
			'phonenumber',
			'phonestampadd',
			'phonestampmod'
		);

		// Call parent constructor
		parent::__construct();
	} // end constructor PhoneNumbers

	protected function mod_pre ( &$data ) {
		unset($data['phonestampadd']);
	}

	// Method: GetTypeNumber
	//
	//	Fetch the list of available number records.
	//
	// Parameters:
	//
	//	$type - Type of phone number. Usually is 'patient' or the name of a
	//	module with which it is associated.
	//
	//	$category - Category of number. Example would be 'work', 'home',
	//	'mobile', etc.
	//
	//	$link - Link to the record of type "$type".
	//
	// Returns:
	//
	//	List of records
	//
	public function GetTypeNumber ( $type, $category, $link ) {
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE phonetype='".addslashes($type)."' AND ".
			"phonecategory='".addslashes($category)."' AND ".
			"phonelink='".addslashes($link)."' ".
			"ORDER BY phonestampadd DESC";
		$result = $GLOBALS['sql']->queryAll( $query );
		return $result;
	} // end public function GetTypeNumber

	// Method: GetRecentNumber
	//
	//	Fetch the most recent number
	//
	// Parameters:
	//
	//	$type - Type of phone number. Usually is 'patient' or the name of a
	//	module with which it is associated.
	//
	//	$category - Category of number. Example would be 'work', 'home',
	//	'mobile', etc.
	//
	//	$link - Link to the record of type "$type".
	//
	// Returns:
	//
	//	Get most recent number
	//
	public function GetRecentNumber ( $type, $category, $link ) {
		$query = "SELECT phonenumber FROM ".$this->table_name." ".
			"WHERE phonetype='".addslashes($type)."' AND ".
			"phonecategory='".addslashes($category)."' AND ".
			"phonelink='".addslashes($link)."' ".
			"ORDER BY phonestampadd DESC";
		$result = $GLOBALS['sql']->queryOne( $query );
		return $result['phonenumber'];
	} // end public function GetRecentNumber

} // end class PhoneNumbers

register_module("PhoneNumbers");

?>
