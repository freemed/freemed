<?php
 // $Id$
 // $Author$

//----- Unset server methods
unset($XMLRPC_METHODS);

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

function rpc_generate_sql_hash($table, $vars, $clause="") {
	// This function converts a table (and associative array of
	// table member names) into an xmlrpcresp object that can
	// be directly returned.
	global $sql;

	$result = $sql->query("SELECT * FROM ".$table." ".$clause);
	if (!$sql->results($result)) {
		return CreateObject('PHP.xmlrpcresp',
			CreateObject('PHP.xmlrpcval')
		);
	}

	$result_array = array();

	// Loop through results
	while ($r = $sql->fetch_array($result)) {
		$element = CreateObject('PHP.xmlrpcval');
		$temp = array ();

		// Build from hash
		foreach ($vars AS $k => $v) {
			if ( (($k+0)>0) or (empty($k)) ) {
				$k = $v;
				$v = xmlrpc_php_encode(stripslashes($r["$k"]));
			} else {
				//$k = $k;
				// TODO: Handle "formed" responses, delimited
				// by ##'s ....
				$v = xmlrpc_php_encode(stripslashes($r["$v"]));
			}
			// Add to _temp
			$_temp["$k"] = $v;
		}
		$element->addStruct($_temp);
		$result_array[] = $element; unset($element); unset($_temp);
	}

	// Create struct to return
	$val = CreateObject('PHP.xmlrpcval');
	$val->addArray($result_array);
	// NOTE - You don't have to create an xmlrpcresp object because
	// the xmlrpc_server object does it for you. It doesn't *really*
	// matter, though, since it autodetects the xmlrpcresp wrapper.
	return CreateObject('PHP.xmlrpcresp', $val);
} // end function rpc_generate_sql_hash

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
	$val = CreateObject('PHP.xmlrpcval');
	$val->addStruct(array(
		"user" => xmlrpc_php_encode($user),
		"pass" => xmlrpc_php_encode($pass)
	));
	return CreateObject('PHP.xmlrpcresp', $val);
} rpc_register("EMRi.Information.auth");

function EMRi_Information_hostname($params) {
	return CreateObject('PHP.xmlrpcresp',
		CreateObject('PHP.xmlrpcval', trim(`hostname`), "string")
	);
} rpc_register("EMRi.Information.hostname");

?>
