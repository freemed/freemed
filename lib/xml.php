<?php
 // $Id$
 // $Author$
 // note: XML routines for import and export of patient records 
 // lic : LGPL


 // Please note that these routines will have to be called *after* the
 // module loading functions, to allow them to pick up on the loadable
 // parts of the EMR. - jeff


if (!defined ("__XML_PHP__")) {

define ('__XML_PHP__', true);

function freemed_emr_xml_export ( $this_patient ) {

	// WARNING: This is *still* untested code, and may or may not
	// function properly. You have been warned. - Jeff
	// ----------------------------------------------------------

	// Also, please note that $this_patient is a patient object,
	// not a patient identifier.

	// clear emr_xml
	$emr_xml = "";

	// add XML header code
	$emr_xml .= "<?xml version=\"1.0\">\n";
	$emr_xml .= "<!DOCTYPE freemed-emr SYSTEM ".
		"\"http://www.freemed.org/dtd/freemed-0.3.dtd\">\n";
	$emr_xml .= "<Patient PID=\"".prepare($this_patient->pid)."\" ".
		"Version=\"".prepare($this_patient->version)."\">\n";
	$emr_xml .= "\t<Name>\n";
	$emr_xml .= "\t\t<First>".prepare($this_patient->ptfname)."</First>\n";
	$emr_xml .= "\t\t<Middle>".prepare($this_patient->ptmname)."</Middle>\n";
	$emr_xml .= "\t\t<Last>".prepare($this_patient->ptlname)."</Last>\n";
	$emr_xml .= "\t</Name>\n";

	$emr_xml .= "\t<Contact>\n";
	if (!empty($this_patient->local_record["ptaddr1"]))
		$emr_xml .= "\t\t<Street>".prepare(
			$this_patient->local_record["ptaddr1"]).
			"</Street>\n";
	if (!empty($this_patient->local_record["ptaddr2"]))
		$emr_xml .= "\t\t<Street>".prepare(
			$this->patient->local_record["ptaddr2"]).
			"</Street>\n";
	$emr_xml .= "\t\t<City>".prepare(
		$this_patient->local_record["ptcity"])."</City>\n";
	$emr_xml .= "\t\t<StateProvince>".prepare(
		$this_patient->local_record["ptstate"])."</StateProvince>\n";
	$emr_xml .= "\t\t<PostalCode>".prepare(
		$this_patient->local_record["ptzip"])."</PostalCode>\n";
	$emr_xml .= "\t\t<Email>".prepare(
		$this_patient->local_record["ptemail"])."</Email>\n";
	if (!empty($this_patient->local_record["pthphone"]))
		$emr_xml .= "\t\t<PhoneNumber Location=\"Home\">".prepare(
			$this->patient->local_record["pthphone"]).
			"</PhoneNumber>\n";
	if (!empty($this_patient->local_record["ptwphone"]))
		$emr_xml .= "\t\t<PhoneNumber Location=\"Work\">".prepare(
			$this->patient->local_record["ptwphone"]).
			"</PhoneNumber>\n";
	$emr_xml .= "\t</Contact>\n";

	$emr_xml .= "\n<!-- ancillary generated section begin -->\n";

	// -----------------------------------------------------------
	// build patient portion (drawn from patient SQL table) here!!
	// -----------------------------------------------------------

	// build module list
	$module_list = new module_list (PACKAGENAME, ".emr.module.php");

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
