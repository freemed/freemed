<?php
	// $Id$
	// $Author$

class FBFacility {

	function Name ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrname'];
	} // end method Name

	function StreetAddress ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psraddr1'];
	} // end method StreetAddress

	function City ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrcity'];
	} // end method City

	function State ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrstate'];
	} // end method State

	function Zipcode ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrzip'];
	} // end method Zipcode

	function PhoneCountry ( $key ) {
		//$f = freemed::get_link_field($key, 'facility', 'psrphone');
		// TODO: Broken behavior
		return '';
	} // end method PhoneCountry

	function PhoneArea ( $key ) {
		// TODO: Need to handle i18n with areas
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		if (strlen($f) < 3) { return '   '; }
		return substr($f, 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		if (strlen($f) < 3) { return '       '; }
		return substr($f, 3, 7);
	} // end method PhoneNumber

	function PhoneExtension ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		if (strlen($f) < 3) { return '    '; }
		return substr($f, 10, 4);
	} // end method PhoneExtension

	function HCFACode ( $fac, $pay ) {
		// TODO: This is probably wrong
		$code = freemed::get_link_field($fac, 'facility', 'psrpos');
		if (!$code) { return 11; }
		return freemed::get_link_field($code, 'pos', 'posname');
	} // end method HCFACode

	function X12Code ( $fac, $pay ) {
		// TODO: This is probably wrong
		$code = freemed::get_link_field($fac, 'facility', 'psrpos');
		if (!$code) { return 11; }
		return freemed::get_link_field($code, 'pos', 'posname');
	} // end method X12Code

} // end class FBFacility

?>
