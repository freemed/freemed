<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.InsuranceCompany
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
			$this->local_record = $GLOBALS['sql']->get_link( 'insco', $insco );

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
