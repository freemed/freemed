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

// Class: org.freemedsoftware.core.CSV
//
//	Handle comma-separated values format
//
class CSV {

	var $_keys;
	var $_cache;

	// Constructor: CSV
	//
	//	Create CSV object
	//
	function CSV ( ) { }

	// Method: ImportSQLQuery
	//
	//	Import a query from an SQL statement
	//
	// Parameters:
	//
	//	$query - SQL query text
	//
	function ImportSQLQuery ( $query ) {
		$q = $GLOBALS['sql']->queryAll($query);

		$notset = false;
		foreach ($q AS $r) {
			// Unset integer keys
			foreach($r AS $k=>$v) {
				if (is_int($k)) {
					unset($r[$k]); 
				} else {	
					$r[$k] = str_replace("\n", "", $r[$k]);
					$r[$k] = str_replace("\r", "", $r[$k]);
					$r[$k] = str_replace("\"", "\\\"", $r[$k]);
					$r[$k] = '"' . $r[$k] . '"';
				}
			}

			// Get all keys if we don't have them already
			if (!$notset) {
				foreach ($r AS $k => $v) { $keys[] = $k; }
				$notset = true;
			}

			// Add results
			$results[] = $r;			
		}

		$this->_keys  = $keys;
		$this->_cache = $results;
	} // end method ImportSQLQuery

	// Method: Export
	//
	//	Export as CSV and die
	//
	function Export ( ) {
		if (!isset($this->_cache)) { return false; }

		$CRLF = "\r\n";

		Header ("Content-type: application/csv");
		Header ("Content-Disposition: inline; filename=\"".mktime().".csv\"");

		print join(',', $this->_keys).$CRLF;

		foreach ($this->_cache AS $v) {
			print join(',', $v).$CRLF;
		}

		die();
	}

} // end class CSV

?>
