<?php
 // $Id$
 // $Author$

//----- Load neccesary headers
define ('SESSION_DISABLE', true);
include_once ("lib/freemed.php");
include_once ("lib/i18n.php");

// Create seperate XML-RPC object map
CreateApplicationMap(array(
	// Actual XML-RPC methods
	'FreeMED' => 'lib/xmlrpc/class.*.php',
	'FreeB'   => 'lib/xmlrpc/FreeB/class.*.php',

	// For internal function calls
	'_FreeMED' => 'lib/class.*.php',
	'_ACL' => 'lib/acl/*.class.php'
));
include_once("lib/acl.php");

//----- Create XMLRPC_METHODS
unset ($XMLRPC_METHODS);

//----- Register services
include_once ("lib/xmlrpc_services.php");

//----- Figure out auth type
if (isset($_GET['user']) and isset($_GET['hash'])) {
	$__auth_function = 'freemed_get_auth';
} else {
	$__auth_function = 'freemed_basic_auth';
}

//----- Define freemed authorization
function freemed_basic_auth () {
	global $sql;
	//----- Check for authentication
	$headers = getallheaders(); $authed = false;
	if (ereg('Basic', $headers['Authorization'])) {
		// Parse headers
		$tmp = $headers['Authorization'];
		$tmp = ereg_replace(' ', '', $tmp);
		$tmp = ereg_replace('Basic', '', $tmp);
		$auth = base64_decode(trim($tmp));
		list ($user, $pass) = split(':', $auth);
		syslog(LOG_INFO, "XMLRPC [basic] username = $user");
	
		// Check for username/password
		$query = "SELECT username, userpassword, userrealphy, id FROM user ".
			"WHERE username='".addslashes($user)."' AND ".
			"userpassword=MD5('".addslashes($pass)."')";
		$result = $sql->query($query);

		if (@$sql->num_rows($result) == 1) {
			$authed = true;
			$r = $sql->fetch_array($result);
			$GLOBALS['__freemed']['basic_auth_id'] = $r['id'];
			$GLOBALS['__freemed']['basic_auth_phy'] = $r['userrealphy'];
			return true;
		} else {
			// Clear basic auth id
			$authed = false;
			$GLOBALS['__freemed']['basic_auth_id'] = 0;
			$GLOBALS['__freemed']['basic_auth_phy'] = 0;
		}
	} else {
		// Otherwise return fault for no authorization
		$authed = false;
		$GLOBALS['__freemed']['basic_auth_id'] = 0;
		$GLOBALS['__freemed']['basic_auth_phy'] = 0;
	}
	return $authed;
} // end function freemed_basic_auth

function freemed_get_auth ( ) {
	global $sql;
	syslog(LOG_INFO, "XMLRPC [get] username = ".$_GET['user']);
	$query = "SELECT username, userpassword, userrealphy, id FROM user ".
		"WHERE username='".addslashes($_GET['user'])."' AND ".
		"userpassword='".addslashes($_GET['hash'])."'";
	$result = $sql->query($query);
	if (@$sql->num_rows($result) == 1) {
		$authed = true;
		$r = $sql->fetch_array($result);
		$GLOBALS['__freemed']['basic_auth_id'] = $r['id'];
		$GLOBALS['__freemed']['basic_auth_phy'] = $r['userrealphy'];
		return true;
	} else {
		// Clear basic auth id
		$authed = false;
		$GLOBALS['__freemed']['basic_auth_id'] = 0;
		$GLOBALS['__freemed']['basic_auth_phy'] = 0;
		return false;
	}
	return false;
} // end function freemed_get_auth

//----- Run XML-RPC server
Header("Content-Type: text/xml");
$XMLRPC_SERVER = CreateObject(
	'PHP.xmlrpc_server',
	$XMLRPC_METHODS,
	true,
	$__auth_function
);
	
?>
