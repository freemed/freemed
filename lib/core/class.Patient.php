<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.Patient
//
//	Patient object container.
//
class Patient {
	var $local_record;                // stores basic patient record
	var $ptlname, $ptfname, $ptmname; // name variables
	var $ptdob;                       // date of birth
	var $ptdep;                       // patient dependencies
	var $ptsex;                       // gender
	var $ptmarital;                   // marital status
	var $ptreldep;                    // relation to guarantor
	var $id;                          // ID number
	var $ptempl;
	var $is_callin;                   // flag for call ins
	var $ptid;			  // local practice ID (chart num)

	// Constructor: org.freemedsoftware.core.Patient
	//
	// Parameters:
	//
	//	$patient_number - Database table identifier for the
	//	specified patient.
	//
	//	$is_callin - (optional) Boolean flag to specify if the
	//	patient is a "call-in" patient. Defaults to false.
	//
	function __construct ($_patient_number, $is_callin = false) { // constructor
		if (is_array($_patient_number)) {
			$patient_number = $_patient_number['id'];
		} else {
			$patient_number = $_patient_number;
		}

		// if the patient number is an error
		if ($patient_number<1) return false;

		// Check for cached record
		$_callin = ($is_callin ? "callin" : "patient");
		if (!isset($GLOBALS['__freemed']['cache'][$_callin][$patient_number])) {
			// Get new record ...
			if (is_array($_patient_number)) {
				$this->local_record = $_patient_number;
			} else {
				$this->local_record = $GLOBALS['sql']->get_link ( $_callin, $patient_number );
			}

			// ... and store in the cache
			$GLOBALS['__freemed']['cache'][$_callin][$patient_number] = $this->local_record;
		} else {
			// Retrieve from cache
			$this->local_record = $GLOBALS['__freemed']['cache'][$_callin][$patient_number];
		}

		// Check if this is supposed to be a call-in
		if (!$is_callin) {
			// Check for null ID, then trigger error
			if (!isset($this->local_record['id']))
				trigger_error ("Patient container: ".
					"invalid patient ID specified!",
					E_USER_ERROR
				);

		
			// pull records
			$this->ptlname      = $this->local_record["ptlname"  ];
			$this->ptfname      = $this->local_record["ptfname"  ];
			$this->ptmname      = $this->local_record["ptmname"  ];
			$this->ptdob        = $this->local_record["ptdob"    ];
			$this->ptsex        = $this->local_record["ptsex"    ];
			$this->ptmarital    = $this->local_record["ptmarital"];
			$this->ptid         = $this->local_record["ptid"     ];
			$this->id           = $this->local_record["id"       ];
			$this->ptempl       = $this->local_record["ptempl"   ];
     
			// callin set as false
			$this->is_callin    = false;
		} else {
			// pull records (limited for callins)
			$this->ptlname      = $this->local_record["cilname"];
			$this->ptfname      = $this->local_record["cifname"];
			$this->ptmname      = $this->local_record["cimname"];
			$this->ptdob        = $this->local_record["cidob"  ];
			$this->id           = $this->local_record["id"     ];
			$this->is_callin    = true;
		} // end if/else for is_callin
	} // end constructor Patient

	// Method: age
	//
	//	Produces a user-friendly age description.
	//
	// Parameters:
	//
	//	$date_to - (optional) Date to which the age is being
	//	calculated. Defaults to today.
	//
	// Returns:
	//
	//	Text string describing the patient's age.
	//
	function age ($date_to="") {
		return date_diff_display ($this->local_record["ptdob"],
		( ($date_to=="") ? date("Y-m-d") : $date_to ),
		__("year(s)"), __("month(s)"), __("day(s)"), __("ago"), __("ahead"));
	} // end method age

	// Method: numericAge
	//
	//	Produces a numeric age value.
	//
	// Returns:
	//
	//	Text string describing the patient's age.
	//
	function numericAge ( ) {
		return array_element(date_diff($this->local_record["ptdob"]), 0);
	} // end method numericAge

	// Method: date_of_last_procedure
	//
	//	Determine last procedure date for this patient object.
	//
	// Returns:
	//
	//	SQL date formatted string, or NULL if there is no previous
	//	procedure date.
	//
	function date_of_last_procedure ( ) {
		$query = "SELECT procdt FROM procrec ".
			"WHERE procpatient = '".addslashes($this->id)."' ".
			"ORDER BY procdt DESC";
		$result = $GLOBALS['sql']->queryOne ( $query );
		return $result;
	} // end method date_of_last_procedure

	// Method: fullName
	//
	//	Get the patient's full name.
	//
	// Parameters:
	//
	//	$with_dob - (optional) Boolean flag to determine if date
	//	of birth is included in the output. Defaults to false.
	//
	// Returns:
	//
	//	Text string containing patient's full name.
	//
	function fullName ($with_dob = false) {
		if (!$with_dob) {
			return $this->_fixcaps($this->ptfname)." ".
				$this->_fixcaps($this->ptmname).
				( strlen($this->ptmname) == 1 ? ". " : " " ).
				$this->_fixcaps($this->ptlname);
		} else {
			return $this->_fixcaps($this->ptlname).", ".
				$this->_fixcaps($this->ptfname)." ".
				$this->_fixcaps($this->ptmname).
				" [ ".$this->ptdob." ] ";
		} // end if for checking for date of birth
	} // end method fullName

	// Method: to_text
	//
	//	Convert record into textual representation.
	//
	// Returns:
	//
	//	Textual representation of record.
	//
	function to_text ( ) {
		return $this->_fixcaps($this->ptlname).", ".
			$this->_fixcaps($this->ptfname)." ".
			$this->_fixcaps($this->ptmname).
			" [ ".$this->ptdob." ] ".
			$this->ptid;
	} // end method to_text

	// Method: cityStateZip
	//
	//	Determine patient's city/state/zip address line.
	//
	// Returns:
	//
	//	String representation of patient's last address line.
	//
	function cityStateZip ( ) {
		return $this->local_record['ptcity'].', '.
			$this->local_record['ptstate'].' '.
			$this->local_record['ptzip'];
	} // end method cityStateZip

	// Method: dateOfBirth
	//
	//	Determine patient's date of birth.
	//
	// Returns:
	//
	//	String representation of patient's date of birth, using
	//	<fm_date_print> format.
	//
	function dateOfBirth ($no_parameters = "") {
		return fm_date_print ($this->ptdob);
	} // end method dateOfBirth

	// Method: dateOfBirthShort
	//
	//	Get short date of birth output.
	//
	// Returns:
	//
	//	MM/DD/YYYY date format for patient date of birth.
	//
	function dateOfBirthShort ( ) {
		if (!$this->ptdob) { return ''; }
		list ($y, $m, $d) = explode('-', $this->ptdob);
		return "${m}/${d}/${y}";
	} // end method dateOfBirthShort

	// Method: idNumber
	//
	//	Determine patient's database table id number.
	//
	// Returns:
	//
	//	SQL table id number for this patient object.
	//
	function idNumber ( ) {
		return $this->ptid;
	} // end method idNumber

	// Method: isEmployed
	//
	//	Determine patient's employment status.
	//
	// Returns:
	//
	//	Boolean value, whether patient is employed.
	//
	function isEmployed ( ) {
		return ($this->ptempl == "y");
	} // end method isEmployed

	// Method: isFemale
	//
	//	Determine whether patient is female.
	//
	// Returns:
	//
	//	Boolean value, whether patient is female.
	//
	function isFemale ( ) {
		return ($this->ptsex == "f");
	} // end method isFemale

	// Method: isMale
	//
	//	Determine whether patient is male.
	//
	// Returns:
	//
	//	Boolean value, whether patient is male.
	//
	function isMale ( ) {
		return ($this->ptsex == "m");
	} // end method isMale

	// Method: phoneNumber
	//
	// Returns:
	//
	//	Formatted phone number string
	//
	function phoneNumber ( ) {
		if (!$this->local_record['pthphone']) {
			return __("No Phone Number");
		}
		return freemed::phone_display($this->local_record['pthphone']);
	} // end method phoneNumber

	// Method: get_list_by_name
	//
	//	Get list of patient id keys based on first and
	//	last name provided
	//
	// Parameters:
	//
	//	$last - Last name substring
	//
	//	$first - First name substring
	//
	// Returns:
	//
	//	Array of patient id keys
	//
	function get_list_by_name ( $last, $first ) {
		$query = "SELECT ptlname, ptfname, ptmname, id ".
			"FROM patient WHERE ".
			"ptlname LIKE '%".addslashes($last)."%' AND ".
			"ptfname LIKE '%".addslashes($first)."%' ".
			"ORDER BY ptlname,ptfname,ptmname";
		$result = $GLOBALS['sql']->queryAll ( $query );
		foreach ( $result AS $r ) {
			$patients[] = $r['id'];
		} // end while loop
		return $patients;
	} // end method get_list_name

	// Method: get_procedures_to_bill
	//
	//	Get list of procedures to bill by patient
	//
	// Parameters:
	//
	//	$by_patient - (optional) Boolean. If this is false,
	//	all procedures which are billable in the system will
	//	be returned. Defaults to true.
	//
	// Returns:
	//
	//	Array of procedure ids.
	//
	function get_procedures_to_bill ( $by_patient = true ) {
		$s = "SELECT proc.id AS p ".
			"FROM patient AS pat, procrec AS proc ".
			"WHERE proc.procpatient = pat.id ".
			"AND proc.procbalcurrent > 0 ".
			"AND proc.proccurcovtp = '0' ".
			( $by_patient ? "AND pat.id = '".addslashes($this->id)."'" : "" );
		$procs = $GLOBALS['sql']->queryCol( $s );
		return $procs;
	} // end method get_procedures_to_bill

	// Method: _fixcaps
	//
	//	Produce proper capitalization of a string.
	//
	// Parameters:
	//
	//	$string - Original string.
	//
	// Returns:
	//
	//	Properly capitalized string.
	//
	protected function _fixcaps ( $string ) {
		$subs = array (
			'Ii' => 'II',
			'Iii' => 'III',
			'Iv' => 'IV'
		);
		$a = explode (' ', $string);
		foreach ($a AS $k => $v) {
			$a[$k] = ucfirst(strtolower($v));
			foreach ($subs AS $s_k => $s_v) {
				if ($a[$k] == $s_k) { $a[$k] = $s_v; }
				if (substr($a[$k], 0, 2) == 'Mc') {
					$a[$k] = 'Mc'.ucfirst(strtolower(substr($a[$k], -(strlen($a[$k])-2))));
				}
			}
		} // end foreach
		return join(' ', $a);
	} // end method _fixcaps

} // end class Patient

?>
