<?php
	// $Id$
	// $Author$
/*      Class: FBClearingHouse

	FreeMEDs implementation of FreeB::FBClearingHouse.

*/
class FBClearingHouse {

/*      Function: Name

        Returns:

        chname record from the clearinghouse table

*/ 
	function Name ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chname'];
	} // end method Name

/*      Function: StreetAddress

        Returns:

        chaddr record from the clearinghouse table

*/ 
	function StreetAddress ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chaddr'];
	} // end method StreetAddress

/*      Function: City

        Returns:

        chcity record from the clearinghouse table

*/ 
	function City ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chcity'];
	} // end method City

/*      Function: State

        Returns:

        chstate record from the clearinghouse table

*/ 
	function State ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chstate'];
	} // end method State

/*      Function: Zipcode

        Returns:

        chzip record from the clearinghouse table

*/ 
	function Zipcode ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chzip'];
	} // end method Zipcode

/*      Function: PhoneCountry

        Returns:

        static '1'

*/ 
	function PhoneCountry ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		// TODO: Fix i18n
		return '1';
	} // end method PhoneCountry

/*      Function: PhoneArea

        Returns:

        first three digits from the chphone record from the clearinghouse table

*/ 
	function PhoneArea ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		$n = substr($r['chphone'], 0, 3);
		if (!$n) { return '000'; }
		return $n;
	} // end method PhoneArea

/*      Function: PhoneNumber

        Returns:

        digits 3 to 7 from the chphone record from the clearinghouse table

*/ 
	function PhoneNumber ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		$n = substr($r['chphone'], 3, 7);
		if (!$n) { return '0000000'; }
		return $n;
	} // end method PhoneNumber

/*      Function: PhoneExtension

        Returns:

        last four digits of the chphone record from the clearinghouse table

*/ 
	function PhoneExtension ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		$n = substr($r['chphone'], 10, 4);
		if (!$n) { return '0000'; }
		return $n;
	} // end method PhoneExtension

/*      Function: ETIN

        Returns:

        chetin record from the clearinghouse table

*/ 
	function ETIN ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chetin'];
	} // end method ETIN

/*      Function: X12GSSenderID

        Returns:

        chx12gssender record from the clearinghouse table

*/ 
	function X12GSSenderID ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chx12gssender'];
	} // end method X12GSSenderID

/*      Function: X12GSReceiverID

        Returns:

        chx12gsreciever record from the clearinghouse table

*/ 
	function X12GSReceiverID ( $key ) {
		$r = freemed::get_link_rec($key, 'clearinghouse');
		return $r['chx12gsreceiver'];
	} // end method X12GSReceiverID

} // end class FBClearingHouse

?>
