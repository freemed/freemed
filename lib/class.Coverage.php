<?php
 // $Id$
 // $Author$

// class Coverage
class Coverage {
	var $local_record;                // stores basic record
	var $id;                          // record ID for insurance company
	var $covpatgrpno;                  // patients group no for this payer
	var $covpatinsno;                  // patients id number for this payer
	var $covstatus;
	var $covtype;                   // payertype 1 prim, 2 sec 3 tert 4 wc
	var $coveffdt;                // effective dates for coverage
	var $covinsco;		// pointer to corresponding insco.
	var $covreldep;                 // guar relation to insured 
	var $covdep;                 // help ease the conversion
	var $covpatient;             // the patient

	// insureds info only if rel is not "S"elf

	function Coverage ($coverageid = "") {
		if ($coverageid=="" OR $coverageid==0) return false;

		// Check in the cache
		if (!isset($GLOBALS['__freemed']['cache']['coverage'][$coverageid])) {
			// Get record
			$this->local_record = freemed::get_link_rec (
				$coverageid, "coverage"
			);

			// Cache it
			$GLOBALS['__freemed']['cache']['coverage'][$coverageid] = $this->local_record;
		} else {
			// Retrieve from cache
			$this->local_record = $GLOBALS['__freemed']['cache']['coverage'][$coverageid];
		}
		$this->covpatgrpno = $this->local_record[covpatgrpno];	
		$this->covpatinsno = $this->local_record[covpatinsno];	
		$this->covstatus = $this->local_record[covstatus];	
		$this->covtype = $this->local_record[covtype];	
		$this->coveffdt = $this->local_record[coveffdt];	
		$this->covinsco = CreateObject('FreeMED.InsuranceCompany', $this->local_record[covinsco]);	
		$this->covreldep = $this->local_record[covrel];	
		$this->id = $this->local_record[id];
		$this->covpatient = $this->local_record[covpatient];	
		if ($this->covreldep != "S") {
			// you pass this to the guarantor class
			$this->covdep = $this->id;
		} else {
			$this->covdep = 0;
		}

	} // end constructor Coverage
} // end class Coverage

?>
