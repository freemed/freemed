<?php
	// $Id$
	// $Author$

// Class: FreeMED.Fax
//
//	This is the FreeMED fax transmission API, and is used to send
//	faxes using the HylaFax fax system.
//
class Fax {

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

		// Check for Hylafax install
		if (!$this->HylaFaxInstalled()) {
			die(basename(__FILE__).": Hylafax binaries not found");
		}
	} // end constructor Fax

	function HylaFaxInstalled ( ) {
		return file_exists("/usr/bin/sendfax");
	} // end method HylaFaxInstalled

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
		$cmd = 'sendfax  '.
				'-f "'.$this->options['sender'].'" '.
				'-s "'.$this->options['size'].'" '.
				'-r "'.$this->options['subject'].'" '.
				'-d "'.(
					$this->options['recipient'] ?
					$this->options['recipient'].'@' : ''
				).$number.'" '.
				' "'.$this->attachment.'"';
		$output = `$cmd`;
		return $output;
	} // end method Send

} // end class Fax

?>
