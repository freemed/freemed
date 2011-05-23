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

// Class: org.freemedsoftware.core.Fax
//
//	This is the FreeMED fax transmission API, and is used to send
//	faxes using the HylaFax fax system.
//
class Fax {

	// Constructor: Fax
	//
	//	Creates a fax object.
	//
	// Parameters:
	//
	//	$attachment - File name of PS/PDF/etc file to fax. This
	//	is an absolute filename. It can also be an array of
	//	seperate files to fax.
	//
	//	$_options - (optional) Associative array of options to
	//	pass to the fax cover page.
	//		* subject
	//		* size - defaults to 'letter'
	//		* sender
	//		* recipient
	//		* comments
	//
	function Fax ( $attachment, $_options = NULL ) {
		$this->attachment = $attachment;

		if (is_array($_options)) {
			// Pass from parameter
			$this->options = $_options;	
		} else {
			$this->options = array ( );
		}

		// Defaults
		if (!isset($this->options['subject']))
			$this->options['subject'] = '';
		if (!isset($this->options['size']))
			$this->options['size'] = 'letter';
		if (!isset($this->options['sender']))
			$this->options['sender'] = '';
		if (!isset($this->options['recipient']))
			$this->options['recipient'] = '';
		if (!isset($this->options['comments']))
			$this->options['comments'] = '';

		// Check for Hylafax install
		if ($this->eFaxInstalled()) {
			$this->options['fax_server'] = 'efax';
		} elseif ($this->HylaFaxInstalled()) {
			$this->options['fax_server'] = 'hylafax';
		} else {
			die(basename(__FILE__).": Hylafax or efax binaries not found");
		}

		// Define error messages
		$this->error_messages = array (
			'No carrier detected',
			'Busy signal detected; too',
			'Kill time expired',
			'REJECT',
			'No answer (T.30 T1 time',
			'No local dialtone; too'
		);
	} // end constructor Fax

	// Method: eFaxInstalled
	//
	//	Determines whether Mac OS X's efax client is properly
	//	installed on the server.
	//
	// Returns:
	//
	//	Boolean, whether or not 'efax' executable is found.
	//
	function eFaxInstalled ( ) {
		return file_exists("/usr/bin/efax");
	} // end method eFaxInstalled

	// Method: HylaFaxInstalled
	//
	//	Determines whether HylaFax's sendfax client is properly
	//	installed on the server.
	//
	// Returns:
	//
	//	Boolean, whether or not 'sendfax' executable is found.
	//
	function HylaFaxInstalled ( ) {
		return file_exists("/usr/bin/sendfax");
	} // end method HylaFaxInstalled

	// Method: GetNumberFromId
	//
	//	Retrieve the number to which a fax was sent from its job id
	//	number
	//
	// Parameters:
	//
	//	$jid - Job id
	//
	// Returns:
	//
	//	Fax number
	//
	function GetNumberFromId ( $jid ) {
		$cmd = "faxstat -d | grep \"^$jid \"";
		syslog(LOG_INFO, "FreeMED.Fax.GetNumberFromId| cmd = $cmd");
		$output = `$cmd`;

		// No output; probably done
		// TODO: Check the past jobs as well
		if (!$output) { return 1; }

		// Tokenize
		$tokens = preg_split('/\s+/', $output);

		// Fourth field. This may break in some installs.
		return $tokens[4];
	} // end method GetNumberFromId

	// Method: Send
	//
	//	Transmit a fax.
	//
	// Parameters:
	//
	//	$destination_number - Number to which the fax is to be sent.
	//
	// Returns:
	//
	//	Output of sendfax executable.
	//
	function Send ( $destination_number ) {
		// Sanitize number
		$number = strtr($destination_number, array(
			';' => '',
			'\\' => '',
			'>' => '',
			'<' => '',
			'\`' => '',
			'\'' => '',
			'"' => '',
			'-' => '',
			'+' => '',
			'(' => '',
			')' => '',
			' ' => '',
			'&' => ''
		));

		// Fix number for area code
		switch (strlen($number)) {
			case 7:
			// Number with no area code, do nothing
			break;

			case 10:
			// Add +1 if this is a 10 digit one
			$number = '+1'.$number;
			break;

			case 11:
			// In format 1XXXXXXXXXX, just need a +
			$number = '+'.$number;
			break;

			default:
			syslog(LOG_INFO, "FreeMED.Fax.Send| error, number $number, length = ".strlen($number));
			break;
		}

		// Log if we couldn't find the attachment
		if (!file_exists($this->attachment)) {
			syslog(LOG_INFO, "FreeMED.Fax.Send| could not find attachment file ".$this->attachment);
		}

		// Form command
		switch ($this->options['fax_server']) {
			case 'efax':
			$cmd = 'efax '.
				'-t '.$number.' '.
				' "'.$this->attachment.'"';
			break; // end efax
		
			case 'hylafax': default:
			$cmd = 'sendfax '.
			( freemed::config_value('fax_nocover') ?
				'-n ' : '' ).
				'-m '. // "fine" resolution for transmission
				'-f "'.$this->options['sender'].'" '.
				'-s "'.$this->options['size'].'" '.
				'-r "'.$this->options['subject'].'" '.
				( $this->options['comments'] ?
					'-c "'.$this->options['comments'].'" '
					: '' ).
				'-x "'.addslashes(INSTALLATION).'" '.
				'-d "'.(
					$this->options['recipient'] ?
					$this->options['recipient'].'@' : ''
				).$number.'" '.
				$this->_attachments();
			syslog(LOG_INFO, "FreeMED.Fax.Send| send cmd = ".$cmd);
			break; // end hylafax
		} // end switch
		$output = `$cmd`;
		syslog(LOG_INFO, "FreeMED.Fax.Send| output = $output");

		// Deal with output properly
		switch ($this->options['fax_server']) {
			case 'efax':
			return $output;
			break;

			case 'hylafax': default:
			if (!(strpos($output, 'request id is ') === false)) {
				$pieces = explode(' ', $output);
				return $pieces[3];
			} else {
				return $output;
			}
			break;
		} // end case fax_server
	} // end method Send

	// Method: State
	//
	//	Get state of job by job ID
	//
	// Parameters:
	//
	//	$jid - Fax job id
	//
	// Returns:
	//
	//	1 = finished, array (-1, string) = error, string = comment
	//
	function State ( $jid ) {
		$cmd = "faxstat -s | grep \"^$jid \"";
		syslog(LOG_INFO, "FreeMED.Fax.State| cmd = $cmd");
		$output = `$cmd`;

		// No output; probably done
		if (!$output) {
			$cmd = "faxstat -d | grep \"^$jid \"";
			syslog(LOG_INFO, "FreeMED.Fax.State| cmd = $cmd");
			$eoutput = `$cmd`;

			// Look for "D" in column for done
			$tokens = explode(' ', $eoutput);
			if ($tokens[2] == 'F') { return array (-1, trim (substr($eoutput, 50, strlen($eoutput)-50)) ); }
			if ($tokens[2] == 'D') { return 1; }

			foreach ($this->error_messages AS $e) {
				if (!(strpos($eoutput, $e) === false)) {
					return array (-1, trim (substr($eoutput, 50, strlen($eoutput)-50)) );
				}
			}

			// Default to succeed if there are no error messages
			return 1;
		}

		// Tokenize
		$tokens = explode(' ', $output);

		// Get comment from position 50
		$comment = trim(substr($output, 50, 30));

		return $comment;
	} // end method State

	// Method: _attachments
	//
	//	Internal method to provide a command-line set of arguments
	//	to be passed to the faxing application.
	//
	// Returns:
	//
	//	Command line arguments as string.
	//
	function _attachments ( ) {
		if (is_array($this->attachment)) {
			$a = $this->attachment;
			foreach ($a AS $k => $v) { $a[$k] = '"'.$v.'"'; }
			return join(' ', $a);
		} else {
			return '"'.$this->attachment.'"';
		}
	}

} // end class Fax

?>
