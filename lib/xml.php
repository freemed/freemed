<?php
 // $Id$
 // $Author$
 // note: XML routines for import and export of patient records 
 // lic : LGPL

if (!defined ("__XML_PHP__")) {

define ('__XML_PHP__', true);

// internal function
function xmlptval( $tag, $val, $tab=2 ) {
	global $__this_patient;
	unset ($tabs);
	for ($i=1;$i<=$tab;$i++) $tabs .= "\t";

	// Determine closing tag, if there's a space
	if (!(strpos($tag, ' ') === false)) {
		// Break it up and do it from there
		$tag_parts = explode(' ', $tag);
		$closing_tag = $tag_parts[0];
	} else {
		// Otherwise pass it thru
		$closing_tag = $tag;
	}
	
	if (isset($__this_patient->local_record["$val"])) {
		return $tabs."<".stripslashes($tag).">".
			prepare($__this_patient->local_record["$val"]).
			"</".stripslashes($tag).">\n";
	}
} // end fucntion xmlptval

function freemed_emr_xml_export ( $this_patient ) {

	// WARNING: This is *still* untested code, and may or may not
	// function properly. You have been warned. - Jeff
	// ----------------------------------------------------------

	// Also, please note that $this_patient is a patient object,
	// not a patient identifier.

	global $__this_patient; $__this_patient = $this_patient;

	// clear emr_xml
	$emr_xml = "";

	// add XML header code
	$emr_xml .= "<?xml version=\"1.0\">\n";
	$emr_xml .= "<!DOCTYPE freemed-emr SYSTEM ".
		"\"http://www.freemed.org/dtd/freemed-0.3.dtd\">\n";
	$emr_xml .= "<Patient PID=\"".prepare($this_patient->pid)."\" ".
		"Version=\"".prepare($this_patient->version)."\">\n";
	$emr_xml .= "\t<Name>\n";
	$emr_xml .= "\t\t<First VALUE=\"".prepare($this_patient->ptfname).
		"\"/>\n";
	$emr_xml .= "\t\t<Middle VALUE=\"".prepare($this_patient->ptmname).
		"\"/>\n";
	$emr_xml .= "\t\t<Last VALUE=\"".prepare($this_patient->ptlname).
		"\"/>\n";
	$emr_xml .= "\t</Name>\n";

	$emr_xml .= "\t<Contact>\n";
	$emr_xml .= xmlptval("Street", "ptaddr1");
	$emr_xml .= xmlptval("Street", "ptaddr2");
	$emr_xml .= xmlptval("City", "ptcity");
	$emr_xml .= xmlptval("StateProvince", "ptstate");
	$emr_xml .= xmlptval("PostalCode", "ptzip");
	$emr_xml .= xmlptval("Country", "ptcountry");
	$emr_xml .= xmlptval("Email", "ptemail");
	$emr_xml .= xmlptval("PhoneNumber Location=\"Home\"", "pthphone");
	$emr_xml .= xmlptval("PhoneNumber Location=\"Work\"", "ptwphone");
	$emr_xml .= xmlptval("PhoneNumber Location=\"Facsimile\"", "ptfax");
	$emr_xml .= xmlptval("NextOfKin", "ptnextofkin");
	$emr_xml .= "\t</Contact>\n";

	$emr_xml .= "\n<!-- need to categorize these -->\n\n";

	$emr_xml .= "\t<Personal>\n";
	$emr_xml .= xmlptval("DateOfBirth", "ptdob");
	$emr_xml .= xmlptval("Gender", "ptsex");
	$emr_xml .= xmlptval("SocialSecurityNumber", "ptssn");
	$emr_xml .= xmlptval("DriversLicence", "ptdmv");
	$emr_xml .= xmlptval("MaritalStatus", "ptmarital");
	$emr_xml .= "\t</Personal>\n";

	$emr_xml .= "\t<Latest>\n";
	$emr_xml .= xmlptval("DateOfLastVisit", "ptdol");
	$emr_xml .= xmlptval("Diagnosis", "ptdiag1");
	$emr_xml .= xmlptval("Diagnosis", "ptdiag2");
	$emr_xml .= xmlptval("Diagnosis", "ptdiag3");
	$emr_xml .= xmlptval("Diagnosis", "ptdiag4");
	$emr_xml .= "\t</Latest>\n";

	$emr_xml .= "\n<!-- ancillary generated section begin -->\n";

	// -----------------------------------------------------------
	// build patient portion (drawn from patient SQL table) here!!
	// -----------------------------------------------------------

	// build module list
	$module_list = CreateObject('PHP.module_list',
		PACKAGENAME,
		array(
			'cache_file' => 'data/cache/modules'
			//,'suffix' => '.emr.module.php'
		)
	);

	// batch execute modules list to grab emr
	$emr_xml .= $module_list->execute (
		"xml_generate",
		 array ( $this_patient )
	);

	// end of EMR code
	$emr_xml .= "\n<!-- ancillary generated section end -->\n\n";
	$emr_xml .= "</Patient>\n";

	// return the XML buffer
	return $emr_xml;
} // end function freemed_emr_xml_export

function freemed_emr_xml_import ( ) {
	// this is the stub for the XML import function
} // end function freemed_emr_xml_import

} // end checking for __XML_PHP__

?>
