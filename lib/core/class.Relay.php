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

class Relay {

	protected $query_string; // from URL

	public function __construct ( ) { }

	public function handle_request ( $_method = NULL, $_params = NULL ) {
		//print "DEBUG: handle_request<br/>\n";
		// Import query string and anything posted
		$this->query_string = $_SERVER['PATH_INFO'];
		$raw = $GLOBALS['HTTP_RAW_POST_DATA'];

		// Deserialize the "raw" data
		$data = $this->deserialize_request( $raw );
		$p = $this->extract_parameters( $data, $_REQUEST );

		syslog(LOG_INFO, "params = ".serialize($p));

		// TODO: call appropriate method:
		// $output = CallMethod ( $data['method'], $data['params'] );
		$output = call_user_func_array ( 'CallMethod', array_merge ( array ( $data['method'] ? $data['method'] : $_method ), $p ) );

		// Reserialize and return the appropriate data
		return $this->serialize_response( $output );
	} // end public function handle_request

} // end class Relay

?>
