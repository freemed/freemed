<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

class MultumDrugLexicon extends SupportModule {

	var $MODULE_NAME = "Multum Drug Lexicon";
	var $MODULE_VERSION = "20080201.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "9c28f0de-403b-4534-9627-d0156eeb721a";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "multum";
	var $widget_hash = '##brand_description## (##description##) ##form##';

	public function __construct () {
		// Call parent constructor
		parent::__construct();
	} // end constructor

	// Method: DosagesForDrug
	//
	//	Get list of dosages from a "multum" table list
	//
	// Parameters:
	//
	//	$drug_id - Drug id in the "multum" aggregation table
	//
	//	$drug_label - (optional) Textual drug name
	//
	// Returns:
	//
	//	Array of [ key, value ]
	//
	public function DosagesForDrug ( $drug_id, $drug_label = NULL ) {
		$q = "SELECT ps.product_strength_description AS strength, ps.product_strength_code AS id
			FROM multum m LEFT OUTER JOIN multum_product_strength ps ON FIND_IN_SET( ps.product_strength_code, m.dose_size_link )
			WHERE m.id = ".$GLOBALS['sql']->quote( $drug_id ).
			( $drug_label ? " AND CONCAT(brand_description, ' (', description,') ',form) = ".$GLOBALS['sql']->quote( $drug_label ) : "" ).
			" GROUP BY ps.product_strength_code";
		$r = $GLOBALS['sql']->queryAll( $q );
		foreach ( $r AS $row ) {
			$res[] = array ( $row['strength'], $row['id'] );
		}
		return $res;
	} // end method DosagesForDrug

	// Method: DrugDosageToText
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
	public function DrugDosageToText( $id ) {
		$q = "SELECT ps.product_strength_description FROM multum_product_strength ps WHERE ps.product_strength_code = " . $GLOBALS['sql']->quote( $id );
		return $GLOBALS['sql']->queryOne( $q );
	} // end method DrugDosageToText

} // end class MultumDrugLexicon

register_module("MultumDrugLexicon");

?>
