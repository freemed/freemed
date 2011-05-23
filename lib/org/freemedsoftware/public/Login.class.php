<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

class Login {

	public function __constructor () { }

	// Method: LoggedIn
	//
	//	Gets present session status to determine if user has logged in.
	//
	// Returns:
	//
	//	Boolean.
	//
	public function LoggedIn ( ) {
		if ( !defined('SESSION_DISABLE') ) {
			if ( is_array( HTTP_Session2::get('authdata') ) ) { return true; }
		}
		return false;
	} // end method LoggedIn

	// Method: Logout
	//
	//	Log out of the current session.
	//
	// Returns:
	//
	//	Success, boolean.
	//
	public function Logout ( ) {
		HTTP_Session2::destroy( );
		return true;
	} // end method Logout

	// Method: Validate
	//
	//	Validate a new session with the provided credentials.
	//
	// Parameters:
	//	
	//	$username - Username
	//
	//	$password - Plain text password
	//
	// Returns:
	//
	//	Boolean, login status.
	//
	public function Validate ( $username, $password,$location=NULL ) {
		syslog (LOG_INFO, "username = ".$username);
		//$username = $data['username'];
		//$password = $data['password'];

		// Drop if no valid username
		if (!$username) {
			syslog(LOG_ERR, "org.freemedsoftware.public.Validate: no valid username");
			return false;
		}

		if (! $GLOBALS['sql'] ) {
			syslog(LOG_ERR, "org.freemedsoftware.public.Validate: failed to instantiate SQL object");
			if (! file_exists ( dirname(__FILE__).'/../../data/cache/healthy' ) ) {
				syslog(LOG_ERR, "org.freemedsoftware.public.Validate: healthy system status not confirmed");
			}
			return false;
		}

		// Find this user
  		$r = $GLOBALS['sql']->queryRow("SELECT * FROM user WHERE username = '".addslashes($username)."'");
	
		// If the user isn't found, false
		if (!$r['username']) {
			//$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Could not find user '${username}'" );
			syslog(LOG_INFO, "org.freemedsoftware.public.Validate: could not find user '${username}'");
			return false;
		}

		//If user is not admin
		if(!($r['id'] == 1)){
			// Find this user in provided facility
	  		$r2 = $GLOBALS['sql']->queryRow("SELECT id FROM user WHERE username = '".addslashes($username)."' and FIND_IN_SET(".$GLOBALS['sql']->quote($location).",userfac)");
		
			// If the user isn't allowed in provied facility
			if (!$r2['id']) {
				syslog(LOG_INFO, "org.freemedsoftware.public.Validate: could not find user '${username}' in facility '$location'");
				return "NOT_IN_FACILITY";//Not in this facility
			}
		}
	
		//syslog(LOG_INFO, "pw in db = $r[userpassword]");

		$db_pass = $r['userpassword'];

		// Check password
		if (md5($password) == $r['userpassword']) {
			// Set session vars
			unset($r['userpassword']);
			// Pull user options
			$authdata = HTTP_Session2::get( 'authdata' );
			$authdata['username'] = $username;
			$authdata['user'] = $r['id'];
			setcookie("user", $r['id'], time() + 3600, "/");
			HTTP_Session2::set( 'authdata', $authdata );
			// Set user facility
			if($location)
				HTTP_Session2::set( 'facility_id', $location );
			$this->SessionPopulate();

			// Set ipaddr for SESSION_PROTECTION
			HTTP_Session2::set( 'ipaddr', $_SERVER['REMOTE_ADDR'] );
	
			// Authorize
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth successful login");}		
			//$log = freemed::log_object();
			//$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Successfully logged in" );
			return true;
		} else { // check password
			// Failed password check
			HTTP_Session2::destroy( );
			//if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){ syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth failed login");	}	
			//$log = freemed::log_object();
			//$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Failed login" );
			return false;
		} // end check password
	} // end method Validate

	// Method: SessionPopulate
	//
	//	Populate / repopulate session data with user information. Requires
	//	valid $_SESSION['authdata']['user'] variable.
	//
	// Returns:
	//
	//	True on success.
	//
	public function SessionPopulate ( ) {
		syslog(LOG_INFO, "SessionPopulate called");
		if ( !$this->LoggedIn() ) { return false; }
		syslog(LOG_INFO, "SessionPopulate called, proceeding");

		$u = freemed::user_cache();

		// Pull user options
		$r = $u->local_record;
		$s = unserialize( $r['usermanageopt'] );
		if ( $s ) { $r['usermanageopt'] = $s; }

		$authdata = HTTP_Session2::get( 'authdata' );
		$authdata['user_record'] = $r;
		HTTP_Session2::set( 'authdata', $authdata );

		return true;
	} // end method SessionPopulate

	// Method: GetLocations
	//
	//	Populate location selection.
	//
	// Returns:
	//
	//	Array of arrays:
	//	* [ language, abbrev ]
	//
	public function GetLocations ( ) {
		$m = module_function( 'facilitymodule', 'GetAll', array ( ) );
		foreach ( $m AS $r ) {
			$res[] = array ( $r['psrname'], $r['id'] );
		}
		return $res;
	} // end method GetLocations

	// Method: GetLanguages
	//
	//	Populate language selection.
	//
	// Returns:
	//
	//	Array of arrays:
	//	* [ language, abbrev ]
	//
	public function GetLanguages ( ) {
		$m = module_function( 'i18nlanguages', 'GetAll', array ( ) );
		foreach ( $m AS $r ) {
			$res[] = array ( $r['language'], $r['abbrev'] );
		}
		return $res;
	} // end method GetLanguages

} // end class Login

?>
