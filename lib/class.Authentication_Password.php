<?php
	// $Id$
	// $Author$

// Class: FreeMED.Authentication_Password
//
//	Classic FreeMED authentication via password.
//
class Authentication_Password {

	function Authentication_Password ( ) { } 

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
		// Find this user
  		$result = $GLOBALS['sql']->query ("SELECT * FROM user ".
			"WHERE username = '".addslashes($credentials['username'])."'");
	
		// If the user isn't found, false
		if (!$GLOBALS['sql']->results($result)) { return false; }
	
		// Get information
		$r = $GLOBALS['sql']->fetch_array ($result);

		if((LOGLEVEL<1)||(LOG_HIPAA || LOG_LOGIN)) {
			syslog(LOG_INFO, "FreeMED.Authentication_Password| verify_auth login attempt $user ");
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
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth successful login");}		
			return true;
		} else { // check password
			// Failed password check
			unset ( $_SESSION['authdata'] );
			unset ( $_SESSION['ipaddr'] );
			if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){ syslog(LOG_INFO,"FreeMED.Authentication_Password| verify_auth failed login");	}	
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

		global $display_buffer, $title, $message, $_URL;
		if ($_REQUEST['user']) {
			$message = __("You have entered an incorrect username or password.");
		}
		require(freemed::template_file('login.php'));

		/*
		Header("Location: index.php?message=".urlencode(__("You have entered an incorrect username or password.")). ( strpos($_SERVER['REQUEST_URI'], 'index.php') === false and strpos($_SERVER['REQUEST_URI'], 'authenticate.php') === false ? "&_URL=".urlencode($_SERVER['REQUEST_URI']."&".implode_with_key($_POST))) : "" );
		template_display();
		*/
	}
} // end class Authentication_Password

?>
