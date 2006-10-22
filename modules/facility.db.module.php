<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

class FacilityModule extends SupportModule {

	var $MODULE_NAME    = "Facility";
	var $MODULE_VERSION = "0.3";
	var $MODULE_DESCRIPTION = "
		Facilities are used by FreeMED to describe locations where 
		services are performed. Any physician/provider can do work 
		at one or more of these facilities.
	";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "8acd5dcf-784f-4441-81a0-fa599c8f03ef";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Facility";
	var $table_name     = "facility";
	var $order_by       = "psrname";

	var $widget_hash = "##psrname## ##psrnote## (##psrcity##, ##psrstate##)";

	var $variables = array (
		"psrname",
		"psraddr1",
		"psraddr2",
		"psrcity",
		"psrstate",
		"psrzip",
		"psrcountry",
		"psrnote",
		"psrdefphy",
		"psrphone",
		"psrfax",
		"psremail",
		"psrein",
		"psrintext",
		"psrpos",
		'psrx12id',
		'psrx12idtype'
	);

	var $rpc_field_map = array (
		'name' => 'psrname',
		'address_1' => 'psraddr1',
		'address_2' => 'psraddr2',
		'city' => 'psrcity',
		'state' => 'psrstate',
		'zip' => 'psrzip',
			'zip_code' => 'psrzip',
		'country' => 'psrcountry',
		'note' => 'psrnote',
		'default_provider' => 'psrdefphy',
		'phone' => 'psrphone',
		'fax' => 'psrfax',
		'email' => 'psremail',
		'ein' => 'psrein',
		'internal' => 'psrintext',
		'place_of_service' => 'psrpos'
	);

	public function __construct () {
		// For i18n: __("Facility")

		$this->list_view = array (
			__("Name")         => "psrname",
			__("Description")  => "psrnote"
		);

		// Run constructor
		parent::__construct();
	} // end constructor

	//----- XML-RPC Methods
	function picklist () {
		global $sql;
		$result = $sql->query("SELECT * FROM ".$this->table_name." ".
			"ORDER BY ".$this->order_by);
		if (!$sql->results($result)) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', 'error', 'string')
			);
		}
		return rpc_generate_sql_hash(
			$this->table_name,
			array (
				"name" => 'psrname',
				"city" => 'psrcity',
				"state" => 'psrstate',
				"id"
			),
			"ORDER BY ".$this->order_by
		);
	} // end function FacilityModule->picklist

	function _update ( ) {
		global $sql;
 		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.3
		//
		//	Added X12 fields (id and idtype)
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN psrx12id VARCHAR(24) AFTER psrpos');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN psrx12idtype VARCHAR(10) AFTER psrx12id');
		}
	} // end method _update

} // end class FacilityModule

register_module ("FacilityModule");

?>
