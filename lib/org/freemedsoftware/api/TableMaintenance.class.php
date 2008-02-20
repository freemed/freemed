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

// Class: org.freemedsoftware.api.TableMaintenance
//
class TableMaintenance {

	public function __construct ( ) { }

	// Method: ExportStockData
	//
	//	Export data for a table.
	//
	// Parameters:
	//
	//	$table - Table name
	//
	public function ExportStockData ( $table ) {
		// Produce a physical location
		$physical_file = PHYSICAL_LOCATION . "/data/locale/" . DEFAULT_LANGUAGE .
			"/" .  $table . "." . DEFAULT_LANGUAGE . ".data.". 
 			date("YmdHi"); 

		// Die if the file doesn't exist
		if (file_exists($physical_file)) { return false; }

		// Create the query
		$query = "SELECT * FROM ".addslashes($table)." ".
			"INTO OUTFILE '".addslashes( $physical_file )."' ".
			"FIELDS TERMINATED BY ',' ".
			"OPTIONALLY ENCLOSED BY '' ".
			"ESCAPED BY '\\\\'";

		$result = $GLOBALS['sql']->query ( $query );

		return true;
	} // end public function ExportStockData

	// Method: ExportTables 
 	// 
 	//      Export data for tables. 
 	// 
 	// Parameters: 
 	// 
 	//      $tables - array of table names 
 	// 
 	public function ExportTables ( $tables ) { 
 		foreach ($tables['exporttables'] as $v) { 
 			$created = $this->ExportStockData($v); 
 			if (!$created) { return false; } 
 		}                
 		return true; 
 	} // end public function ExportTables 
     
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
 	public function GetModuleTables ( $param = '' ) { 
 		$q = "SELECT m.module_name AS m_name, m.module_table AS m_table FROM modules m WHERE m.module_name LIKE '".addslashes( $param )."%' ORDER BY m.module_name"; 
 		$res = $GLOBALS['sql']->queryAll( $q ); 
 		if (!count($res)) { return array(); } 
 		foreach ( $res AS $r ) { 
 			$return[$r['m_table']] = $r['m_name']." (".$r['m_table'].")"; 
 		} 
 		return $return; 
 	} // end method GetModuleTables 	
	
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
	public function GetModules ( $assoc, $like = NULL, $picklist = false ) {
		$query = "SELECT module_name, module_version, module_class FROM modules WHERE FIND_IN_SET( ".$GLOBALS['sql']->quote( $assoc ).", module_associations ) ". ( $like ? " AND LOWER(module_name) LIKE '%".$GLOBALS['sql']->escape( strtolower($like) )."%'" : "" )." ORDER BY module_name";
		$result = $GLOBALS['sql']->queryAll( $query );
		if ( $picklist ) {
			foreach ( $result AS $v ) {
				$r[] = array ( $v['module_name'], $v['module_class'] );
			}
			return $r;
		}
		return $result;
	} // end method GetModules

	// Method: ImportStockData
	//
	//	Import data for a table.
	//
	// Parameters:
	//
	//	$table_name - Table name
	//
	public function ImportStockData ( $table_name ) {
		// Produce a physical location
		$physical_file = PHYSICAL_LOCATION . "/data/locale/" . DEFAULT_LANGUAGE .
			"/" .  $table_name . "." . DEFAULT_LANGUAGE . ".data";

		// Die if the phile doesn't exist
		if (!file_exists($physical_file)) return false;

		// Create the query
		$query = "LOAD DATA LOCAL INFILE '".addslashes( $physical_file )."' ".
			"INTO TABLE ".addslashes( $table_name )." ".
			"FIELDS TERMINATED BY ','";

		$result = $GLOBALS['sql']->query ( $query ); 
	} // end public function ImportStockData

	// Method: ImportTables 
 	// 
 	//      Import data for tables. 
 	// 
 	// Parameters: 
 	// 
 	//      $tables - array of table names 
 	// 
 	public function ImportTables ( $tables ) { 
 		foreach ($tables['exporttables'] as $v) { 
 			//$created = $this->ImportStockData($v); 
 			if (!$created) { return false; } 
 		}                
 		return true; 
 	} // end public function ExportTables 	

} // end class TableMaintenance

?>
