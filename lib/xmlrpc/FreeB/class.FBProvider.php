<?php
	// $Id$
	// $Author$


// Class: FreeB.FBProvider
//
//	XML-RPC provider-related methods.
//

class FBProvider {

	// Function: FBProvider::LastName
	//
	//	Returns the last name of the provider referenced by its
	//	parameter.
	//
	function LastName ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phylname'];
	} // end method LastName

	function FirstName ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phyfname'];
	} // end method FirstName

	function MiddleName ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phymname'];
	} // end method MiddleName

	function StreetAddress ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phyaddr1a'];
	} // end method StreetAddress

	function City ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phycitya'];
	} // end method City

	function State ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phystatea'];
	} // end method State

	function Zipcode ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phyzipa'];
	} // end method Zipcode

	function PhoneCountry ( $provider ) {
		// TODO: i18n fix
		return '1';
	} // end method PhoneCountry

	function PhoneArea ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		$n = substr($p->local_record['phyphonea'], 0, 3);
		if (!$n) { return '000'; }
		return $n;
	} // end method PhoneArea

	function PhoneNumber ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		$n = substr($p->local_record['phyphonea'], 3, 7);
		if (!$n) { return '0000000'; }
		return $n;
	} // end method PhoneNumber

	function PhoneExtension ( $provider ){
		$p = CreateObject('_FreeMED.Physician', $provider);
		$n = substr($p->local_record['phyphonea'], 10, 4);
		if (!$n) { return '0000'; }
		return $n;
	} // end method PhoneExtension

	function SocialSecurityNumber ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['physsn'];
	} // end method SocialSecurityNumber

	function TIN ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return $p->local_record['phyein'];
	} // end method TIN

	function IPN ( $provider ) {
		//$p = CreateObject('_FreeMED.Physician', $provider);
		// TODO: What is an IPN?
		return ' ';
	} // end method IPN

} // end class FBProvider

?>
