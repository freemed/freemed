<?php
	// $Id$
	// $Author$

class FBInsured {

	function FirstName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covfname'];
	} // end method FirstName

	function MiddleName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covmname'];
	} // end method MiddleName

	function LastName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covlname'];
	} // end method LastName

	function StreetAddress ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covaddr1'];
	} // end method StreetAddress

	function City ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covcity'];
	} // end method City

	function State ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covstate'];
	} // end method State

	function Zipcode ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covzip'];
	} // end method Zipcode

	function DateOfBirth ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		$dob = $c['covdob'];
		list ($y, $m, $d) = explode('-', $dob);
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00',
				xmlrpcDateTime);
	} // end method DateOfBirth

	function Sex ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covsex'];
	} // end method Sex

	function ID ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covpatinsno'];
	} // end method ID

	function PlanName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covplanname'];
	} // end method PlanName

	function GroupName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covplanname'];
	} // end method GroupName

	function GroupNumber ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covpatgrpno'];
	} // end method GroupNumber

	function IsMale ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return (strtoupper($c['covsex']) == 'M');
	} // end method IsFemale

	function IsFemale ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return (strtoupper($c['covsex']) == 'F');
	} // end method IsFemale

	function PhoneCountry ( $cov ) {
		// TODO: i18n broken
		return '';
	} // end method PhoneCountry

	function PhoneArea ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// TODO: i18n broken
		return substr($c['covphone'], 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// TODO: i18n broken
		return substr($c['covphone'], 3, 7);
	} // end method PhoneNumber

	function PhoneExtension ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// TODO: i18n broken
		return substr($c['covphone'], 7, 4);
	} // end method PhoneExtension

	function isEmployed ( $cov ) {
		// TODO: don't store this anywhere?
		return false;
	} // end method isEmployed

	function EmployerName ( $cov ) {
		// TODO: don't store this anywhere?
		return '';
	} // end method EmployerName

	function isStudent ( $cov ) {
		// TODO: don't store this anywhere?
		return false;
	} // end method isStudent

	function SchoolName ( $cov ) {
		// TODO: don't store this anywhere?
		return '';
	} // end method SchoolName

	function isAssigning ( $cov ) {
		// If this isn't true, we don't bill
		// TODO: Look into this
		return true;
	} // end method isAssigning

} // end class FBInsured

?>
