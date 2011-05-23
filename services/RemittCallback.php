<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

include_once( dirname( __FILE__ ) . '/../lib/freemed.php' );

$uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY );

if ($uri == "wsdl" ) {
	Header( "Content-type: text/xml" );
	readfile( PHYSICAL_LOCATION . '/data/wsdl/RemittCallback.wsdl' );
	die();
}

class RemittCallback {

	public function getProtocolVersion() {
		return 0.5;
	} // end method getProtocolVersion

	public function sendRemittancePayload( $payloadType, $originalReference, $payload ) {
		// Insert into table properly
		$query = $GLOBALS['sql']->insert_query(
			'rqueue',
			array (
			          'payload' => $payload
				, 'reference_id' => (int) $originalReference
			        , 'processed' => 0
			)
		);
		$GLOBALS['sql']->query( $query );
		return 1;

		// TODO: handle payloadType properly. For now, assume 835 XML
		//$billkey = $originalReference;
		//$parser = CreateObject( 'org.freemedsoftware.core.Parser_835XML', $payload );
		//return $parser->Handle();
	} // end method sendRemittancePayload

} // end class RemittCallback

/*
$soap = new SoapServer(
	  PHYSICAL_LOCATION . '/data/wsdl/RemittCallback.wsdl'
	, array( 'uri' => $_SERVER['REQUEST_URI'] )
);
$soap->setClass( 'RemittCallback' );
$soap->handle( );
*/

// Since SoapServer on PHP5 is a piece of crap in most places, we'll hack
// around it using nusoap for the moment.

require_once ( PHYSICAL_LOCATION . '/lib/agata7/classes/util/nusoap.php' );

function getProtocolVersion() {
	return RemittCallback::getProtocolVersion();
}

function sendRemittancePayload( $payloadType, $originalReference, $payload ) {
	return RemittCallback::sendRemittancePayload( $payloadType, $originalReference, $payload );
}

// Create the server instance
$server = new soap_server;

// Register the method to expose
$server->register( 'getProtocolVersion' );
$server->register( 'sendRemittancePayload' );

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>
