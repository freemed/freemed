<?php
 // $Id$
 // $Author$
 // $Log$
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
if (!is_array($_xmlrpcs_listMethods_sig))
	die("There is no XML-RPC support in this build of phpwebtools!");

//----- Create XMLRPC_METHODS
unset ($XMLRPC_METHODS);

//----- Register services
include_once ("lib/xmlrpc_services.php");

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
	$query = "SELECT username, userpassword FROM user ".
		"WHERE username='".addslashes($user)."' AND ".
		"userpassword='".addslashes($pass)."'";
	$result = $sql->query($query);

	if (@$sql->num_rows($result) == 1) {
		$authed = true;
	}
} else {
	// Otherwise return fault for no authorization
	$authed = false;
}

//----- Return fault for no authorization
if (!$authed) {
	$XMLRPC_SERVER = new xmlrpc_server ( $XMLRPC_METHODS, false );
	$XMLRPC_SERVER->returnFault('auth_error');
	die();
}

//----- Run XML-RPC server
Header("Content-Type: text/xml");
$XMLRPC_SERVER = new xmlrpc_server ( $XMLRPC_METHODS );

?>
