<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.5  2002/11/06 13:55:45  rufustfirefly
 // Pass the user and real physician info so it can be used by methods.
 //
 // Revision 1.4  2002/11/03 20:41:14  rufustfirefly
 // Changes for phpwebtools 0.3 XML-RPC support.
 //
 // Revision 1.3  2002/08/17 14:59:40  rufustfirefly
 // Updated XML-RPC services to scan lib/xmlrpc/ for additional functions.
 // Removed old kruft.
 //
 // Revision 1.2  2002/08/06 14:11:11  rufustfirefly
 // XMLRPC services now working with basic authentication and methods
 //
 // Revision 1.1  2001/12/14 16:35:38  rufustfirefly
 // renamed from soap_* to xmlrpc_* (since it's really XMLRPC, not SOAP)
 //
 // Revision 1.1  2001/11/20 15:02:45  rufustfirefly
 // added SOAP/XMLRPC services provider
 //

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
