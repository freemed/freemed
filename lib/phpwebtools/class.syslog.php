<?php
 // $Id$
 // $Author$

class syslog {

	var $filename;
	var $fp;

	function syslog ( $filename ) {
		// Set filename
		$this->filename = '/var/log/'.$filename;

		// Make NULL file pointer
		//$this->fp = NULL;
	} // end constructor syslog

	function write ( $data ) {
		// Check for file pointer open, if not, open it
		if ($this->fp == NULL) {
			$this->fp = fopen($this->filename, "a+");

			// If it's *still* null, the file isn't writeable
			if ($this->fp == NULL) return false;
		} // end checking for file pointer

		// Return write results
		$return = ( @fwrite($this->fp, $data) != false );

		// Close the file handle, so we don't lock the record
		@fclose($this->fp);
	} // end function syslog->write

} // end class syslog

?>
