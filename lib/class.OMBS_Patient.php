<?php
	// $Id$
	// $Author$
	// Support for the Open Medical Billing System by Synitech

class OMBS_Patient {

	function OMBS_Patient ($patient) {
		// Handle either patient object or patient id
		if (is_object($patient)) {
			$this->patient = $patient;
		} else {
			$this->patient = CreateObject('FreeMED.Patient', $patient);
		}
	} // end constructor OMBS_Patient

	function generate () {
		$buffer = '';
		$buffer .= "\n <!-- Bill for ".prepare($this->patient->fullName())." ( ".$this->patient->local_record['ptid']." ) -->\n\n";
		$buffer .= "<BILL>\n";
		$buffer .= "<PATIENT>\n";
		$buffer .= "\t<CONTACT>\n";
		$buffer .= "\t<NAME>\n";
		$buffer .= "\t\t<FIRST>".prepare($this->patient->ptfname)."</FIRST>\n";
		$buffer .= "\t\t<LAST>".prepare($this->patient->ptlname)."</LAST>\n";
		$buffer .= "\t\t<MIDDLE>".prepare($this->patient->ptmname)."</MIDDLE>\n";
		$buffer .= "\t</NAME>\n";

		$buffer .= "\t<ADDRESS>\n";
		$buffer .= "\t\t<STREET>".prepare($this->patient->local_record['ptaddr1'])."</STREET>\n";
		$buffer .= "\t\t<CITY>".prepare($this->patient->local_record['ptcity'])."</CITY>\n";
		$buffer .= "\t\t<STATE>".prepare($this->patient->local_record['ptstate'])."</STATE>\n";
		$buffer .= "\t\t<ZIP>".prepare($this->patient->local_record['ptzip'])."</ZIP>\n";
		$buffer .= "\t</ADDRESS>\n";

		$buffer .= "\t<TELEPHONE>\n";
		if ($this->patient->local_record['pthphone']) {
			// Only display phone if it exists
			$buffer .= "\t\t<FULLPHONE>".$this->_PhoneFormat($this->patient->local_record['pthphone'])."</FULLPHONE>\n";
		}
		$buffer .= "\t</TELEPHONE>\n";

		$buffer .= "\t</CONTACT>\n";

		// Date of birth
		$buffer .= $this->_DateToXML($this->patient->ptdob, 'DOB');

		// Gender
		if ($this->patient->isMale()) { $buffer .= "\t<SEX>Male</SEX>\n"; }
		if ($this->patient->isFemale()) { $buffer .= "\t<SEX>Female</SEX>\n"; }

		$buffer .= "</PATIENT>\n";

		// Physician
		if ($this->patient->local_record['ptdoc']) {
			$buffer .= $this->_PhysicianToXML($this->patient->local_record['ptdoc'], 0);
		}

		// Get procedures
		$procedures = $this->_GetProcedures();
		foreach ($procedures AS $__garbage => $procedure) {
			// Generate procedure
			$buffer .= $this->_ServiceToXML($procedure);
		}
		
		$buffer .= "</BILL>\n";

		return $buffer;
	} // end function OMBS_Patient->generate

	//----- Internal functions

	function _DateToXML ( $date, $element_name='STARTDATE', $tab = 1 ) {
		// Render tabs (for clean look)
		$t = ''; for ($i = 1; $i <= $tab; $i++) $t .= "\t";
		$buffer .= $t."<".$element_name.">\n";
		$buffer .= $t."\t<MONTH type=\"numeric\">".substr($date, 5, 2)."</MONTH>\n";
		$buffer .= $t."\t<DAY>".substr($date, 8, 2)."</DAY>\n";
		$buffer .= $t."\t<YEAR>".substr($date, 0, 4)."</YEAR>\n";
		$buffer .= $t."</".$element_name.">\n";
		return $buffer;
	}

	function _FacilityToXML ( $facility ) {
		// Get facility record
		$f = freemed::get_link_rec('facility', $facility);
		
		$buffer .= "<FACILITY>\n";
		$buffer .= "\t<FACILITYNAME>".prepare($f['psrname'])."</FACILITYNAME>\n";

		// Address information
		
		$buffer .= "\t<ADDRESS>\n";
		$buffer .= "\t\t<STREET>".prepare($f['psraddr1'])."</STREET>\n";
		$buffer .= "\t\t<CITY>".prepare($f['psrcity'])."</CITY>\n";
		$buffer .= "\t\t<STATE>".prepare($f['psrstate'])."</STATE>\n";
		$buffer .= "\t\t<ZIP>".prepare($f['psrzip'])."</ZIP>\n";
		$buffer .= "\t</ADDRESS>\n";

		// Telephone
		if (!empty($f['psrphone'])) {
			$buffer .= "\t<TELEPHONE>\n";
			$buffer .= "\t\t<FULLPHONE>".$this->_PhoneFormat($f['psrphone'])."</FULLPHONE>\n";
			$buffer .= "\t</TELEPHONE>\n";
		}

		$buffer .= "</FACILITY>\n";
		return $buffer;
	}

	function _GetProcedures ( ) {
		global $sql;
		// Get all procedures
		$query = "SELECT * FROM procrec ".
			"WHERE procbillable = '0' AND ".
				"procbilled = '0' AND ".
				"procpatient = '".addslashes($this->patient->local_record['id'])."' ".
			"ORDER BY procpos, procphysician, procrefdoc, ".
				"proceoc, procclmtp, procdt";
		$result = $sql->query($query);
		if ($sql->results($result)) {
			$results = array ();
			while ($r = $sql->fetch_array($result)) {
				$results[] = $r['id'];
			}
			return $results;
		} else {
			return array ();
		}
	}

	function _PhoneFormat ( $phone ) {
		$area_code = substr($phone, 0, 3);
		$prefix    = substr($phone, 3, 3);
		$ext       = substr($phone, 6, 4);
		return '1-'.$area_code.'-'.$prefix.'-'.$ext;
	} // end function OMBS_Patient->_PhoneFormat

	function _PhysicianToXML ( $physician, $tab = 1 ) {
		$p = freemed::get_link_rec($physician, 'physician');
		$t = ''; for ($i=1; $i<=$tab; $i++) { $t .= "\t"; }

		$buffer .= $t."<PHYSICIAN>\n";
		$buffer .= $t."\t<CONTACT>\n";
		$buffer .= $t."\t<NAME>\n";
		$buffer .= $t."\t\t<FIRST>".prepare($p['phyfname'])."</FIRST>\n";
		$buffer .= $t."\t\t<MIDDLE>".prepare($p['phymname'])."</MIDDLE>\n";
		$buffer .= $t."\t\t<LAST>".prepare($p['phylname'])."</LAST>\n";
		$buffer .= $t."\t</NAME>\n";
		$buffer .= $t."\t</CONTACT>\n";
		$buffer .= $t."\t<FEDERALTAXID>".prepare($p['phyupin'])."</FEDERALTAXID>\n";
		$buffer .= $t."</PHYSICIAN>\n";
		return $buffer;
	} // end function OMBS_Patient->_PhysicianToXML

	function _PlanToXML ( $plan ) {

	} // end function OMBS_Patient->_PlanToXML

	function _ServiceToXML ( $procedure ) {
		$p = freemed::get_link_rec($procedure, 'procrec');

		$buffer .= "<SERVICE>\n";

		// <CODE> for diagnoses and procedural codes
		$buffer .= "\t<CODE>\n";

		// Add start date
		$buffer .= $this->_DateToXML($p['procdt'], 'STARTDATE', 2);

		// Render procedural code
		$buffer .= "\t\t<PROCEDURECODE>".freemed::get_link_field(
				$p['proccpt'],
				'cpt',
				'cptcode'
			)."</PROCEDURECODE>\n";

		// Loop through diagnoses
		for ($i=1; $i<=4; $i++) {
			// If it exists, add it
			if ($p['procdiag'.$i] > 0) {
				$buffer .= "\t\t<DIAGNOSIS>".
					freemed::get_link_field(
						$p['procdiag'.$i],
						'icd9',
						'icd9code'
					)."</DIAGNOSIS>\n";
			}
		}

		// <CODE> element has to contain both proc codes and diags
		$buffer .= "\t</CODE>\n";

		// Derive this based on actual service
		$buffer .= "\t<AMOUNTPAID>".($p['procamtpaid'] + 0).
				"</AMOUNTPAID>\n";

		// Footer
		$buffer .= "</SERVICE>\n";
		return $buffer;
	} // end function OMBS_Patient->_ServiceToXML

} // end class OMBS_Patient

?>
