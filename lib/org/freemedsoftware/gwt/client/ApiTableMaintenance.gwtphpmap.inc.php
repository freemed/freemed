<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.ApiTableMaintenance'
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
				, 'mappedName' => 'ExportStockData'
				, 'returnType' => 'java.lang.Boolean'
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
			//	$picklist - (optional) Return in picklist format ( k, v )
			//
			// Returns:
			//
			//	Array of hashes.
			//
			, array (
				  'name' => 'GetModules'
				, 'mappedName' => 'GetModules'
				, 'returnType' => '[java.util.HashMap'
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
			, array (
				  'name' => 'ImportStockData'
				, 'mappedName' => 'ImportStockData'
				, 'returnType' => TypeSignatures::$NULL
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
