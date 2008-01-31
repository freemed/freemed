<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.vCalendar
class vCalendar {
	private $name;
	private $criteria;

	// Method: vCalendar constructor
	//
	// Parameters:
	//
	//	$name - Name of the calendar to be generated
	//
	//	$criteria - SQL "WHERE" clause text defining the
	//	qualifiers of the schedule.
	//
	public function __construct ( $name, $criteria ) {
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
		$result = $GLOBALS['sql']->queryAll( $query );
		
		// Add vCalendar header
		$buffer .= "BEGIN:VCALENDAR\n".
			"VERSION:1.0\n".
			"PRODID:".$this->name."\n".
			"TZ:-07\n"; // TODO: Fix timezone

		// Loop through applicable calendar entries
		foreach ( $result AS $r ) {
			$entry = CreateObject('org.freemedsoftware.core.vCalendarEvent', $r);
			$buffer .= $entry->generate( );
		}

		// Add vCalendar footer
		$buffer .= "END:VCALENDAR\n";

		return $buffer;
	} // end method generate

} // end class vCalendar

?>
