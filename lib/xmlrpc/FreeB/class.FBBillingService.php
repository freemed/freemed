<?php
	// $Id$
	// $Author$

class FBBillingService {

	function Name ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsname'];
	} // end method Name

	function StreetAddress ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsaddr'];
	} // end method StreetAddress

	function City ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bscity'];
	} // end method City

	function State ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsstate'];
	} // end method State

	function Zipcode ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bszip'];
	} // end method Zipcode

	function PhoneCountry ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return '1';
	} // end method PhoneCountry

	function PhoneArea ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		$n = substr($r['bsphone'], 0, 3);
		if (!$n) { return '000'; }
		return $n;
	} // end method PhoneArea

	function PhoneNumber ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		$n = substr($r['bsphone'], 3, 7);
		if (!$n) { return '0000000'; }
		return $n;
	} // end method PhoneNumber

	function PhoneExtension ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		$n = substr($r['bsphone'], 10, 4);
		if (!$n) { return '0000'; }
		return $n;
	} // end method PhoneExtension

	function ETIN ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsetin'];
	} // end method ETIN

	function TIN ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bstin'];
	} // end method TIN

} // end class FBBillingService

?>
