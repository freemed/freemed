<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.2  2002/08/17 14:59:41  rufustfirefly
 // Updated XML-RPC services to scan lib/xmlrpc/ for additional functions.
 // Removed old kruft.
 //
 // Revision 1.1  2001/12/14 16:35:41  rufustfirefly
 // renamed from soap_* to xmlrpc_* (since it's really XMLRPC, not SOAP)
 //
 // Revision 1.1  2001/11/20 21:59:06  rufustfirefly
 // WDDX/XMLRPC services
 //

//----- Unset server methods
unset($XMLRPC_METHODS);

// array rpc_capability ( void )
//   This function is a clone of IMAP's CAPABILITY function,
//   in that it reports what it is capable of doing, as an array.
function rpc_capability () {
	// Set the capabilities
	$capabilities = array (
		"capability",
		"ping"
	);

	// Create blank xmlrpcval
	$val = new xmlrpcval ();

	// Add the capabilities array to that...
	$okay = $val->addArray ( $capabilities );

	// Return the appropriate array
	return xmlrpcresp ( $val );
} // end function rpc_capability
$XMLRPC_METHODS["freemed.capability"] = array (
	"function" => "rpc_capability",
	"signature" => array ( $xmlrpcArray ),
	"docstring" =>
		"Provides the capabilities of the current server."
);

// TODO: Implement module pull for functions (??????.rpc.module.php, perhaps)

// Internal function for preparing raw values for XML-RPC transport
function rpc_prepare ($value) {
	return xmlrpc_php_encode(stripslashes($value));
}

// Internal function for registering methods with XML-RPC server
function rpc_register ($method, $doc='') {
	global $xmlrpc_server, $XMLRPC_METHODS;
	$func = str_replace(".", "_", $method);
	$XMLRPC_METHODS[$method] = array(
		'function'  => $func,
		'docstring' => $doc
	);
}

//----- XMLRPC Function Definitions ----------------------------------------

//----- EMRi Namespace

function EMRi_Information_auth($params) {
	// Resplit headers for basic auth information
	$headers = getallheaders();
	if (ereg('Basic', $headers['Authorization'])) {
		// Parse headers
		$tmp = $headers['Authorization'];
		$tmp = ereg_replace(' ', '', $tmp);
		$tmp = ereg_replace('Basic', '', $tmp);
		$auth = base64_decode(trim($tmp));
		list ($user, $pass) = split(':', $auth);
	}

	// Create struct to return
	$val = new xmlrpcval();
	$val->addStruct(array(
		"user" => xmlrpc_php_encode($user),
		"pass" => xmlrpc_php_encode($pass)
	));
	return new xmlrpcresp($val);
} rpc_register("EMRi.Information.auth");

function EMRi_Information_hostname($params) {
	return new xmlrpcresp(new xmlrpcval(trim(`hostname`), "string"));
} rpc_register("EMRi.Information.hostname");

?>
