<?php
	// $Id$
	// $Author$
	// HL7 Parser

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

// SCH
define ('HL7v2_SCH_DURATION', 9);
define ('HL7v2_SCH_UNIT', 10);
define ('HL7v2_SCH_NOTE', 8);
	define ('HL7v2_SCH_NOTE_SHORT', 0);
	define ('HL7v2_SCH_NOTE_LONG', 1);

// Class: _FreeMED.Parser_HL7v2
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
	function Parser_HL7v2 ( $message, $_options = NULL ) {
		// Assume separator is a pipe
		$this->field_separator = '|';
		if (is_array($_options)) {
			$this->options = $_options;
		}
	
		// Split HL7v2 message into lines
		$segments = explode("\r", $message);

		// Fail if there are no or one segments
		if (count($segments) <= 1) {
			return false;
		}

		// Loop through messages
		$count = 0;
		foreach ($segments AS $__garbage => $segment) {
			$count++;

			// Determine segment ID
			$type = substr($segment, 0, 3);

			switch ($type) {
				case 'MSH':
				case 'EVN':
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
				// Parse all other segments
				$this->__default_segment_parser($segment);
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
	function Handle() {
		// Set to handle current method
		list ($top_level, $type) = explode ('^', $this->MSH['message_type']);

		// Check for an appropriate handler
		$handler = CreateObject('_FreeMED.Handler_HL7v2_'.$type, $this);

		// Error out if the handler doesn't exist
		if (!is_object($handler)) {
			if ($this->options['debug']) {
				print "<b>Could not load class ".
					"_FreeMED.Handler_HL7v2_".$type.
					"</b><br/>\n";
			}
			return false;
		}

		// Run appropriate handler
		return $handler->Handle();
	} // end method Handle

	//----- All handlers go below here

	function _EVN ($segment) {
		$composites = $this->__parse_segment ($segment);
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

	function _MSH ($segment) {
		// Get separator
		$this->field_separator = substr($segment, 0, 1);
		$composites = $this->__parse_segment ($segment);
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

		// TODO: Extract $this->MSH['encoding_characters'] and use
		// it instead of assuming the defaults.

		if ($this->options['debug']) {
			print "MSH Segment verbose:<hr/><pre>\n";
			print_r ( $this->MSH );
			print "</pre><hr/>\n";
		}
	} // end method _MSH

	//----- Truly internal functions

	function __default_segment_parser ($segment) {
		$composites = $this->__parse_segment($segment);

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
				$composites[$key] = $this->__parse_composite($composite);
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
	} // end method __default_segment_parser

	function __parse_composite ($composite) {
		return explode('^', $composite);
	} // end method __parse_composite

	function __parse_segment ($segment) {
		return explode($this->field_separator, $segment);
	} // end method __parse_segment

} // end class Parser_HL7v2

?>
