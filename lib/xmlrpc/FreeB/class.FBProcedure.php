<?php
	// $Id$
	// $Author$

class FBProcedure {

	function CPT4Code ( $procedure ) {
		$c_id = freemed::get_link_field($procedure, 'procrec', 'proccpt');
		return freemed::get_link_field($c_id, 'cpt', 'cptname');
	} // end method CPT4Code

	function CPT5Code ( $procedure ) {
		// TODO: This needs to be real
		return FBProcedure::CPT4Code($procedure);
	} // end method CPT5Code

	function CPTCOB ( $procedure ) {
		// This is broken behavior, but will work for the time being
		return false;
	} // end method CPTCOB

	function CPTCharges ( $procedure ) {
		$p = freemed::get_link_rec($proc, 'procrec');
		return $p['proccharges'];
	} // end method CPTCharges

	function CPTCount ( $procedure ) {
		// This is broken behavior, but will work for the time being
		return 1;
	} // end method CPTCount

	function CPTEmergency ( $procedure ) {
		// This is broken behavior, but will work for the time being
		return false;
	} // end method CPTEmergency

	function CPTEPSDT ( $procedure ) {
		// This is broken behavior, but will work for the time being
		return false;
	} // end method CPTEPSDT

	function CPTModifier ( $procedure ) {
		$c_id = freemed::get_link_field($procedure, 'procrec', 'proccptmod');
		return freemed::get_link_field($c_id, 'cptmod', 'cptmod');
	} // end method CPTModifier

	function CPTUnits ( $procedure ) {
		return freemed::get_link_field($procedure, 'procrec', 'procunits');
	} // end method CPTUnits

	function WeightGrams ( $procedure ) {
		return 0; // kludge ... have to fix this
	} // end method WeightGrams

	function ProcArray ( $billkey ) {
		// This, provided a "billing number" should ideally return a
		// subset of what should be billed. For now, we break that
		// behavior and return everything that needs to be billed.
		//$query = "SELECT id,procpatient FROM procrec WHERE ".
		//	"proccurcovtp > 0 AND ".
		//	"procbalcurrent > 0 AND ".
		//	"procbillable = '0' ".
		//	"ORDER BY procpatient,procdt";
		//$result = $GLOBALS['sql']->query($query);
		//
		//if (!$GLOBALS['sql']->results($result)) return array ( );
		//
		//while ($row = $GLOBALS['sql']->fetch_array($result)) {
		//	$p[] = $row['id'];
		//}
		//
		//return $p;

		// Deserialize from BillKey, and return the procedures
		$key = unserialize($billkey);
		return $key['procedures'];
	} // end method ProcArray

	function DiagArray ( $proc ) {
		$p = freemed::get_link_rec($proc, 'procrec');

		// Split out episode of care to be prepended, since we're
		// using composite keys to do this ...
		$e = explode (':', $proc['proceoc']);
		$eoc = $e[0];
		
		for ($i=1; $i<=4; $i++) {
			if ($p['procdiag'.$i] > 0) {
				$diag[] = $eoc.','.$p['procdiag'.$i];
			}
		}
		return $diag;
	} // end method DiagArray

	function PatientKey ( $proc ) {
		$p = freemed::get_link_rec($proc, 'procrec');
		return $p['procpatient'];
	} // end method PatientKey

	function PayerKey ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');
		// Where is the bill going, by coverage
		switch ($p['proccurcovtp']) {
			case '1': $covnum = 1; break;
			case '2': $covnum = 2; break;
			case '3': $covnum = 3; break;
			case '4': $covnum = 4; break;
			default: return false; break;
		}
		$coverage = freemed::get_link_rec($p['proccov'.$covnum], 'coverage');
		// Return the insurance company key
		return $coverage['covinsco'];
	} // end method PayerKey

	function SecondPayerKey ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');
		// Where is the bill going, by coverage
		switch ($p['proccurcovtp']) {
			case '1': $covnum = 2; break;
			case '2': $covnum = 3; break;
			case '3': $covnum = 4; break;
			default: return false; break;
		}
		if ($p['proccov'.$covnum] < 1) return false;
		$coverage = freemed::get_link_rec($p['proccov'.$covnum], 'coverage');

		// Return the insurance company key
		return $coverage['covinsco'];
	} // end method SecondPayerKey

	function InsuredKey ( $procedure ) {
		// This is actually the coverage key, since we don't
		// have a separate database table for insured people.
		$p = freemed::get_link_rec($procedure, 'procrec');
		// Where is the bill going, by coverage
		switch ($p['proccurcovtp']) {
			case '2': $covnum = 2; break;
			case '3': $covnum = 3; break;
			case '4': $covnum = 4; break;
			case '1': default: $covnum = 1; break;
		}
		return freemed::get_link_rec($p['proccov'.$covnum], 'coverage');
	} // end method InsuredKey

	function OtherInsuredKey ( $proc ) {
		// TODO: Not sure where we would get this from.
		return 0;
	} // end method OtherInsuredKey

	function BillingContactKey ( $bill, $proc ) {
		$r = unserialize($bill);
		return $bill['contact'];
	} // end method BillingContactKey

	function isUsingBillingService ( $proc ) {
		return true;
	} // end method isUsingBillingService

	function BillingServiceKey ( $bill, $proc ) {
		$r = unserialize($bill);
		return $bill['service'];
	} // end method BillingServiceKey

	function isUsingClearingHouse ( $proc ) {
		return true;
	} // end method isUsingClearingHouse

	function ClearingHouseKey ( $bill, $proc ) {
		$r = unserialize($bill);
		return $bill['clearinghouse'];
	} // end method ClearingHouseKey

	function MedicaidResubmissionCode ( $proc ) {
		// TODO: STUB
		return '';
	} // end method MedicaidResubmissionCode

	function MedicaidOriginalReference ( $proc ) {
		// TODO: STUB
		return '';
	} // end method MedicaidOriginalReference

	function HCFALocalUse19 ( $proc ) {
		// TODO: STUB
		return '';
	} // end method HCFALocalUse19

	function HCFALocalUse10d ( $proc ) {
		// TODO: STUB
		return '';
	} // end method HCFALocalUse10d

	function AmountPaid ( $proc ) {
		$query = "SELECT SUM(proccharges) AS charges FROM procrec ".
			"WHERE FIND_IN_SET(id, '".join(',', $proc)."')";
		$result = $GLOBALS['sql']->query($query);
		$r = $GLOBALS['sql']->fetch_array($query);
		return $r['charges'];
	} // end method AmountPaid

	function ProviderKey ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');
		return $p['procphysician'];
	} // end method ProviderKey

	function FacilityKey ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');
		return $p['procpos'];
	} // end method FacilityKey

	function PracticeKey ( $procedure ) {
		// TODO: Un-wrap this
		return FBProcedure::FacilityKey($procedure);
	} // end method PracticeKey

	function TypeOfService ( $procedure, $insured ) {
		// This is going to hurt. We go like this:
		// [procedure] -> [cptcode]
		// [coverage] -> [insco]
		// [cptcode -> map] -> [tos -> tosname]

		// Get basic data types (procedure, coverage, CPT code)
		$p = freemed::get_link_rec($procedure, 'procrec');
		$cpt = freemed::get_link_rec($p['proccpt'], 'cpt');
		$cov = freemed::get_link_rec($insured, 'coverage');

		// Now we have to get the insurance company number so
		// we can review the CPT hash
		$insco = $cov['covinsco'];

		// Check the hash, and if it isn't there, use the default
		$hash = explode (':', $cpt['cpttos']);
		if ($hash[$insco] > 0) {
			$tos_id = $hash[$insco];
		} else {
			$tos_id = $cpt['cptdeftos'];
		}

		// Return name
		return freemed::get_link_field($tos_id, 'tos');
	} // end method TypeOfService

	function PriorAuth ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		if ($p['procauth'] < 1) return '';
		$auth = freemed::get_link_rec($p['procauth'], 'authorizations');
		return $auth['authnum'];
	} // end method PriorAuth

	function isOutsideLab ( $prockey ) {
		// TODO: This needs a spot in the db. stub for now
		return false;
	} // end method isOutsideLab

	function OutsideLabCharges ( $prockey ) {
		// TODO: This needs a spot in the db. stub for now
		return 0.00;
	} // end method OutsideLabCharges

	function DateOfServiceStart ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		list ($y, $m, $d) = explode ('-', $p['procdt']);
		return $y.$m.$d.'T00:00:00';
	} // end method DateOfServiceStart

	function DateOfServiceEnd ( $prockey ) {
		// TODO: Hack needed for multiple day procedures
		return FBProcedure::DateOfServiceStart($prockey);
	} // end method DateOfServiceEnd

	function isHospitalized ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		list ($eoc, $__garbage) = explode (':', $p['proceoc']);
		$e = freemed::get_link_rec($eoc, 'eoc');
		return ($e['eochospital'] == 1);
	} // end method isHospitalized

	function DateOfHospitalStart ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		list ($eoc, $__garbage) = explode (':', $p['proceoc']);
		$e = freemed::get_link_rec($eoc, 'eoc');
		list ($y, $m, $d) = explode ('-', $e['eochosadmdt']);
		return $y.$m.$d.'T00:00:00';
	} // end method DateOfHospitalStart

	function DateOfHospitalEnd ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		list ($eoc, $__garbage) = explode (':', $p['proceoc']);
		$e = freemed::get_link_rec($eoc, 'eoc');
		list ($y, $m, $d) = explode ('-', $e['eochosdischrgdt']);
		return $y.$m.$d.'T00:00:00';
	} // end method DateOfHospitalEnd

} // end class FBProcedure

?>
