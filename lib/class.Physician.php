<?php
 // $Id$
 // $Author$

// class Physician
class Physician {
	var $local_record;                 // stores basic record
	var $id;                           // record ID for physician
	var $phylname,$phyfname,$phymname; // name of physician
	var $phyidmap;                     // id map

	function Physician ($physician = 0) {
		global $database;

		if ($physician==0) return false;    // error checking

		// Check for cache
		if (!isset($GLOBALS['__freemed']['cache']['physician'][$physician])) {
			// Get physician record
			$this->local_record = freemed::get_link_rec ($physician,
				"physician");

			// and cache the record
			$GLOBALS['__freemed']['cache']['physician'][$physician] = $this->local_record;
			
		} else {
			// Retrieve from cache
			$this->local_record = $GLOBALS['__freemed']['cache']['physician'][$physician];
		}
		$this->phylname     = $this->local_record["phylname"];
		$this->phyfname     = $this->local_record["phyfname"];
		$this->phymname     = $this->local_record["phymname"];
		$this->phyidmap     = fm_split_into_array(
		$this->local_record["phyidmap"]);
	} // end constructor Physician

	function fullName () {
		return $this->phyfname . " " . $this->phymname .
		( (!empty($this->phymname)) ? " " : "" ) . $this->phylname;
	} // end function Physician->fullName

	function getMapId ($this_id = 0) {
		return ( ($this_id == 0) ? "" : $this->phyidmap[$this_id] );
	} // end function Physician->getMapId

	function practiceName () {
		return $this->local_record["phypracname"];
	} // end function Physician->practiceName

} // end class Physician

?>
