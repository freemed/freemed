<?php
 // $Id$
 // HL7 v2.3 Parser
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

// TODO --- NaturalDocs all of the macros here (using Macro)

// Field position definition list
define ('HL7v2_PID_ID', 3);
define ('HL7v2_PID_NAME', 5);
	define ('HL7v2_PID_NAME_LAST', 0);
	define ('HL7v2_PID_NAME_FIRST', 1);
	define ('HL7v2_PID_NAME_MIDDLE', 2);
define ('HL7v2_PID_DATEOFBIRTH', 7);
define ('HL7v2_PID_GENDER', 8);
define ('HL7v2_PID_SOCIALSECURITY', 19);
define ('HL7v2_PID_PHONE_HOME', 13);
define ('HL7v2_PID_PHONE_WORK', 14);
define ('HL7v2_PID_ADDRESS', 11);
	define ('HL7v2_PID_ADDRESS_LINE1', 0);
	define ('HL7v2_PID_ADDRESS_LINE2', 1);
	define ('HL7v2_PID_ADDRESS_CITY', 2);
	define ('HL7v2_PID_ADDRESS_STATE', 3);
	define ('HL7v2_PID_ADDRESS_ZIPCODE', 4);

// PD1
define ('HL7v2_PD1_PROVIDER', 4);
	define ('HL7v2_PD1_PROVIDER_ID', 0);
	define ('HL7v2_PD1_PROVIDER_LASTNAME', 1);
	define ('HL7v2_PD1_PROVIDER_FIRSTNAME', 2);

// PV1 - Patient Visit
define ('HL7v2_PV1_REFERRING', 8);
	define ('HL7v2_PV1_REFERRING_ID', 0);
	define ('HL7v2_PV1_REFERRING_LASTNAME', 1);
	define ('HL7v2_PV1_REFERRING_FIRSTNAME', 2);

// AIL - Appointment Information Location - NOT USED RIGHT NOW

// AIP - Appointment Information Personnel Resource
define ('HL7v2_AIP_PROVIDER', 3);
	define ('HL7v2_AIP_PROVIDER_ID', 0);
	define ('HL7v2_AIP_PROVIDER_NAME', 1);
define ('HL7v2_AIP_DATETIME', 6);
define ('HL7v2_AIP_DURATION', 9);

// SCH
define ('HL7v2_SCH_DURATION', 9);
define ('HL7v2_SCH_UNIT', 10);
define ('HL7v2_SCH_NOTE', 8);
	define ('HL7v2_SCH_NOTE_SHORT', 0);
	define ('HL7v2_SCH_NOTE_LONG', 1);
define ('HL7v2_SCH_EXTNOTE', 7);
	define ('HL7v2_SCH_EXTNOTE_SHORT', 0);
	define ('HL7v2_SCH_EXTNOTE_LONG', 1);

// Class: FreeMED.Parser_HL7v2
//
//	HL7 v2.3 compatible generic parser
//
class Parser_HL7v2 {

	var $field_separator;
	var $map;
	var $message;
	var $message_type;

	var $MSH;
	var $EVN;

	// Method: Parser_HL7v2 constructor
	//
	// Parameters:
	//
	//	$message - Text of HL7 v2.3 message
	//
	//	$options - (optional) Additional options to be passed
	//	to the parser. This is an associative array.
	//
	public function __construct ( $message, $_options = NULL ) {
		syslog(LOG_INFO, 'HL7 parser|Created HL7 parser object');
	
		// Assume separator is a pipe
		$this->field_separator = '|';
		if (is_array($_options)) {
			$this->options = $_options;
		}
	
		syslog (LOG_INFO, 'HL7 parser| length of data = '.strlen($message));

		// Determine line separator
		if (strpos($message, "\r") !== false) {
			syslog (LOG_INFO, 'HL7 parser| line sep = \r');
			$linesep = "\r";
		} else {
			syslog (LOG_INFO, 'HL7 parser| line sep = \n');
			$linesep = "\n";
		}

		// Split HL7v2 message into lines with extracted line seperator
		$segments = explode($linesep, $message);

		// Fail if there are no or one segments
		if (count($segments) <= 1) {
			syslog (LOG_INFO, 'HL7 parser| no segments to parse');
			return false;
		}

		// Loop through messages
		$count = 0;
		foreach ($segments AS $segment) {
			$count++;

			// Log segment
			syslog(LOG_INFO, 'HL7 parser| length = '.strlen($segment).', segment['.$count.'] = '.$segment);

			// Determine segment ID
			$type = substr($segment, 0, 3);

			switch ($type) {
				case 'MSH':
				case 'EVN':
				syslog(LOG_INFO, $type.' segment found');
				call_user_func_array(
					array(&$this, '_'.$type),
					array(
						// All but type
						substr(
							$segment,
							-(strlen($segment)-3)
						)
					)
				);
				$this->map[$count]['type'] = $type;
				$this->map[$count]['position'] = 0;
				break;

				default:
				// Ignore null segments
				if (trim($type) == '') { break; }
				// Parse all other segments
				$this->default_segment_parser($segment);
				$this->map[$count]['type'] = $type;
				$this->map[$count]['position'] = count($this->message[$type]);
				break;
			} // end switch type
		}

		// Depending on message type, handle differently
		/*
		switch ($this->message_type) {
			default:
			print ('Message type '.$this->message_type.' is '.
				'currently unhandled'."<br/>\n");
			break;
		} // end switch
		*/
	} // end constructor Parser_HL7v2
	
	// Method: Parser_HL7v2->Handle
	//
	//	Method to be called by other parts of the program to execute
	//	the action associated with the provided message type.
	//
	// Returns:
	//
	//	Output of the specified handler.
	//
	public function Handle() {
		syslog(LOG_INFO, 'HL7 parser|in handle for '.$this->MSH['message_type']);
		// Set to handle current method
		list ($top_level, $type) = explode ('^', $this->MSH['message_type']);
		// Check for an appropriate handler
		$handler = CreateObject('org.freemedsoftware.core.Handler_HL7v2_'.$type, $this);

		// Error out if the handler doesn't exist
		if (!is_object($handler)) {
			if ($this->options['debug']) {
				print "<b>Could not load class ".
					"org.freemedsoftware.core.Handler_HL7v2_".$type.
					"</b><br/>\n";
			}
			syslog (LOG_INFO, 'HL7 parser|Could not load class org.freemedsoftware.core.Handler_HL7v2_'.$type);
			return false;
		}

		// Run appropriate handler
		syslog(LOG_INFO, 'HL7 parser|running appropriate handler ('.$type.')');
		return $handler->Handle();
	} // end method Handle

	//----- All handlers go below here

	protected function _EVN ($segment) {
		$composites = $this->parse_segment ($segment);
		if ($this->options['debug']) {
			print "<b>EVN segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".prepare($v)."<br/>\n";
			}
		}

		list (
			$__garbage,
			$this->EVN['event_type_code'],
			$this->EVN['event_datetime'],
			$this->EVN['event_planned'],
			$this->EVN['event_reason'],
			$this->EVN['operator_id']
		) = $composites;

		if ($this->options['debug']) {
			print "EVN Segment verbose:<hr/><pre>\n";
			print_r ( $this->EVN );
			print "</pre><hr/>\n";
		}
	} // end method _EVN

	protected function _MSH ($segment) {
		// Get separator
		$this->field_separator = substr($segment, 0, 1);
		$composites = $this->parse_segment ($segment);
		if ($this->options['debug']) {
			print "<b>MSH segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".prepare($v)."<br/>\n";
			}
		}
		
		// Assign values
		list (
			$__garbage, // Skip index [0], it's the separator
			$this->MSH['encoding_characters'],
			$this->MSH['sending_application'],
			$this->MSH['sending_facility'] ,
			$this->MSH['receiving_application'],
			$this->MSH['receiving_facility'],
			$this->MSH['message_datetime'],
			$this->MSH['security'],
			$this->MSH['message_type'],
			$this->MSH['message_control_id'],
			$this->MSH['processing_id'],
			$this->MSH['version_id'],
			$this->MSH['sequence_number'],
			$this->MSH['confirmation_pointer'],
			$this->MSH['accept_ack_type'],
			$this->MSH['application_ack_type'],
			$this->MSH['country_code']
		) = $composites;

		// Deal with borked "message_type" position, just in case
		if (substr($this->MSH['message_type'], 3, 1) != '^') {
			syslog(LOG_INFO, 'HL7 parser| borked message_type, using '.$this->MSH['security']);
			$this->MSH['message_type'] = $this->MSH['security'];	
		}

		// TODO: Extract $this->MSH['encoding_characters'] and use
		// it instead of assuming the defaults.

		if ($this->options['debug']) {
			print "MSH Segment verbose:<hr/><pre>\n";
			print_r ( $this->MSH );
			print "</pre><hr/>\n";
		}
	} // end method _MSH

	//----- Truly internal functions

	private function default_segment_parser ($segment) {
		$composites = $this->parse_segment($segment);

		// The first composite is always the message type
		$type = $composites[0];

		// Debug
		if ($this->options['debug']) {
			print "<b>".$type." segment</b><br/>\n";
			foreach ($composites as $k => $v) {
				print "composite[$k] = ".prepare($v)."<br/>\n";
			}
		}

		// Try to parse composites
		foreach ($composites as $key => $composite) {
			// If it is a composite ...
			if (!(strpos($composite, '^') === false)) {
				$composites[$key] = $this->parse_composite($composite);
			}
		}

		// Find out where we are
		if (is_array($this->message[$type])) {
			$pos = count($this->message[$type]);
		} else {
			$pos = 0;
		}

		// Add parsed segment to message
		$this->message[$type][$pos] = $composites;
	} // end method default_segment_parser

	private function parse_composite ($composite) {
		return explode('^', $composite);
	} // end method parse_composite

	private function parse_segment ($segment) {
		return explode($this->field_separator, $segment);
	} // end method parse_segment

	public function date_to_sql ( $date ) {
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);
		return $year.'-'.$month.'-'.$day;
	} // end method date_to_sql

	public function date_to_hour ( $date ) {
		return substr($date, 8, 2);
	} // end method date_to_hour

	public function date_to_minute ( $date ) {
		return substr($date, 10, 2);
	} // end method date_to_minute

	public function pid_to_patient ( $pid_id ) {
		$query = "SELECT id FROM patient WHERE ptid='".addslashes($pid_id)."' AND ptarchive=0 ORDER BY id";
		$result = $GLOBALS['sql']->query($query);
		$r = @$GLOBALS['sql']->fetch_array($result);
		return $r['id'];
	} // end method pid_to_patient

	public function aip_to_provider ( $aip_id ) {
		$query = "SELECT id FROM physician WHERE phyhl7id='".addslashes($aip_id)."'";
		$r = $GLOBALS['sql']->queryOne($query);
		return $r['id'];
	} // end method aip_to_provider

	// Method: composite_to_provider
	//
	//	Resolve CN composite to provider in FreeMED.
	//
	// Parameters:
	//
	//	$composite - Array describing an HL7 CN composite
	//
	// Returns:
	//
	//	Valid ID or 0 to indicate non-existance.
	//
	public function composite_to_provider ( $composite ) {
		//syslog('HL7| composite[0] = '.$composite[0]);
		$query = "SELECT id FROM physician WHERE phyhl7id='".addslashes($composite[0])."' OR phyupin='".addslashes($composite[0])."'";
		$result = $GLOBALS['sql']->queryOne($query);
		if ($result['id']) {
			// Process from existing ID
			//syslog('HL7 parser| found single with id = '.$result['id']);
			return $result['id'];
		} else {
			// Infer using first and last name
			$query = "SELECT id FROM physician WHERE phyfname='".addslashes($composite[2])."' AND phylname='".addslashes($composite[1])."'";
			$result = $GLOBALS['sql']->queryOne($query);
			if ($result['id']) {
				// Use the names ...
				return $result['id'];
			} else {
				// Otherwise, bork and return none
				return 0;
			}
		}
	} // end method composite_to_provider

} // end class Parser_HL7v2

?>
