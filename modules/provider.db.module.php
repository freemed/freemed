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

class ProviderModule extends SupportModule {

	var $MODULE_NAME    = "Provider Maintenance";
	var $MODULE_VERSION = "0.3.5";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "d7eeac23-fa84-410a-af46-36a67b7813a1";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Provider";
	var $table_name     = "physician";
	var $variables      = array (
	        "phylname",
		"phyfname",
		"phytitle",
		"phymname",
		"phypracname",
		"phypracein",
		"phyaddr1a",
		"phyaddr2a",
		"phycitya",
		"phystatea",
		"phyzipa",
		"phyphonea",
		"phyfaxa",
		"phyaddr1b",
		"phyaddr2b",
		"phycityb",
		"phystateb",
		"phyzipb",
		"phyphoneb",
		"phyfaxb",
		"phyemail",
		"phycellular",
		"phypager",
		"phyupin",
		"physsn",
		"phydeg1",
		"phydeg2",
		"phydeg3",
		"physpe1",
		"physpe2",
		"physpe3",
		"phyid1",
		"phystatus",
		"phyref",
		"phyrefcount",
		"phyrefamt",
		"phyrefcoll",
		"phychargemap",
		"phyidmap",
		"phyanesth",
		"phyhl7id",
		"phydea",
		"phyclia"
	); // end of variables list
	var $order_field = 'phylname, phyfname';

	// XML-RPC field mapping
	var $rpc_field_map = array (
		'last_name' => 'phylname',
		'first_name' => 'phyfname',
		'middle_name' => 'phymname',
		'city' => 'phycitya',
		'state' => 'phystatea',
		'zip' => 'phyzipa',
		'practice' => 'phypracname'
	);
	var $widget_hash = '##phylname##, ##phyfname## ##phymname## (##phypracname##)';

	public function __contruct () {
		// For i18n: __("Provider Maintenance")

		$this->list_view = array (
			__("Last Name") => "phylname",
			__("First Name") => "phyfname"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$data['phychargemap'] = serialize( $data['phychargemap'] );
		$data['phyidmap'] = serialize( $data['phyidmap'] );
	}

	protected function mod_pre ( &$data ) {
		$data['phychargemap'] = serialize( $data['phychargemap'] );
		$data['phyidmap'] = serialize( $data['phyidmap'] );
	}

	// Method: fullName
	//
	//	Get full name for specified provider.
	//
	// Parameters:
	//
	//	$id - Record id for specified provider.
	//
	//	$full - (optional) Boolean, whether to output full name with all
	//	qualifications. Defaults to false.
	//
	// Returns:
	//
	//	Full name of provider.
	//
	public function fullName ( $id, $full=false ) {
		$phy = CreateObject('org.freemedsoftware.core.Physician', $id);
		return $phy->fullName( $full );
	} // end method fullName

	function _update ( ) {
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.3
		//
		//	Add hl7 id field
		//
		if (!version_check($version, '0.3')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phyhl7id INT UNSIGNED AFTER phyanesth');
		}

		// Version 0.3.1
		//
		//	Add DEA number for drugs
		//	Change practice name to max 45 characters
		//
		if (!version_check($version, '0.3.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phydea VARCHAR(16) AFTER phyhl7id');
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN phypracname phypracname VARCHAR(45)');
		}

		// Version 0.3.2
		//
		//	Add practice EIN number
		//
		if (!version_check($version, '0.3.2')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phypracein VARCHAR(16) AFTER phypracname');
		}

		// Version 0.3.3
		//
		//	HL7 ID needs to be alpha
		//
		if (!version_check($version, '0.3.3')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN phyhl7id phyhl7id VARCHAR(16)');
		}

		// Version 0.3.4
		//
		//	Extend practice name to 80 chars
		//
		if (!version_check($version, '0.3.4')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN phypracname phypracname VARCHAR(80)');
		}

		// Version 0.3.5
		//
		//	Add CLIA number
		//
		if (!version_check($version, '0.3.5')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phyclia VARCHAR(32) AFTER phydea');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' SET '.
				'phyclia=\'\' WHERE id>0');
		}

	} // end method _update

} // end class ProviderModule

register_module ("ProviderModule");

?>
