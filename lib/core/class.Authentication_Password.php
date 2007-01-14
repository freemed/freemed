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

// Class: org.freemedsoftware.core.Authentication_Password
//
//	Classic FreeMED authentication via password.
//
class Authentication_Password {

	public function __construct ( ) { } 

	function GetCredentials ( ) {
		// Use _hash (md5) if passed, else plain _password
		$login_md5 = ( $_REQUEST['_hash'] ? $_REQUEST['_hash'] : md5($_REQUEST['_password']) );

		$user = $_REQUEST['_username'];

		return array (
			'username' => $user,
			'password' => $login_md5
		);
	} // end method GetCredentials

	function IsValid ( $credentials ) {
		// Drop if no valid username
		if (!$credentials['username']) { return false; }

		// Find this user
  		$r = $GLOBALS['sql']->queryRow ("SELECT * FROM user ".
			"WHERE username = '".addslashes($credentials['username'])."'");
	
		// If the user isn't found, false
		if (!$r['id']) { return false; }
	
		if((LOGLEVEL<1)||(LOG_HIPAA || LOG_LOGIN)) {
			syslog(LOG_INFO, "FreeMED.Authentication_Password| verify_auth login attempt $user ");
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
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth successful login");}		
			$log = freemed::log_object();
			$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Successfully logged in" );
			return true;
		} else { // check password
			// Failed password check
			unset ( $_SESSION['authdata'] );
			unset ( $_SESSION['ipaddr'] );
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){ syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth failed login");	}	
			$log = freemed::log_object();
			$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Failed login" );
			return false;
		} // end check password
	} // end method IsValid

	function Logout ( ) {
		// Stub method, just to keep track for audit purposes
		$log = freemed::log_object();
		$log->SystemLog( LOG__SECURITY, 'Authentication', get_class($this), "Logged out" );
	} // end method Logout

	function RequestNewAuthentication ( ) {
		// IE fix for REQUEST_URI
		if(!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['argv'][0],
				strpos($_SERVER['argv'][0], ';') + 1);
		}

		// Log to syslog
		syslog(LOG_INFO, "Authentication: password| requesting new auth");

		Header('Location: index.php?message='.urlencode($message));
		die();

		/*
		Header("Location: index.php?message=".urlencode(__("You have entered an incorrect username or password.")). ( strpos($_SERVER['REQUEST_URI'], 'index.php') === false and strpos($_SERVER['REQUEST_URI'], 'authenticate.php') === false ? "&_URL=".urlencode($_SERVER['REQUEST_URI']."&".implode_with_key($_POST))) : "" );
		template_display();
		*/
	}
} // end class Authentication_Password

?>
