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

class Zipcodes extends SupportModule {

	var $MODULE_NAME = "Zipcodes";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "28c632de-b52a-491b-84d1-fb53898ca76f";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.2';

	var $table_name = "zipcodes";

	public function __construct () {
		// __("Zipcodes")

		// Call parent constructor
		parent::__construct();
	} // end constructor Zipcodes

	// Method: CalculateDistance
	//
	//	Calculate distance between two zipcodes. Many thanks to
	//	http://jan.ucc.nau.edu/~cvm/latlon_formula.html from whence I
	//	napped the magic formula.
	//
	// Parameters:
	//
	//	$zipa - Source zipcode
	//
	//	$zipb - Destination zipcode
	//
	// Returns:
	//
	//	Distance in statute miles between the two zipcodes.
	//
	public function CalculateDistance ( $zipa, $zipb ) {
		$r = 3963.1; // 3963.1 statute miles, 6378 km

		$arec = $GLOBALS['sql']->get_link ( $this->table_name, $zipa, 'zip' );
		$brec = $GLOBALS['sql']->get_link ( $this->table_name, $zipb, 'zip' );

		$c = M_PI / 180;

		$a[1] = abs ( $arec['latitude'] * $c );
		$b[1] = abs ( $arec['longitude'] * $c );
		$a[2] = abs ( $brec['latitude'] * $c );
		$b[2] = abs ( $brec['longitude'] * $c );

		return acos( 
			( cos($a[1]) * cos($b[1]) * cos($a[2]) * cos($b[2]) ) + 
			( cos($a[1]) * sin($b[1]) * cos($a[2]) * sin($b[2]) ) + 
			( sin($a[1]) * sin($a[2]) ) 
		) * $r;
	} // end function CalculateDistance

	// Method: CityStateZipPicklist
	//
	//	Give picklist based on criteria given
	//
	// Parameters:
	//
	//	$param - Textual query
	//
	// Returns:
	//
	//	Array of results containing fully formed "C, S Z Country" field.
	//
	public function CityStateZipPicklist ( $param ) {
		// If two letters then a space, st city
		if ( strlen($param) >= 4 and substr($param, 2, 1) == ' ' ) {
			$where = "state = UPPER(".$GLOBALS['sql']->quote(substr($param, 0, 2)).") AND city LIKE '%".addslashes(substr($param, -(strlen($param)-3)))."%'";
		} elseif ( strlen($param) >= 4 and substr($param, strlen($param)-3, 1) == ' ' ) {
			// Handle city st or city, st
			$where = "state = UPPER(".$GLOBALS['sql']->quote(substr($param, -2)).") AND city LIKE '%".addslashes(str_replace(',', '', substr($param, 0, strlen($param)-3)))."%'";
		} elseif ( (strlen($param) >= 3) and ($param+0) == 0 ) {
			$where = "city LIKE '%".addslashes($param)."%'";
		}

		// Handle zip code entry
		if ( ($param + 0) != 0 and empty($where) ) {
			if (strlen($param) < 3) { return array(); }
			if (strlen($param) < 5) {
				$where = "zip LIKE '".addslashes($param)."%'";
			} else {
				$where = "zip=".$GLOBALS['sql']->quote( $param );
			}
		}

		// Ignore blanks
		if ( $where == '' ) { return array(); }

		$query = "SELECT CONCAT(city, ', ', state, ' ', zip, ' ', country) AS v FROM ".$this->table_name." WHERE ${where} LIMIT 20";

		$a = $GLOBALS['sql']->queryCol( $query );
		foreach ($a AS $r) {
			$return[$r] = $r;
		}
		return $return;
	} // end function CityStateZipPicklist

} // end class Zipcodes

register_module("Zipcodes");

?>
