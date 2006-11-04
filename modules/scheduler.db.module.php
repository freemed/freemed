<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class SchedulerTable extends SupportModule {

	var $MODULE_NAME = "Scheduler Table";
	var $MODULE_VERSION = "0.6.6";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "a992b0a0-97f7-4deb-a56d-5970bf6d3c97";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $widget_hash = "##caldateof## ##calhour##:##calminute## (##calduration##m)";

	var $table_name = "scheduler";

	public function __construct () {
		// __("Scheduler Table")

		// Have main menu handler for physician appointments, etc
		$this->_SetHandler('MainMenu', 'MainMenuAppointments');

		// Call parent constructor
		parent::__construct();
	} // end constructor SchedulerTable

	function MainMenuAppointments ( ) {
		if (!freemed::acl('schedule', 'view')) { return false; }
	
		// Decide if this user is a physician or not...
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('org.freemedsoftware.core.User');
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
			$day_count = $GLOBALS['sql']->queryOne(
				"SELECT COUNT(*) AS day_count FROM scheduler WHERE ".
				"caldateof='".$begin_date."' AND ".
				"calphysician='".$GLOBALS['this_user']->getPhysician()."'"
			);

			// Figure out appointments for this week
			$week_count = $GLOBALS['sql']->queryOne(
				"SELECT COUNT(*) AS week_count FROM scheduler WHERE ".
				"caldateof >= '".$begin_date."' AND ".
				"caldateof <= '".$end_date."' AND ".
				"calphysician='".$GLOBALS['this_user']->getPhysician()."'"
			);

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
			$day_count = $GLOBALS['sql']->query(
				"SELECT COUNT(*) AS day_count FROM scheduler WHERE ".
				"caldateof = '".date('Y-m-d')."' ".
				( $_SESSION['default_facility'] ?
					"AND calfacility='".addslashes($_SESSION['default_facility'])."' " : "" )
			);

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

		// Version 0.6.6
		//
		//	Add calappttemplate
		//
		if (!version_check($version, '0.6.6')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN calappttemplate INT UNSIGNED AFTER calrecurid');
			$sql->query('UPDATE '.$this->table_name.' '.
				'SET calappttemplate=0 WHERE id>0');
		}
	} // end function _update
}

register_module("SchedulerTable");

?>
