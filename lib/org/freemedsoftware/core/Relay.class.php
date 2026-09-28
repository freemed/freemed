<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist');

// Task 2.8, fix round 1 (M1). LoadObjectDependency() only includes a file when it
// exists, so a packaging mistake that drops Relay_Allowlist.class.php would make
// the FIRST relay request fatal with 'Class "Relay_Allowlist" not found' -- an
// outage path inside the control that exists to prevent outages. The class file is
// in version control (the risk is packaged deployment only), but a missing file
// must degrade instead of taking the relay down: it is logged LOUDLY here, and the
// request then behaves exactly as it did before this control existed (the same
// posture as the data file's documented fail-open). The convention itself --
// LoadObjectDependency at file scope -- is unchanged; 154 files use it.
if ( ! class_exists ( 'Relay_Allowlist' ) ) {
	syslog( LOG_CRIT, 'Relay: Relay_Allowlist is not available (lib/org/freemedsoftware/core/Relay_Allowlist.class.php is missing from this installation); the relay allowlist is NOT enforcing' );
}

class Relay {

	protected $query_string; // from URL

	public function __construct ( ) { }

	public function handle_request ( $_method = NULL, $_params = NULL ) {
		//print "DEBUG: handle_request<br/>\n";
		// Import query string and anything posted
		$this->query_string = $_SERVER['PATH_INFO'];
		$raw = file_get_contents('php://input');

		// Deserialize the "raw" data
		$data = $this->deserialize_request( $raw );
		$p = $this->extract_parameters( $data, $_REQUEST );
		//syslog(LOG_INFO, "params = ".serialize($p));

		// Figure method
		$method = isset($data['method']) ? $data['method'] : $_method;

		// Determine if this is okay to do, based on the namespace
		if ( substr($method, 0, 27) != 'org.freemedsoftware.public.' ) {
			if ( ! CallMethod ( 'org.freemedsoftware.public.Login.LoggedIn' ) ) {
				syslog( LOG_INFO, "Access attempt for '${method}' denied due to user not being logged in" );
				return 'INVALID_SESSION';
				trigger_error( "Access attempt for '${method}' denied due to user not being logged in", E_USER_ERROR );
			}
		}

		// Deny-by-default at the relay (Task 2.8). CallMethod() instantiates ANY
		// registered class and calls ANY public method with request-supplied
		// arguments, and this check runs for EVERY method -- including the
		// org.freemedsoftware.public.* namespace that skips the guard above -- so
		// the *next* quoting omission is not remotely reachable just because the
		// caller has a session. The call set lives in
		// data/config/relay-allowlist.php (Relay_Allowlist, doc/RELAY_ALLOWLIST).
		//
		// The decision and the miss log line both live in
		// Relay_Allowlist::refuse(), which is ALSO what a relay-reachable
		// re-dispatcher calls before an inner dispatch of its own (fix round 1,
		// C1: api/UserInterface.class.php:Multicall()). One entry point, so an
		// inner call gets the same allowlist, the same stage and the same log
		// line as an outer one. A miss is ALWAYS logged, with the method and the
		// remote address, so an operator can act on it; whether it is REFUSED
		// depends on the site's rollout stage -- the shipped default is
		// enforce = false, which logs and lets the call run, and enforce = true
		// answers INVALID_CALL.
		//
		// class_exists() is the M1 degradation path: when the class file is
		// missing from the installation the request proceeds unchecked rather
		// than fataling, exactly as it did before this control existed.
		if ( class_exists ( 'Relay_Allowlist' ) && Relay_Allowlist::refuse ( $method ) ) {
			return 'INVALID_CALL';
		}

		// TODO: call appropriate method:
		// $output = CallMethod ( $data['method'], $data['params'] );
		$output = @call_user_func_array ( 'CallMethod', array_merge ( array ( $method ), $p ) );

		// Reserialize and return the appropriate data
		return $this->serialize_response( $output );
	} // end public function handle_request

	// Method: extract_parameters
	//
	//	This should be overridden with a Relay provider's ability
	//	to extract parameter data from $_REQUEST or other sources.
	//
	// Parameters:
	//
	//
	protected function extract_parameters ( $data, $post ) {
		return isset($data['params']) ? $data['params'] : NULL;
	} // end method extract_parameters

} // end class Relay

?>
