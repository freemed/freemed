<?php
	// $Id$
	// $Author$


/*	Class: FBBillingContact 

	The FreeMED implementation of FreeB::FBBillingContact.

*/
class FBBillingContact {


/*	Function: FirstName 

	Returns:

	bcfname from the bcontact table

*/
	function FirstName ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcfname'];
	} // end method FirstName


/*	Function: MiddleName

	Returns:

	bcmname from the bcontact table
*/
	function MiddleName ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcmname'];
	} // end method MiddleName

/*	Function: LastName 

	Returns:

	bclname from the bcontact table
	
*/
	function LastName ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bclname'];
	} // end method FirstName

/*	Function: StreetAddress

	Returns:

	bcaddr from the bcontact table
*/
	function StreetAddress ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcaddr'];
	} // end method StreetAddress

/*	Function: City

	Returns:

	bccity from the bcontact table

*/
	function City ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bccity'];
	} // end method City

/*	Function: State

	Returns:
	
	bcstate from the bcontact table

*/
	function State ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bcstate'];
	} // end method State

/*	Function: Zipcode

	Returns:

	bczip from the bcontact table

*/
	function Zipcode ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		return $r['bczip'];
	} // end method Zipcode

/*	Function: PhoneCountry

	Returns:

	A static 1

*/
	function PhoneCountry ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		// TODO: Fix i18n
		return '1';
	} // end method PhoneCountry

/*	Function: PhoneArea

	Returns:
	
	The first three digits of the bcphone record from the the bcontact table

*/
	function PhoneArea ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		$n = substr($r['bcphone'], 0, 3);
		if (!$n) { return '000'; }
		return $n;
	} // end method PhoneArea

/*	Function: PhoneNumber

	Returns:

	seven digits from the bcphone field of the bcontact table. Starting
	with the fourth digit.

*/
	function PhoneNumber ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		$n = substr($r['bcphone'], 3, 7);
		if (!$n) { return '0000000'; }
		return $n;
	} // end method PhoneNumber

/*	Function: PhoneExtension

	Returns:
	
	The last 4 digits of the bcphone record in the bcontact table.

*/
	function PhoneExtension ( $key ) {
		$r = freemed::get_link_rec($key, 'bcontact');
		$n = substr($r['bcphone'], 10, 4);
		if (!$n) { return '0000'; }
		return $n;
	} // end method PhoneExtension

} // end class FBBillingContact

?>
