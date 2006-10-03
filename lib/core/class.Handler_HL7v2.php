<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

// Class: org.freemedsoftware.core.Handler_HL7v2
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
		$result = $GLOBALS['sql']->queryOne( $query );

		// If we found it, return the id
		if ($result['id']) {
			return stripslashes($result['id']);
		} else {
			return 0;
		}
	} // end method _PIDToPatient

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
