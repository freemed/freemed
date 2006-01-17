<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class SchedulerTable extends MaintenanceModule {

	var $MODULE_NAME = 'Scheduler Table';
	var $MODULE_AUTHOR = 'jeff b (jeff@ourexchange.net)';
	var $MODULE_VERSION = '0.6.5';
	var $MODULE_FILE = __FILE__;
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $table_name = "scheduler";

	function SchedulerTable () {
		$this->table_definition = array (
			'caldateof' => SQL__DATE,
			'calcreated' => SQL__TIMESTAMP(16),
			'calmodified' => SQL__TIMESTAMP(16),
			'caltype' => SQL__ENUM(array('temp', 'pat')),
			'calhour' => SQL__INT_UNSIGNED(0),
			'calminute' => SQL__INT_UNSIGNED(0),
			'calduration' => SQL__INT_UNSIGNED(0),
			'calfacility' => SQL__INT_UNSIGNED(0),
			'calroom' => SQL__INT_UNSIGNED(0),
			'calphysician' => SQL__INT_UNSIGNED(0),
			'calpatient' => SQL__INT_UNSIGNED(0),
			'calcptcode' => SQL__INT_UNSIGNED(0),
			'calstatus' => SQL__ENUM(array('scheduled','confirmed','attended','cancelled','noshow','tenative')),
			'calprenote' => SQL__VARCHAR(250),
			'calpostnote' => SQL__TEXT,
			'calmark' => SQL__INT_UNSIGNED(0),
			'calgroupid' => SQL__INT_UNSIGNED(10),
			'calrecurnote' => SQL__VARCHAR(100),
			'calrecurid' => SQL__INT_UNSIGNED(10),
			'id' => SQL__SERIAL
		);

		// Have main menu handler for physician appointments, etc
		$this->_SetHandler('MainMenu', 'MainMenuAppointments');

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor SchedulerTable

	function MainMenuAppointments ( ) {
		if (!freemed::acl('schedule', 'view')) { return false; }
	
		// Decide if this user is a physician or not...
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}
		if ($GLOBALS['this_user']->isPhysician()) {
			// If physician, give links to daily and weekly
			// schedules, as well as a total of appointments

			// Get day that is one week from today
			$begin_date = date("Y-m-d");
			$end_date   = $begin_date;
			for ($day=1; $day<7; $day++) {
				$end_date = freemed_get_date_next($end_date);
			}

			// Figure out appointments for today
			$day_result = $GLOBALS['sql']->query(
				"SELECT COUNT(*) AS day_count FROM scheduler WHERE ".
				"caldateof='".$begin_date."' AND ".
				"calphysician='".$GLOBALS['this_user']->getPhysician()."'"
			);
			extract($GLOBALS['sql']->fetch_array($day_result));

			// Figure out appointments for this week
			$week_result = $GLOBALS['sql']->query(
				"SELECT COUNT(*) AS week_count FROM scheduler WHERE ".
				"caldateof >= '".$begin_date."' AND ".
				"caldateof <= '".$end_date."' AND ".
				"calphysician='".$GLOBALS['this_user']->getPhysician()."'"
			);
			extract($GLOBALS['sql']->fetch_array($week_result));

			return array (
				__("Patient Scheduler"),
				sprintf(__("You have %s%d appointment(s) today%s and %s%d appointment(s) this week%s."), "<a href=\"physician_day_view.php?physician=".urlencode($GLOBALS['this_user']->getPhysician())."\">",  $day_count, "</a>", "<a href=\"physician_week_view.php?physician=".urlencode($GLOBALS['this_user']->getPhysician())."\">", $week_count, "</a>"),
				"img/calendar_icon.png"
				//"You have <a href=\"physician_day_view.php\">".$day_count." appointent(s) today</a> and <a href=\"physician_week_view.php\">".$week_count." appointment(s) this week</a>."
				//"You have <a href=\"physician_day_view.php\">15 appointent(s) today</a> and <a href=\"physician_week_view.php\">47 appointment(s) this week</a>."
			);
		} else {
			// If not a physician, give number of appointments
			// for the current facility if there is one
			$day_result = $GLOBALS['sql']->query(
				"SELECT COUNT(*) AS day_count FROM scheduler WHERE ".
				"caldateof = '".date('Y-m-d')."' ".
				( $_SESSION['default_facility'] ?
					"AND calfacility='".addslashes($_SESSION['default_facility'])."' " : "" )
			);
			extract($GLOBALS['sql']->fetch_array($day_result));

			// Figure out appointments for this week
			return array (
				__("Patient Scheduler"),
				sprintf(__("There are %s appointments scheduled for today."), "<b>$day_count</b>"),
				"img/calendar_icon.png"
			);
		}
	} // end method MainMenuAppointments

	// Use _update to update table definitions with new versions
	function _update () {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.6.3
		//
		//	Added group and recurring scheduler capabilities
		//	Updated some enums
		//
		if (!version_check($version, '0.6.3')) {
			// Add extra columns
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN calgroupid INT UNSIGNED AFTER calmark');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN calrecurnote VARCHAR(100) AFTER calgroupid');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN calrecurid INT UNSIGNED AFTER calrecurnote');
			// Alter ENUMs
			//CHANGE [COLUMN] old_col_name create_definition
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN caltype '.
				'caltype ENUM(\'temp\', \'pat\', \'block\')');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN calstatus '.
				'calstatus ENUM(\'scheduled\',\'confirmed\',\'attended\',\'cancelled\',\'noshow\',\'tenative\')');
		}
		// Version 0.6.3.1
		//
		//	Add extra space for appointment note.
		//
		if (!version_check($version, '0.6.3.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN calprenote '.
				'calprenote VARCHAR(250)');
		}

		// Version 0.6.4
		//
		//	Attempt revision again
		//
		if (!version_check($version, '0.6.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN caltype '.
				'caltype ENUM(\'temp\', \'pat\', \'block\')');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN calstatus '.
				'calstatus ENUM(\'scheduled\',\'confirmed\',\'attended\',\'cancelled\',\'noshow\',\'tenative\')');
			$sql->query('UPDATE '.$this->table_name.' '.
				'SET calstatus=\'scheduled\' '.
				'WHERE id>0');
		}

		// Version 0.6.5
		//
		//	Add calcreated, calmodified
		//
		if (!version_check($version, '0.6.5')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN calcreated TIMESTAMP(16) AFTER caldateof');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN calmodified TIMESTAMP(16) AFTER calcreated');
		}
	} // end function _update
}

register_module("SchedulerTable");

?>
