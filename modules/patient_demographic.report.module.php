<?php
 // $Id$
 // desc: patient demographic report module
 // lic : GPL

if (!defined("__PATIENT_DEMOGRAPHIC_REPORT_MODULE_PHP__")) {

class patientDemographicReport extends freemedReportsModule {

	var $MODULE_NAME = "Patient Demographic Report";
	var $MODULE_VERSION = "0.1";

	var $show = array (
		"Total Patients"	=>		"total_patients",
		"Total Males"		=>		"total_male",
		"Total Females"		=>		"total_female"
	);

	function patientDemographicReport () {
		$this->freemedReportsModule();
	} // end constructor patientDemographicReport

	// function "view" is used to show a form that would be submitted to
	// generate the report shown in "display".

	function display () {
		global $sql;
		$query = "SELECT
			COUNT(*)              AS total_patients,
			SUM(LCASE(ptsex)='m') AS total_male,
			SUM(LCASE(ptsex)='f') AS total_female
			FROM patient"; 
		$result = $sql->query ($query);
		if (!$result) {
			echo "FAILED (query = \"$query\") <BR>\n";
			return false;
		} // end if not result
		extract($sql->fetch_array($result));	
		
		echo "
		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
		 ALIGN=CENTER>
		";

		foreach ($this->show AS $k => $v) {
			echo "
			<TR>
				<TD ALIGN=RIGHT BGCOLOR=\"#ccccff\">
					".prepare(_($k))."
				</TD><TD ALIGN=LEFT BGCOLOR=\"#aaaaff\">
					".prepare($$v)."
				</TD>
			</TR>
			";
		} // end foreach sums
	
		echo "
		</TABLE>
		";
	
	} // end function freemedReportsModule->display

} // end class freemedReportsModule

register_module ("patientDemographicReport");

} // end if not defined

?>
