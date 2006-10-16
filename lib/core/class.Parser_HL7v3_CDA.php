<?php
 // $Id$
 // HL7 v3 CDA Parser
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

// Class: org.freemedsoftware.core.Parser_HL7v3_CDA
//
//	HL7 v2.3 compatible generic parser
//
class Parser_HL7v3_CDA {

	public $message;
	public $obj;

	// Method: constructor
	//
	// Parameters:
	//
	//	$message - Text of HL7 v3 CDA message
	//
	//	$options - (optional) Additional options to be passed
	//	to the parser. This is an associative array.
	//
	public function __construct ( $message, $_options = NULL ) {
		syslog(LOG_INFO, 'HL7v3 CDA |Created HL7 CDA parser object');

		$this->message = $message;
		$this->obj = new SimpleXMLElement( $message );

		syslog (LOG_INFO, 'HL7v3 CDA | length of data = '.strlen($message));
	} // end constructor Parser_HL7v3_CDA
	
	// Method: Handle
	//
	//	Method to be called by other parts of the program to execute
	//	the action associated with the provided message type.
	//
	// Returns:
	//
	//	Output of the specified handler.
	//
	public function Handle() {
		syslog(LOG_INFO, 'HL7 CDA parser|running appropriate handler');

		// TODO : Handle messages [ print_r( $this->obj ); ]
	} // end method Handle

	public function date_to_sql ( $date ) {
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);
		return $year.'-'.$month.'-'.$day;
	} // end method date_to_sql

} // end class Parser_HL7v3_CDA

?>
