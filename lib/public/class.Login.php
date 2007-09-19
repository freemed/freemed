<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

	public function LoggedIn ( ) {
		if ( $_SESSION['authdata'] ) { return true; }
		return false;
	} // end method LoggedIn

	public function Logout ( ) {
		unset ( $_SESSION['authdata'] );
		unset ( $_SESSION['ipaddr'] );
		return true;
	} // end method Logout

	public function Validate ( $username, $password ) {
		syslog (LOG_INFO, "username = ".$username);
		//$username = $data['username'];
		//$password = $data['password'];

		// Drop if no valid username
		if (!$username) { return false; }

		if (! $GLOBALS['sql'] ) {
			syslog(LOG_ERROR, "org.freemedsoftware.public.Validate: failed to instantiate SQL object");
			if (! file_exists ( dirname(__FILE__).'/../../data/cache/healthy' ) ) {
				syslog(LOG_ERROR, "org.freemedsoftware.public.Validate: healthy system status not confirmed");
			}
			return false;
		}

		// Find this user
  		$r = $GLOBALS['sql']->queryRow("SELECT * FROM user WHERE username = '".addslashes($username)."'");
	
		// If the user isn't found, false
		if (!$r['username']) {
			$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Could not find user '${username}'" );
			syslog(LOG_INFO, "org.freemedsoftware.public.Validate: could not find user '${username}'");
			return false;
		}
	
		//syslog(LOG_INFO, "pw in db = $r[userpassword]");

		$db_pass = $r['userpassword'];

		// Check password
		if (md5($password) == $r['userpassword']) {
			// Set session vars
			unset($r['userpassword']);
			// Pull user options
			$s = unserialize( $r['usermanageopt'] );
			if ( $s ) { $r['usermanageopt'] = $s; }
			$_SESSION['authdata'] = array (
				"username" => $username,
				"user" => $r['id'],
				"user_record" => $r
			);
			// Set ipaddr for SESSION_PROTECTION
			$_SESSION['ipaddr'] = $_SERVER['REMOTE_ADDR'];
	
			// Authorize
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth successful login");}		
			//$log = freemed::log_object();
			//$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Successfully logged in" );
			return true;
		} else { // check password
			// Failed password check
			unset ( $_SESSION['authdata'] );
			unset ( $_SESSION['ipaddr'] );
			//if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){ syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth failed login");	}	
			//$log = freemed::log_object();
			//$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Failed login" );
			return false;
		} // end check password
	} // end method Validate

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
