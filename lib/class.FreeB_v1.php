<?php
	// $Id$
	// $Author$

// Class: FreeMED.FreeB_v1
//
//	Provides an interface to the FreeB billing system, so that all
//	calls can be abstracted to this class. All of the XML-RPC
//	server side calls are handled by FreeMED's internal XML-RPC
//	server, and they are located in lib/xmlrpc/FreeB/
//
class FreeB_v1 {

	// Method: FreeB_v1 constructor
	function FreeB_v1 ( ) {
		// Try to get configuration from FreeMED configuration
		// database
		$this->server = freemed::config_value('freeb_server');
		$this->path = freemed::config_value('freeb_path');
		$this->port = freemed::config_value('freeb_port');
		$this->protocol = freemed::config_value('freeb_protocol');

		// If this isn't set, default to localhost parameters
		if (empty($this->server) or empty($this->path) or empty($this->port)) {
			$this->server = 'localhost';
			$this->port = '18081';
			$this->path = '/RPC2';
			$this->protocol = 'http';
		}

		// Create proper constructor
		$this->xmlrpc = CreateObject (
			'PHP.xmlrpc_client',
			$this->path,
			$this->server,
			$this->port
		);
	} // end constructor FreeB_v1

	// Method: FormatList
	//
	//	Retrieve the list of formats that the connected FreeB
	//	server has available.
	//
	// Returns:
	//
	//	Array containing the available formats.
	//
	function FormatList ( ) {
		return $this->_call ( 'FreeB.Format.list', NULL );
	} // end method FormatList

	// Method: ProcessBill
	//
	//	Executes FreeB.Bill.process method on the server with the
	//	specified Bill Key. Since FreeMED does not store a Bill
	//	Key database, this is just serialized data which is
	//	passed around.
	//
	// Parameters:
	//
	//	$key - Serialized data regarding procedures and patients
	//	to be billed.
	//
	//	$format - Format for the current queue of bills
	//
	//	$target - Target for the current queue of bills
	//
	//	$safe_key - Sanitized version of bill key, so
	//	we have something to refer to.
	//
	// Returns:
	//
	//	Result code.
	//
	function ProcessBill ( $key, $format, $target, $safe_key ) {
		return $this->_call (
			'FreeB.Bill.process', 
			array (
				CreateObject('PHP.xmlrpcval', $key, 'string'),
				CreateObject('PHP.xmlrpcval', $format, 'string'),
				CreateObject('PHP.xmlrpcval', $target, 'string'),
				CreateObject('PHP.xmlrpcval', $safe_key, 'string')
			)
		);
	} // end method ProcessBill

	// Method: ProtocolVersion
	//
	//	Determines the version of the FreeB protocol that is being
	//	used by the specified server.
	//
	// Returns:
	//
	//	FreeB protocol version used by the specified server. If this
	//	is >= 2, FreeB_v1 will not properly handle this.
	//
	function ProtocolVersion ( ) {
		return $this->_call ( 'FreeB.Protocol.version', NULL );
	} // end method ProtocolVersion

	// Method: StoreBillKey
	//
	//	Stores the billing key in a temporary table to hold the
	//	data for FreeB to use.
	//
	// Parameters:
	//
	//	$billkey - Data to be serialized
	//
	// Returns:
	//
	//	Table key for billkey, which can be passed to FreeB
	//
	function StoreBillKey ( $billkey ) {
		$query = $GLOBALS['sql']->insert_query (
			'billkey',
			array (
				'billkeydate' => date('Y-m-d'),
				'billkey' => serialize($billkey)
			)
		);
		$result = $GLOBALS['sql']->query($query);
		$id = $GLOBALS['sql']->last_record($result, 'billkey');
		syslog(LOG_INFO, 'FreeB_v1.StoreBillKey| new key = '.$id);
		return $id;
	} // end method StoreBillKey

	// Method: TargetList
	//
	//	Retrieve the list of targets that the connected FreeB
	//	server has available.
	//
	// Returns:
	//
	//	Array containing the available targets.
	//
	function TargetList ( ) {
		return $this->_call ( 'FreeB.Target.list', NULL );
	} // end method TargetList

	// Method: _autoserialize
	//
	//	Automagically determines what kind of resource this is
	//	supposed to be and creates a PHP.xmlrpcval object to
	//	wrap it in.
	//
	// Parameters:
	//
	//	$mixed - Original object, any type
	//
	// Returns:
	//
	//	PHP.xmlrpcval object
	//
	function _autoserialize ( $mixed ) {
		// Handle already serialized
		if (is_object($mixed) and method_exists($mixed, 'serialize')) {
			return $mixed;
		}

		if (is_array($mixed)) {
			// If dealing with an array, recursively figure out
			@reset($mixed);
			while(list($k, $v) = @each($mixed)) {
				$ele[$k] = $this->_autoserialize($v);
			}
			// Determine if struct or array as we return it
			return CreateObject (
				'PHP.xmlrpcval',
				$ele,
				$this->_is_struct($ele) ? 'struct' : 'array'
			);
		} else {
			// Otherwise, use PHP auto-typing
			// FIXME: Needs to figure out if it is binary to make base64 type things work correctly
			$type = (is_integer($mixed) ? 'int' : gettype($mixed));
			return CreateObject (
				'PHP.xmlrpcval',
				$mixed,
				$type
			);
		}
	} // end method _autoserialize

	// Method: _call
	//
	//	Call FreeB server with the specified parameters. This
	//	should only be used by internal FreeB_v1 methods to
	//	complete abstraction.
	//
	// Parameters:
	//
	//	$method - Method on the FreeB server to call
	//
	//	$parameters - (optional) Array of parameters to call
	//	$method with. Defaults to NULL.
	//
	//	$debug - (optional) Whether debug code should be shown.
	//	Defaults to false.
	//
	// Returns:
	//
	//	Reply to call as PHP variable.
	//
	function _call ( $method, $parameters = NULL, $debug = false ) {
		// Form proper message object
		if ($parameters != NULL) {
			$message = CreateObject(
				'PHP.xmlrpcmsg',
				$method,
				( is_array($parameters) ? $parameters : array($parameters) )
			);
		} else {
			$message = CreateObject(
				'PHP.xmlrpcmsg',
				$method
			);
		}

		// If we're debugging, we set the debug flag
		$this->xmlrpc->setDebug ( $debug );

		// Dispatch message to server
		$response = $this->xmlrpc->send (
			$message,
			0,
			$this->protocol
		);

		// Deserialize response
		return $response->deserialize();
	} // end method _call

	// Method: _is_struct
	//
	//	Determines if in an array is a structure (associative array)
	//	or a regular array.
	//
	// Parameters:
	//
	//	$var - Variable to be typed
	//
	// Returns:
	//
	//	Boolean, true if $var is an associative array, false if it
	//	is not.
	//
	function _is_struct ( $var ) {
		// Catch non-array instance
		if (!is_array($var)) return false;

		// If there are non-numeric keys, it is a structure, otherwise
		// default to false.
		foreach ($var AS $k => $v) {
			if (!is_integer($k)) return true;
		}
		return false;
	} // end method _is_struct

} // end class FreeB_v1

?>
