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
	// this is the stub for the XML export function
} // end function freemed_emr_xml_export

function freemed_emr_xml_import ( ) {
	// this is the stub for the XML import function
} // end function freemed_emr_xml_import

} // end checking for __XML_PHP__

?>
