<?php
	// $Id$
	// $Author$

class FBPractice {

	function Name ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $r['psrname'];
	} // end method Name

	function StreetAddress ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $r['psraddr1'];
	} // end method StreetAddress

	function City ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $r['psrcity'];
	} // end method City

	function State ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $r['psrstate'];
	} // end method State

	function Zipcode ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $r['psrzip'];
	} // end method Zipcode

	function PhoneCountry ( $key ) {
		//$f = freemed::get_link_field($key, 'facility', 'psrphone');
		// TODO: Broken behavior
		return '';
	} // end method PhoneCountry

	function PhoneArea ( $key ) {
		// TODO: Need to handle i18n with areas
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		return substr($f, 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		return substr($f, 3, 7);
	} // end method PhoneNumber

	function PhoneExtension ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		return substr($f, 10, 4);
	} // end method PhoneExtension

	function PracticeID ( $prac, $pay, $prov ) {
		$p = freemed::get_link_rec($prov, 'physician');
		$map = unserialize($p['phyidmap']);
		return $map[$pay]['id'];
	} // end method PracticeID

	function GroupID ( $prac, $pay, $prov ) {
		$p = freemed::get_link_rec($prov, 'physician');
		$map = unserialize($p['phyidmap']);
		return $map[$pay]['group'];
	} // end method GroupID

	function isAcceptsAssignment ( $prac ) {
		return true;
	} // end method isAcceptsAssignment

	function X12Id ( $payer ) {
		return ''; // stub
	} // end method X12Id

	function X12IdType ( $prac ) {
		// fixme - hardcoded 0B value
		return '0B';
	} // end method X12IdType

} // end class FBPractice

?>
