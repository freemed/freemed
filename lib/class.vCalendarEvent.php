<?php
	// $Id$
	// $Author$

// Class: FreeMED.vCalendarEvent
//
//	Class to encapsulate a single vCalendar event.
//
class vCalendarEvent {

	// Method: vCalendarEvent constructor
	//
	// Parameters:
	//
	//	$event - If passed as a number, this denotes the scheduler
	//	table identifier. If passed as an array, this is the record
	//	from the scheduler table, as returned by
	//	<freemed::get_link_rec>.
	//
	function vCalendarEvent ( $_event ) {
		// Based on array or not, do we import?
		if (is_array($_event)) {
			$event = $_event;
		} else {
                	$event = freemed::get_link_rec($_event, "scheduler");
		}

		// Parse out from the array
		$this->event = $event;
		$this->hour = $event['calhour'];
		$this->minute = $event['calminute'];
		$this->duration = $event['calduration'];
		$this->patient = $event['calpatient'];
		list ($this->year, $this->month, $this->day) =
			explode('-', $event['caldateof']);
		$this->note = $event['calprenote'];
	} // end constructor vCalendarEvent

	// Method: vCalendarEvent->generate
	//
	//	Produce the text of a singular vCalendar event.
	//
	// Returns:
	//
	//	vCalendar event to be included in vCalendar export.
	//
	function generate ( ) {
		$buffer .= "BEGIN:VEVENT\n".
			"SUMMARY:".$this->note."\n".
			"DESCRIPTION:ENCODING=QUOTED-PRINTABLE: ".
				$this->description()."\n".
			"DTSTART:".$this->start_time()."\n".
			"DTEND:".$this->end_time()."\n".
			"END:VEVENT\n";
		return $buffer;
	} // end method vCalendarEvent->generate

	// Method: vCalendarEvent->description
	//
	//	Form a proper description of the event so that it is
	//	human-readable.
	//
	// Returns:
	//
	//	Human readable event description.
	//
	function description ( ) {
		// Get patient information
		$patient = CreateObject('FreeMED.Patient', $this->patient);
		$buffer .= $patient->fullName()." (".
			$patient->local_record['ptid'].")\n";
		return $this->_txt2vcal($buffer);
	} // end method vCalendarEvent->description

	// Method: vCalendarEvent->start_time
	function start_time ( ) {
		return $this->_ts2vcal(mktime(
			$this->hour,
			$this->minute,
			0, // seconds
			$this->month,
			$this->day,
			$this->year
		));
	} // end method vCalendarEvent->start_time

	// Method: vCalendarEvent->end_time
	function end_time ( ) {
		// Calculate the end hour and minute
		$hour = $this->hour;
		$minute = $this->minute;

		if (($this->duration + $this->minute) > 59) {
			// Do a little math
			$minute += ($this->duration % 60);
			$hour += floor(($this->duration / 60));
		} else {
			// If it doesn't roll, we don't need to deal with hours
			$minute += $this->duration;
		}

		// Return proper values
		return $this->_ts2vcal(mktime(
			$hour,
			$minute,
			0, // seconds
			$this->month,
			$this->day,
			$this->year
		));
	} // end method vCalendarEvent->end_time

	// ----- Internal methods

	// Method: vCalendarEvent->_ts2vcal
	//
	//	Internal method to convert timestamps into vCalendar
	//	format times.
	//
	// Parameters:
	//
	//	$timestamp - UNIX timestamp
	//
	// Returns:
	//
	//	vCalendar format date.
	//
	// See Also:
	//	vCalendar->_txt2vcal
	//
	function _ts2vcal ( $timestamp ) {
		return date ( "Ymd\THi00", $timestamp );
	} // end method vCalendarEvent->_ts2vcal

	// Method: vCalendarEvent->_text2vcal
	//
	//	Internal method to convert regular text into vCalendar
	//	formatted text. vCalendar uses MIME-style line breaks.
	//
	// Parameters:
	//
	//	$text - Standard text.
	//
	// Returns:
	//
	//	vCalendar format text.
	//
	// See Also:
	//	vCalendar->_ts2vcal
	//
	function _txt2vcal ( $text ) {
		return str_replace ("\n", "=0D=0A=", $text);
	} // end method vCalendarEvent->_txt2vcal

} // end class vCalendarEvent

?>
