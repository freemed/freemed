<?php
 // $Id$
 // $Author$
 // note: stub module for scheduler table definition

LoadObjectDependency('FreeMED.MaintenanceModule');

class SchedulerTable extends MaintenanceModule {

	var $MODULE_NAME = 'Scheduler Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.0';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = "scheduler";

	function SchedulerTable () {
		$this->table_definition = array (
			'caldateof' => SQL__DATE,
			'caltype' => SQL__ENUM(array('temp', 'pat')),
			'calhour' => SQL__INT_UNSIGNED(0),
			'calminute' => SQL__INT_UNSIGNED(0),
			'calduration' => SQL__INT_UNSIGNED(0),
			'calfacility' => SQL__INT_UNSIGNED(0),
			'calroom' => SQL__INT_UNSIGNED(0),
			'calphysician' => SQL__INT_UNSIGNED(0),
			'calpatient' => SQL__INT_UNSIGNED(0),
			'calcptcode' => SQL__INT_UNSIGNED(0),
			'calstatus' => SQL__INT_UNSIGNED(0),
			'calprenote' => SQL__VARCHAR(100),
			'calpostnote' => SQL__TEXT,
			'calmark' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		// Have main menu handler for physician appointments, etc
		$this->_SetHandler('MainMenu', 'MainMenuAppointments');

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor SchedulerTable

	function MainMenuAppointments ( ) {
		// Decide if this user is a physician or not...
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}
		if ($GLOBALS['this_user']->isPhysician()) {
			// If physician, give links to daily and weekly
			// schedules, as well as a total of appointments

			// Include calendar functions, if we need them
			include_once ("lib/calendar-functions.php");

			// Get day that is one week from today
			$begin_date = date("Y-m-d");
			$end_date   = $begin_date;
			for ($day=1; $day<7; $day++) {
				$end_date = freemed_get_date_next($end_date);
			}

			// Figure out appointments for today
			$day_result = $GLOBALS['sql']->query(
				"SELECT COUNT(*) AS day_count FROM scheduler WHERE ".
				"caldateof >= '".$begin_date."' AND ".
				"caldateof <= '".$end_date."' AND ".
				"calphysician='".$GLOBALS['this_user']->getPhysician()."'"
			);
			extract($GLOBALS['sql']->fetch_array($day_result));

			// Figure out appointments for this week
			$week_result = $GLOBALS['sql']->query(
				"SELECT COUNT(*) AS week_count FROM scheduler WHERE ".
				"caldateof='".$begin_date."' AND ".
				"calphysician='".$GLOBALS['this_user']->getPhysician()."'"
			);
			extract($GLOBALS['sql']->fetch_array($week_result));

			return array (
				__("Patient Scheduler"),
				sprintf(__("You have %s<b>%d</b> appointment(s) today%s and %s<b>%d</b> appointment(s) this week%s."), "<a href=\"physician_day_view.php?physician=".urlencode($GLOBALS['this_user']->getPhysician())."\">",  $day_count, "</a>", "<a href=\"physician_week_view.php?physician=".urlencode($GLOBALS['this_user']->getPhysician())."\">", $week_count, "</a>")
				//"You have <a href=\"physician_day_view.php\">".$day_count." appointent(s) today</a> and <a href=\"physician_week_view.php\">".$week_count." appointment(s) this week</a>."
				//"You have <a href=\"physician_day_view.php\">15 appointent(s) today</a> and <a href=\"physician_week_view.php\">47 appointment(s) this week</a>."
			);
		} else {
			// If not a physician, give number of appointments
			// for the current facility if there is one
			return array (
				__("Patient Scheduler"),
				__("There are <b>15</b> appointments scheduled for today.")
			);
		}
	} // end method MainMenuAppointments

	// Use _update to update table definitions with new versions
	function _update () {
		$version = freemed::module_version($this->MODULE_NAME);
		/* 
			// Example of how to upgrade with ALTER TABLE
			// Successive instances change the structure of the table
			// into whatever its current version is, without having
			// to reload the table at all. This pulls in all of the
			// changes a version at a time. (You can probably use
			// REMOVE COLUMN as well, but I'm steering away for now.)

		if (!version_check($version, '0.1.0')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN ptglucose INT UNSIGNED AFTER id');
		}
		if (!version_check($version, '0.1.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN somedescrip TEXT AFTER ptglucose');
		}
		if (!version_check($version, '0.1.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN fakefield AFTER ptglucose');
		}
		*/
	} // end function _update
}

register_module("SchedulerTable");

?>
