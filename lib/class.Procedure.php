<?php
	// $Id$
	// $Author$

// Class: FreeMED.Procedure
class Procedure {

	function Procedure ( $id ) {
		$this->id = $id;
		$this->local_record = freemed::get_link_rec($id, 'procrec');
	} // end constructor Procedure

	function CurrentBalance ( ) {
		$total_payments = 0.00;
		$total_charges  = 0.00;

		// Process payment records
		$result = $GLOBALS['sql']->query(
			"SELECT * FROM payrec AS a, procrec AS b ".
			"WHERE b.id = a.payrecproc AND ".
			"a.payrecproc = '".addslashes($this->id)."'"
		);
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
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
	function check_for_payer ( $proc, $payer ) {
		if (is_array($proc)) {
			$_proc = $proc;
		} else {
			$_proc = freemed::get_link_rec($proc, 'procrec');
		}

		// If there is no payer, return true
		if (!$payer) { return true; }

		if ($_proc['proc'] == $payer) { return true; }

//		print $_proc['id']." != ".$payer."<br/>\n";
		return true; // KLUDGE FOR TESTING
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
	function get_open_procedures_by_date_and_patient ( $patient, $date ) {
		static $_cache;
		if (!isset($_cache[$patient][$date])) {
		$query = "SELECT * FROM procrec ".
			"WHERE ".
			( $date ? "procdt='".addslashes($date)."' AND " :
			"" )." procpatient='".addslashes($patient)."'";
		//print "query = $query<br/>\n";
		$result = $GLOBALS['sql']->query ( $query );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
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
	function get_procedure ( $id = NULL ) {
		if ($id != NULL) {
			return freemed::get_link_rec($id, 'procrec');
		} else {
			return $this->local_record; 
		}
	} // end method get_procedure

} // end class Procedure

?>
