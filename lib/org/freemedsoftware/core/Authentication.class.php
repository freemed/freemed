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

// Class: org.freemedsoftware.core.Authentication
//
//	Superclass for authentication. This is the loader/bootstrap for all FreeMED
//	authentication classes.
//
class Authentication {

	var $handler;

	public function __construct ( $type ) {
		$this->handler = CreateObject('org.freemedsoftware.core.Authentication_'.$type);
		if (!is_object($this->handler)) {
			die ( "org.freemedsoftware.core.Authentication: could not load ".$type );
		}
	} // end constructor Authentication

	// Method: RequestNewAuthentication
	//
	//	Handle requests for new authentication if the present authentication
	//	has failed.
	//
	function RequestNewAuthentication ( ) {
		// Pass request to handler
		return $this->handler->RequestNewAuthentication();
	} // end method RequestNewAuthentication

	// Method: VerifyAuthentication
	//
	//	Determine if the session is current, and if not, attempt to verify
	//	authentication using the current handler.
	//
	function VerifyAuthentication ( ) {
		// If the session is valid, skip credentials
		if ($this->ValidSession()) { return true; }

		// Get credentials from authentication handler
		$credentials = $this->handler->GetCredentials();

		// ... and pass them back to the handler to find out if this is valid
		return $this->handler->IsValid( $credentials );
	} // end method VerifyAuthentication

	// Method: ValidSession
	//
	//	Determines if the current session (or lack thereof) is valid.
	//
	// Returns:
	//
	//	Boolean, result
	//
	function ValidSession ( ) {
		// Associate "SESSION" with proper session variable
		$PHP_SELF = $_SERVER['PHP_SELF'];
 
		// Check for authdata array
		if (is_array(HTTP_Session2::get('authdata'))) {
			// Check to see if ipaddr is set or not...
			if (!SESSION_PROTECTION) {
				return true;
			} else {
				if ( !empty(HTTP_Session2::get('ipaddr')) ) {
					if (HTTP_Session2::get('ipaddr') == $_SERVER['REMOTE_ADDR']) {
						// We're already authorized
						return true;
					} else {
						// IP address has changed, ERROR
						HTTP_Session2::set('ipaddr', null);
						syslog(LOG_INFO, "Authentication Layer| IP address changed for session");
						return false;
					} // end checking ipaddr
				} else {
					// Force check if no ip address is present. This
					// should get around null IPs getting set by
					// accident without compromising security.
					return false;
				} // end if isset ipaddr
			} // end checking for SESSION_PROTECTION
		} // end checking for authdata in session

		// If all else fails, return false
		return false;
	} // end method ValidSession

} // end class Authentication

?>
