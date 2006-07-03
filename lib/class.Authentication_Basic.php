<?php
	// $Id$
	// $Author$

// Class: FreeMED.Authentication_Basic
//
//	FreeMED authentication via HTTP basic auth
//
class Authentication_Basic {

	function Authentication_Basic ( ) { } 

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
		global $refresh;
		$refresh = "index.php";
		template_display();
	}

	function IsValid ( $credentials ) {
		syslog(LOG_INFO, "isvalid");
		if (!isset($credentials['username'])) { return false; }

		// Find this user
  		$result = $GLOBALS['sql']->query ("SELECT * FROM user ".
			"WHERE username = '".addslashes($credentials['username'])."'");
	
		// If the user isn't found, false
		if (!$GLOBALS['sql']->results($result)) { return false; }
	
		// Get information
		$r = $GLOBALS['sql']->fetch_array ($result);

		if((LOGLEVEL<1)||(LOG_HIPAA || LOG_LOGIN)) {
			syslog(LOG_INFO, "FreeMED.Authentication_Basic| verify_auth login attempt $user ");
		}

		$db_pass = $r['userpassword'];

		// Check password
		if ($credentials['password'] == $r['userpassword']) {
			// Set session vars
			$_SESSION['authdata'] = array (
				"username" => $credentials['username'],
				"user" => $r['id']
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

		// Determine whether or not we have a "refresh URL"
		switch ($GLOBALS['page_name']) {
			// Entry and exit pages don't need to be reloaded
			case 'index.php':
			case 'logout.php':
				$GLOBALS['_URL'] = 'main.php';
				break;

			case 'authenticate.php':
				return false;
				break;

			default:
				$GLOBALS['_URL'] = $_SERVER['REQUEST_URI'];
				break;
		} // end page_name switch

		// Log to syslog
		syslog(LOG_INFO, "Authentication: password| requesting new auth");

		Header('WWW-Authenticate: Basic realm="FreeMED"');
		Header('HTTP/1.0 401 Unauthorized');
		die(__("Access denied."));
	}
} // end class Authentication_Basic

?>
