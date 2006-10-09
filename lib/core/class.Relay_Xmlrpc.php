<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

// Make sure to include support functions for XML-RPC parsing
include_once ( dirname(__FILE__).'/../xmlrpc_tools.php' );

class Relay_Xmlrpc extends Relay {

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
	public function deserialize_request ( $data ) {
		// Switch to XML-RPC error handler
		error_reporting( );
		set_error_handler( 'xmlrpc_error_handler' );

		$parser = xml_parser_create($GLOBALS['xmlrpc_defencoding']);
	
		$GLOBALS['_xh'][$parser] = array();
		$GLOBALS['_xh'][$parser]['st']     = '';
		$GLOBALS['_xh'][$parser]['cm']     = 0; 
		$GLOBALS['_xh'][$parser]['isf']    = 0; 
		$GLOBALS['_xh'][$parser]['params'] = array();
		$GLOBALS['_xh'][$parser]['method'] = '';

		// decompose incoming XML into request structure
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, true);
		xml_set_element_handler($parser, 'xmlrpc_se', 'xmlrpc_ee');
		xml_set_character_data_handler($parser, 'xmlrpc_cd');
		xml_set_default_handler($parser, 'xmlrpc_dh');
		if (!xml_parse($parser, $data, 1)) {
			// return XML error as a faultCode
			$r = CreateObject('org.freemedsoftware.core.xmlrpcresp','',
				$GLOBALS['xmlrpcerrxml'] + xml_get_error_code($parser),
				sprintf('XML error: %s at line %d',
				xml_error_string(xml_get_error_code($parser)),
				xml_get_current_line_number($parser))
			);
			print_r($r);
			xml_parser_free($parser);
		} else {
			xml_parser_free($parser);
			$method = $GLOBALS['_xh'][$parser]['method'];
			$rawparams = CreateObject('org.freemedsoftware.core.xmlrpcval' );
			foreach ($GLOBALS['_xh'][$parser]['params'] AS $_p) {
				eval('$p[] = '.$_p.';');
			}
			$rawparams->addArray ( $p );
			$params = xmlrpc_php_decode (  $rawparams );
			return array (
				'method' => $method,
				'params' => $params
			);
		}
	} // end method deserialize_request

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
		$msg = CreateObject( 'org.freemedsoftware.core.xmlrpcresp', xmlrpc_php_encode( $response ) );
		return $msg->serialize( );
	} // end public function serialize_response

} // end class Relay_Xmlrpc

?>
