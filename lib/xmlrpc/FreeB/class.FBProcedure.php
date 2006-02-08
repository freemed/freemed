<?php
	// $Id$
	// $Author$

class FBProcedure {

	function CPT4Code ( $procedure ) {
		$c_id = freemed::get_link_field($procedure, 'procrec', 'proccpt');
		return freemed::get_link_field($c_id, 'cpt', 'cptcode');
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
		$p = freemed::get_link_rec($procedure, 'procrec');
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

	// Method: CPTModifier
	//
	//	Get text name for the current CPT code modifier.
	//
	// Parameters:
	//
	//	$proceduer - Procedure key
	//
	// Returns:
	//
	//	Text name for the current CPT code modifier
	//
	function CPTModifier ( $procedure ) {
		$c_id = freemed::get_link_field($procedure, 'procrec', 'proccptmod');
		return freemed::get_link_field($c_id, 'cptmod', 'cptmod');
	} // end method CPTModifier

	function CPTUnits ( $procedure ) {
		return freemed::get_link_field($procedure, 'procrec', 'procunits');
	} // end method CPTUnits

	// Method: WeightGrams
	//
	//	Get weight in grams for infants. This is not stored anywhere
	//	currently, so it is stubbed with a 0.
	//
	// Parameters:
	//
	//	$procedure - Procedure key
	//
	function WeightGrams ( $procedure ) {
		return 0; // kludge ... have to fix this
	} // end method WeightGrams
	
	// Method: ProcArray
	//
	//	Return a list of procedures to be billed from a billing
	//	key. This data is deserialized from the billkey table.
	//
	// Parameters:
	//
	//	$billkey - Billkey
	//
	// Returns:
	//	Array of procedure keys
	//
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

		// Deserialize from BillKey, and return the procedures...
		// This is kept in a special table, with important information
		// regarding if the bill was sent, etc.
		$key_rec = freemed::get_link_rec($billkey, 'billkey');
		$key = unserialize($key_rec['billkey']);
		if(LOG_FREEB){syslog(LOG_INFO, 'FBProcedure.ProcArray| size = '.count($key['procedures']));}
		return $key['procedures'];
	} // end method ProcArray

	// Method: DiagArray
	//
	//	Get array of diagnoses associated with a procedure key. The
	//	procedure key's diagnosis codes are joined with the episode
	//	of care (if present) to pass the EOC information.
	//
	// Parameters:
	//
	//	$proc - Procedure key
	//
	// Returns:
	//	Array of diagnosis keys
	//
	function DiagArray ( $proc ) {
		$p = freemed::get_link_rec($proc, 'procrec');

		// Split out episode of care to be prepended, since we're
		// using composite keys to do this ...
		$e = explode (':', $p['proceoc']);
		$eoc = $e[0];
		
		for ($i=1; $i<=4; $i++) {
			if ($p['procdiag'.$i] > 0) {
				$diag[] = $eoc.','.$p['procdiag'.$i];
			}
		}
		return $diag;
	} // end method DiagArray

	// Method: PatientKey
	//
	//	Get the patient key from the procedure key
	//
	// Parameters:
	//
	//	$proc - Procedure key
	//
	// Returns:
	//	Patient key
	//
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
			default:  return '0';  break;
		}
		if ($p['proccov'.$covnum] < 1) return '0';
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
		return $p['proccov'.$covnum];
	} // end method InsuredKey

	function OtherInsuredKey ( $procedure ) {
		// This is also a coverage key
		// The difference is this is the coverage AFTER the current coverage
		$p = freemed::get_link_rec($procedure, 'procrec');
		// Where is the bill going, by coverage
		switch ($p['proccurcovtp']) {
			case '2': $covnum = 3; break;
			case '3': $covnum = 4; break;
			case '4': $covnum = 0; break;
			case '1': default: $covnum = 2; break;
		}
		return $p['proccov'.$covnum];

		// TODO: Not sure where we would get this from.
	} // end method OtherInsuredKey

	function BillingContactKey ( $bill, $proc ) {
		$key_rec = freemed::get_link_rec($bill, 'billkey');
		$r = unserialize($key_rec['billkey']);
		if (is_array($r['contact'])) {
			return $r['contact'][0];
		} else {
			return $r['contact'];
		}
	} // end method BillingContactKey

	function isUsingBillingService ( $proc ) {
		return true;
	} // end method isUsingBillingService

	function BillingServiceKey ( $bill, $proc ) {
		$key_rec = freemed::get_link_rec($bill, 'billkey');
		$r = unserialize($key_rec['billkey']);
		if (is_array($r['service'])) {
			return $r['service'][0];
		} else {
			return $r['service'];
		}
	} // end method BillingServiceKey

	// Function: isUsingClearingHouse
	//
	//	Determines whether a clearinghouse is being used for an
	//	electronic transmission. Is stubbed to "yes", since there
	//	is currently no other way to submit them.
	//
	// Parameters:
	//
	//	$proc - Procedure key
	//
	// Returns:
	//	Boolean
	//
	function isUsingClearingHouse ( $proc ) {
		return true;
	} // end method isUsingClearingHouse

	function ClearingHouseKey ( $bill, $proc ) {
		$key_rec = freemed::get_link_rec($bill, 'billkey');
		$r = unserialize($key_rec['billkey']);
		if (is_array($r['clearinghouse'])) {
			return $r['clearinghouse'][0];
		} else {
			return $r['clearinghouse'];
		}
	} // end method ClearingHouseKey

	function MedicaidResubmissionCode ( $proc ) {
		$p = freemed::get_link_field($procedure, 'procrec', 'procmedicaidresub');
		return CreateObject('PHP.xmlrpcval', $p, xmlrpcString);
	} // end method MedicaidResubmissionCode

	function MedicaidOriginalReference ( $proc ) {
		$p = freemed::get_link_field($procedure, 'procrec', 'procmedicaidref');
		return CreateObject('PHP.xmlrpcval', $p, xmlrpcString);
	} // end method MedicaidOriginalReference

	function HCFALocalUse19 ( $proc ) {
		// need to work from payer key and physician
		$payer = FBProcedure::PayerKey($proc);
		$provider = FBProcedure::ProviderKey($proc);
		$map = unserialize(freemed::get_link_field(
			$payer, 'insco', 'inscoidmap'
		));
		return CreateObject(
			'PHP.xmlrpcval', 
			$map[$provider]['local19'],
			xmlrpcString
		);
	} // end method HCFALocalUse19

	function HCFALocalUse10d ( $proc ) {
		// need to work from payer key and physician
		$payer = FBProcedure::PayerKey($proc);
		$provider = FBProcedure::ProviderKey($proc);
		$map = unserialize(freemed::get_link_field(
			$payer, 'insco', 'inscoidmap'
		));
		return CreateObject(
			'PHP.xmlrpcval', 
			$map[$provider]['local10d'],
			xmlrpcString
		);
	} // end method HCFALocalUse10d

	// Method: AmountPaid
	//
	//	Gets the current amount paid, given a procedure, or
	//	array of procedures. Creates an SQL SUM() statement in
	//	the procrec table.
	//
	// Parameters:
	//
	//	$proc - Procedure key (or array of procedures)
	//
	// Returns:
	//	Amount paid from current procedure(s).
	//
	function AmountPaid ( $proc ) {
		$query = "SELECT SUM(proccharges) AS charges FROM procrec ".
			"WHERE FIND_IN_SET(id, '".join(',', $proc)."')";
		$result = $GLOBALS['sql']->query($query);
		$r = $GLOBALS['sql']->fetch_array($query);
		return $r['charges'];
	} // end method AmountPaid

	// Method: ProviderKey
	//
	//	Extract the provider key from the procedure key.
	//
	// Parameters:
	//
	//	$procedure - Procedure key
	//
	// Returns:
	//	Provider key.
	//
	function ProviderKey ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');
		return $p['procphysician'];
	} // end method ProviderKey

	// Method: FacilityKey
	//
	//	Extract the facility key from the procedure key.
	//
	// Parameters:
	//
	//	$procedure - Procedure key
	//
	// Returns:
	//	Facility key.
	//
	function FacilityKey ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');
		return $p['procpos'];
	} // end method FacilityKey

	// Method: PracticeKey
	//
	//	Extract the practice key from the procedure key. Currently
	//	returns the facility key, since they are the same.
	//
	// Parameters:
	//
	//	$procedure - Procedure key
	//
	// Returns:
	//	Practice key.
	//
	function PracticeKey ( $procedure ) {
		// TODO: Un-wrap this
		return FBProcedure::FacilityKey($procedure);
	} // end method PracticeKey

	// Method: TypeOfService
	//
	//	Get type of service code from procedure and insured keys.
	//	This is extracted by pulling the insurance code out of
	//	the insured key (coverage table), and comparing it against
	//	the cpttos table (cpt table), then pulling the actual
	//	name out of the type of service table (tos table).
	//
	// Parameters:
	//
	//	$procedure - Procedure key
	//
	//	$insured - Insured key
	//
	// Returns:
	//
	//	Text name of the type of service.
	//
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
		$hash = unserialize($cpt['cpttos']);
		if ($hash[$insco] > 0) {
			$tos_id = $hash[$insco];
		} else {
			$tos_id = $cpt['cptdeftos'];
		}

		// Return name
		return freemed::get_link_field($tos_id, 'tos', 'tosname');
	} // end method TypeOfService

	function PriorAuth ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		if ($p['procauth'] < 1) return '';
		$auth = freemed::get_link_rec($p['procauth'], 'authorizations');
		return $auth['authnum'];
	} // end method PriorAuth

	function isOutsideLab ( $prockey ) {
		$c = freemed::get_link_field($prockey, 'procrec', 'proclabcharges');
		// If charges are > 0, there is an outside lab
		return ($c > 0);
	} // end method isOutsideLab

	function OutsideLabCharges ( $prockey ) {
		$c = freemed::get_link_field($prockey, 'procrec', 'proclabcharges');
		// Force this to be passed as a "double"
		return CreateObject('PHP.xmlrpcval', $c, xmlrpcDouble);
	} // end method OutsideLabCharges

	function DateOfServiceStart ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		list ($y, $m, $d) = explode ('-', $p['procdt']);
		if (strlen($y) < 4) {
			return CreateObject('PHP.xmlrpcval', '00000000T00:00:00', xmlrpcDateTime);
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00', xmlrpcDateTime);
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
		if (strlen($y) < 4) {
			return CreateObject('PHP.xmlrpcval', '00000000T00:00:00', xmlrpcDateTime);
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00', xmlrpcDateTime);
	} // end method DateOfHospitalStart

	function DateOfHospitalEnd ( $prockey ) {
		$p = freemed::get_link_rec($prockey, 'procrec');
		list ($eoc, $__garbage) = explode (':', $p['proceoc']);
		$e = freemed::get_link_rec($eoc, 'eoc');
		list ($y, $m, $d) = explode ('-', $e['eochosdischrgdt']);
		if (strlen($y) < 4) {
			return CreateObject('PHP.xmlrpcval', '00000000T00:00:00', xmlrpcDateTime);
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00', xmlrpcDateTime);
	} // end method DateOfHospitalEnd

} // end class FBProcedure

?>
