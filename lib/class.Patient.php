<?php
 // $Id$
 // $Author$

// class Patient
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

	function Patient ($patient_number, $is_callin = false) { // constructor
		// if the patient number is an error
		if ($patient_number<1) return false;

		// Check for cached record
		$_callin = ($is_callin ? "callin" : "patient");
		if (!isset($GLOBALS['__freemed']['cache'][$_callin][$patient_number])) {
			// Get new record ...
			$this->local_record = freemed::get_link_rec (
				$patient_number, $_callin
			);

			// ... and store in the cache
			$GLOBALS['__freemed']['cache'][$_callin][$patient_number] = $this->local_record;
		} else {
			// Retrieve from cache
			$this->local_record = $GLOBALS['__freemed']['cache'][$_callin][$patient_number];
		}

		// Check if this is supposed to be a call-in
		if (!$is_callin) {
			// Check for null ID, then trigger error
			if (!isset($this->local_record[id]))
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

	function age ($date_to="") {
		return date_diff_display ($this->local_record["ptdob"],
		( ($date_to=="") ? date("Y-m-d") : $date_to ),
		__("year(s)"), __("month(s)"), __("day(s)"), __("ago"), __("ahead"));
	} // end function Patient->age

	function fullName ($with_dob = false) {
		if (!$with_dob) {
			return $this->ptlname.", ".$this->ptfname." ".
			$this->ptmname;
		} else {
			return $this->ptlname.", ".$this->ptfname." ".
			$this->ptmname." [ ".$this->ptdob." ] ";
		} // end if for checking for date of birth
	} // end function Patient->fullName

	function dateOfBirth ($no_parameters = "") {
		return fm_date_print ($this->ptdob);
	} // end function Patient->dateOfBirth

	function idNumber ($no_parameters = "") {
		return ($this->ptid);
	} // end func idNumber

	function isEmployed ($no_parameters = "") {
		return ($this->ptempl == "y");
	} // end function Patient->isEmployed

	function isFemale ($no_parameters = "") {
		return ($this->ptsex == "f");
	} // end function Patient->isFemale

	function isMale ($no_parameters = "") {
		return ($this->ptsex == "m");
	} // end function Patient->isMale

} // end class Patient

?>
