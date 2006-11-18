<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.Relay');

// Class: org.freemedsoftware.core.Relay_Json
//
//	JSON data relay methods.
//
class Relay_Json extends Relay {

	// Method: deserialize_request
	//
	//	Deserialize the incoming request
	//
	// Parameters:
	//
	//	$request - Request, as received by the relay
	//
	// Returns:
	//	Array containing:
	//	* method
	//	* params (always an array)
	//
	public function deserialize_request ( $request ) {
		syslog(LOG_INFO, "request = $request");
		if (function_exists( 'json_decode' )) {
			// Try the JSON PECL native function first
			$return = json_decode( $request, true );
		} else {
			$json = CreateObject('net.php.pear.Services_JSON');
			$return = $json->decode( $request );
		}
		if (!$return) {
			return array ( $_REQUEST );
		} else {
			return $return;
		}
	} // end public function deserialize_request

	// Method: extract_parameters
	public function extract_parameters ( $data, $post ) {
		// First, extract parameters from data
		if ( is_array( $data['params'] ) ) { return $data['params']; }

		// Use param[] next
		if ( is_array( $post['param'] ) ) { return $post['param']; }

		// Use param1 ... paramN
		if ( isset( $post['param0'] ) ) {
			foreach ($post AS $k => $v) {
				if ( substr( $k, 0, 5 ) == 'param' ) {
					$key = substr( $k, 5, strlen($k) - 5 );
					if ( ( $key == '0' ) or ( ($key + 0) > 0 ) ) {
						$r[$key] = $v;
					}
				} // end if substr(param)
			} // end foreach

			// Make sure that everything is in the right order
			ksort( $r );

			// Send it back
			return $r;
		} // end if isset(param0)

		// Return empty array, otherwise
		return array ( );
	} // end public function extract_parameters

	// Method: serialize_response
	//
	//	Serialize the outgoing response back to the client
	//
	// Parameters:
	//
	//	$response - Response to be serialized
	//
	// Returns:
	//
	//	Serialized data string
	//
	public function serialize_response ( $response ) {
		if (function_exists( 'json_encode' )) {
			// Try the JSON PECL native function first
			return json_encode( $response );
		} else {
			$json = CreateObject('net.php.pear.Services_JSON');
			return $json->encode( $response );
		}
	} // end public function serialize_response

} // end class Relay_Json

?>
