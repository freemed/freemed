<?php
	// $Id$
	// $Author$

// Class: FreeMED.Scheduler
//
//	Holds methods for dealing with calendar ans scheduling appointments.
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
	var $calendar_field_mapping = array (
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
		'groupid' => 'calgroupid',
			'group_id' => 'calgroupid',
			'group' => 'calgroupid',
		'recurnote' => 'calrecurnote',
		'recurid' => 'calrecurid'
	);

	// STUB constructor
	function Scheduler ( ) { }

	// Method: copy_appointment
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
	//	<copy_group_appointment>
	//
	function copy_appointment ( $id, $date ) {
		$appointment = $this->get_appointment ( $id );
		$appointment['caldateof'] = $date;
		$result = $this->set_appointment ( $appointment );
		return $result;
	} // end method copy_appointment

	// Method: copy_group_appointment
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
	//	<copy_appointment>
	//
	function copy_group_appointment ( $group_id, $date ) {
		$group_appointments = $this->find_group_appointments($group_id);
		$result = true;
		foreach ($group_appointments AS $appointment) {
			$temp_result = $this->copy_appointment ( $appointment, $date );
			if (!($result and $temp_result)) {
				$result = false;
			}
		}
		return $result;
	} // end method copy_group_appointment

	// Method: date_add
	//
	//	Addition method for date. Adds days to starting date.
	//
	// Parameters:
	//
	//
	// Returns:
	//
	//	Date in SQL date format.
	//
	function date_add ( $starting, $interval ) {
		if ($interval < 1) { return $starting; }
		$q = $GLOBALS['sql']->query("SELECT DATE_ADD('".
			addslashes($starting)."', INTERVAL ".
			($interval+0)." DAY) AS mydate");
		extract($GLOBALS['sql']->fetch_array($q));
		return $mydate;
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
	function date_in_range ($checkdate, $dtbegin, $dtend) {
		// split all dates into component parts
		list ($begin_y, $begin_m, $begin_d) = explode('-', $dtbegin);
		list ($end_y, $end_m, $end_d) = explode('-', $dtend);
		list ($cur_y, $cur_m, $cur_d) = explode('-', $checkdate);

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
	function date_in_the_past ($datestamp) {
		list ($y_c, $m_c, $d_c) = explode ('-', date('Y-m-d'));
		list ($y, $m, $d) = explode ('-', $datestamp);

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
  	function display_hour ( $hour ) {
		// time checking/creation if/else clause
		if ($hour<12)
			return $hour." AM";
		elseif ($hour == 12)
			return $hour." PM";
		else
			return ($hour-12)." PM";
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
	function display_time ( $hour, $minute ) {
		$m = ($minute<10 ? '0' : '').($minute+0);
		if ($hour<12)
			return $hour.":$m AM";
		elseif ($hour == 12)
			return $hour.":$m PM";
		else
			return ($hour-12).":$m PM";
		
	} // end method display_time

	// Method: event_calendar_print
	//
	//	Display calendar event from scheduler.
	//
	// Parameters:
	//
	//	$event - scheduler table event id number.
	//
	//	$short - (optional) boolean, whether to shorten the
	//	view for a concise output. Defaults to false.
	//
	// Returns:
	//
	//	XHTML formatted calendar event.
	//
	function event_calendar_print ( $event, $short = false ) {
		global $sql;

		$cache = freemed::module_cache();

		// Get event
		$my_event = freemed::get_link_rec($event, "scheduler");

		// Handle travel
		if ($my_event['calpatient'] == 0) {
			return $this->event_special($my_event['calmark'])." ".
			"(".$my_event['calduration']."m)\n";
		}

		// Get patient information
		$my_patient = CreateObject('_FreeMED.Patient', $my_event['calpatient'],
			($my_event['caltype']=="temp"));

		if (!$short) {
			return "<a HREF=\"".(($my_event['caltype']=="temp") ?
				"call-in.php?action=display&id=" :
				"manage.php?id=" ).
			$my_patient->id."\"".
			">".trim($my_patient->fullName())."</a> ".
			"(".$my_event['calduration']."m)<br/>\n".
			"<a href=\"book_appointment.php?id=".
				urlencode($my_event['id'])."&".
				"type=".$my_event['caltype']."\" ".
			">".__("Move")."</a>".
			( module_function('progressnotes', 'noteForDate',
					array($my_event['calpatient'], $my_event['caldateof'])) ?
			"&nbsp;[".__("NOTE")."] " : "" ).
			//" ( phy = ".$my_event['calphysician']." ) ".
			( !empty($my_event['calprenote']) ?
			"<br/>&nbsp;&nbsp;<i>".
			prepare(stripslashes($my_event[calprenote])).
			"</i>\n" : "" );
		} else {
			return "<a HREF=\"".(($my_event['caltype']=="temp") ?
				"call-in.php?action=display&id=" :
				"manage.php?id=" ).
			$my_patient->id."\"".
			"><acronym TITLE=\"".prepare(stripslashes($my_event[calprenote]))."\">".
			"<small>".
			trim($my_patient->fullName()).
			"</small>".
			"</acronym></a> ".
			"<acronym TITLE=\"".prepare(stripslashes($my_event[calprenote]))."\">".
			"<small>(".$my_event['calduration']."m)</small>".
			"</acronym>\n";
		}
	} // end method event_calendar_print

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
	function event_special ( $mapping ) {
		switch ($mapping) {
			case 1: case 2: case 3: case 4:
			case 5: case 6: case 7: case 8:
				return freemed::config_value("cal". $mapping );
				break;

			default: return __("Travel"); break;
		}
	} // end method event_special

	// Method: find_date_appointments
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
	function find_date_appointments ( $date, $provider = -1 ) {
		$query = "SELECT * FROM scheduler WHERE ".
			"(caldateof = '".addslashes($date)."' ".
			( $provider != -1 ? 
				"AND calphysician = '".prepare($provider)."'" :
				"" ).
			") ORDER BY calhour,calminute";
		return $this->_query_to_result_array ( $query );
	} // end method find_date_appointments

	// Method: find_group_appointments
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
	function find_group_appointments ( $group_id ) {
		$query = "SELECT * FROM scheduler WHERE ".
			"( calgroupid = '".addslashes($group_id)."' ) ".
			"ORDER BY caldateof, calhour, calminute";
		return $this->_query_to_result_array ( $query );
	} // end method find_group_appointments

	// Method: generate_calendar_mini
	//
	//	Generate a miniature calendar, linking to the given page
	//
	// Parameters:
	//
	//	$given_date - Date to be selected
	//
	//	$this_url - URL to append the date to in links
	//
	// Returns:
	//
	//	HTML code for miniature calendar
	//
	function generate_calendar_mini ($given_date, $this_url) {
		// mostly hacked code from TWIG's calendar - ancient
		$cur_date = date('Y-m-d');

		$lang_days = array (
			"",
			__("Sun"),
			__("Mon"),
			__("Tue"),
			__("Wed"),
			__("Thu"),
			__("Fri"),
			__("Sat")
		);
		$lang_months = array (
			'',
			__("Jan"),
			__("Feb"),
			__("Mar"),
			__("Apr"),
			__("May"),
			__("Jun"),
			__("Jul"),
			__("Aug"),
			__("Sep"),
			__("Oct"),
			__("Nov"),
			__("Dec")
		);

    // break current day into pieces
    list ($cur_year, $cur_month, $cur_day) = explode ("-", $cur_date);
    if ($cur_month < 10) $cur_month = "0".$cur_month;
    if ($cur_day   < 10) $cur_day   = "0".$cur_day  ;

    // validate day
    if ((empty ($given_date)) or (!strpos($given_date, "-")))
          { $this_date = $cur_date;   }
     else { $this_date = $given_date; }

    // break day into pieces
    list ($this_year, $this_month, $this_day) = explode ("-", $this_date);

    // Figure out the last day of the month
    $lastday  [4] = $lastday [6] = $lastday [9] = $lastday [11] = 30;
    // check for leap years in february)
    if (checkdate( $this_month, 29, $this_year )) { $lastday [2] = 29; }
      else                                        { $lastday [2] = 28; }
    $lastday  [1] = $lastday  [3] = $lastday  [5] = $lastday [7] =
    $lastday  [8] = $lastday [10] = $lastday [12] = 31;

    // generate top of table
    $buffer .= "
     <center>
     <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"3\" VALIGN=\"MIDDLE\"
      ALIGN=\"CENTER\" class=\"calendar_mini\" bgcolor=\"#dfdfdf\">
      <tr BGCOLOR=\"#ffffff\">
       <td ALIGN=\"LEFT\" colspan=\"2\">
    ";

    // previous month link
    $buffer .= "     
     <a href=\"$this_url&selected_date=".
       $this->scroll_prev_month(
        $this->scroll_prev_month(
         $this->scroll_prev_month($this_date)
        )
       )."\" class=\"button_text\"
      >3</A>
     <a href=\"$this_url&selected_date=".$this->scroll_prev_month($this_date)."\"
      class=\"button_text\"><small>".__("prev")."</small></a>
     </td>
     <td COLSPAN=\"5\" ALIGN=\"CENTER\">
       <b>".prepare($lang_months[0+$this_month])." ".$this_year."</b>
     </td>
     <td ALIGN=\"RIGHT\" colspan=\"2\">
     <a href=\"$this_url&selected_date=".$this->scroll_next_month($this_date)."\"
      class=\"button_text\"><small>".__("next")."</small></a>
     <a href=\"$this_url&selected_date=".
       $this->scroll_next_month(
        $this->scroll_next_month(
         $this->scroll_next_month($this_date)
        )
       )."\" class=\"button_text\"
      >3</a>
     </td>
     </tr>
     <tr>
      <td colspan=\"1\">&nbsp;</td>
    ";
    // print days across top
    for( $i = 1; $i <= 7; $i++) {
     $buffer .= "
      <td ALIGN=\"CENTER\">
       <small>".htmlentities($lang_days[$i])."</small>
      </td>
     ";
    } // end of day display
    $buffer .= "
      <td colspan=\"1\">&nbsp;</td>
     </tr>
     <tr>
      <td colspan=\"1\">&nbsp;</td>
    ";

    // calculate first day
    $first_day = date( 'w', mktime( 0, 0, 0, $this_month, 1, $this_year ) );
    $day_row = 0;

    if( $first_day > 0 ) {
  	while( $day_row < $first_day ) {
   		$buffer .= "\t<td ALIGN=\"RIGHT\" BGCOLOR=\"#dfdfdf\">&nbsp;</td>\n";
   		$day_row += 1;  
  		}
 	} // end while day row < first day

 	while( $day < $lastday[($this_month + 0)] ) 
		{
  		if( ( $day_row % 7 ) == 0) 
			{
   			$buffer .= "\t<td colspan=\"1\">&nbsp;</td>\n".
				"</tr>\n<tr>\n".
				"<td colspan=\"1\">&nbsp;</td>\n";
  			}

  		$dayp = $day + 1;

        $thisclass = (
	  ( $dayp       == $cur_day AND
            $this_month == $cur_month AND
            $this_year  == $cur_year ) ?
            "calendar_mini_selected" : 
	  ( $dayp       == $this_day ?
	    "calendar_mini_current" : "calendar_mini_cell" ) );
       
	$buffer .= "<td align=\"RIGHT\" class=\"".$thisclass."\">\n";

        $buffer .= "<a ".
	  "href=\"$this_url&selected_date=".
         date("Y-m-d",mktime(0,0,0,$this_month,$dayp,$this_year) ).
         "\">$dayp</a>\n";
      $buffer .= "
       </td>
      ";
      $day++;
      $day_row++;
    }

		while( $day_row % 7 ) {
			$buffer .= "<td ALIGN=\"RIGHT\" BGCOLOR=\"#dfdfdf\">&nbsp;</td>\n";
			$day_row += 1;  
		} // end of day row
		$buffer .= "<td colspan=\"1\">&nbsp;</td>\n".
		"</tr><tr>\n".
		"<td COLSPAN=\"9\" ALIGN=\"RIGHT\" class=\"button_style\">\n".
		"<a HREF=\"$this_url&selected_date=".$cur_year."-".$cur_month."-".
		$cur_day."\" class=\"button_text\" ".
		"><small>".__("go to today")."</small></a>\n".
		"</td></tr></table></center>\n";
		return $buffer;
	} // end function generate_calendar_mini

	// Method: get_appointment
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
	function get_appointment ( $id ) {
		$query = "SELECT * FROM scheduler WHERE ".
			" ( id = '".addslashes($id)."' ) ";
		$result = $GLOBALS['sql']->query( $query );
		return $GLOBALS['sql']->fetch_array( $result );
	} // end method get_appointment

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
	function get_time_string ( $hour, $minute ) {
		if ($minute==0) $minute="00";

		// time checking/creation if/else clause
		if ($hour<12) {
			$_time = $hour.":".$minute." AM";
		} elseif ($hour == 12) {
			$_time = $hour.":".$minute." PM";
		} else {
			$_time = ($hour-12).":".$minute." PM";
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
	function map ( $query ) {
		global $sql;

		// Initialize the map;
		$idx = "";
		$map = $this->map_init();

		// Get the query
		$result = $sql->query($query);

		// If nothing, return empty map
		if (!$sql->results($result)) return $map;

		// Run through query
		while ($r = $sql->fetch_array($result)) {
			// Move to "c" array, which is stripslashes'd
			foreach ($r AS $k => $v) {
				$c[(stripslashes($k))] = stripslashes($v);
			} // end removing slashes

			// Determine index
			$idx = ($c['calhour']+0).":".( $c['calminute']==0 ?
				"00" : ($c['calminute']+0) );
			
			// Insert into current position
			$map[$idx]['link'] = $c['id'];
			$map[$idx]['span'] = ceil($c['calduration'] / 15);
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
	//	minutes. This is 15 by default.
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
	function map_fit ( $map, $time, $duration=15, $id = -1 ) {
		// If this is already booked, return false
		if ($map[$time]['span'] == 0) { return false; }
		if ($map[$time]['link'] != 0) { return false; }

		// If anything *after* it for its duration is booked...
		if ($duration > 15) {
			// Determine number of blocks to search
			$blocks = ceil(($duration - 1) / 15); $cur_pos = $time;
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
		} // end if duration > 15

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
	function map_init () {
		$map = array ( );
		$map['count'] = 0;
		for ($hour=freemed::config_value("calshr");$hour<freemed::config_value("calehr");$hour++) {
			for ($minute=00; $minute<60; $minute+=15) {
				$idx = $hour.":".($minute==0 ? "00" : $minute);
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

	// Method: move_appointment
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
	function move_appointment ( $original, $data = NULL ) {
		// Check for bogus data
		if ($data == NULL) { return false; }

		// Only pass fields that are set
		$fields = array ( );
		foreach ($this->calendar_field_mapping AS $k => $v) {
			if (isset($data[$k])) {
				$fields[$v] = $data[$k];
			}
		}

		$query = $GLOBALS['sql']->update_query (
			'scheduler',
			$fields,
			array ('id' => $original )
		);
		$result = $GLOBALS['sql']->query ( $query );
		return $result;
	} // end method move_appointment

	// Method: move_group_appointment
	//
	//	Given a group id (for a group of appointments), modify a
	//	group of appointment records with the given data. This
	//	follows the same basic format as <move_appointment>
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
	//	<move_appointment>
	//
	function move_group_appointment ( $group_id, $data ) {
		$group_appointments = $this->find_group_appointments($group_id);
		$result = true;
		foreach ($group_appointments AS $appointment) {
			$temp_result = $this->move_appointment (
				$appointment['id'],
				$data
			);
			if (!($result and $temp_result)) { $result = false; }
		} // end foreach
		return $result;
	} // end method move_group_appointment

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
	function multimap ( $query, $selected = -1 ) {
		global $sql;

		// Initialize the first map and current index
		$idx = "";
		$maps[0] = $this->map_init();

		// Get the query
		$result = $sql->query($query);

		// If nothing, return empty multimap
		if (!$sql->results($result)) return $maps;

		// Run through query
		while ($r = $sql->fetch_array($result)) {
			// Move to "c" array, which is stripslashes'd
			foreach ($r AS $k => $v) {
				$c[(stripslashes($k))] = stripslashes($v);
			} // end removing slashes

			// Determine index
			$idx = ($c['calhour']+0).":".( $c['calminute']==0 ?
				"00" : ($c['calminute']+0) );

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
			$maps[$cur_map][$idx]['span'] = ceil($c['calduration'] / 15);
			$maps[$cur_map][$idx]['physician'] = $c['calphysician'];
			$maps[$cur_map][$idx]['room'] = $c['calroom'];

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
	//	* duration - In minutes (defaults to 15)
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
	function next_available ( $_criteria ) {
		// Error checking
		if ($_criteria['days']<1 or $_criteria['days']>90) {
			$days = 4;
		} else {
			$days = $_criteria['days'];
		}

		// Get duration
		$duration = $_criteria['duration'] ? $_criteria['duration'] : 15;

		// Loop through days to create c_days array
		$i_cur = $_criteria['date'] ? $_criteria['date'] : date('Y-m-d');
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
				array("caldateof = '".addslashes($this_day)."'")
			);
			$map = $this->map(
				"SELECT * FROM scheduler WHERE ".
				join(' AND ', $m_criteria)
			);

			// Loop through the map and use map_fit() queries
			// to return the first possible fit
			for($h=$starting_time; $h<freemed::config_value('calehr'); $h++) {
				for ($m='00'; $m<60; $m+=15) {
					if ($this->map_fit($map, "$h:$m", $duration)) {
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
	//	Increment time slot by 15 minutes.
	//
	// Parameters:
	//
	//	$time - Time in HH:MM format.
	//
	// Returns:
	//
	//	Next time slot in HH:MM format.
	//
	function next_time_increment ( $time ) {
		// Split into time components
		list ($h, $m) = explode (":", $time);
		
		// Decide what to do based on the minutes
		switch ($m) {
			case "00": $return = $h.":15"; break;
			case "15": $return = $h.":30"; break;
			case "30": $return = $h.":45"; break;
			case "45": $return = ($h+1).":00"; break;
		}
		return $return;
	} // end method next_time_increment

	// Method: set_appointment
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
	function set_appointment ( $data = NULL ) {
		// Check for bogus data
		if ($data == NULL) { return false; }

		// Only pass fields that are set
		$fields = array ( );
		foreach ($this->calendar_field_mapping AS $k => $v) {
			if (isset($data[$k])) {
				$fields[$v] = $data[$k];
			}
		}

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
	} // end method set_appointment

	// Method: set_group_appointment
	//
	// Parameters:
	//
	//	$patients - Array of patient identifiers. The first of this
	//	array will be the appointment used to generate the group id.
	//
	//	$data - Associative array of data used to populate the
	//	appointment data. Same syntax as <set_appointment>.
	//
	// Returns:
	//
	//	Group key id for new group created.
	//
	// See Also:
	//	<set_appointment>
	//
	function set_group_appointment ( $patients, $data ) {
		$first_patient = $patients[0];

		// Create the initial appointment, and get the key
		$key = $this->set_appointment ( $first_patient, $data );
		$count = 0;
		$my_data = $data;
		foreach ($patient_array as $patient) {
			// For the first patient, we update, everyone else
			// we wrap set_appointment() again
			if ($count == 0) {
				// Pass the id back as the group key
				$this->move_appointment (
					$key,
					array ('group' => $key)
				);
				// Make sure subsequent runs have this
				$my_data['group'] = $key;
			} else {
				// Just wrap it
				$this->set_appointment($patient, $my_data);
			}
			$count++;
		} // end foreach patient_array

		// Pass the group key back to FreeMED
		return $key;
	} // end method set_group_appointment

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
	function set_recurring_appointment ( $appointment, $ts, $desc ) {
		// Fetch the original
		$a = $this->get_appointment ( $appointment );

		// Instead of actually physically modifying the record,
		// we use move_appointment to set the recurring information,
		// causing it to begin a group of recurring appts.
		if ($a['calgroupid'] == 0) {
			$this->move_appointment (
				$appointment,
				array (
					'recurnote' => $desc,
					'recurid' => $appointment
				)
			);
		} else {
			$this->move_group_appointment (
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
	function scroll_prev_month ($given_date="") {
		$cur_date = date("Y-m-d");
		$this_date = (
			(empty($given_date) or !strpos($given_date, "-")) ?
			$cur_date :
			$given_date );
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
	function scroll_next_month ($given_date="") {
		$cur_date = date("Y-m-d");
		$this_date = (
			(empty($given_date) or !strpos($given_date, "-")) ?
			$cur_date :
			$given_date );
		list ($y, $m, $d) = explode ("-", $this_date);
		$m++;
		if ($m > 12) { $m -= 12; $y++; }
		if (!checkdate ($m, $d, $y)) {
			$d = 28; // be safe for February...
		}
		return date( "Y-m-d",mktime(0,0,0,$m,$d,$y));
	} // end function scroll_next_month

	// Method: _query_to_result_array
	//
	//	Internal helper function to convert SQL queries into
	//	scheduler appointment arrays (arrays of associative arrays).
	//
	// Parameters:
	//
	//	$query - SQL query text
	//
	// Returns:
	//
	//	Array of associative arrays containing appointment data.
	//
	function _query_to_result_array ( $query ) {
		$result = $GLOBALS['sql']->query ( $query );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			$return[$r['id']] = $r;
		}
		return $return;
	} // end method _query_to_result_array

} // end method Scheduler

?>
