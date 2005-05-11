<?php
	// $Id$
	// $Author$

// Class: FreeMED.Fax
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
	//	is an absolute filename.
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
			'\"' => '',
			'&' => ''
		));

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
				'-f "'.$this->options['sender'].'" '.
				'-s "'.$this->options['size'].'" '.
				'-r "'.$this->options['subject'].'" '.
				( $this->options['comments'] ?
					'-c "'.$this->options['comments'].'" '
					: '' ).
				'-d "'.(
					$this->options['recipient'] ?
					$this->options['recipient'].'@' : ''
				).$number.'" '.
				' "'.$this->attachment.'"';
			syslog(LOG_INFO, "Fax| send cmd = ".$cmd);
			break; // end hylafax
		} // end switch
		$output = `$cmd`;
		syslog(LOG_INFO, "Fax| output = $output");

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
	//	1 = finished
	//
	function State ( $jid ) {
		$cmd = "faxstat -s | grep \"^$jid \"";
		syslog(LOG_INFO, "FreeMED.Fax.State| cmd = $cmd");
		$output = `$cmd`;

		// No output; probably done
		// TODO: Check the past jobs as well
		if (!$output) { return 1; }

		// Tokenize
		$tokens = explode(' ', $output);

		// Get comment from position 50
		$comment = trim(substr($output, 50, 30));

		return $comment;
	} // end method State

} // end class Fax

?>
