<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.Authentication_Get
//
//	FreeMED authentication via HTTP GET request
//
class Authentication_Get {

	public function __construct ( ) { } 

	function GetCredentials ( ) {
		return array (
			'username' => $_GET['user'],
			'password' => $_GET['hash']
		);
	} // end method GetCredentials

	function Logout ( ) { }

	function IsValid ( $credentials ) {
		syslog(LOG_INFO, "isvalid");
		if (!isset($credentials['username'])) { return false; }

		// Find this user
  		$r = $GLOBALS['sql']->queryRow ("SELECT * FROM user ".
			"WHERE username = '".addslashes($credentials['username'])."'");
	
		// If the user isn't found, false
		if (!$r['id']) { return false; }
	
		if((LOGLEVEL<1)||(LOG_HIPAA || LOG_LOGIN)) {
			syslog(LOG_INFO, "FreeMED.Authentication_Basic| verify_auth login attempt $user ");
		}

		$db_pass = $r['userpassword'];

		// Check password
		if ($credentials['password'] == $r['userpassword']) {
			// Set session vars
			unset($r['userpassword']);
			$_SESSION['authdata'] = array (
				"username" => $credentials['username'],
				"user" => $r['id'],
				"user_record" => $r
			);
			// Set ipaddr for SESSION_PROTECTION
			$_SESSION['ipaddr'] = $_SERVER['REMOTE_ADDR'];
	
			// Authorize
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){syslog(LOG_INFO,"FreeMED.Authentication_Basic| verify_auth successful login");}
			$log = freemed::log_object();
			$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Successfully logged in" );
			return true;
		} else { // check password
			// Failed password check
			unset ( $_SESSION['authdata'] );
			unset ( $_SESSION['ipaddr'] );
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){ syslog(LOG_INFO,"FreeMED.Authentication_Basic| verify_auth failed login");	}	
			$log = freemed::log_object();
			$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Failed login" );
			return false;
		} // end check password
	} // end method IsValid

	function RequestNewAuthentication ( ) {
		die(__("Access denied."));
	}

} // end class Authentication_Get

?>
