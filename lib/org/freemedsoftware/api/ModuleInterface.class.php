<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.api.ModuleInterface
//
//	"Factory" type of interface to module functions to greatly
//	simplify RPC.
//
//	Task 2.8, fix round 2 (R32), the CLASS AXIS. Every wrapper below dispatches
//	a FIXED method name ('add', 'del', 'GetRecord', 'GetRecords', 'mod',
//	'picklist', 'RenderHtmlView', 'to_text', and the print wrappers' literal
//	'RenderToPDF') against a CALLER-CHOSEN module class -- module_function()
//	resolves `$module` to lib/org/freemedsoftware/module/<Class>.class.php and
//	instantiates it. The relay allowlist names relay METHOD strings, so listing
//	the wrapper method ('org.freemedsoftware.api.ModuleInterface.ModuleAddMethod')
//	cannot constrain the class the caller passes: a listed wrapper reached every
//	module class in the tree. That is the same shape as the C1 bypass, one level
//	down, so it gets the same answer: each dispatch resolves the CONCRETE string
//	it is about to perform -- `org.freemedsoftware.module.<$module>.<literal>`,
//	derived from the source of the call below, never guessed -- and runs it
//	through the SAME decision point the relay and Multicall() use
//	(Relay_Allowlist::refuse(), inner scope). In the shipped log-only stage a
//	miss is logged and the dispatch still happens (that is how an operator
//	discovers the patterns a site actually needs); under enforcement it answers
//	'INVALID_CALL' and the dispatch does not happen.
//
//	LoadObjectDependency at file scope is the repo convention (154 files) and
//	Relay.class.php loads the same class for the outer check, so this is a no-op
//	in the relay request path.
LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist');

class ModuleInterface {

	public function __construct ( ) { }

	// Method: _allowlist_gate
	//
	//	Resolve the concrete relay method string that a module_function()
	//	dispatch in this class is about to perform and run it through the
	//	relay's shared decision point.
	//
	//	The concrete string is derived from the dispatch itself:
	//	module_function($module, $literal) performs
	//	`org.freemedsoftware.module.<$module>.<$literal>` -- resolve_module()
	//	maps the class name to lib/org/freemedsoftware/module/<Class>.class.php
	//	and PHP then calls the method on it, which is exactly what the relay's
	//	own method string `org.freemedsoftware.module.<Class>.<Method>` does.
	//
	//	The inner scope (third argument TRUE) is the scope the pre-2.8 code used
	//	for re-dispatched calls; for this axis it is inert in practice (no
	//	`module.*` string can be inside the shipped never-allow namespace
	//	'org.freemedsoftware.core.'), but it is the honest scope: the class name
	//	comes from the caller.
	//
	// Parameters:
	//
	//	$module - The module class name, as the caller supplied it.
	//
	//	$method - The literal method name this wrapper dispatches.
	//
	// Returns:
	//
	//	Boolean. TRUE means the dispatch may proceed, FALSE means it must be
	//	refused and answered with 'INVALID_CALL'.
	private function _allowlist_gate ( $module, $method ) {
		// class_exists() is the M1 degradation path: a missing class file must
		// not fatal the request.
		if ( ! class_exists ( 'Relay_Allowlist' ) ) { return true; }
		// A non-string argument cannot dispatch anyway; building the string
		// this way keeps that from emitting a PHP conversion warning, and the
		// resulting string is never listed, so enforcement refuses it.
		$concrete = 'org.freemedsoftware.module.'
			. ( is_string ( $module ) ? $module : '' ) . '.'
			. ( is_string ( $method ) ? $method : '' );
		return ! Relay_Allowlist::refuse ( $concrete, NULL, true );
	} // end method _allowlist_gate

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
		if ( ! $this->_allowlist_gate ( $module, 'add' ) ) { return 'INVALID_CALL'; }
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
		if ( ! $this->_allowlist_gate ( $module, 'del' ) ) { return 'INVALID_CALL'; }
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
		if ( ! $this->_allowlist_gate ( $module, 'GetRecord' ) ) { return 'INVALID_CALL'; }
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
		if ( ! $this->_allowlist_gate ( $module, 'GetRecords' ) ) { return 'INVALID_CALL'; }
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
		if ( ! $this->_allowlist_gate ( $module, 'mod' ) ) { return 'INVALID_CALL'; }
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
	public function ModuleSupportPicklistMethod ( $module, $criteria, $fieldValues = NULL ) {
		syslog(LOG_INFO, "module_function( $module, 'picklist', array ( $criteria,$fieldValues ) )" );
		if ( ! $this->_allowlist_gate ( $module, 'picklist' ) ) { return 'INVALID_CALL'; }
		return module_function( $module, 'picklist', array ( $criteria, $fieldValues ) );
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
		syslog(LOG_INFO, "module_function( $module, 'picklist', array ( $patient, $criteria ) )" );
		if ( ! $this->_allowlist_gate ( $module, 'picklist' ) ) { return 'INVALID_CALL'; }
		return module_function( $module, 'picklist', array ( $patient,$criteria ) );
	} // end method ModuleSupportPicklistMethod

	// Method: ModuleRenderHtmlMethod
	public function ModuleRenderHtmlMethod( $module, $id ) {
		if ( ! $this->_allowlist_gate ( $module, 'RenderHtmlView' ) ) { return 'INVALID_CALL'; }
		module_function( $module, 'RenderHtmlView', array ( $id ) );
	} // end method ModuleRenderHtmlMethod

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
		if ( ! $this->_allowlist_gate ( $module, 'to_text' ) ) { return 'INVALID_CALL'; }
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
			if ( ! $this->_allowlist_gate ( $r[0]['module'], 'RenderToPDF' ) ) { return 'INVALID_CALL'; }
			$render = module_function( $r[0]['module'], 'RenderToPDF', array( $r[0]['oid'] ) );
		} else {
			// Multiples, use composite object
			$c = CreateObject( 'org.freemedsoftware.core.MultiplePDF' );
			foreach ($r AS $o) {
				if ( ! $this->_allowlist_gate ( $o['module'], 'RenderToPDF' ) ) { return 'INVALID_CALL'; }
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
			if ( ! $this->_allowlist_gate ( $r[0]['module'], 'RenderToPDF' ) ) { return 'INVALID_CALL'; }
			$render = module_function( $r[0]['module'], 'RenderToPDF', array( $r[0]['oid'] ) );
		} else {
			// Multiples, use composite object
			$c = CreateObject( 'org.freemedsoftware.core.MultiplePDF' );
			foreach ($r AS $o) {
				if ( ! $this->_allowlist_gate ( $o['module'], 'RenderToPDF' ) ) { return 'INVALID_CALL'; }
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
			if ( ! $this->_allowlist_gate ( $r[0]['module_namespace'], 'RenderToPDF' ) ) { return 'INVALID_CALL'; }
			Header ("Content-type: application/x-pdf");
			Header ("Content-Disposition: inline; filename=\"".time().".pdf\"");
			$thisFile = module_function( $r[0]['module_namespace'], 'RenderToPDF', array( $r[0]['oid'] ) );
			print file_get_contents( $thisFile );
			@unlink( $thisFile );
		} else {
			// Multiples, use composite object
			$c = CreateObject( 'org.freemedsoftware.core.MultiplePDF' );
			foreach ($r AS $o) {
				if ( ! $this->_allowlist_gate ( $o['module_namespace'], 'RenderToPDF' ) ) { return 'INVALID_CALL'; }
				$thisFile = module_function( $o['module_namespace'], 'RenderToPDF', array( $o['oid'] ) );
				$comp->Add( $thisFile );
				$f[] = $thisFile;
			}
			Header ("Content-type: application/x-pdf");
			Header ("Content-Disposition: inline; filename=\"".time().".pdf\"");
			print file_get_contents( $comp->Composite() );
			@unlink( $comp->Composite() );
			foreach ($f AS $fn) { @unlink( $fn ); }
		}
	} // end method PrintToBrowser

} // end class ModuleInterface

?>
