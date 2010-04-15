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

class FacilityModule extends SupportModule {

	var $MODULE_NAME    = "Facility";
	var $MODULE_VERSION = "0.3";
	var $MODULE_DESCRIPTION = "Facilities are used by FreeMED to describe locations where services are performed. Any physician/provider can do work at one or more of these facilities.";
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
		"psrnpi",
		"psrtaxonomy",
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

	protected function add_pre( &$data ) {
		if ( $data['psrcsz'] ) {
			list( $data['psrcity'], $data['psrstate'], $data['psrzip'] ) = $this->SplitCSZ( $data['psrcsz'] );
		}
	}

	protected function mod_pre( &$data ) {
		if ( $data['psrcsz'] ) {
			list( $data['psrcity'], $data['psrstate'], $data['psrzip'] ) = $this->SplitCSZ( $data['psrcsz'] );
		}
	}

	// Method: GetAll
	//
	// Returns:
	//
	//	Array of hashes.
	//	* id
	//	* psrname
	//
	public function GetAll ( ) {
		$q = "SELECT id, psrname FROM ".$this->table_name;
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAll


        public function GetDefaultFacility(){
        	if(HTTP_Session2::get( 'facility_id' )){
        		$defaultDFacility['id']=HTTP_Session2::get( 'facility_id' )."";
        		$defaultDFacility['facility']=$this->get_field(HTTP_Session2::get( 'facility_id'),'psrname');
        		return $defaultDFacility;
        	}	
        	
        }
        
	// Method: picklist
	//
	//	Generate associative array of facility table id to facility
	//	text based on criteria given.
	//
	// Parameters:
	//
	//	$string - String containing text parameters.
	//
	//	$limit - (optional) Limit number of results. Defaults to 10.
	//
	//	$inputlimit - (optional) Lower limit number of digits which
	//	have to be entered in order for this routine to return a
	//	valid value. Defaults to 2.
	//
	// Returns:
	//
	//	Associative array.
	//	* key - Facility table id key
	//	* value - Text representing Facility record identifying info.
	//
	public function picklist ( $string, $_limit = 10, $inputlimit = 2 ) {
		$limit = ($_limit < 10) ? 10 : $_limit;
		if (strlen($string) < $inputlimit) {
			syslog(LOG_INFO, "under $inputlimit");
			return false;
		}

		$string = trim(addslashes( $string ));
		
		$query = "SELECT * FROM facility WHERE psrname LIKE '".addslashes($string)."%'".
			" LIMIT $limit";
			
		syslog(LOG_INFO, "PICK| $query");
		$result = $GLOBALS['sql']->queryAll( $query );
		if (count($result) < 1) { return array (); }
		$count = 0;
		foreach ($result AS $r) {
			$return[(int)$r['id']] = trim($this->to_text($r));
		}
		syslog(LOG_INFO, "picklist| found ".count($return)." results returned");
		return $return;
	} // end public function picklist

} // end class FacilityModule

register_module ("FacilityModule");

?>
