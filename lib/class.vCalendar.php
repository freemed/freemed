<?php
	// $Id$
	// $Author$

class vCalendar {

	function vCalendar ( $name, $criteria ) {
		$this->name = $name;
		$this->criteria = $criteria;
	} // end constructor vCalendar
		
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
