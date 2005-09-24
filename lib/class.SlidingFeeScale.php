<?php
	// $Id$
	// $Author$

// Class: SlidingFeeScale
//
//	Class for supporting the US Federal Government sliding fee scale.
//
class SlidingFeeScale {

	var $financial_record;
	var $patient;

	function __constructor ( $patient ) { $this->SlidingFeeScale($patient); }

	// Constructor: SlidingFeeScale
	//
	// Parameters:
	//
	//	$patient - Record id of patient
	//
	function SlidingFeeScale ( $patient ) {
		$this->patient = $patient;
		$this->financial_record = $this->_GetLastFinancialRecord();
	} // end constructor SlidingFeeScale

	// Method: BracketToMultiplier
	//
	//	Convert a sliding fee scale bracket into a multiplier,
	//	which when multiplied by a procedure will generate the
	//	end cost for that procedure.
	//
	// Parameters:
	//
	//	$bracket - Label name of bracket
	//
	// Returns:
	//
	//	"Real" number, non-integer
	//
	function BracketToMultiplier ( $bracket ) {
		switch ($bracket) {
			case 'A':	return 0.0;	break;
			case 'B':	return 0.2;	break;
			case 'C':	return 0.4;	break;
			case 'D':	return 0.6;	break;
			case 'E':	return 0.8;	break;
			default:	return 1.0;	break;
		}
	} // end method BracketToMultiplier

	// Method: PatientBracket
	//
	//	Get patient bracket
	//
	// Returns:
	//
	//	Designator for sliding scale: 'A', 'B' ...
	//
	// SeeAlso:
	//	<DetermineBracket>
	//
	function PatientBracket ( ) {
		// Check for no financial records, return false
		if (!is_array($this->financial_record)) { return false; }

		// Wrap DetermineBracket method
		return $this->DetermineBracket(
			$this->financial_record['fdincome'],
			$this->financial_record['fdhousehold']
		);
	} // end method Patient Bracket

	// Method: DetermineBracket
	//
	//	Determine sliding scale fee bracket (A, B, C, D, E)
	//
	// Parameters:
	//
	//	$hhsize - Size of household
	//
	//	$income - Yearly income
	//
	// Returns:
	//
	//	Designator for sliding scale: 'A', 'B' ...
	//
	function DetermineBracket ( $hhsize, $income ) {
		// Formula:
		//	( fed_pov_level + ( dependents * increment) ) * percent of povlev

		$fed_pov_level = freemed::config_value('fed_pov_level');
		if (!$fed_pov_level) { $fed_pov_level = 8980; }
		$increment = freemed::config_value('fed_pov_inc');
		if (!$increment) { $increment = 3140; }

		$dependents = $hhsize - 1;
		$base = $fed_pov_level + ($dependents * $increment);

		// Bracket A: income at or below 100% of poverty guideline
		if ($income <= ($base * 1.00)) { return 'A'; }

		// Bracket B: income at or below 125% of poverty guideline
		if ($income <= ($base * 1.25)) { return 'B'; }

		// Bracket C: income at or below 150% of poverty guideline
		if ($income <= ($base * 1.25)) { return 'C'; }

		// Bracket D: income at or below 175% of poverty guideline
		if ($income <= ($base * 1.25)) { return 'D'; }

		// Bracket E: income at or above 200% of poverty guideline
		if ($income >= ($base * 2.00)) { return 'E'; }

		// Fall through
		return false;
	} // end method DetermineBracket

	// Method: _GetLastFinancialRecord
	//
	//	Retrieve the most recent financial record on file for the
	//	current patient.
	//
	// Returns:
	//
	//	Associative array of information
	//
	function _GetLastFinancialRecord ( ) {
		$query = "SELECT * FROM financialdemographics ".
			"ORDER BY fdtimestamp DESC LIMIT 1";
		$result = $GLOBALS['sql']->query ( $query );
		$r = $GLOBALS['sql']->fetch_array( $result );
		return $r;
	} // end method _GetLastFinancialRecord

} // end class SlidingFeeScale

?>
