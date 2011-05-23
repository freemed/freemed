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

// Class: org.freemedsoftware.api.Procedure
class Procedure {

	// Constructor: Procedure
	//
	// Parameters:
	//
	//	$id - (optional) Procedure id
	//
	public function __construct ( $id = 0 ) {
		$this->id = $id;
		$this->local_record = $GLOBALS['sql']->get_link( 'procrec', $id );
	} // end constructor Procedure

	// Method: current_balance
	//
	//	Determine current balance of this object's procedure
	//
	// Returns:
	//
	//	Monetary balance amount.
	//
	function current_balance ( $_id = 0 ) {
		$id = $_id ? $_id : $this->id;

		$total_payments = 0.00;
		$total_charges  = 0.00;

		// Process payment records
		$result = $GLOBALS['sql']->query(
			"SELECT * FROM payrec AS a, procrec AS b ".
			"WHERE b.id = a.payrecproc AND ".
			"a.payrecproc = '".addslashes($id)."'"
		);
		foreach ( $result AS $r ) {
			switch ($r['payreccat']) {
				case REFUND:
				case PROCEDURE:
					$total_charges += $r['payrecamt'];
					break;
				case WITHHOLD:
				case DEDUCTABLE:
				case FEEADJUST:
				case WRITEOFF:
				case DENIAL:
					$total_charges -= $r['payrecamt'];
					break;
				case ADJUSTMENT:
					$total_payments -= $r['payrecamt'];
					break;
				case PAYMENT:
				case COPAY:
					$total_payments += $r['payrecamt'];
					break;
				default: break;
			}
		}
		return $total_charges - $total_payments;
	} // end method CurrentBalance

	// Method: check_for_payer
	//
	//	Determine if a procedure has a payer associated with it.
	//
	// Parameters:
	//
	//	$proc - Procedure id or associative array of procedure
	//
	//	$payer - Payer to be checked
	//
	// Returns:
	//
	//	Boolean, whether payer is associated with procedure.
	//
	public function check_for_payer ( $proc, $payer ) {
		if ( is_array( $proc ) ) {
			$_proc = $proc;
		} else {
			$_proc = $GLOBALS['sql']->get_link( 'procrec', $proc );
		}

		// If there is no payer, return true
		if (!$payer) { return true; }
		if ($_proc['proccurcovid'] == $payer) { return true; }
		return false;
	} // end method

	// Method: get_open_procedures_by_date_and_patient
	//
	//	Get open procedures by date and patient id criteria
	//
	// Parameters:
	//
	//	$patient - Patient id key
	//
	//	$date - Date to limit. If none (or a NULL date), date
	//	is disregarded.
	//
	// Returns:
	//
	//	Array of procedure keys
	//
	public function get_open_procedures_by_date_and_patient ( $patient, $date ) {
		static $_cache;
		if (!isset($_cache[$patient][$date])) {
			$query = "SELECT * FROM procrec ".
				"WHERE ".
				( $date ? "procdt='".addslashes($date)."' AND " :
				"" )." procpatient='".addslashes($patient)."'";
			//print "query = $query<br/>\n";
			$result = $GLOBALS['sql']->queryAll ( $query );
			foreach ( $result AS $r ) {
				$procedures[] = $r;
			}
			$_cache[$patient][$date] = $procedures;
		}
		return $_cache[$patient][$date];
	} // end method get_open_procedures_by_date_and_patient

	// Method: get_procedure
	//
	//	Get procedure record from the current object, or retrieve
	//	a procedure object based on a passed key.
	//
	// Parameters:
	//
	//	$id - (optional) Procedure id key
	//
	// Returns:
	//
	//	Associative array containing procedure record information.
	//
	public function get_procedure ( $id = NULL ) {
		if ($id != NULL) {
			return $GLOBALS['sql']->get_link( 'procrec', $id );
		} else {
			return $this->local_record; 
		}
	} // end method get_procedure

} // end class Procedure

?>
