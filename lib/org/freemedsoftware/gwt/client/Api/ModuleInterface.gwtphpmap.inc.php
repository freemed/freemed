<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Api.ModuleInterface'
		, 'mappedBy' => 'org.freemedsoftware.api.ModuleInterface'
		, 'methods' => array (

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
			  array (
				  'name' => 'ModuleAddMethod'
				, 'mappedName' => 'ModuleAddMethod'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: ModuleDeleteMethod
			//
			// Parameters:
			//
			//	$module - Module name
			//
			//	$id - Id to be removed
			//
			, array (
				  'name' => 'ModuleDeleteMethod'
				, 'mappedName' => 'ModuleDeleteMethod'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'ModuleGetRecordMethod'
				, 'mappedName' => 'ModuleGetRecordMethod'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'ModuleGetRecordsMethod'
				, 'mappedName' => 'ModuleGetRecordsMethod'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'ModuleModifyMethod'
				, 'mappedName' => 'ModuleModifyMethod'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'ModuleSupportPicklistMethod'
				, 'mappedName' => 'ModuleSupportPicklistMethod'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'ModuleToTextMethod'
				, 'mappedName' => 'ModuleToTextMethod'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: PrintToFax
			//
			// Parameters:
			//
			//      $faxnumber - Destination number
			//
			//      $items - Array of items
			//
			// Return:
			//
			//      Boolean, success
			//
			, array (
				  'name' => 'PrintToFax'
				, 'mappedName' => 'PrintToFax'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => '[Ljava.lang.Integer;' )
				)
				, 'throws' => array ( )
			)

			// Method: PrintToPrinter
			//
			// Parameters:
			//
			//      $printer - Printer name
			//
			//      $items - Array of items
			//
			// Return:
			//
			//      Boolean, success
			//
			, array (
				  'name' => 'PrintToPrinter'
				, 'mappedName' => 'PrintToPrinter'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => '[Ljava.lang.Integer;' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
