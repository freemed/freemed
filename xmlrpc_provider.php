<?php
 // $Id$
 // $Author$

//----- Load neccesary headers
define ('SESSION_DISABLE', true);
include_once ("lib/freemed.php");

//----- Check for XML-RPC support in PHP build
if (!file_exists(WEBTOOLS_ROOT.'/class.xmlrpc_server.php'))
	die("There is no XML-RPC support in this build of phpwebtools!");

// Create seperate XML-RPC object map
CreateApplicationMap(array('FreeMED' => 'lib/xmlrpc/class.*.php'));

//----- Create XMLRPC_METHODS
unset ($XMLRPC_METHODS);

//----- Register services
include_once ("lib/xmlrpc_services.php");

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
	
		// Check for username/password
		$query = "SELECT username, userpassword, userrealphy, id FROM user ".
			"WHERE username='".addslashes($user)."' AND ".
			"userpassword='".addslashes($pass)."'";
		$result = $sql->query($query);

		if (@$sql->num_rows($result) == 1) {
			$authed = true;
			$r = $sql->fetch_array($result);
			$GLOBALS['__freemed']['basic_auth_id'] = $r['id'];
			$GLOBALS['__freemed']['basic_auth_phy'] = $r['userrealphy'];
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
} // function freemed_basic_auth

//----- Run XML-RPC server
Header("Content-Type: text/xml");
$XMLRPC_SERVER = CreateObject(
	'PHP.xmlrpc_server',
	$XMLRPC_METHODS,
	true,
	'freemed_basic_auth'
);
	
?>
