<?php
	// $Id$
	// $Author$

class FBPatient {

	function FirstName ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptfname'];
	} // end method FirstName

	function MiddleName ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptmname'];
	} // end method MiddleName

	function LastName ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptlname'];
	} // end method LastName

	function Title ( $patient ) {
		//$p = CreateObject('_FreeMED.Patient', $patient);
		// TODO: Need to add this to patient record
		return '';
	} // end method Title

	function StreetAddress ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptaddr1'];
	} // end method StreetAddress

	function City ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptcity'];
	} // end method City

	function State ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptstate'];
	} // end method State

	function Zipcode ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptzip'];
	} // end method Zipcode

	function DateOfBirth ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		$dob = $p->local_record['ptdob'];
		list ($y, $m, $d) = explode('-', $dob);
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00',
				xmlrpcDateTime);
	} // end method DateOfBirth

	function Sex ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptgender'];
	} // end method Sex

	function SocialSecurityNumber ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptssn'];
	} // end method SocialSecurityNumber

	function PhoneCountry ( $patient ) {
		// TODO: Broken i18n
		return '';
	} // end method PhoneCountry

	function PhoneArea ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return substr($p['pthphone'], 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return substr($p['pthphone'], 3, 7);
	} // end method PhoneNumber

	function isMale ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return (strtoupper($p->local_record['ptgender']) == 'M');
	} // end method isMale

	function isFemale ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return (strtoupper($p->local_record['ptgender']) == 'F');
	} // end method isFemale

	function isSingle ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return ($p->local_record['ptmarital'] == 'single');
	} // end method isSingle

	function isMarried ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return ($p->local_record['ptmarital'] == 'married');
	} // end method isMarried

	function isMaritalOtherHCFA ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return (($p->local_record['ptmarital'] != 'married') &&
			($p->local_record['ptmarital'] != 'single'));
	} // end method isMaritalOtherHCFA

	function isEmployed ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return (($p->local_record['ptempl'] != 'y') &&
			($p->local_record['ptempl'] != 'p') &&
			($p->local_record['ptempl'] != 'm') &&
			($p->local_record['ptempl'] != 's'));
	} // end method isEmployed

	function isFullTimeStudent ( $patient ) {
		// TODO: Needs to pull from the database
		return false;
	} // end method isFullTimeStudent

	function isPartTimeStudent ( $patient ) {
		// TODO: Needs to pull from the database
		return false;
	} // end method isPartTimeStudent

	function CoverageCount ( $patient, $proc ) {
		$p = freemed::get_link_rec($proc, 'procrec');
		$count = 0;
		for ($i=1; $i<=4; $i++) {
			if ($p['proccov'.$i] > 0) { $count++; }
		}
		return $count;
	} // end method CoverageCount

	function ReferringPhysicianKey ( $patient ) {
		$p = freemed::get_link_rec($patient, 'patient');
		return $p['ptrefdoc'];
	} // end method ReferringPhysicianKey

	function isChildOfInsured ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return ($c['covrel'] == 'C');
	} // end method isChildOfInsured

	function isHusbandOfInsured ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return ($c['covrel'] == 'H');
	} // end method isHusbandOfInsured

	function isWifeOfInsured ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return ($c['covrel'] == 'W');
	} // end method isWifeOfInsured

	function isSelfOfInsured ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return ($c['covrel'] == 'S');
	} // end method isSelfOfInsured

	function isDivorceeOfInsured ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return ($c['covrel'] == 'DV');
	} // end method isDivorceeOfInsured

	function isOtherOfInsured ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return ! ( ($c['covrel'] == 'S') or
			($c['covrel'] == 'W') or
			($c['covrel'] == 'H') or
			($c['covrel'] == 'C') or
			($c['covrel'] == 'DV'));
	} // end method isOtherOfInsured

} // end class FBPatient

?>
