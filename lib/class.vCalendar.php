<?php
	// $Id$
	// $Author$

// Class: FreeMED.vCalendar
class vCalendar {

	// Method: vCalendar constructor
	//
	// Parameters:
	//
	//	$name - Name of the calendar to be generated
	//
	//	$criteria - SQL "WHERE" clause text defining the
	//	qualifiers of the schedule.
	//
	function vCalendar ( $name, $criteria ) {
		$this->name = $name;
		$this->criteria = $criteria;
	} // end constructor vCalendar
		
	// Method: vCalendar->Generate
	//
	//	Create a vCalendar file from information given.
	//
	// Returns:
	//
	//	vCalendar format file text.
	//
	function generate ( ) {
		$query = "SELECT * FROM scheduler WHERE ".$this->criteria;
		$result = $GLOBALS['sql']->query($query);
		
		// Add vCalendar header
		$buffer .= "BEGIN:VCALENDAR\n".
			"VERSION:1.0\n".
			"PRODID:".$this->name."\n".
			"TZ:-07\n"; // TODO: Fix timezone

		// Loop through applicable calendar entries
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$entry = CreateObject('FreeMED.vCalendarEvent', $r);
			$buffer .= $entry->generate();
		}

		// Add vCalendar footer
		$buffer .= "END:VCALENDAR\n";

		return $buffer;
	} // end method vCalendar->generate

} // end class vCalendar

?>
