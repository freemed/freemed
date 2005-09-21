<?php
	// $Id$
	// $Author$

// Class: FreeMED.CSV
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
		$q = $GLOBALS['sql']->query($query);

		$notset = false;
		while ($r = $GLOBALS['sql']->fetch_array($q)) {
			// Unset integer keys
			foreach($r AS $k=>$v) {
				if (is_int($k)) {
					unset($r[$k]); 
				} else {	
					$r[$k] = str_replace("\n", "", $r[$k]);
					$r[$k] = str_replace("\r", "", $r[$k]);
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
		Header ("Content-Disposition: \"inline; filename=".mktime().".csv\"");

		print join(',', $this->_keys).$CRLF;

		foreach ($this->_cache AS $v) {
			print join(',', $v).$CRLF;
		}

		die();
	}

} // end class CSV

?>
