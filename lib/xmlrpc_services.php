<?php
 // $Id$
 // $Author$
 // $Log$
 // Revision 1.1  2001/12/14 16:35:41  rufustfirefly
 // renamed from soap_* to xmlrpc_* (since it's really XMLRPC, not SOAP)
 //
 // Revision 1.1  2001/11/20 21:59:06  rufustfirefly
 // WDDX/XMLRPC services
 //

//----- Unset server methods
unset($XMLRPC_METHODS);

//----- Internal function
define ('XMLRPC_SCALAR', 1);
define ('XMLRPC_ARRAY',  2);
function xmlrpc_decode_parameter ( $params, $type=XMLRPC_SCALAR ) {
	static $this_pointer, $this_params;

	// If there is no pointer, start at the beginning (if this is the same)
	if (!isset($this_pointer) or ($this_params != $params))
		$this_pointer = 0;

	// Pull in local params
	$this_params = $params;

	// Pull parameter object into temporary storage
	$temporary = $params->getParam($this_pointer);

	// Increment the pointer for next time
	$this_pointer++;

	// Check what we have to return
	switch ($type) {
		// By default, return scalar values
		case XMLRPC_SCALAR:
		default:
			return $temporary->scalarval();
	} // end function switch
} // end function decode_parameter

// boolean rpc_authenticate ( username, password )
function rpc_authenticate ( $params ) {
	global $sql;

	// Decode parameters
	//$username = xmlrpc_decode_parameter($params);
	$_username = $params->getParam(0);
	$username = $_username->scalarVal();
	//$password = xmlrpc_decode_parameter($params);
	$_password = $params->getParam(1);
	$password = $_password->scalarVal();

	// Perform the query for username and password
	$query = $sql->query("SELECT * FROM user ".
		"WHERE username='".addslashes($username)."' AND ".
		"userpassword='".addslashes($password)."'"
	);
	
	// Build a value and return it
	return new xmlrpcresp(
		new xmlrpcval($sql->results($query), "boolean")
	);
} // end function rpc_authenticate
$XMLRPC_METHODS["freemed.authenticate"] = array (
	"function" => "rpc_authenticate",
	"signature" => array (
		$xmlrpcBoolean,
		$xmlrpcString,
		$xmlrpcString),
	"docstring" =>
		"Authenticates a username/password pair against a ".
		"FreeMED installation."
);

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

?>
