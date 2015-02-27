<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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

	var $MODULE_NAME    = "Provider";
	var $MODULE_VERSION = "0.3.6";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "d7eeac23-fa84-410a-af46-36a67b7813a1";
	
	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Provider";
	var $table_name     = "physician";
	var $archive_field = "phyarchive";
	var $variables      = array (
	        "phylname",
		"phyfname",
		"phytitle",
		"phypractice",
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
		"phydegrees",
		"physpecialties",
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
		"phyclia",
		"phynpi",
		"phyarchive"
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

	public function __construct () {
		// For i18n: __("Provider")

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

	// Method: internalPicklist
	//
	//	Internal provider picklist.
	//
	// Parameters:
	//
	//	$criteria - (optional) String to narrow search.
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function internalPicklist ( $criteria = NULL ) {
		if (!(strpos($this->widget_hash, "##") === false)) {
			$value = '';
			$hash_split = explode('##', $this->widget_hash);
			foreach ($hash_split AS $_k => $_v) {
				if ($_k & 1) {
					$c[] = "LOWER(". $_v .") LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%')";
				}
			}
		} else {
			$c[] = "LOWER(".$this->widget_hash.") LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%')";
		}

		$query = "SELECT * FROM ".$this->table_name.
			" ".$this->FormJoinClause()." ".
			( is_array($c) ? " WHERE  ( ".join(' OR ',$c)." ) " : "" ).
			( $this->order_field ? " ORDER BY ".$this->order_field : "" ).
			" LIMIT 20";
		//syslog(LOG_INFO, $query);
		$result = $GLOBALS['sql']->queryAll($query);
		if (!count($result)) { return array(); }
		foreach ($result AS $r) {
			$return[$r['id']] = trim( $this->to_text( $r ) );
		}
		return $return;
		
	} // end method picklist

	// Method: LookupNPI
	//
	//	Lookup NPI number in National Provider Registry.
	//
	// Parameters:
	//
	//	$first_name - Provider's first name
	//
	//	$last_name - Provider's last name
	//
	//	$state - Two letter state abbreviation
	//
	// Returns:
	//
	//	NPI number or boolean false if none has been found
	//
	public function LookupNPI ( $first_name, $last_name, $state ) {
		$url = "https://nppes.cms.hhs.gov/NPPES/NPIRegistrySearch.do?searchType=ind&lastName=".urlencode( $last_name )."&firstName=".urlencode( $first_name )."&state=".urlencode( strtoupper( $state ) );

		$ch = curl_init( );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$f = curl_exec( $ch );

		//print "length = ".strlen($f)."\n";

		if ( strpos( $f, 'No matching records found.' ) !== false ) {
			syslog( LOG_DEBUG, "LookupNPI: No matching records found for $first_name, $last_name, $state" );
			return false;
		}

		$hash = explode( "\n", $f );
		foreach ( $hash AS $k => $h ) {
			if (strpos($h, '>NPI:<')) {
				//print "Found 'NPI:'\n";
				$npi_at = $k + 1;
				break;
			}
		}

		if ( $npi_at ) {
			preg_match( '/>([^<]+)<\//i', $hash[$npi_at], $match );
			return $match[1];
		} else {
			return false;
		}
	} // end method LookupNPI

} // end class ProviderModule

register_module ("ProviderModule");

?>
