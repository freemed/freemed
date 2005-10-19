<?php
	// $Id$
	// $Author$

// Class: FreeMED.Authentication
//
//	Superclass for authentication. This is the loader/bootstrap for all FreeMED
//	authentication classes.
//
class Authentication {

	var $handler;

	function Authentication ( $type ) {
		$this->handler = CreateObject('_FreeMED.Authentication_'.$type);
		if (!is_object($this->handler)) {
			die ( "FreeMED.Authentication: could not load ".$type );
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
		if (is_array($_SESSION['authdata'])) {
			// Check to see if ipaddr is set or not...
			if (!SESSION_PROTECTION) {
				return true;
			} else {
				if ( !empty($_SESSION['ipaddr']) ) {
					if ($_SESSION['ipaddr'] == $_SERVER['REMOTE_ADDR']) {
						// We're already authorized
						return true;
					} else {
						// IP address has changed, ERROR
						unset($_SESSION['ipaddr']);
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
