<?php
	// $Id$
	// $Author$

// Class: FreeMED.InsuranceCompany
//
//	Class container for Insurance Company.
//
class InsuranceCompany {
	var $local_record;           // stores basic record
	var $id;                     // record ID for insurance company
	var $insconame;              // name of company
	var $inscoalias;             // insurance company alias (for forms)
	var $modifiers;              // modifiers array

	// Method: InsuranceCompany constructor
	//
	// Parameters:
	//
	//	$insco - Database table identifier for insurance company.
	//
	function InsuranceCompany ($insco = 0) {
		if ($insco==0) return false;    // error checking

		if (!isset($GLOBALS['__freemed']['cache']['insco'][$insco])) {
			// Get record
			$this->local_record = freemed::get_link_rec (
				$insco, "insco"
			);

			// Cache it
			$GLOBALS['__freemed']['cache']['insco'][$insco] = $this->local_record;
		} else {
			// Retrieve from the cache
			$this->local_record = $GLOBALS['__freemed']['cache']['insco'][$insco];

		}
		$this->id           = $this->local_record["id" ];
		$this->insconame    = $this->local_record["insconame" ];
		$this->inscoalias   = $this->local_record["inscoalias"];
		$this->modifiers    = fm_split_into_array (
			$this->local_record["inscomod"]
		);
	} // end constructor InsuranceCompany

	// Method: get_name
	//
	//	Form name of insurance company / payer.
	//
	// Returns:
	//
	//	Common name of insurance company / payer.
	//
	function get_name ( ) {
		return $this->local_record['insconame'].' ('.
			$this->local_record['inscocity'].', '.
			$this->local_record['inscostate'].')';
	} // end method get_name

} // end class InsuranceCompany

?>
