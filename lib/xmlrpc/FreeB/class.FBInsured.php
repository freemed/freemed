<?php
	// $Id$
	// $Author$


/*	Class: FBInsured 

	The FreeMED implementation of FreeB::FBInsured.

*/
class FBInsured {

/*	Function: FirstName 

	depends on coverage being self. the coverage is not self,
	this returns the first name 
	record, otherwise it returns the patients first name.

*/
	function FirstName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
			return $c['covfname'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptfname'];
		}
	} // end method FirstName

/*	Function: MiddleName 

	depends on coverage being self. if the coverage is not self, 
	this returns the middle name record of the coverage table, otherwise
	it returns the patients first name from the patient table.

*/
	function MiddleName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');

		if($c['covrel']!='S')
		{
			return $c['covmname'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptmname'];	
		}
	} // end method MiddleName

/*	Function: LastName 

	depends on coverage being self. if the coverage is not self, 
	this returns the last name record of the coverage table, otherwise
	it returns the patients last name from the patient table.

*/	function LastName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
			return $c['covlname'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptlname'];	
		}
	} // end method LastName

/*	Function: StreetAddress

	depends on coverage being self. if the coverage is not self, 
	this returns the address record of the coverage table, otherwise
	it returns the patients address from the patient table.
*/
	function StreetAddress ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
				return $c['covaddr1'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptaddr1'];	
		}
	} // end method StreetAddress

/*	Function: City

	depends on coverage being self. if the coverage is not self, 
	this returns the city record of the coverage table, otherwise
	it returns the patients city from the patient table.

*/
	function City ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
				return $c['covcity'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptcity'];	
		}
	} // end method City

/*	Function: State

	depends on coverage being self. if the coverage is not self, 
	this returns the state record of the coverage table, otherwise
	it returns the patients state from the patient table.

*/
	function State ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
				return $c['covstate'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptstate'];	
		}
	} // end method State

/*	Function: Zipcode

	depends on coverage being self. if the coverage is not self, 
	this returns the zipcode record of the coverage table, otherwise
	it returns the patients zipcode from the patient table.

*/
	function Zipcode ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
				return $c['covzip'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptzip'];	
		}
	} // end method Zipcode

/*	Function: DateOfBirth

	depends on coverage being self. if the coverage is not self, 
	this returns the date of birth record of the coverage table, otherwise
	it returns the patients date of birth from the patient table.

*/
	function DateOfBirth ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
				$dob = $c['covdob'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			$dob = $p['ptdob'];	
		}

		list ($y, $m, $d) = explode('-', $dob);
		return CreateObject('PHP.xmlrpcval', $y.$m.$d.'T00:00:00',
				xmlrpcDateTime);
	} // end method DateOfBirth

/*	Function: Sex

	depends on coverage being self. if the coverage is not self, 
	this returns the date of birth record of the coverage table, otherwise
	it returns the patients date of birth from the patient table.

*/
	function Sex ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		if($c['covrel']!='S')
		{
					return $c['covsex'];
		}else{
			$p = freemed::get_link_rec($c['covpatient'],'patient');
			return $p['ptsex'];	
		}
	} // end method Sex

/*	Function: ID

	Returns the insured ID, with respect to this insurance company
	
*/
	function ID ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covpatinsno'];
	} // end method ID

/*	Function: PlanName

	Returns the Insureds PlanName.
	
*/
	function PlanName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covplanname'];
	} // end method PlanName

/*	Function: GroupName

	Returns the Insureds GroupName.
	
*/
	function GroupName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covplanname'];
	} // end method GroupName

/*	Function: GroupNumber

	Returns the Insureds GroupNumber.
	
*/
	function GroupNumber ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return $c['covpatgrpno'];
	} // end method GroupNumber
/*
	These are not part of the FreeB API.

	function IsMale ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return (strtoupper($c['covsex']) == 'M');
	} // end method IsFemale

	function IsFemale ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return (strtoupper($c['covsex']) == 'F');
	} // end method IsFemale
*/

	function PhoneCountry ( $cov ) {
		// TODO: i18n broken
		return '1';
	} // end method PhoneCountry


/*	Function: PhoneArea

	Returns the area code of the insureds phone number.
	
*/
	function PhoneArea ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// TODO: i18n broken
		return CreateObject('PHP.xmlrpcval', substr($c['covphone'], 0, 3), xmlrpcString);
	} // end method PhoneArea

/*	Function: PhoneNumber

	Returns the base number of the insureds phone number.
	
*/
	function PhoneNumber ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// TODO: i18n broken
		return CreateObject('PHP.xmlrpcval', substr($c['covphone'], 3, 7), xmlrpcString);
	} // end method PhoneNumber

/*	Function: PhoneExtension

	Returns the base number of the insureds phone number.
	
*/
	function PhoneExtension ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// TODO: i18n broken
		return CreateObject('PHP.xmlrpcval', substr($c['covphone'], 10, 4), xmlrpcString);
	} // end method PhoneExtension

/*	Function: isEmployed

	Returns: covemployer not empty (boolean) from coverage
	
*/
	function isEmployed ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return !empty($c['covemployer']);
	} // end method isEmployed

/*	Function: EmployerName

	Returns: covemployer from coverage
	
*/
	function EmployerName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return CreateObject('PHP.xmlrpcval', $c['covemployer'], xmlrpcString);
	} // end method EmployerName

/*	Function: isStudent

	Returns: covschool not empty (boolean) from coverage
	
*/
	function isStudent ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return !empty($c['covschool']);
	} // end method isStudent

/*	Function: SchoolName

	Returns: covschool from coverage
	
*/
	function SchoolName ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		return CreateObject('PHP.xmlrpcval', $c['covschool'], xmlrpcString);
	} // end method SchoolName

/*	Function: isAssigning

	Returns: covisassigning > 0 from coverage
	
*/
	function isAssigning ( $cov ) {
		$c = freemed::get_link_rec($cov, 'coverage');
		// If this isn't true, we don't bill
		//  - force as boolean
		return ($c['covisassigning'] > 0);
	} // end method isAssigning

} // end class FBInsured

?>
