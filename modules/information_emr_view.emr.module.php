<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class InformationEMRView extends EMRModule {

	var $MODULE_NAME = "Patient Information";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function InformationEMRView () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor 

	// The EMR box; probably the most important part of this module
	function summary ($patient, $num_summary_items) {
		global $this_patient;
		if (!is_object($this_patient)) { $this_patient = CreateObject('_FreeMED.Patient', $patient); }
		
		//----- Determine date of last visit
		$dolv_result = $GLOBALS['sql']->query(
			"SELECT * FROM scheduler WHERE ".
			"id='".addslashes($patient)."' AND ".
			"(caldateof < '".date("Y-m-d")."' OR ".
			"(caldateof = '".date("Y-m-d")."' AND ".
			"calhour < '".date("H")."'))".
			"ORDER BY caldateof DESC, calhour DESC"
		);
		if (!$GLOBALS['sql']->results($dolv_result)) {
			$dolv = __("NONE");
		} else {
			$dolv_r = $GLOBALS['sql']->fetch_array($dolv_result);
			$dolv = prepare(fm_date_print($dolv_r["caldateof"]));
		} // end if there is no result
		//----- Create the panel
		if ($this_patient->local_record['ptpcp'] > 0) { $pcp = CreateObject('FreeMED.Physician', $this_patient->local_record['ptpcp']); }
		if ($this_patient->local_record['ptrefdoc'] > 0) { $refdoc = CreateObject('FreeMED.Physician', $this_patient->local_record['ptrefdoc']); }
		$buffer .= "
			<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\"
			 CELLPADDING=\"3\">
			<!-- <tr><td ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				<b>".__("Date of Last Visit")."</b> :
			</TD><td ALIGN=\"LEFT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				".$dolv."
			</tr> -->
			<tr><td ALIGN=\"RIGHT\" VALIGN=\"TOP\" WIDTH=\"50%\">
				<b>".__("Address")."</b> :
			</td><td ALIGN=\"LEFT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				".$this_patient->local_record['ptaddr1']."<br/>
				".( $this_patient->local_record['ptaddr2'] ? $this_patient->local_record['ptaddr2']."<br/>" : "" )."
				".$this_patient->local_record['ptcity'].", 
				".$this_patient->local_record['ptstate']."
				".$this_patient->local_record['ptzip']."
			</td></tr>
			<tr><td ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				<b>".__("Home Phone")."</b> :
			</td><td ALIGN=\"LEFT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				".freemed::phone_display($this_patient->local_record["pthphone"])."
			</td></tr>
			<tr><td ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				<b>".__("Work Phone")."</b> :
			</td><td ALIGN=\"LEFT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\">
				".freemed::phone_display($this_patient->local_record["ptwphone"])."
			</td></tr>
			".($this_patient->local_record['ptssn'] > 0 ? "
			<tr><td ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\"><b>".__("SSN")."</b> :</td> 
			<td ALIGN=\"LEFT\">".substr($this_patient->local_record['ptssn'], 0, 3).'-'.substr($this_patient->local_record['ptssn'], 3, 2).'-'.substr($this_patient->local_record['ptssn'], 5, 4)."</td></tr>
			" : "" )."
			".($this_patient->local_record['ptpcp'] > 0 ? "
			<tr><td ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\"><b>".__("PCP")."</b> :</td> 
			<td ALIGN=\"LEFT\">".prepare($pcp->fullName())."</td></tr>
			" : "" )."
			".($this_patient->local_record['ptrefdoc'] > 0 ? "
			<tr><td ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" WIDTH=\"50%\"><b>".__("Referring")."</b>:</td> 
			<td ALIGN=\"LEFT\">".prepare($refdoc->fullName())."</td></tr>
			" : "" )."
			</table>
		";
		return $buffer;
	} // end method summary

	// Disable summary bar
	function summary_bar() {
		$buffer .= "
		<a HREF=\"patient.php?action=modform&id=".( $_REQUEST['patient'] ? $_REQUEST['patient'] : $_REQUEST['id'] )."\" 
		>".__("Modify")."</a>
		";
		return $buffer;
	} // end method summary_bar

} // end class InformationEMRView

register_module ("InformationEMRView");

?>
