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

	// return the XML buffer
	return $emr_xml;
} // end function freemed_emr_xml_export

function freemed_emr_xml_import ( ) {
	// this is the stub for the XML import function
} // end function freemed_emr_xml_import

} // end checking for __XML_PHP__

?>
