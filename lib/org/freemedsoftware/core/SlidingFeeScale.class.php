<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.SlidingFeeScale
//
//	Class for supporting the US Federal Government sliding fee scale.
//
class SlidingFeeScale {

	private $financial_record;
	private $patient;

	// Constructor: SlidingFeeScale
	//
	// Parameters:
	//
	//	$patient - Record id of patient
	//
	function __construct ( $patient ) {
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
	public function BracketToMultiplier ( $bracket ) {
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
	public function PatientBracket ( ) {
		// Check for no financial records, return false
		if (!is_array($this->financial_record)) { return false; }

		// Wrap DetermineBracket method
		$return = $this->DetermineBracket(
			$this->financial_record['fdhousehold'],
			$this->financial_record['fdincome']
		);
		return $return;
	} // end method PatientBracket

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
	public function DetermineBracket ( $hhsize, $income ) {
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
		if ($income <= ($base * 1.50)) { return 'C'; }

		// Bracket D: income at or below 175% of poverty guideline
		if ($income <= ($base * 1.75)) { return 'D'; }

		// Bracket E: income at or above 200% of poverty guideline
		if ($income <= ($base * 2.00)) { return 'E'; }

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
	protected function _GetLastFinancialRecord ( ) {
		$query = "SELECT * FROM financialdemographics ".
			"WHERE fdpatient='".addslashes($this->patient)."' ".
			"ORDER BY fdtimestamp DESC LIMIT 1";
		$r = $GLOBALS['sql']->queryRow ( $query );
		return $r;
	} // end method _GetLastFinancialRecord

} // end class SlidingFeeScale

?>
