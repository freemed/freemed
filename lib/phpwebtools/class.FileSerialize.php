<?php
 // $Id$
 // $Author$

// Class: PHP.FileSerialize
//
//	Class to allow serialization of any serialize()-able datatype
// 	to a file.
//
class FileSerialize {

	var $base64;
	var $filename;
	var $fp;

	// Method: FileSerialize Constructor
	//
	// Parameters:
	//
	//	$filename - Name of file for data to be serialized to
	//
	//	$base64_encoding - (optional) If this is set to true,
	//	base64 encode the data. Default is false.
	//
	function FileSerialize ( $filename, $base64_encoding = false ) {
		// Set filename
		$this->filename = $filename;

		// Make NULL file pointer
		$this->fp = NULL;

		// Determine if this file exists
		$this->file_exists = file_exists($this->filename);

		// Check for base64 encoding
		$this->base64 = $base64_encoding;
	} // end constructor FileSerialize

	// Method: FileSerialize->read
	//
	//	Deserialize data from the file.
	//
	// Returns:
	//
	//	Variable containing deserialized data.
	//
	function read ( ) {
		// Return false if the file doesn't exist
		if (!$this->file_exists) return false;
	
		$fp = fopen($this->filename, 'r');
		if (!$fp) return false;
		while (!feof($fp)) {
			$data .= fread($fp, 4096);
		}
		fclose($fp);

		// Check for flag for base64 encoding
		if ($this->base64) {
			// If encoded, decode the data
			$data = base64_decode($data);
		}

		// Return the deserialized data
		return unserialize($data);
	} // end function FileSerialize->read

	// Method: FileSerialize->write
	//
	//	Serialize provided data to file
	//
	// Parameters:
	//
	//	$data - Data to be serialized
	//
	function write ( $data ) {
		// Check for not writable
		if (!is_writable($this->filename) and file_exists($this->filename)) {
			return false;
		}

		// Open file pointer (prefix with ./ so that the path is relative)
		//print "this->filename = ./".$this->filename."<br/>\n";
		$this->fp = fopen('./'.$this->filename, "w");

		// If it's *still* null, the file isn't writeable
		if ($this->fp == NULL) return false;

		// Check for base64 encoding
		if ($this->base64) {
			$write_data = base64_encode(serialize($data));
		} else {
			$write_data = serialize($data);
		}

		// Return write results
		return ( @fwrite($this->fp, $write_data) != false );
	} // end function FileSerialize->write

} // end class FileSerialize

?>
