<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.FileSerialize
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
	public function __construct ( $filename, $base64_encoding = false ) {
		// Set filename
		$this->filename = $filename;

		// Make NULL file pointer
		$this->fp = NULL;

		// Determine if this file exists
		$this->file_exists = file_exists($this->filename);

		// Check for base64 encoding
		$this->base64 = $base64_encoding;
	} // end constructor FileSerialize

	// Method: read
	//
	//	Deserialize data from the file.
	//
	// Returns:
	//
	//	Variable containing deserialized data.
	//
	public function read ( ) {
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
	} // end method read

	// Method: write
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
	} // end method write

} // end class FileSerialize

?>
