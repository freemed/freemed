<?php
	// $Id$
	// $Author$

class FBPatient {

	function Account ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptid'];
	} // end method Account

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
		return $p->local_record['ptsex'];
	} // end method Sex

	function SocialSecurityNumber ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return $p->local_record['ptssn'];
	} // end method SocialSecurityNumber

	function PhoneCountry ( $patient ) {
		// TODO: Broken i18n
		return CreateObject('PHP.xmlrpcval', '1', xmlrpcString);
	} // end method PhoneCountry

	function PhoneArea ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return CreateObject('PHP.xmlrpcval',
			substr($p->local_record['pthphone'], 0, 3),
			xmlrpcString);
	} // end method PhoneArea

	function PhoneNumber ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return CreateObject('PHP.xmlrpcval',
			substr($p->local_record['pthphone'], 3, 7),
			xmlrpcString);
	} // end method PhoneNumber

	function PhoneExtension ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return CreateObject('PHP.xmlrpcval',
			substr($p->local_record['pthphone'], 10, 4),
			xmlrpcString);
	} // end method PhoneExtension

	function isMale ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return (strtoupper($p->local_record['ptgender']) == 'M');
	} // end method isMale

	function isDead ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		return ($p->local_record['ptdead'] == '1');
	} // end method isDead

	function DateOfDeath ( $patient ) {
		$p = CreateObject('_FreeMED.Patient', $patient);
		list ($y, $m, $d) = explode('-', $p->local_record['ptdeaddt']);
		if (strlen($y) < 4) {
			$y = '0000'; $m = '00'; $d = '00';
		}
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00',
				xmlrpcDateTime);
	} // end method DateOfDeath

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

	function ReferringProviderKey ( $patient ) {
		$p = freemed::get_link_rec($patient, 'patient');
		return $p['ptrefdoc'];
	} // end method ReferringProviderKey

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

	function X12InsuredRelationship ( $pat, $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covrel'];
	} // end method X12InsuredRelationship

} // end class FBPatient

?>
