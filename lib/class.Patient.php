<?php
 // $Id$
 // $Author$

// class: FreeMED.Patient
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

	// method: Patient constructor
	//
	// Parameters:
	//
	//	$patient_number - Database table identifier for the
	//	specified patient.
	//
	//	$is_callin - (optional) Boolean flag to specify if the
	//	patient is a "call-in" patient. Defaults to false.
	//
	function Patient ($_patient_number, $is_callin = false) { // constructor
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
				$this->local_record = freemed::get_link_rec ( $patient_number, $_callin );
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

	// Method: Patient->age
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
	} // end function Patient->age

	// Method: Patient->date_of_last_procedure
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
			"WHERE procpatient = '".addslashes($this->patient_number)."' ".
			"ORDER BY procdt DESC";
		$res = $GLOBALS['sql']->query ( $query );
		$r = $GLOBALS['sql']->fetch_array ( $res );
		return $r['procdt'];
	} // end method date_of_last_procedure

	// Method: Patient->fullName
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
	} // end function Patient->fullName

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

	// Method: Patient->dateOfBirth
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
	} // end function Patient->dateOfBirth

	// Method: Patient->idNumber
	//
	//	Determine patient's database table id number.
	//
	// Returns:
	//
	//	SQL table id number for this patient object.
	//
	function idNumber ($no_parameters = "") {
		return ($this->ptid);
	} // end func idNumber

	// Method: Patient->isEmployed
	//
	//	Determine patient's employment status.
	//
	// Returns:
	//
	//	Boolean value, whether patient is employed.
	//
	function isEmployed ($no_parameters = "") {
		return ($this->ptempl == "y");
	} // end function Patient->isEmployed

	// Method: Patient->isFemale
	//
	//	Determine whether patient is female.
	//
	// Returns:
	//
	//	Boolean value, whether patient is female.
	//
	function isFemale ($no_parameters = "") {
		return ($this->ptsex == "f");
	} // end function Patient->isFemale

	// Method: Patient->isMale
	//
	//	Determine whether patient is male.
	//
	// Returns:
	//
	//	Boolean value, whether patient is male.
	//
	function isMale ($no_parameters = "") {
		return ($this->ptsex == "m");
	} // end function Patient->isMale

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
		// TODO: Fix non-US formatting...
		return '('.
			substr($this->local_record['pthphone'], 0, 3).
			') '.
			substr($this->local_record['pthphone'], 3, 3).
			'-'.
			substr($this->local_record['pthphone'], 6, 4);
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
		$result = $GLOBALS['sql']->query ( $query );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$patients[] = $r['id'];
		} // end while loop
		return $patients;
	} // end method get_list_name

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
	function _fixcaps ( $string ) {
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
