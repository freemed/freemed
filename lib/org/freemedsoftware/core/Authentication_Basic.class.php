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

// Class: org.freemedsoftware.core.Authentication_Basic
//
//	FreeMED authentication via HTTP basic auth
//
class Authentication_Basic {

	public function __construct ( ) { } 

	function GetCredentials ( ) {
		return array (
			'username' => $_SERVER['PHP_AUTH_USER'],
			'password' => md5($_SERVER['PHP_AUTH_PW'])
		);
	} // end method GetCredentials

	function Logout ( ) {
		$log = freemed::log_object();
		$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Logged out" );

		Header('WWW-Authenticate: Basic realm="FreeMED"');
		Header('HTTP/1.0 401 Unauthorized');
		Header('Location: index.php');
		die();
	}

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
		// IE fix for REQUEST_URI
		if(!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['argv'][0],
				strpos($_SERVER['argv'][0], ';') + 1);
		}

		// Log to syslog
		syslog(LOG_INFO, "Authentication: password| requesting new auth");

		Header('WWW-Authenticate: Basic realm="FreeMED"');
		Header('HTTP/1.0 401 Unauthorized');
		die(__("Access denied."));
	}
} // end class Authentication_Basic

?>
