<?php
 // $Id$
 // $Author$

// class Guarantor
class Guarantor {
	var $local_record;                // stores basic record
	var $id;                          // record ID for insurance company
	var $guarfname;
	var $guarlname;
	var $guarmname;
	var $guaraddr1;
	var $guaraddr2;
	var $guarcity;
	var $guarstate;
	var $guarzip;
	var $guarsex;
	var $guardob;
	var $guarsame;

	function Guarantor ($coverageid = "") {
		if ($coverageid=="") return false;    // error checking

		if (!isset($GLOBALS['__freemed']['cache']['coverage'][$coverageid])) {
			// Retrieve record
			$this->local_record = freemed::get_link_rec (
				$coverageid, "coverage"
			);

			// Cache record
			$GLOBALS['__freemed']['cache']['coverage'][$coverageid] = $this->local_record;
		} else {
			// Retrieve from cache
			$this->local_record = $GLOBALS['__freemed']['cache']['coverage'][$coverageid];
		}

		$this->guarfname = $this->local_record["covfname"];
		$this->guarlname = $this->local_record["covlname"];
		$this->guarmname = $this->local_record["covmname"];
		$this->guaraddr1 = $this->local_record["covaddr1"];
		$this->guaraddr2 = $this->local_record["covaddr2"];
		$this->guarcity = $this->local_record["covcity"];
		$this->guarstate = $this->local_record["covstate"];
		$this->guarzip = $this->local_record["covzip"];
		$this->guardob = $this->local_record["covdob"];
		$this->guarsex = $this->local_record["covsex"];
		$this->id = $this->local_record["id"];
		if (empty($this->local_record[covaddr1])) {
			$this->guarsame = 1;
		}
	
	} // end constructor Guarantor
} // end class Guarantor

?>
