<?php
	// $Id$
	// $Author$

// Class: FreeMED.Payer
//
//	Class container for a "payer" in the system.
//
class Payer {
	var $local_record;                // stores basic record
	var $id;                          // record ID for insurance company
	var $patgroupno;                  // patients group no for this payer
	var $patinsidno;                  // patients id number for this payer
	var $payerstatus;
	var $payertype;                   // payertype 0 prim, 1 sec 2 tert 4 wc
	var $payerstartdt;                // effective dates for coverage
	var $payerenddt;
	var $inscoid;			// pointer to corresponding insco.

	// Method: Payer constructor
	//
	// Parameters:
	//
	//	$payerid - Database table identifier for the specified payer.
	//
	function Payer ($payerid = "") {
		if ($payerid=="") return false;    // error checking

		// Check for a cached copy
		if (!isset($GLOBALS['__freemed']['cache']['payer'][$payerid])) {
			// Retrieve copy
			$this->local_record = freemed::get_link_rec (
				$payerid, "payer"
			);

			// Cache it
			$GLOBALS['__freemed']['cache']['payer'][$payerid] = $this->local_record;
		} else {
			// Retrieve from cache
			$this->local_record = $GLOBALS['__freemed']['cache']['payer'][$payerid];
		}

		$this->patgroupno = $this->local_record[payerpatientgrp];	
		$this->patinsidno = $this->local_record[payerpatientinsno];	
		$this->payerstatus = $this->local_record[payerstatus];	
		$this->payertype = $this->local_record[payertype];	
		$this->payerstartdt = $this->local_record[payerstartdt];	
		$this->payerenddt = $this->local_record[payerenddt];	
		$this->inscoid = $this->local_record[payerinsco];	
	} // end constructor Payer

} // end class Payer

?>
