<?php
	// $Id$
	// $Author$

class FBBillingContact {

	function FirstName ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcfname'];
	} // end method FirstName

	function MiddleName ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcmname'];
	} // end method MiddleName

	function LastName ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bclname'];
	} // end method FirstName

	function StreetAddress ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcaddr'];
	} // end method StreetAddress

	function City ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bccity'];
	} // end method City

	function State ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcstate'];
	} // end method State

	function Zipcode ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bczip'];
	} // end method Zipcode

	function PhoneCountry ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		// TODO: Fix i18n
		return '';
	} // end method PhoneCountry

	function PhoneArea ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return substr($r['bcphone'], 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return substr($r['bcphone'], 3, 7);
	} // end method PhoneNumber

	function PhoneExtension ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return substr($r['bcphone'], 10, 4);
	} // end method PhoneExtension

} // end class FBBillingContact

?>
