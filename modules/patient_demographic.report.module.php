<?php
 // $Id$
 // desc: patient demographic report module
 // lic : GPL

LoadObjectDependency('FreeMED.ReportsModule');

class PatientDemographicReport extends ReportsModule {

	var $MODULE_NAME = "Patient Demographic Report";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $show = array (
		"Total Patients"	=>		"total_patients",
		"Total Males"		=>		"total_male",
		"Total Females"		=>		"total_female"
	);

	function PatientDemographicReport () {
		$this->record_name = $this->MODULE_NAME;
		$this->ReportsModule();
	} // end constructor PatientDemographicReport

	// function "view" is used to show a form that would be submitted to
	// generate the report shown in "display".

	function display () {
		global $display_buffer;
		global $sql;
		$query = "SELECT
			COUNT(*)              AS total_patients,
			SUM(LCASE(ptsex)='m') AS total_male,
			SUM(LCASE(ptsex)='f') AS total_female
			FROM patient"; 
		$result = $sql->query ($query);
		if (!$result) {
			$display_buffer .= "FAILED (query = \"$query\") <BR>\n";
			return false;
		} // end if not result
		extract($sql->fetch_array($result));	
		
		$display_buffer .= "
		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
		 ALIGN=CENTER>
		";

		foreach ($this->show AS $k => $v) {
			$display_buffer .= "
			<TR>
				<TD ALIGN=RIGHT BGCOLOR=\"#ccccff\">
					".prepare(__($k))."
				</TD><TD ALIGN=LEFT BGCOLOR=\"#aaaaff\">
					".prepare($$v)."
				</TD>
			</TR>
			";
		} // end foreach sums
	
		$display_buffer .= "
		</TABLE>
		";
	
	} // end function PatientDemographicReport->display

	function view() { $this->display(); }

} // end class freemedReportsModule

register_module ("PatientDemographicReport");

?>
