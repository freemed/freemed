<?php
	// $Id$
	// $Author$

/*	Class: FBBillingService 

	FreeMED implementation of FreeB::FBBillingService

*/

class FBBillingService {

/*	Function: Name 

	Returns:

	bsname record from the bservice table

*/
	function Name ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsname'];
	} // end method Name


/*	Function: StreetAddress 

	Returns:

	bsaddr record from the bservice table

*/
	function StreetAddress ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsaddr'];
	} // end method StreetAddress

/*	Function: City

	Returns:

	bscity record from the bservice table

*/
	function City ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bscity'];
	} // end method City

/*	Function: State

	Returns:

	bsstate record from the bservice table

*/
	function State ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsstate'];
	} // end method State

/*	Function: Zipcode

	Returns:

	bszip record from the bservice table

*/
	function Zipcode ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bszip'];
	} // end method Zipcode

/*	Function: PhoneCountry

	Returns:

	static '1'.

*/
	function PhoneCountry ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return '1';
	} // end method PhoneCountry

/*	Function: PhoneArea

	Returns:

	first three digits of the bsphone record from the bservice table

*/
	function PhoneArea ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		$n = substr($r['bsphone'], 0, 3);
		if (!$n) { return '000'; }
		return $n;
	} // end method PhoneArea

/*	Function: PhoneNumber

	Returns:

	digits between 3 and 7 of the bpshone record from the bservice table

*/
	function PhoneNumber ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		$n = substr($r['bsphone'], 3, 7);
		if (!$n) { return '0000000'; }
		return $n;
	} // end method PhoneNumber

/*	Function: PhoneExtension

	Returns:

	digits between 10 and 4 of the bsphone record from the bservice table.

*/
	function PhoneExtension ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		$n = substr($r['bsphone'], 10, 4);
		if (!$n) { return '0000'; }
		return $n;
	} // end method PhoneExtension

/*	Function: ETIN

	Returns:

	bsetin record from the bservice table

*/
	function ETIN ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bsetin'];
	} // end method ETIN

/*	Function: TIN 

	Returns:

	bstin record from the bservice table

*/
	function TIN ( $key ) {
		$r = freemed::get_link_rec($key, 'bservice');
		return $r['bstin'];
	} // end method TIN

} // end class FBBillingService

?>
