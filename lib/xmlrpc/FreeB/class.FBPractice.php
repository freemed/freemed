<?php
	// $Id$
	// $Author$

class FBPractice {

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
		return CreateObject('PHP.xmlrpcval', '', xmlrpcString);
	} // end method PhoneCountry

	function PhoneArea ( $key ) {
		// TODO: Need to handle i18n with areas
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		return CreateObject('PHP.xmlrpcval', substr($f, 0, 3), xmlrpcString);
	} // end method PhoneArea

	function PhoneNumber ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		return CreateObject('PHP.xmlrpcval', substr($f, 3, 7), xmlrpcString);
	} // end method PhoneNumber

	function PhoneExtension ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		return CreateObject('PHP.xmlrpcval', substr($f, 10, 4), xmlrpcString);
	} // end method PhoneExtension

	function PracticeID ( $prac, $pay, $prov ) {
		$p = freemed::get_link_rec($pay, 'insco');
		$map = unserialize($p['inscoidmap']);
		return $map[$prov]['id'];
	} // end method PracticeID

	function GroupID ( $prac, $pay, $prov ) {
		$p = freemed::get_link_rec($pay, 'insco');
		$map = unserialize($p['inscoidmap']);
		return $map[$prov]['group'];
	} // end method GroupID

	function isAcceptsAssignment ( $prac ) {
		return true;
	} // end method isAcceptsAssignment

	function X12Id ( $payer ) {
		$f = freemed::get_link_field($key, 'facility', 'psrx12id');
			// Force string so as not to trip out FreeB
		return CreateObject('PHP.xmlrpcval', $f, xmlrpcString);
		//return '1111111111'; // stub
	} // end method X12Id

	function X12IdType ( $prac ) {
		$f = freemed::get_link_field($key, 'facility', 'psrx12idtype');
		return CreateObject('PHP.xmlrpcval', $f, xmlrpcString);
		// hardcoded 0B value
		//return 'BQ';
	} // end method X12IdType

} // end class FBPractice

?>
