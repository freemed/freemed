<?php
	// $Id$
	// $Author$

class FBDiagnosis {
/*      Function: ICD9Code

        Returns:

        icd9code record from the icd9 table

*/ 
	function ICD9Code ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		return freemed::get_link_field($diag, 'icd9', 'icd9code');
	} // end method ICD9Code

/*      Function: ICD10Code

        Returns:

        icd10code record from the icd9 table

*/ 
	function ICD10Code ( $diagkey ) {
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		return freemed::get_link_field($diag, 'icd9', 'icd10code');
	} // end method ICD10Code

/*      Function: RelatedToHCFA

        Returns:

        static ' '

*/ 
	function RelatedToHCFA ( $diagkey ) {
		// TODO: slot 10 in ICD code
		list ($eoc, $diag) = FBDiagnosis::_ExplodeParameters( $diagkey );
		return ' '; // hack until we fix the type detection or get
				// this to actually do something - Jeff
	} // end method RelatedToHCFA

/*      Function: isRelatedToAutoAccident

        Returns:

        returns true if the eocrelauto field in the eoc table is set to 'yes'.

*/ 
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


/*      Function: AutoAccidentState

        Returns:

        eocrelautostpr record from the eoc table.

*/ 
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

/*      Function: isRelatedToOtherAccident

        Returns:

        returns true if eocrelother from the eoc table is set to 'yes'.

*/ 
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

/*      Function: isRelatedToEmployment

        Returns:

        returns true if the eocrelemp record from the eoc table is set to 'yes'.

*/ 
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

/*      Function: DateOfOnset

        Currently a wrapper to DateOfFirstOccurrence

*/ 
	function DateOfOnset ( $diagkey ) {
		return FBDiagnosis::DateOfFirstOccurrence($diagkey);
	} // end method DateOfOnset

/*      Function: DateOfFirstOccurrence

        Returns:

        eocstartdate record from the eoc table

*/ 
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


/*      Function: DateOfFirstSymptom

        This is currently a wrapper to DateOfFirstOccurrence

*/ 
	function DateOfFirstSymptom ( $diagkey ) {
		// TODO: Think FreeMED tracks this as date of first occurrence
		return FBDiagnosis::DateOfFirstOccurrence($diagkey);
	} // end method DateOfFirstSymptom


/*      Function: isFirstOccurence

        BROKEN there is no way that this function can know the procedure.

*/ 
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


/*      Function: isCantWork

        Returns:

        true if the eocdistype from the eoc table is above zero and below four. 
	TODO: this function violates principle of encapsulation by assuming that the values
	of eocdistype cannot change. 

*/ 
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

/*      Function: DateOfCantWorkStart

        Returns:

        eocdisfromdt record from the eoc table. TODO: this assumes that disability is 
	the same thing as not being able to work. Is this always correct?

*/ 
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

/*      Function: DateOfCantWorkEnd

        Returns:

        eocdistodt record from the eoc table

*/ 
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

	// Internal Helper Functions -----------------------------------

/*      Function: _ExplodeParameters

	Internal FreeMED function to handle the 'diagkeys' that are used as arguments to the FBDiagnosis functions. The FreeB "Diagnosis" object maps to two different objects in FreeMED, the eoc (episode of care) and the diagnosis record, which is FreeMEDs internal ICD records. As a result, when FreeB requests a diagnosis key, FreeMED sends a comma delimited list of the two keys. This function breaks them apart when they are returned to FreeMED. 

*/ 
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
