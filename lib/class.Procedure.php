<?php
	// $Id$
	// $Author$

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

} // end class Procedure

?>
