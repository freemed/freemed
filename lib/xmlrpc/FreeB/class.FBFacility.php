<?php
	// $Id$
	// $Author$

// Class: FBFacility
//
//	FreeMEDs implementation of FreeB::FBFacility.
//
class FBFacility {

/*      Function: Name

        Returns:

        psrname record from the facility table

*/ 
	function Name ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrname'];
	} // end method Name

/*      Function: StreetAddress

        Returns:

        psraddr1 record from the facility table

*/ 
	function StreetAddress ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psraddr1'];
	} // end method StreetAddress

/*      Function: City

        Returns:

        psrcity record from the facility table

*/ 
	function City ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrcity'];
	} // end method City

/*      Function: State

        Returns:

        psrstate record from the facility table.

*/ 
	function State ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrstate'];
	} // end method State

/*      Function: Zipcode

        Returns:

        psrzip record from the facility table.

*/ 
	function Zipcode ( $key ) {
		$f = freemed::get_link_rec($key, 'facility');
		return $f['psrzip'];
	} // end method Zipcode

	// Method: PhoneCountry
	//
	//	Return the phone country code. This defaults to 1, as there
	//	is no foreign country handling currently in the FreeB code.
	//
	// Parameters:
	//
	//	$key - Facility key
	//
	// Returns:
	//	Country code
	//
	function PhoneCountry ( $key ) {
		//$f = freemed::get_link_field($key, 'facility', 'psrphone');
		// TODO: Broken behavior
		return '1';
	} // end method PhoneCountry

/*      Function: PhoneArea

        Returns:

        returns the first three digits of the psrphone field from the facility table

*/ 
	function PhoneArea ( $key ) {
		// TODO: Need to handle i18n with areas
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		if (strlen($f) < 3) { return '   '; }
		return substr($f, 0, 3);
	} // end method PhoneArea

/*      Function: PhoneNumber

        Returns:

        the third to the seventh digit in the psrphone field from the facility table. 

*/ 
	function PhoneNumber ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		if (strlen($f) < 3) { return '       '; }
		return substr($f, 3, 7);
	} // end method PhoneNumber

/*      Function: PhoneExtension

        Returns:

        returns the last four digits of the psrphone field from the facility table.

*/ 
	function PhoneExtension ( $key ) {
		// TODO: Need to handle i18n with rest of number
		$f = freemed::get_link_field($key, 'facility', 'psrphone');
		if (strlen($f) < 3) { return '    '; }
		return substr($f, 10, 4);
	} // end method PhoneExtension

/*      Function: HCFACode

        Returns:

          uses the psrpos field from the facility table to get the posname field from the pos table. 

*/ 
	function HCFACode ( $fac, $pay ) {
		// TODO: This is probably wrong
		$code = freemed::get_link_field($fac, 'facility', 'psrpos');
		if (!$code) { return 11; }
		return freemed::get_link_field($code, 'pos', 'posname');
	} // end method HCFACode

/*      Function: X12Code

        Returns:

        uses the psrpos field from the facility table to get the posname field from the pos table. 

*/ 
	function X12Code ( $fac, $pay ) {
		// TODO: This is probably wrong
		$code = freemed::get_link_field($fac, 'facility', 'psrpos');
		if (!$code) { return 11; }
		return freemed::get_link_field($code, 'pos', 'posname');
	} // end method X12Code

} // end class FBFacility

?>
