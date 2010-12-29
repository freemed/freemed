<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.CalendarModule');

class DailyAppointmentCalendar extends CalendarModule {

	var $MODULE_NAME = "Daily Appointments";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Scheduler";
	var $table_name  = "scheduler";

	function DailyAppointmentCalendar () {
		// Run constructor
		$this->CalendarModule();
	} // end constructor DailyAppointmentCalendar	

	function view () {
		global $display_buffer;

		if ($_REQUEST['my_action']) {
			// For extra space, turn off template
			$GLOBALS['__freemed']['no_template_display'] = true;

			$this->displayCalendar($_REQUEST['provider']);
			return true;
		}

		$display_buffer .= "
		<div align=\"center\">
		<form method=\"post\">
		".__("Date")." :
		".fm_date_entry("date")."<br/>
		".__("Select a Provider")." : 
		".module_function('providermodule', 'widget', array ( 'provider', "phyref != 'yes'" ) )."
		<input type=\"submit\" name=\"my_action\" class=\"button\" value=\"".__("Select Provider")."\" />
		</form>
		</div>
		";
	} // end method view

	function displayCalendar ($provider) {
		ob_start();

		$date = fm_date_assemble('date');

		$scheduler = CreateObject('_FreeMED.Scheduler');
		include_once('lib/calendar-functions.php');
		if ($provider > 0) {
			$r = $scheduler->find_date_appointments($date, $provider);
		} else {
			$r = $scheduler->find_date_appointments($date);
		}

		print "<div align=\"center\">\n";
		print "<h3>".__("Daily Appointments")."</h3>\n";
		if ($provider > 0) {
			$p = CreateObject('_FreeMED.Physician', $provider);
			print "<h4>".$p->fullName()."</h4>\n";
		}
		print "<h4>".fm_date_print($date)."</h4>\n";
		print "</div>\n";

		print "<div align=\"center\">\n";
		print "<table border=\"0\" cellpadding=\"3\">\n";

		print "<tr>\n".
			"<th>".__("Date")."</th>\n".
			"<th>".__("Time")."</th>\n".
			"<th>".__("Acct #")."</th>\n".
			"<th>".__("Patient")."</th>\n".
			"<th>".__("Age")."</th>\n".
			"<th>".__("Gender")."</th>\n".
			"<th>".__("DOB")."</th>\n".
			"<th>".__("Provider")."</th>\n".
			"<th>".__("Home Phone")."</th>\n".
			"<th>".__("Work Phone")."</th>\n".
			"</tr>\n";
		foreach ($r AS $a) {
			$patient = CreateObject('_FreeMED.Patient', $a['calpatient']);
			$provider = CreateObject('_FreeMED.Physician', $a['calphysician']);
			$age = array_element(my_date_diff($patient->local_record['ptdob']), 0);
			print "<tr>\n".
				"<td>".$a['caldateof']."</td>\n".
				"<td>".fc_get_time_string($a['calhour'],$a['calminute'])."</td>\n".
				"<td>".$patient->idNumber()."</td>\n".
				"<td>".$patient->fullName()."</td>\n".
				"<td>".( $age < 1 ? '&lt; 1' : $age )."</td>\n".
				"<td>".strtoupper($patient->ptsex)."</td>\n".
				"<td>".$patient->dateOfBirth()."</td>\n".
				"<td>".$provider->fullName()."</td>\n".
				"<td>".freemed::phone_display($patient->local_record['pthphone'])."</td>\n".
				"<td>".freemed::phone_display($patient->local_record['ptwphone'])."</td>\n".
				"</tr>\n";
			print "<tr>\n".
				"<td>&nbsp;</td>\n".
				"<td colspan=\"8\">".__("NOTE").": ".$a['calprenote']."</td>\n".
				"</tr>\n";
		}

		print "</table></div>\n";

		$GLOBALS['display_buffer'] .= ob_get_contents();
		ob_end_clean();
	} // end method displayCalendar

} // end class DailyAppointmentCalendar

register_module ('DailyAppointmentCalendar');

?>
