<?php
	// $Id$
	// $Author$

class FBClearingHouse {

	function Name ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chname'];
	} // end method Name

	function StreetAddress ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chaddr'];
	} // end method StreetAddress

	function City ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chcity'];
	} // end method City

	function State ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chstate'];
	} // end method State

	function Zipcode ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chzip'];
	} // end method Zipcode

	function PhoneCountry ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		// TODO: Fix i18n
		return '1';
	} // end method PhoneCountry

	function PhoneArea ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return substr($r['chphone'], 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return substr($r['chphone'], 3, 7);
	} // end method PhoneNumber

	function PhoneExtension ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return substr($r['chphone'], 10, 4);
	} // end method PhoneExtension

	function ETIN ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chetin'];
	} // end method ETIN

	function X12GSSenderID ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chx12gssender'];
	} // end method X12GSSenderID

	function X12GSReceiverID ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chx12gsreceiver'];
	} // end method X12GSReceiverID

} // end class FBClearingHouse

?>
