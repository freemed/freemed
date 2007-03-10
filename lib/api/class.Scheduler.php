<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.Scheduler
//
//	Holds methods for dealing with calendar and scheduling appointments.
//	Most methods from lib/calendar-functions.php that were important
//	should have been migrated here.
//
class Scheduler {

	// Variable: calendar_field_mapping
	//
	//	Contains the common names of calendar fields, mapped to
	//	their SQL names. For copying purposes, it also contains
	//	SQL names mapped to themselves.
	//
	protected $calendar_field_mapping = array (
		// Original mappings are first so they can be overwritten
		'caldateof' => 'caldateof',
		'caltype' => 'caltype',
		'calhour' => 'calhour',
		'calminute' => 'calminute',
		'calduration' => 'calduration',
		'calfacility' => 'calfacility',
		'calroom' => 'calroom',
		'calphysician' => 'calphysician',
		'calpatient' => 'calpatient',
		'calcptcode' => 'calcptcode',
		'calstatus' => 'calstatus',
		'calprenote' => 'calprenote',
		'calgroupid' => 'calgroupid',
		'calrecurnote' => 'calrecurnote',
		'calrecurid' => 'calrecurid',
		'calmark' => 'calmark',
		'calappttemplate' => 'calappttemplate',

		'date' => 'caldateof',
		'type' => 'caltype',
		'hour' => 'calhour',
		'minute' => 'calminute',
		'duration' => 'calduration',
		'facility' => 'calfacility',
		'patient' => 'calpatient',
		'room' => 'calroom',
		'physician' => 'calphysician',
			'provider' => 'calphysician',
		'cptcode' => 'calcptcode',
			'cpt' => 'calcptcode',
		'status' => 'calstatus',
		'note' => 'calprenote',
		'mark' => 'calmark',
		'groupid' => 'calgroupid',
			'group_id' => 'calgroupid',
			'group' => 'calgroupid',
		'recurnote' => 'calrecurnote',
		'recurid' => 'calrecurid',
		'appttemplate' => 'calappttemplate'
	);

	// STUB constructor
	public function __construct ( ) { } 

	// Method: GetDailyAppointments
	//
	// Parameters:
	//
	//	$date - (optional) Date to get appointments for. Defaults to current date.
	//
	//	$provider - (optional) Provider number
	//
	// Returns:
	//
	//	Hash of daily appointments
	//	* scheduler_id
	//	* patient
	//	* patient_id
	//	* provider
	//	* provider_id
	//	* note
	//	* hour
	//	* minute
	//	* appointment_time
	//	* status
	//
	// SeeAlso:
	//	<GetDailyAppointmentsRange>
	//
	public function GetDailyAppointments ( $date = NULL, $provider = 0 ) {
		return $this->GetDailyAppointmentsRange( $date, $date, $provider );
	} // end method GetDailyAppointments

	// Method: GetDailyAppointmentsRange
	//
	// Parameters:
	//
	//	$datefrom - Starting date.
	//
	//	$dateto - Ending date.
	//
	//	$provider - (optional) Provider number
	//
	// Returns:
	//
	//	Hash of daily appointments
	//	* scheduler_id
	//	* patient
	//	* patient_id
	//	* provider
	//	* provider_id
	//	* note
	//	* hour
	//	* minute
	//	* appointment_time
	//	* status
	//	* resource_type ( pat, temp )
	//
	// SeeAlso:
	//	<GetDailyAppointments>
	//
	public function GetDailyAppointmentsRange ( $datefrom = NULL, $dateto = NULL, $provider = 0 ) {
		$this_date = $datefrom ? $this->ImportDate($datefrom) : date('Y-m-d');
		if ($dateto != NULL) {
			$r_q = "s.caldateof >= '".addslashes($this_date)."' AND s.caldateof <= '".addslashes($this->ImportDate($dateto))."'";
		} else {
			// Single date query ....
			$r_q = "s.caldateof = '".addslashes($this_date)."'";
			
		}
		$query = "SELECT s.caldateof AS date_of, DATE_FORMAT(s.caldateof, '%m/%d/%Y') AS date_of_mdy, s.calhour AS hour, s.calminute AS minute, CONCAT(s.calhour, ':',LPAD(s.calminute, 2, '0')) AS appointment_time, CONCAT(ph.phylname, ', ', ph.phyfname) AS provider, ph.id AS provider_id, s.caltype AS resource_type, CASE s.caltype WHEN 'temp' THEN CONCAT( '[!] ', ci.cilname, ', ', ci.cifname, ' (', ci.cicomplaint, ')' ) ELSE CONCAT(pa.ptlname, ', ', pa.ptfname, ' (', pa.ptid, ')') END AS patient, pa.id AS patient_id, s.calprenote AS note, st.sname AS status, s.id AS scheduler_id FROM scheduler s LEFT OUTER JOIN scheduler_status ss ON s.id=ss.csappt LEFT OUTER JOIN schedulerstatustype st ON st.id=ss.csstatus LEFT OUTER JOIN physician ph ON s.calphysician=ph.id LEFT OUTER JOIN patient pa ON s.calpatient=pa.id LEFT OUTER JOIN callin ci ON s.calpatient=pa.id WHERE ( ${r_q} ) AND s.calstatus != 'cancelled' ".( $provider ? " AND s.calphysician=".$GLOBALS['sql']->quote($provider) : "" )." GROUP BY s.id, ss.csstamp ORDER BY s.caldateof, s.calhour, s.calminute, s.calphysician";
		return $GLOBALS['sql']->queryAll ( $query );
	} // end method GetDailyAppointmentsRange

	// Method: CopyAppointment
	//
	//	Copy the given appointment to a specified date
	//
	// Parameters:
	//
	//	$id - id for the specified appointment
	//
	//	$date - SQL date format (YYYY-MM-DD) specifying the
	//	date to copy the appointment
	//
	// Returns:
	//
	//	Boolean, whether successful
	//
	// See Also:
	//	<CopyGroupAppointment>
	//
	public function CopyAppointment ( $id, $date ) {
		$appointment = $this->get_appointment ( $id );
		$appointment['caldateof'] = $this->ImportDate( $date );
		$result = $this->SetAppointment ( $appointment );
		return $result;
	} // end method CopyAppointment
	public function copy_appointment ( $id, $date ) { return $this->CopyAppointment( $id, $date ); }

	// Method: CopyGroupAppointment
	//
	// Parameters:
	//
	//	$group_id - id for the group appointments
	//
	//	$date - Target date
	//
	// Return:
	//
	//	Boolean, whether successful
	//
	// See Also:
	//	<CopyAppointment>
	//
	public function CopyGroupAppointment ( $group_id, $date ) {
		$group_appointments = $this->FindGroupAppointments( $group_id );
		$result = true;
		foreach ($group_appointments AS $appointment) {
			$temp_result = $this->copy_appointment ( $appointment, $date );
			if (!($result and $temp_result)) {
				$result = false;
			}
		}
		return $result;
	} // end method CopyGroupAppointment
	public function copy_group_appointment ( $group_id, $date ) { return $this->CopyGroupAppointment( $group_id, $date ); }

	// Method: date_add
	//
	//	Addition method for date. Adds days to starting date.
	//
	// Parameters:
	//
	//	$starting - Date in YYYY-MM-DD format
	//
	//	$interval - Number of days to add to the starting date.
	//
	// Returns:
	//
	//	Date in SQL date format.
	//
	public function date_add ( $starting, $interval ) {
		if ($interval < 1) { return $starting; }
		$q = $GLOBALS['sql']->queryOne("SELECT DATE_ADD('".
			addslashes($this->ImportDate( $starting ))."', INTERVAL ".
			($interval+0)." DAY) AS mydate");
		return $q;
	} // end method date_add

	// Method: date_in_range
	//
	//	Determine if a date falls between a beginning and end date.
	//
	// Parameters:
	//
	//	$checkdate - Date to check. Should be in ANSI SQL date format
	//	(YYYY-MM-DD).
	//
	//	$dtbegin - Beginning of time span to compare against.
	//
	//	$dtend - Ending of time span to compare against.
	//
	// Returns:
	//
	//	Boolean value, whether date falls between specified dates.
	//
	public function date_in_range ($checkdate, $dtbegin, $dtend) {
		// split all dates into component parts
		list ($begin_y, $begin_m, $begin_d) = explode('-', $this->ImportDate( $dtbegin ));
		list ($end_y, $end_m, $end_d) = explode('-', $this->ImportDate( $dtend ));
		list ($cur_y, $cur_m, $cur_d) = explode('-', $this->ImportDate( $checkdate ));

		$end = $end_y . $end_m . $end_d;
		$start = $begin_y . $begin_m . $begin_d;
		$current = $cur_y . $cur_m . $cur_d;

		if ( ($current >= $begin) AND ($current <= $end) ) {
			return true;
		}

		return false;
	} // end method date_in_range
	
	// Method: date_in_the_past
	//
	//	Check to see if date is in the past
	//
	// Parameters:
	//
	//	$date - SQL formatted date string (YYYY-MM-DD)
	//
	// Returns:
	//
	//	Boolean, true if date is past, false if date is present or future.
	//
	public function date_in_the_past ( $datestamp ) {
		list ($y_c, $m_c, $d_c) = explode ('-', date('Y-m-d'));
		list ($y, $m, $d) = explode ('-', $this->ImportDate( $datestamp ));

		if ($y < $y_c) {
			return true;
		} elseif ($y > $y_c) {
			return false;
		}

		if ($m < $m_c) {
			return true;
		} elseif ($m > $m_c) {
			return false;
		}

		if ($d < $d_c) {
			return true;
		} elseif ($d > $d_c) {
			return false;
		} else {
			return false;
		}
	} // end method date_in_the_past

	// Method: display_hour
	//
	//	Creates AM/PM user-friendly hour display.
	//
	// Parameters:
	//
	//	$hour - Hour in 0..24 military format.
	//
	// Returns:
	//
	//	AM/PM display of hour
	//
  	public function display_hour ( $hour ) {
		// time checking/creation if/else clause
		if ($hour<12) {
			return $hour." AM";
		} elseif ($hour == 12) {
			return $hour." PM";
		} else {
			return ($hour-12)." PM";
		}
  	} // end method display_hour

	// Method: display_time
	//
	//	Creates AM/PM user-friendly time display.
	//
	// Parameters:
	//
	//	$hour - Hour in 0..24 military format.
	//
	//	$minute - Minute in 0..60 format.
	//
	// Returns:
	//
	//	User-friendly AM/PM display of time.
	//
	public function display_time ( $hour, $minute ) {
		$m = sprintf('%02s', $minute);
		if ($hour<12) {
			return $hour.":$m AM";
		} elseif ($hour == 12) {
			return $hour.":$m PM";
		} else {
			return ($hour-12).":$m PM";
		}
	} // end method display_time

	// Method: event_special
	//
	//	Return proper names for special event mappings, as per the
	//	group calendar and Travel.
	//
	// Parameters:
	//
	//	$mapping - Special id mapping. This is usually a number from
	//	0 to 8.
	//
	// Returns:
	//
	//	Text name of specified mapping.
	//
	public function event_special ( $mapping ) {
		switch ($mapping) {
			case 1: case 2: case 3: case 4:
			case 5: case 6: case 7: case 8:
				return freemed::config_value("cal". $mapping );
				break;

			default: return __("Travel"); break;
		}
	} // end method event_special

	// Method: FindDateAppointments
	//
	//	Look up list of appointments for specified day and provider.
	//
	// Parameters:
	//
	//	$date - Date in YYYY-MM-DD
	//
	//	$provider - (optional) id for the provider in question. If
	//	this is omitted, all providers will be queried.
	//
	// Returns:
	//
	//	Array of associative arrays containing appointment
	//	information
	//
	public function FindDateAppointments ( $date, $provider = -1 ) {
		$query = "SELECT * FROM scheduler WHERE ".
			"(caldateof = '".addslashes( $this->ImportDate( $date ) )."' ".
			"AND calstatus != 'cancelled' ".
			( $provider != -1 ? 
				"AND calphysician = '".prepare($provider)."'" :
				"" ).
			") ORDER BY calhour,calminute";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method FindDateAppointments
	public function find_date_appointments ( $date, $provider = -1 ) { return $this->FindDateAppointments( $date, $provider ); }

	// Method: FindGroupAppointments
	//
	//	Given a group id, return the appointments in that group
	//
	// Parameters:
	//
	//	$group_id - id for the group that is being searched for
	//
	// Returns:
	//
	//	Array of associative arrays containing appointment 
	//	information.
	//
	public function FindGroupAppointments ( $group_id ) {
		$query = "SELECT * FROM scheduler WHERE ( ".
			"calgroupid = '".addslashes($group_id)."' ".
			"AND calstatus != 'cancelled' ".
			" ) ".
			"ORDER BY caldateof, calhour, calminute";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method FindGroupAppointments
	public function find_group_appointments ( $group_id ) { return $this->FindGroupAppointments( $group_id ); }

	// Method: GetAppointment
	//
	//	Retrieves an appointment record from its id
	//
	// Parameters:
	//
	//	$id - id for the specified appointment
	//
	// Returns:
	//
	//	Associative array containing appointment information
	//
	public function GetAppointment ( $id ) {
		return $GLOBALS['sql']->get_link( 'scheduler', $id );
	} // end method GetAppointment
	public function get_appointment ( $id ) { return $this->GetAppointment( $id ); }

	// Method: get_time_string
	//
	//	Form a human readable time string from an hour and a minute.
	//
	// Parameters:
	//
	//	$hour - Hour in 24 hour format (0 to 24).
	//
	//	$minute - Minutes (0 to 60).
	//
	// Returns:
	//
	//	Formatted time string.
	//
	public function get_time_string ( $hour, $minute ) {
		if ($minute==0) $minute="00";

		// time checking/creation if/else clause
		if ($hour<12) {
			$_time = sprintf('%02d:%02d AM', $hour, $minute);
		} elseif ($hour == 12) {
			$_time = sprintf('%02d:%02d PM', $hour, $minute);
		} else {
			$_time = sprintf('%02d:%02d PM', $hour-12, $minute);
		}
		return $_time;
	} // end method get_time_string

	// Method: map
	//
	//	Creates a scheduler map. This is the 2nd generation of
	//	the depreciated interference map.
	//
	// Parameters:
	//
	//	$query - SQL query string.
	//
	// Returns:
	//
	//	"map" associative multi-dimentional array containing
	//	scheduling interference data.
	//
	// See Also:
	//	<map_fit>
	//	<map_init>
	//
	public function map ( $query ) {
		// Initialize the map;
		$idx = "";
		$map = $this->map_init();

		// Get the query
		$result = $GLOBALS['sql']->queryAll($query);

		// If nothing, return empty map
		if (count($result) < 1) { return $map; }

		// Run through query
		foreach ($result AS $r) {
			// Don't regard cancelled appointments
			if ($r['calstatus'] != 'cancelled') {
				// Move to "c" array, which is stripslashes'd
				foreach ($r AS $k => $v) {
					$c[(stripslashes($k))] = stripslashes($v);
				} // end removing slashes

				// Determine index
				$idx = ($c['calhour']+0).":".( $c['calminute']==0 ?
					"00" : ($c['calminute']+0) );
				
				// Insert into current position
				$map[$idx]['link'] = $c['id'];
				$map[$idx]['span'] = ceil($c['calduration'] / 5);
				if ($c['calmark'] > 0) {
					$map[$idx]['mark'] = $c['calmark'];
				}
				$cur_pos = $idx;
	
				// Clear out remaining portion of slot
				$count = 1;
				while ($count < $map[$idx]['span']) {
					// Move pointer forward
					$cur_pos = $this->next_time_increment($cur_pos);
					$count++;
	
					// Zero those records
					$map[$cur_pos]['link'] = 0;
					$map[$cur_pos]['span'] = 0;
				} // end clear out remaining portion of slot

			} // end if !cancelled
		} // end running through array

		// Return completed map
		return $map;
	} // end method map

	// Method: map_fit
	//
	//	Determine whether an appointment of the specified duration
	//	at the specified time will fit in the specified map.
	//
	// Parameters:
	//
	//	$map - Scheduler "map" as generated by <map>.
	//
	//	$time - Time string specifying the time of the appointment
	//	to check. Should be in format HH:MM.
	//
	//	$duration - (optional) Duration of the appointment in
	//	minutes. This is 5 by default.
	//
	//	$id - (optional) If this is specified it shows the
	//	pre-existing scheduler id for an appointment, so that if
	//	it is being moved, it does not conflict with itself.
	//
	// Returns:
	//
	//	Boolean, whether specified appointment fits into the
	//	specified map.
	//
	// See Also:
	//	<map>
	//	<map_init>
	//
	public function map_fit ( $map, $time, $duration = 5, $id = -1 ) {
		// If this is already booked, return false
		if ($map[$time]['span'] == 0) { return false; }
		if ($map[$time]['link'] != 0) { return false; }

		// If anything *after* it for its duration is booked...
		if ($duration > 5) {
			// Determine number of blocks to search
			$blocks = ceil(($duration - 1) / 5); $cur_pos = $time;
			for ($check=1; $check<$blocks; $check++) {
				// Increment pointer to time
				$cur_pos = $this->next_time_increment($cur_pos);

				// If we're part of this id, return true
				// (so we can slightly move a booking time)
				if ($map[$cur_pos]['link'] == $id) {
					return true;
				}

				// Check for past boundaries
				list ($a, $b) = explode (":", $cur_pos);
				if ($a>=freemed::config_value("calehr")) {
					return false;
				}

				// If there's a link, return false
				if ($map[$cur_pos]['link'] != 0) return false;
			} // end looping through longer duration
		} // end if duration > 5

		// If all else fails, return true
		return true;
	} // end method map_fit

	// Method: map_init
	//
	//	Creates a blank scheduler map.
	//
	// Returns:
	//
	//	Blank scheduler map (associative array).
	//
	// See Also:
	//	<map>
	//	<map_fit>
	//
	public function map_init () {
		$map = array ( );
		$map['count'] = 0;
		for ($hour=freemed::config_value("calshr");$hour<freemed::config_value("calehr");$hour++) {
			for ($minute=00; $minute<60; $minute+=5) {
				$idx = sprintf('%02s:%02s', $hour, $minute);
				$map[$idx]['link'] = 0; // no link
				$map[$idx]['span'] = 1; // one slot per
				$map[$idx]['mark'] = 0; // default marking
				$map[$idx]['selected'] = false; // selection
				$map[$idx]['physician'] = 0;
				$map[$idx]['room'] = 0;
			} // end init minute loop
		} // end init hour loop
		return $map;
	} // end method map_init

	// Method: MoveAppointment
	//
	//	Given an appointment id and data, modify an appointment
	//	record.
	//
	// Parameters:
	//
	//	$original - Original appointment id
	//
	//	$data - Associative array of data to be changed in the
	//	appointment record. See <calendar_field_mapping> for a
	//	list of acceptable keys.
	//
	// Returns:
	//
	//	Boolean, whether successful.
	//
	public function MoveAppointment ( $original, $data = NULL ) {
		// Check for bogus data
		if ($data == NULL) { return false; }

		// Only pass fields that are set
		$fields = array ( );
		foreach ($this->calendar_field_mapping AS $k => $v) {
			if (isset($data[$k])) {
				$fields[$v] = $data[$k];
			}
		}

		// Set modify
		$fields['caldateof'] = $this->ImportDate( $fields['caldateof'] );
		$fields['calmodified'] = SQL__NOW;

		$query = $GLOBALS['sql']->update_query (
			'scheduler',
			$fields,
			array ('id' => $original )
		);
		$result = $GLOBALS['sql']->query ( $query );
		return $result;
	} // end method MoveAppointment
	public function move_appointment ( $original, $data = NULL ) { return $this->MoveAppointment ( $original, $data ); }

	// Method: MoveGroupAppointment
	//
	//	Given a group id (for a group of appointments), modify a
	//	group of appointment records with the given data. This
	//	follows the same basic format as <MoveAppointment>
	//
	// Parameters:
	//
	//	$group_id - id for the appointment group
	//
	//	$data - Associative array of data to be changed in the
	//	appointment record. See <calendar_field_mapping> for a
	//	list of acceptable keys.
	//
	// Returns:
	//
	//	Boolean, whether successful.
	//
	// See Also:
	//	<MoveAppointment>
	//
	public function MoveGroupAppointment ( $group_id, $data ) {
		$group_appointments = $this->FindGroupAppointments($group_id);
		$result = true;
		foreach ($group_appointments AS $appointment) {
			$temp_result = $this->MoveAppointment (
				$appointment['id'],
				$data
			);
			if (!($result and $temp_result)) { $result = false; }
		} // end foreach
		return $result;
	} // end method MoveGroupAppointment
	public function move_group_appointment ( $group_id, $data ) { return $this->MoveGroupAppointment ( $group_id, $data ); }

	// Method: multimap
	//
	//	Creates 3rd generation multiple scheduling map. This is
	//	used to automatically create additional columns due to
	//	overlapping and overbooking.
	//
	// Parameters:
	//
	//	$query - SQL query string describing options.
	//
	//	$selected - (optional) Scheduler table id of selected
	//	appointment. If this is not specified, no appointment
	//	will be selected by default.
	//
	// Returns:
	//
	//	Multimap (associative array).
	//
	// See Also:
	//	<map>
	//
	public function multimap ( $query, $selected = -1 ) {
		// Initialize the first map and current index
		$idx = "";
		$maps[0] = $this->map_init();

		// Get the query
		$result = $GLOBALS['sql']->queryAll($query);

		// If nothing, return empty multimap
		if (count($result) < 1) { return $maps; }

		// Run through query
		foreach ($result AS $r) {
			// Ignore cancelled appointments
			if ($r['calstatus'] != 'cancelled') {
				// Move to "c" array, which is stripslashes'd
				foreach ($r AS $k => $v) {
					$c[(stripslashes($k))] = stripslashes($v);
				} // end removing slashes

				// Determine index
				$idx = sprintf('%02s:%02s', $c['calhour'], $c['calminute']);
				// Determine which is the first map that this fits into
				$cur_map = 0; $mapped = false;
				while (!$mapped) {
					if (!$this->map_fit($maps[$cur_map], $idx, $c['calduration'])) {
						// Check for recursion ....
						if ($cur_map > 10) {
							syslog(LOG_INFO, "Scheduler| appointment recursion detected for scheduler record #".$c['id']);
							$mapped = true; // skip
						}
						// Move to the next map
						$cur_map++;
						if (!is_array($maps[$cur_map])) {
							$maps[$cur_map] = $this->map_init();
						}
					} else {
						// Jump out of the loop
						$mapped = true;
					}
				} // end while not mapped
			
				// Insert into current position
				$maps[$cur_map][$idx]['link'] = $c['id'];
				$maps[$cur_map][$idx]['span'] = ceil($c['calduration'] / 5);
				$maps[$cur_map][$idx]['physician'] = $c['calphysician'];
				$maps[$cur_map][$idx]['room'] = $c['calroom'];
				// Handle appointment template colors, if joined
				$maps[$cur_map][$idx]['color'] = $c['atcolor'];

				// Check for selected
				if ($c['id'] == $selected) {
					$maps[$cur_map][$idx]['selected'] = true;
				}
			
				if ($c['calmark'] > 0) {
					$maps[$cur_map][$idx]['mark'] = $c['calmark'];
				}
				$cur_pos = $idx;

				// Clear out remaining portion of slot
				$count = 1;
				while ($count < $maps[$cur_map][$idx]['span']) {
					// Move pointer forward
					$cur_pos = $this->next_time_increment($cur_pos);
					$count++;

					// Zero those records
					$maps[$cur_map][$cur_pos]['link'] = 0;
					$maps[$cur_map][$cur_pos]['span'] = 0;
				} // end clear out remaining portion of slot
			} // end if !cancelled
		} // end running through array

		// Return completed maps
		return $maps;
	} // end method multimap

	// Method: next_available
	//
	//	Get next available slot with appropriate parameters.
	//
	// Parameters:
	//
	//	$_criteria - Hash containing one or more of the following:
	//	* after    - After a particular hour
	//	* date     - Date to start the search from
	//	* days     - Number of days to search (defaults to 4)
	//	* duration - In minutes (defaults to 5)
	//	* forceday - Force day to be day of week (1..7 ~ Mon..Sun)
	//	* location - Room location
	//	* provider - With a particular provider
	//	* single   - Provide single answer
	//	* weekday  - Force weekday (boolean) 
	//
	// Returns:
	//
	//	array ( of array ( date, hour, minute ) )
	//	false if nothing is open
	//
	public function next_available ( $_criteria ) {
		// Error checking
		if ($_criteria['days']<1 or $_criteria['days']>90) {
			$days = 4;
		} else {
			$days = $_criteria['days'];
		}

		// Get duration
		$duration = $_criteria['duration'] ? $_criteria['duration'] : 5;

		// Loop through days to create c_days array
		$i_cur = $_criteria['date'] ? $this->ImportDate( $_criteria['date'] ) : date('Y-m-d');
		$i_add = true;
		list ($i_y, $i_m, $i_d) = explode ('-', $i_cur);
		// Check for criteria ...
		if ($_criteria['weekday']) {
			$dow = strftime("%u", mktime(0,0,0,$i_m,$i_d,$i_y));
			if ($dow > 5) { $i_add = false; }
		}
		if ($_criteria['forceday']) {
			$dow = strftime("%u", mktime(0,0,0,$i_m,$i_d,$i_y));
//			print "current day = $i_cur, dow = $dow<br/>\n";
			if ($dow != $_criteria['forceday']) { $i_add = false; }
		}
		if ($i_add) { $c_days[] = $i_cur; } // start with current?
		for ($i=1; $i<=$days; $i++) {
			$i_cur = $this->date_add($i_cur, 1);
			list ($i_y, $i_m, $i_d) = explode ('-', $i_cur);
			$i_add = true;
			// Check for criteria ...
			if ($_criteria['weekday']) {
				$dow = strftime("%u", mktime(0,0,0,$i_m,$i_d,$i_y));
				if ($dow > 5) { $i_add = false; }
			}
			if ($_criteria['forceday']) {
				$dow = strftime("%u", mktime(0,0,0,$i_m,$i_d,$i_y));
//				print "current day = $i_cur, dow = $dow<br/>\n";
				if ($dow != $_criteria['forceday']) { $i_add = false; }
			}
			if ($i_add) { $c_days[] = $i_cur; }
		} // end for i loop for number of days

		// Return false if there are no days available as specified
		if (count($c_days) < 1) { return array(false); }

		// Create basic SQL criteria
		if ($_criteria['after']) {
			$starting_time = $_criteria['after'];
		} else {
			$starting_time = freemed::config_value("calshr");
		}
		if ($_criteria['location']) {
			$b_criteria[] = "calfacility = '".addslashes($_criteria['location'])."'";
		}

		// After we have gotten all of the prospective days, run
		// some maps to see what we have
		foreach ($c_days AS $this_day) {
			$m_criteria = array_merge(
				$b_criteria,
				array("caldateof = '".addslashes($this_day)."'", "calstatus != 'cancelled'")
			);
			$map = $this->map(
				"SELECT * FROM scheduler WHERE ".
				join(' AND ', $m_criteria)
			);

			// Loop through the map and use map_fit() queries
			// to return the first possible fit
			for($h=$starting_time; $h<freemed::config_value('calehr'); $h++) {
				for ($m='00'; $m<60; $m+=5) {
					if ($this->map_fit($map, sprintf('%02s:%02s', $h, $m), $duration)) {
						if ($_criteria['single']) {
							return array ($this_day, $h, $m);
						} else {
							$found = true;
							$res[] = array ($this_day, $h, $m);
						}
					} // end if map_fit
				} // end minute loop
			} // end hour loop
		} // end for each possible day

		// If all else fails, return array(false), otherwise results
		if (!$found) {
			return false;
		} else {
			return $res;
		}
	} // end method next_available

	// Method: next_time_increment
	//
	//	Increment time slot by 5 minutes.
	//
	// Parameters:
	//
	//	$time - Time in HH:MM format.
	//
	//	$increment - (optional) Size of time slots. Defaults to 5 min.
	//
	// Returns:
	//
	//	Next time slot in HH:MM format.
	//
	public function next_time_increment ( $time, $increment = 5 ) {
		// Save us from bad data
		if ($increment < 1) { return $time; }

		// Split into time components
		list ($h, $m) = explode (":", $time);

		$new_m = ($m + $increment) % 60;
		$new_h = (int)(($m + $increment) / 60) + $h;
		return sprintf('%02s:%02s', $new_h, $new_m);
	} // end method next_time_increment

	// Method: SetAppointment 
	//
	//	Create an appointment record with the specified data
	//
	// Parameters:
	//
	//	$data - Associative array of values to be used when
	//	setting the appointment. Uses <calendar_field_mapping>
	//	to determine values from keys.
	//
	// Returns:
	//
	//	id of created appointment
	//
	public function SetAppointment ( $data = NULL ) {
		// Check for bogus data
		if ($data == NULL) { return false; }

		// Set defaults
		$fields = array (
			'caltype' => 'pat',
			'calstatus' => 'scheduled'
		);

		// Only pass fields that are set as overrides
		foreach ($this->calendar_field_mapping AS $k => $v) {
			if (isset($data[$k])) {
				$fields[$v] = $data[$k];
			}
		}

		// Set add and modify
		$fields['calcreated'] = SQL__NOW;
		$fields['calmodified'] = SQL__NOW;
		$fields['caldateof'] = $this->ImportDate( $fields['caldateof'] );

		$query = $GLOBALS['sql']->insert_query (
			'scheduler',
			$fields
		);
		$result = $GLOBALS['sql']->query ( $query );
		if (!$result) {
			return false; 
		} else {
			return $GLOBALS['sql']->last_record ( $result );
		}
	} // end method SetAppointment
	public function set_appointment ( $data = NULL ) { return $this->SetAppointment ( $data ); }

	// Method: SetGroupAppointment
	//
	// Parameters:
	//
	//	$patients - Array of patient identifiers. The first of this
	//	array will be the appointment used to generate the group id.
	//
	//	$data - Associative array of data used to populate the
	//	appointment data. Same syntax as <SetAppointment>.
	//
	// Returns:
	//
	//	Group key id for new group created.
	//
	// See Also:
	//	<SetAppointment>
	//
	public function SetGroupAppointment ( $patients, $data ) {
		$first_patient = $patients[0];

		// Create the initial appointment, and get the key
		$key = $this->SetAppointment ( $first_patient, $data );
		$count = 0;
		$my_data = $data;
		foreach ($patient_array as $patient) {
			// For the first patient, we update, everyone else
			// we wrap SetAppointment() again
			if ($count == 0) {
				// Pass the id back as the group key
				$this->MoveAppointment (
					$key,
					array ('group' => $key)
				);
				// Make sure subsequent runs have this
				$my_data['group'] = $key;
			} else {
				// Just wrap it
				$this->SetAppointment($patient, $my_data);
			}
			$count++;
		} // end foreach patient_array

		// Pass the group key back to FreeMED
		return $key;
	} // end method SetGroupAppointment
	public function set_group_appointment ( $patients, $data ) { return $this->SetGroupAppointment ( $patients, $data ); }

	// Method: set_recurring_appointment
	//
	//	Given an appointment (by its id) and a set of dates,
	//	replicate the appointment exactly on given dates. All
	//	of the appointments can later be accessed through the
	//	use of the calrecurid field. This allows for recurring
	//	appointments to be modified and deleted. A natural
	//	language description of the appointment is placed in
	//	recurnote.
	//
	// Parameters:
	//
	//	$appointment - id of the appointment in question
	//
	//	$ts - Array of timestamps containing the dates for the
	//	appointment to repeat
	//
	//	$desc - Description of the recurrance
	//
	public function set_recurring_appointment ( $appointment, $ts, $desc ) {
		// Fetch the original
		$a = $this->get_appointment ( $appointment );

		// Instead of actually physically modifying the record,
		// we use MoveAppointment() to set the recurring information,
		// causing it to begin a group of recurring appts.
		if ($a['calgroupid'] == 0) {
			$this->MoveAppointment (
				$appointment,
				array (
					'recurnote' => $desc,
					'recurid' => $appointment
				)
			);
		} else {
			$this->MoveGroupAppointment (
				$a['calgroupid'],
				array (
					'recurnote' => $desc,
					'recurid' => $appointment
				)
			);
		}

		// Loop through array of timestamps, without replicating
		// the original appointment. Since we have already set the
		// original recurrence properly, all subsequent instances
		// will also be correct
		foreach ($ts AS $timestamp) {
			$sql_date = strftime('%Y-%m-%d', $timestamp);
			if (strcmp($sql_date, $a['caldateof']) != 0) {
				// Check for group appointment
				if ($a['calgroupid']) {
					$this->copy_appointment( $appointment, $sql_date );
				} else {
					$this->copy_group_appointment( $appointment, $sql_date );
				} // end if
			} // end checking for current date
		} // end foreach
	} // end method set_recurring_appointment

	// Method: scroll_prev_month
	//
	//	Scroll a given date back by a month
	//
	// Parameters:
	//
	//	$given_date - (optional) Date to scroll back from in SQL date
	//	format (YYYY-MM-DD). Defaults to current date.
	//
	// Returns:
	//
	//	SQL formatted date string for a date approximately one month
	//	previous to the given date.
	//
	public function scroll_prev_month ($given_date="") {
		$cur_date = date("Y-m-d");
		$this_date = $given_date ? $this->ImportDate( $given_date ) : date('Y-m-d');
		list ($y, $m, $d) = explode ("-", $this_date);
		$m--;
		if ($m < 1) { $m = 12; $y--; }
		if (!checkdate ($m, $d, $y)) {;
			if ($d > 28) $d = 28; // be safe for February...
		}
		return date( "Y-m-d",mktime(0,0,0,$m,$d,$y));
	} // end function fc_scroll_prev_month

	// Method: scroll_next_month
	//
	//	Scroll a given date forward by a month
	//
	// Parameters:
	//
	//	$given_date - (optional) Date to scroll forward from in SQL date
	//	format (YYYY-MM-DD). Defaults to current date.
	//
	// Returns:
	//
	//	SQL formatted date string for a date approximately one month
	//	after the given date.
	//
	public function scroll_next_month ($given_date="") {
		$cur_date = date("Y-m-d");
		$this_date = $given_date ? $this->ImportDate( $given_date ) : date('Y-m-d');
		list ($y, $m, $d) = explode ("-", $this_date);
		$m++;
		if ($m > 12) { $m -= 12; $y++; }
		if (!checkdate ($m, $d, $y)) {
			$d = 28; // be safe for February...
		}
		return date( "Y-m-d",mktime(0,0,0,$m,$d,$y));
	} // end function scroll_next_month

	// Method: ImportDate
	//
	//	Import date to internal FreeMED format.
	//
	// Parameters:
	//
	//	$input - Input date.
	//
	// Returns:
	//
	//	YYYY-MM-DD formatted date
	//
	public function ImportDate ( $input ) {
		$data = $input;
		switch (true) {
			case ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $data, $regs):
			return sprintf('%04d-%02d-%02d', $regs[1], $regs[2], $regs[3]);
			break;

			case ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $data, $regs):
			if ($regs[3] < 30) {
				$regs[3] += 2000;
			} elseif ($regs[3] < 1800) {
				$regs[3] += 1900;
			}
			return sprintf('%04d-%02d-%02d', $regs[3], $regs[1], $regs[2]);
			break;

			default:
			return false;
			break;
		}
	} // end method ImportDate

} // end method Scheduler

?>
