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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Api.TableMaintenance'
		, 'mappedBy' => 'org.freemedsoftware.api.TableMaintenance'
		, 'methods' => array (

			// Method: ExportStockData
			//
			//	Export data for a table.
			//
			// Parameters:
			//
			//	$table - Table name
			//
			// Returns:
			//
			//	Boolean, success
			//
			  array (
				  'name' => 'ExportStockData'
				, 'mappedBy' => 'ExportStockData'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: ExportTables 
		 	// 
		 	//      Export data for tables. 
		 	// 
		 	// Parameters: 
		 	// 
		 	//      $tables - array of table names 
		 	// 
			// Returns:
			//
			//	Boolean, success
			//
			, array (
				  'name' => 'ExportTables'
				, 'mappedBy' => 'ExportTables'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)
     
		 	// Method: GetModuleTables 
		 	// 
		 	//      Get picklist formatted module tables information. 
		 	// 
		 	// Parameters: 
		 	// 
		 	//      $param - Substring to search for. Defaults to ''. 
		 	// 
		 	// Returns: 
		 	// 
		 	//      Array of arrays containing ( module name, table name). 
		 	// 
			, array (
				  'name' => 'GetModuleTables'
				, 'mappedBy' => 'GetModuleTables'
				, 'returnType' => '[[Ljava.lang.String;'
				, 'returnTypeCRC' => '392769419[2364883620[L2004016611;'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)
	
			// Method: GetModules
			//
			//	Get list of modules based on their associations.
			//
			// Parameters:
			//
			//	$assoc - Association
			//
			//	$like - (optional) String to search names for
			//
			//	$picklist - (optional) Return in picklist format ( k, v ), boolean
			//
			// Returns:
			//
			//	Array of hashes.
			//
			, array (
				  'name' => 'GetModules'
				, 'mappedBy' => 'GetModules'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

			// Method: ImportStockData
			//
			//	Import data for a table.
			//
			// Parameters:
			//
			//	$table_name - Table name
			//
			// Returns:
			//
			//	Boolean
			//
			, array (
				  'name' => 'ImportStockData'
				, 'mappedBy' => 'ImportStockData'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
