<?php
	// $Id$
	// $Author$

class FBDiagnosis {

	function ICD9Code ( $diagkey ) {
		return freemed::get_link_field($diagkey, 'icd', 'icd9code');
	} // end method ICD9Code

	function ICD10Code ( $diagkey ) {
		return freemed::get_link_field($diagkey, 'icd', 'icd10code');
	} // end method ICD10Code

	function RelatedToHCFA ( $prockey, $diagkey ) {
		// TODO: slot 10 in ICD code
		return '';
	} // end method RelatedToHCFA

	function isRelatedToAutoAccident ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');

		// Determine from data
		return ($eoc['eocrelauto'] == 'yes');
	} // end method isRelatedToAutoAccident

	function AutoAccidentState ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');
		return $eoc['eocrelautostpr'];
	} // end method AutoAccidentState

	function isRelatedToOtherAccident ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');

		// Determine from data
		return ($eoc['eocrelother'] == 'yes');
	} // end method isRelatedToOtherAccident

	function isRelatedToEmployment ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');

		// Determine from data
		return ($eoc['eocrelemp'] == 'yes');
	} // end method isRelatedToEmployment

	function DateOfOnset ( $prockey, $diagkey ) {
		return FBDiagnosis::DateOfFirstOccurrence($prockey, $diagkey);
	} // end method DateOfOnset

	function DateOfFirstOccurrence ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');
		list ($y, $m, $d) = explode ('-', $eoc['eocstartdate']);
		return $y.$m.$d.'T00:00:00';
	} // end method DateOfFirstOccurrence

	function DateOfFirstSymptom ( $prockey, $diagkey ) {
		// TODO: Actually put this in
		return FBDiagnosis::DateOfFirstOccurrence($prockey, $diagkey);
	} // end method DateOfFirstSymptom

	function isFirstOccurrence ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');
		
		// SUCH a hack.... if the procedure was on the start date,
		// it's the first occurrance. Probably broken in some cases.
		if ($eoc['startdate'] == $p['procdt']) {
			return true;
		} else {
			return false;
		}
	} // end method isFirstOccurrence

	function isCantWork ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');

		// Determine from data
		return ( ($eoc['eocdistype'] > 0) and ($eoc['eocdistype'] < 4) );
	} // end method isCantWork

	function DateOfCantWorkStart ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');
		list ($y, $m, $d) = explode ('-', $eoc['eocdisfromdt']);
		return $y.$m.$d.'T00:00:00';
	} // end method DateOfCantWorkStart

	function DateOfCantWorkEnd ( $prockey, $diagkey ) {
		// Get related EOC
		$p = freemed::get_link_rec($prockey, 'procrec');
		$e = explode (':', $p['proceoc']);
		if (!$e[0]) { return false; }
		$eoc = freemed::get_link_rec($e[0], 'eoc');
		list ($y, $m, $d) = explode ('-', $eoc['eocdistodt']);
		return $y.$m.$d.'T00:00:00';
	} // end method DateOfCantWorkEnd

	function LocalUseHCFA ( $prockey, $diagkey ) {
		// TODO: What does this thing do?
		return '';
	} // end method LocalUseHCFA

} // end class FBDiagnosis

?>
