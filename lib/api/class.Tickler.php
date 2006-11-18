<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.api.Tickler
//
//	This class handles calling any system "ticklers" (dynamic routines
//	which are called and execute at predetermined times or intervals)
//
class Tickler {

	// Method: call
	//
	//	Call all available tickler functions
	//
	// Parameters:
	//
	//	$params - (optional) Array of parameters to be passed to
	//	all ticklers. Defaults to NULL.
	//
	public function call ( $params = NULL ) {
		// Start output buffering, to catch all messages
		ob_start();

		// Load all 'Tickler' handlers
		$reply = freemed::handler_breakpoint('Tickler', array($params));

		// Make sure to send back whatever messages we generated
		$return = ob_get_contents();
		ob_end_clean();
		return $return.( is_array($reply) ? "\n".join("\n", $reply) : "" );
	} // end method call

} // end class Tickler

?>
