<?php
	// $Id$
	// $Author$

class FBProvider {

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
		return '';
	} // end method PhoneCountry

	function PhoneArea ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return substr($p->local_record['phyzipa'], 0, 3);
	} // end method PhoneArea

	function PhoneNumber ( $provider ) {
		$p = CreateObject('_FreeMED.Physician', $provider);
		return substr($p->local_record['phyzipa'], 3, 7);
	} // end method PhoneNumber

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
		return '';
	} // end method IPN

} // end class FBProvider

?>
