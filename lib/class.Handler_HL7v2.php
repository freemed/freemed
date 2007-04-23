<?php
	// $Id$
	// $Author$

// Class: FreeMED.Handler_HL7v2
//
//	Superclass to encapsulate HL7 v2.3 message handlers. Contains
//	methods to aid in translating HL7 segments to appropriate
//	FreeMED database links.
//
class Handler_HL7v2 {

	var $parser;

	// Method: Handler_HL7v2 constructor
	//
	// Parameters:
	//
	//	$parser - Passed parser object of type Parser_HL7v2
	//
	function Handler_HL7v2 ($parser) {
		$this->parser = &$parser;
	}

	function Type () {
		return false;
	}

	//----- Internal methods

	// Method: Handler_HL7v2->_ConvertDate
	//
	//	Convert date from HL7 v2.3 date format to standard
	//	SQL date format.
	//
	// Parameters:
	//
	//	$string - HL7 date format
	//
	// Returns:
	//
	//	SQL date formatted date.
	//
	function _ConvertDate ($string) {
		// Handle invalid dates
		if (strlen($string) != 8) return '';
		// Seperate out by components
		$y = substr($string, 0, 4);
		$m = substr($string, 4, 2);
		$d = substr($string, 6, 2);
		// ... and reassemble
		return $y . '-' . $m . '-' . $d;
	} // end method _ConvertDate

	// Method: Handler_HL7v2->_PIDToPatient
	//
	//	Determine patient identifier by PID segment.
	//
	// Parameters:
	//
	//	$pid - HL7v2 PID segment
	//
	// Returns:
	//
	//	Patient identifier, or 0 if none is found.
	//
	function _PIDToPatient ($pid) {
		$lname = $pid[HL7v2_PID_NAME][HL7v2_PID_NAME_LAST];
		$fname = $pid[HL7v2_PID_NAME][HL7v2_PID_NAME_FIRST];
		$mname = $pid[HL7v2_PID_NAME][HL7v2_PID_NAME_MIDDLE];
		$dob   = $this->_ConvertDate($pid[HL7v2_PID_DATEOFBIRTH]);
		$query = "SELECT * FROM patient WHERE ".
			"ptlname LIKE '".addslashes($lname)."' AND ".
			"ptfname LIKE '".addslashes($fname)."' AND ".
			"ptdob = '".addslashes($dob)."' AND ".
			"ptarchive = 0";
		$result = $GLOBALS['sql']->query($query);
		if (!$GLOBALS['sql']->results($result)) {
			return 0; // false.... none found
		}
		// If we found it, return the id
		if ($GLOBALS['sql']->num_rows($result)) {
			$r = $GLOBALS['sql']->fetch_array($result);
			return stripslashes($r['id']);
		} else {
			die('_PIDToPatient - Need smarter algorithm ... several patients found in search.');
		}
	} // end method _PIDToPatient

	// Method: Handler_HL7v2->_IN1ToPayer
	//
	//	Resolve or create and resolve a payer record (insco table)
	//	from an HL7 v2.3 IN1 segment.
	//
	// Parameters:
	//
	//	$in1 - IN1 segment array
	//
	// Returns:
	//
	//	Id number for new or old record.
	//
	function _IN1ToPayer ( $in1 ) {
		// Check for company name
		$result = $GLOBALS['sql']->query( "SELECT * FROM insco WHERE LOWER(insconame)=LOWER('".$in1[4]."')" );
		if ($GLOBALS['sql']->num_rows( $result ) > 1 ) {
			// Further qualify
			//$result = $GLOBALS['sql']->query( "SELECT * FROM insco WHERE LOWER(insconame)=LOWER('".$in1[4]."')" );
			die("TODO: futher qualify\n");
		} elseif ($GLOBALS['sql']->num_rows( $result ) == 1 ) {
			// Found, return
			$r = $GLOBALS['sql']->fetch_array( $result );
			return $r['id'];
		} else {
			// Create, not found
			$query = $GLOBALS['sql']->insert_query(
				'insco',
				array (
					'insconame' => $in1[4],
					'inscoaddr1' => $in1[5][0],
					'inscoaddr2' => $in1[5][1],
					'inscocity' => $in1[5][2],
					'inscostate' => $in1[5][3],
					'inscozip' => $in1[5][4],
					'inscophone' => $this->_StripToNumeric( $in1[7] )
				)
			);
			return $GLOBALS['sql']->last_record ( $result );
		}
	} // end method _IN1ToPayer

	// Method: Handler_HL7v2->_ResolveICDCode
	function _ResolveICDCode ( $code ) {
		$result = $GLOBALS['sql']->query( "SELECT id FROM icd9 WHERE icd9code='".addslashes($code)."' " );
		$r = $GLOBALS['sql']->fetch_array ( $result );
		return $r['id'];
	} // end method _ResolveICDCode

	// Method: Handler_HL7v2->_StripToNumeric
	//
	//	Strip all non-numeric characters from a string
	//
	// Parameters:
	//
	//	$string - Original string
	//
	// Returns:
	//
	//	Numeric string.
	//
	function _StripToNumeric ($string) {
		$target = '';
		for ($pos=0; $pos<strlen($string); $pos++) {
			switch (substr($string, $pos, 1)) {
				case '0': case '1': case '2': case '3':
				case '4': case '5': case '6': case '7':
				case '8': case '9':
					$target .= substr($string, $pos, 1);
					break;
				default: // do nothing
					break;
			}
		}
		//print "original = $string, stripped = $target<br/>\n";
		return $target;
	} // end method _StripToNumeric

	// Method: Handler_HL7v2->_FixPhoneNumber
	//
	//	Make sure phone number has area code when pulling into
	//	system.
	//
	//	WARNING! THIS MAY NOT WORK OUTSIDE THE UNITED STATES!
	//
	// Parameters:
	//
	//	$phone - Original phone number
	//
	// Returns:
	//
	//	Phone number, possibly with system default area code.
	//
	function _FixPhoneNumber ($phone) {
		if (strlen($phone) == 7) {
			return freemed::config_value('default_area_code').$phone;
		} else {
			return $phone;
		}
	} // end method _FixPhoneNumber

} // end class Handler_HL7v2

?>
