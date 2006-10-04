<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

// Class: org.freemedsoftware.core.vCalendarEvent
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
	public function __construct ( $_event ) {
		// Based on array or not, do we import?
		if (is_array($_event)) {
			$event = $_event;
			$this->uid = $event['id'];
		} else {
                	$event = $GLOBALS['sql']->get_link( 'scheduler', $_event );
			$this->uid = $_event;
		}

		// Parse out from the array
		$this->event = $event;
		$this->hour = $event['calhour'];
		$this->minute = $event['calminute'];
		$this->duration = $event['calduration'];
		$this->patient = $event['calpatient'];
		$this->facility = $event['calfacility'];
		list ($this->year, $this->month, $this->day) =
			explode('-', $event['caldateof']);
		$this->note = $event['calprenote'];
	} // end constructor vCalendarEvent

	// Method: generate
	//
	//	Produce the text of a singular vCalendar event.
	//
	// Returns:
	//
	//	vCalendar event to be included in vCalendar export.
	//
	public function generate ( ) {
		$buffer .= "BEGIN:VEVENT\n".
			"SUMMARY:".$this->note."\n".
			"STATUS:TENTATIVE\n".
			"CLASS:PUBLIC\n".
			"DESCRIPTION:".$this->description()."\n".
			"LOCATION:".$this->location()."\n".
			"UID:".md5($this->uid)."\n".
			"DTSTART:".$this->start_time()."\n".
			"DTEND:".$this->end_time()."\n".
			"END:VEVENT\n";
		return $buffer;
	} // end method generate

	// Method: description
	//
	//	Form a proper description of the event so that it is
	//	human-readable.
	//
	// Returns:
	//
	//	Human readable event description.
	//
	protected function description ( ) {
		// Get patient information
		if ($this->patient == 0) { return __("Non-Patient Event"); }
		$patient = CreateObject('org.freemedsoftware.core.Patient', $this->patient);
		$buffer .= $patient->fullName()." (".
			$patient->local_record['ptid'].")\n";
		return $this->_txt2vcal($buffer);
	} // end method description

	// Method: location
	//
	//	Generate text location.
	//
	// Returns:
	//
	//	Textual location.
	//
	protected function location ( ) {
		if ($this->facility < 1) { return ''; }
		if ($this->facility == '') { return ''; }
		$f = $GLOBALS['sql']->get_link( 'facility', $this->facility );
		return $f['psrname'].' ('.$f['psrcity'].', '.$f['psrstate'].')';
	} // end method location

	// Method: start_time
	//
	//	Generate vCalendar formatted start time.
	//
	// Returns:
	//
	//	vCalendar formatted start time for this event.
	//
	protected function start_time ( ) {
		return $this->_ts2vcal(mktime(
			$this->hour,
			$this->minute,
			0, // seconds
			$this->month,
			$this->day,
			$this->year
		));
	} // end method start_time

	// Method: end_time
	//
	//	Generate vCalendar formatted end time.
	//
	// Returns:
	//
	//	vCalendar formatted end time for this event.
	//
	protected function end_time ( ) {
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
	} // end method end_time

	// ----- Internal methods

	// Method: _ts2vcal
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
	//	_txt2vcal
	//
	private function _ts2vcal ( $timestamp ) {
		return date ( "Ymd\THi00", $timestamp );
	} // end method _ts2vcal

	// Method: _text2vcal
	//
	//	Internal method to convert regular text into vCalendar
	//	formatted text. vCalendar uses MIME-style line breaks.
	//
	//	For now, we break it into comma separated lines for ease
	//	of reading.
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
	//	_ts2vcal
	//
	private function _txt2vcal ( $text ) {
		$_text = $text;
		if (substr($_text, -1) == "\n") { $_text = substr($_text, 0, strlen($_text)-1); }
		return str_replace ("\n", ", ", $_text);
		//return str_replace ("\n", "=0D=0A=", $text);
	} // end method _txt2vcal

} // end class vCalendarEvent

?>
