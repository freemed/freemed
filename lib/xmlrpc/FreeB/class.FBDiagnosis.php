<?php
	// $Id$
	// $Author$

class FBDiagnosis {

	function ICD9Code ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		return freemed::get_link_field($diag, 'icd', 'icd9code');
	} // end method ICD9Code

	function ICD10Code ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		return freemed::get_link_field($diag, 'icd', 'icd10code');
	} // end method ICD10Code

	function RelatedToHCFA ( $diagkey ) {
		// TODO: slot 10 in ICD code
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		return ' '; // hack until we fix the type detection or get
				// this to actually do something - Jeff
	} // end method RelatedToHCFA

	function isRelatedToAutoAccident ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');
		$eoc_r = freemed::get_link_rec($eoc, 'eoc');

		// Determine from data
		return ($eoc_r['eocrelauto'] == 'yes');
	} // end method isRelatedToAutoAccident

	function AutoAccidentState ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');
		return $eoc_r['eocrelautostpr'];
	} // end method AutoAccidentState

	function isRelatedToOtherAccident ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');

		// Determine from data
		return ($eoc_r['eocrelother'] == 'yes');
	} // end method isRelatedToOtherAccident

	function isRelatedToEmployment ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');
		$eoc_r = freemed::get_link_rec($eoc, 'eoc');

		// Determine from data
		return ($eoc_r['eocrelemp'] == 'yes');
	} // end method isRelatedToEmployment

	function DateOfOnset ( $diagkey ) {
		return FBDiagnosis::DateOfFirstOccurrence($diagkey);
	} // end method DateOfOnset

	function DateOfFirstOccurrence ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');
		list ($y, $m, $d) = explode ('-', $eoc_r['eocstartdate']);
		if (strlen($y) < 4) { 
			return CreateObject('PHP.xmlrpcval', '00000000T00:00:00', xmlrpcDateTime); 
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00', xmlrpcDateTime);
	} // end method DateOfFirstOccurrence

	function DateOfFirstSymptom ( $diagkey ) {
		// TODO: Think FreeMED tracks this as date of first occurrence
		return FBDiagnosis::DateOfFirstOccurrence($diagkey);
	} // end method DateOfFirstSymptom

	function isFirstOccurrence ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');

		// FIXME FIXME FIXME
		
		// SUCH a hack.... if the procedure was on the start date,
		// it's the first occurrance. Probably broken in some cases.
		if ($eoc_r['startdate'] == $p['procdt']) {
			return true;
		} else {
			return false;
		}
	} // end method isFirstOccurrence

	function isCantWork ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');

		// Determine from data
		return ( ($eoc_r['eocdistype'] > 0) and ($eoc_r['eocdistype'] < 4) );
	} // end method isCantWork

	function DateOfCantWorkStart ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );

		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');
		list ($y, $m, $d) = explode ('-', $eoc_r['eocdisfromdt']);
		if (strlen($y) < 4) { 
			return CreateObject('PHP.xmlrpcval', '00000000T00:00:00', xmlrpcDateTime); 
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00', xmlrpcDateTime);
	} // end method DateOfCantWorkStart

	function DateOfCantWorkEnd ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// Get related EOC
		//$p = freemed::get_link_rec($prockey, 'procrec');
		//$e = explode (':', $p['proceoc']);
		//if (!$e[0]) { return false; }
		//$eoc = freemed::get_link_rec($e[0], 'eoc');

		$eoc_r = freemed::get_link_rec($eoc, 'eoc');
		list ($y, $m, $d) = explode ('-', $eoc_r['eocdistodt']);
		if (strlen($y) < 4) { 
			return CreateObject('PHP.xmlrpcval', '00000000T00:00:00', xmlrpcDateTime); 
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00', xmlrpcDateTime);
	} // end method DateOfCantWorkEnd

	function LocalUseHCFA ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		// TODO: What does this thing do?
		return '';
	} // end method LocalUseHCFA

	// Internal Helper Functions -----------------------------------

	function _ExplodeParameters ( $joint_key ) {
		if (!(strpos($joint_key, ',') === false)) {
			// If this is a joint key
			return explode ( ',', $joint_key );
		} else {
			// Otherwise just pass a null EOC key
			return array ( NULL, $joint_key );
		}
	} // end method _ExplodeParameters

} // end class FBDiagnosis

?>
