<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.api.ModuleInterface
//
//	"Factory" type of interface to module functions to greatly
//	simplify RPC.
//
class ModuleInterface {

	public function __construct ( ) { }

	// Method: ModuleAddMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$data - Associative array of data to be added.
	//
	// Returns:
	//
	//	New id created.
	//
	public function ModuleAddMethod ( $module, $data ) {
		return module_function( $module, 'add', array ( $data ) );		
	} // end method ModuleAddMethod

	// Method: ModuleDeleteMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$id - Id to be removed
	//
	public function ModuleDeleteMethod ( $module, $id ) {
		return module_function( $module, 'del', array ( $id ) );
	} // end method ModuleDeleteMethod

	// Method: ModuleGetRecordMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$id - Id to be retrieved
	//
	// Returns:
	//
	//	Associative array of values.
	//
	public function ModuleGetRecordMethod ( $module, $id ) {
		return module_function( $module, 'GetRecord', array ( $id ) );
	} // end method ModuleGetRecordMethod

	// Method: ModuleGetRecordsMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$count - Maximum count
	//
	//	$ckey - Criteria key
	//
	//	$cval - Criteria value
	//
	// Returns:
	//
	//	Array of associative array of values.
	//
	public function ModuleGetRecordsMethod ( $module, $count, $ckey, $cval ) {
		return module_function( $module, 'GetRecords', array ( $count, $ckey, $cval ) );
	} // end method ModuleGetRecordsMethod

	// Method: ModuleModifyMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$data - Associative array of data to be modified.
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function ModuleModifyMethod ( $module, $data ) {
		return module_function( $module, 'mod', array ( $data ) );
	} // end method ModuleModifyMethod

	// Method: ModuleSupportPicklistMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$criteria - Search text
	//
	// Returns:
	//
	//	Associative array of values. Key = id, value = display name
	//
	public function ModuleSupportPicklistMethod ( $module, $criteria ) {
		syslog(LOG_INFO, module_function( $module, 'picklist', array ( $criteria ) ) );
		return module_function( $module, 'picklist', array ( $criteria ) );
	} // end method ModuleSupportPicklistMethod
	
	// Method: EMRSupportPicklistMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$patient - patient id
	//
	//	$criteria - Search text
	//
	// Returns:
	//
	//	Associative array of values. Key = id, value = display name
	//
	public function EMRSupportPicklistMethod ( $module, $patient, $criteria ) {
		syslog(LOG_INFO, module_function( $module, 'picklist', array ( $patient, $criteria ) ) );
		return module_function( $module, 'picklist', array ( $patient,$criteria ) );
	} // end method ModuleSupportPicklistMethod


	// Method: ModuleToTextMethod
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$id - Id to be retrieved
	//
	// Returns:
	//
	//	String
	//
	public function ModuleToTextMethod ( $module, $id ) {
		return module_function( $module, 'to_text', array ( $id ) );
	} // end method ModuleToTextMethod

	// Method: PrintToFax
	//
	// Parameters:
	//
	//	$faxnumber - Destination number
	//
	//	$items - Array of items
	//
	// Return:
	//
	//	Boolean, success
	//
	public function PrintToFax( $faxnumber, $items ) {
		foreach ($items AS $i) {
			$k[] = (int) $i;
		}
		$q = "SELECT * FROM patient_emr WHERE id IN ( ".join(',', $k)." )";
		$r = $GLOBALS['sql']->queryAll( $q );

		// Handle differently depending on single or multiple
		if (count($items) < 2) {
			// Single render
			$render = module_function( $r[0]['module'], 'RenderToPDF', array( $r[0]['oid'] ) );
		} else {
			// Multiples, use composite object
			$c = CreateObject( 'org.freemedsoftware.core.MultiplePDF' );
			foreach ($r AS $o) {
				$thisFile = module_function( $o['module'], 'RenderToPDF', array( $o['oid'] ) );
				$comp->Add( $thisFile );
				$f[] = $thisFile;
			}
			$render = $comp->Composite();
		}

		$wrapper = CreateObject( 'org.freemedsoftware.core.Fax', $render, array(
			'sender' => freemed::user_cache()->user_descrip,
			'comments' => __("HIPPA Compliance Notice: This transmission contains confidential medical information which is protected by the patient/physician privilege. The enclosed message is being communicated to the intended recipient for the purposes of facilitating healthcare. If you have received this transmission in error, please notify the sender immediately, return the fax message and delete the message from your system.")
		) );

		$wrapper->Send( $faxnumber );
		@unlink( $render );
		if (is_array($f)) { foreach ($f AS $fn) { @unlink( $fn ); } }
		return true;
	} // end method PrintToFax

	// Method: PrintToPrinter
	//
	// Parameters:
	//
	//	$printer - Printer name
	//
	//	$items - Array of items
	//
	// Return:
	//
	//	Boolean, success
	//
	public function PrintToPrinter( $printer, $items ) {
		foreach ($items AS $i) {
			$k[] = (int) $i;
		}
		$q = "SELECT * FROM patient_emr WHERE id IN ( ".join(',', $k)." )";
		$r = $GLOBALS['sql']->queryAll( $q );

		$wrapper = CreateObject( 'org.freemedsoftware.core.PrinterWrapper' );

		// Handle differently depending on single or multiple
		if (count($items) < 2) {
			// Single render
			$render = module_function( $r[0]['module'], 'RenderToPDF', array( $r[0]['oid'] ) );
		} else {
			// Multiples, use composite object
			$c = CreateObject( 'org.freemedsoftware.core.MultiplePDF' );
			foreach ($r AS $o) {
				$thisFile = module_function( $o['module'], 'RenderToPDF', array( $o['oid'] ) );
				$comp->Add( $thisFile );
				$f[] = $thisFile;
			}
			$render = $comp->Composite();
		}

		$wrapper->PrintFile( $printer, $render );
		@unlink( $render );
		if (is_array($f)) { foreach ($f AS $fn) { @unlink( $fn ); } }
		return true;
	} // end method PrintToPrinter

	// Method: PrintToBrowser
	//
	//	Print patient_emr items to browser as PDF
	//
	// Parameters:
	//
	//	$items - Array of items
	//
	public function PrintToBrowser ( $items ) {
		foreach ($items AS $i) {
			$k[] = (int) $i;
		}
		$q = "SELECT p.patient AS patient, p.module AS module, p.oid AS oid, p.annotation AS annotation, p.summary AS summary, p.stamp AS stamp, DATE_FORMAT(p.stamp, '%m/%d/%Y') AS date_mdy, m.module_name AS type, m.module_class AS module_namespace, p.locked AS locked, p.id AS id FROM patient_emr p LEFT OUTER JOIN modules m ON m.module_table = p.module WHERE p.id IN ( ".join( ',', $k )." )";
		$r = $GLOBALS['sql']->queryAll( $q );

		// Handle differently depending on single or multiple
		if (count($items) < 2) {
			// Single render
			Header ("Content-type: application/x-pdf");
			Header ("Content-Disposition: inline; filename=\"".mktime().".pdf\"");
			$thisFile = module_function( $r[0]['module_namespace'], 'RenderToPDF', array( $r[0]['oid'] ) );
			print file_get_contents( $thisFile );
			@unlink( $thisFile );
		} else {
			// Multiples, use composite object
			$c = CreateObject( 'org.freemedsoftware.core.MultiplePDF' );
			foreach ($r AS $o) {
				$thisFile = module_function( $o['module_namespace'], 'RenderToPDF', array( $o['oid'] ) );
				$comp->Add( $thisFile );
				$f[] = $thisFile;
			}
			Header ("Content-type: application/x-pdf");
			Header ("Content-Disposition: inline; filename=\"".mktime().".pdf\"");
			print file_get_contents( $comp->Composite() );
			@unlink( $comp->Composite() );
			foreach ($f AS $fn) { @unlink( $fn ); }
		}
	} // end method PrintToBrowser

} // end class ModuleInterface

?>
