<?php
	// $Id$
	// $Author$
	// Defines FreeMED.Tickler.* namespace

	// This XML-RPC module handles calling any system
	// "ticklers" (dynamic routines which are called and
	// execute at predetermined times or intervals)

class Tickler {

	// Method: FreeMED.Ticker.call
	//
	//	Call all available tickler functions
	//
	// Parameters:
	//
	//	$params - (optional) Array of parameters to be passed to
	//	all ticklers. Defaults to NULL.
	//
	function call ( $params = NULL ) {
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
