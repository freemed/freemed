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

class NDCLexicon extends SupportModule {

	var $MODULE_NAME = "FDA National Drug Code Lexicon";
	var $MODULE_VERSION = "20090331.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "2a25dbcb-dd64-4df0-8856-c660ed0adf6d";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "ndc";

	public function __construct () {
		// Call parent constructor
		parent::__construct();
	} // end constructor

	// Method: TradenamePicklist
	//
	//	Basi
	//
	// Parameters:
	//
	//	$criteria - (optional) String to narrow search.
	//
	// Returns:
	//
	//	Array of hashes
	//
	function TradenamePicklist ( $criteria = NULL, $limit = 20 ) {
		$c[] = "LOWER(tradename) LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%')";
		$query = "SELECT * FROM ndc_name_lookup ".
			( is_array($c) ? " WHERE ".join(' OR ',$c) : "" ).
			" ORDER BY tradename ".
			" LIMIT ".( (int) $limit );
		//syslog(LOG_INFO, $query);
		$result = $GLOBALS['sql']->queryAll($query);
		if (!count($result)) { return array(); }
		foreach ($result AS $r) {
			$return[$r['id']] = trim( $r['tradename'] );
		}
		return $return;
	} // end method TradenamePicklist

	// Method: DosagesForDrug
	//
	//	Get list of dosages from a "multum" table list
	//
	// Parameters:
	//
	//	$drug_id - Drug id in the "ndc_name_lookup" aggregation table
	//
	//	$text (optional) - Criteria to narrow it down.
	//
	// Returns:
	//
	//	Array of [ key, value ]
	//
	public function DosagesForDrug ( $drug_id, $drug_label = NULL ) {
		// For time saving in db, look up textual name
		$tradename = $this->NameLookupToText( $drug_id );	

		$q = "SELECT CONCAT( l.strength, l.unit, ' (', p.packsize, ' ', p.packtype, ')' ) AS drug_strength, l.id FROM ndc_listings l ".
			" LEFT OUTER JOIN ndc_packages p ON l.id = p.listing_seq_no ".
			" WHERE l.tradename = ".$GLOBALS['sql']->quote( $tradename )." ".
			( $text ? " HAVING drug_strength LIKE '%".$GLOBALS['sql']->escape( $text )."%'" : "" );
		$r = $GLOBALS['sql']->queryAll( $q );
		foreach ( $r AS $row ) {
			$res[] = array ( $row['drug_strength'], $row['id'] );
		}
		return $res;
	} // end method DosagesForDrug

	// Method: NameLookupToText
	//
	//	Lookup textual name from arbitrary name lookup id.
	//
	// Parameters:
	//
	//	$id - ndc_name_lookup id code
	//
	// Returns:
	//
	//	Textual description of drug.
	//
	public function NameLookupToText( $id ) {
		$q = "SELECT tradename FROM ndc_name_lookup WHERE id = " . $GLOBALS['sql']->quote( $id );
		return $GLOBALS['sql']->queryOne( $q );
	} // end method NameLookupToText

	// Method: DrugStrengthToText
	//
	//	Get textual name of drug dosage from id
	//
	// Parameters:
	//
	//	$id - Drug dosage id code
	//
	// Returns:
	//
	//	Textual description of drug dosage.
	//
	public function DrugStrengthToText( $id ) {
		$q = "SELECT CONCAT(strength, unit) FROM ndc_listings WHERE id = " . $GLOBALS['sql']->quote( $id );
		return $GLOBALS['sql']->queryOne( $q );
	} // end method DrugStrengthToText

} // end class NDCLexicon

register_module("NDCLexicon");

?>
