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

class Pharmacy extends SupportModule {

	var $MODULE_NAME    = "Pharmacy";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "47941b4e-cf68-431d-881a-79c5c63885e2";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Pharmacies";
	var $table_name     = "pharmacy";

	var $widget_hash    = "##phname## (##phcity##, ##phstate##)";

	var $variables = array (
		'phname',
		'phaddr1',
		'phaddr2',
		'phcity',
		'phstate',
		'phzip',
		'phfax',
		'phemail',
		'phncpdp',
		'phmethod'
	);

	public function __construct ( ) {
		// For i18n: __("Pharmacies")
		$this->list_view = array (
			__("Name") => "phname",
			__("City, State") => "citystate"
		);
		$this->additional_fields = array (
			"CONCAT(phcity, ', ', phstate) AS citystate"
		);

		parent::__construct();
	} // end constructor Pharmacy

	protected function add_pre ( &$data ) {
		// Split city, state zip if it's one field
		if ($data['phcsz']) {
			if (preg_match("/([^,]+), ([A-Z]{2}) (.*)/i", $data['phcsz'], $reg)) {
				$data['phcity'] = $reg[1];
				$data['phstate'] = $reg[2];
				$data['phzip'] = $reg[3];
			}
		}
	} // end method add_pre

	protected function mod_pre ( &$data ) {
		// Split city, state zip if it's one field
		if ($data['phcsz']) {
			if (preg_match("/([^,]+), ([A-Z]{2}) (.*)/i", $data['phcsz'], $reg)) {
				$data['phcity'] = $reg[1];
				$data['phstate'] = $reg[2];
				$data['phzip'] = $reg[3];
			}
		}
	} // end method mod_pre


	// Method: picklist
	//
	//	Generate associative array of Pharmacy table id to Pharmacy
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
	//	* value - Text representing Pharmacy record identifying info.
	//
	public function picklist ( $string, $_limit = 10, $inputlimit = 2 ) {
		$limit = ($_limit < 10) ? 10 : $_limit;
		if (strlen($string) < $inputlimit) {
			syslog(LOG_INFO, "under $inputlimit");
			return false;
		}

		$string = trim(addslashes( $string ));
		
		$query = "SELECT * FROM pharmacy WHERE phname LIKE '".addslashes($string)."%'".
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

} // end class Pharmacy

register_module ("Pharmacy");

?>
