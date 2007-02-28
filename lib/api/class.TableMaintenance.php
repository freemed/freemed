<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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
		$physical_file = PHYSICAL_LOCATION . "/data/" . DEFAULT_LANGUAGE .
			"/" .  $table_name . "." . DEFAULT_LANGUAGE . ".data.".
			date("Ymd");

		// Die if the phile doesn't exist
		if (file_exists($physical_file)) { return false; }

		// Create the query
		$query = "SELECT * FROM '".addslashes($table)."' ".
			"INTO OUTFILE '".addslashes( $physical_file )."' ".
			"FIELDS TERMINATED BY ',' ".
			"OPTIONALLY ENCLOSED BY '' ".
			"ESCAPED BY '\\\\'";

		$result = $GLOBALS['sql']->query ( $query );

		return true;
	} // end public function ExportStockData

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
		$physical_file = PHYSICAL_LOCATION . "/data/" . DEFAULT_LANGUAGE .
			"/" .  $table_name . "." . DEFAULT_LANGUAGE . ".data";

		// Die if the phile doesn't exist
		if (!file_exists($physical_file)) return false;

		// Create the query
		$query = "LOAD DATA LOCAL INFILE '".addslashes( $physical_file )."' ".
			"INTO TABLE ".addslashes( $table_name )." ".
			"FIELDS TERMINATED BY ','";

		$result = $GLOBALS['sql']->query ( $query ); 
	} // end public function ImportStockData

} // end class TableMaintenance

?>
