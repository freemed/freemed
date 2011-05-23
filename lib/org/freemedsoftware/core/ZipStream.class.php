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

// Class: org.freemedsoftware.core.ZipStream
//
//	ZipStream emulation of zip:// for PHP builds which don't have it.
//	From http://www.php.net/manual/en/ref.zip.php#75984
//

class ZipStream {
	public $zip; //the zip file
	public $entry; //the opened zip entry
	public $length; //the uncompressed size of the zip entry
	public $position; //the current position in the zip entry read

	//Opens the zip file then retrieves and opens the entry to stream
	public function stream_open ( $path, $mode, $options, &$opened_path ) {
		if ($mode != 'r' && $mode != 'rb') {
			return false;
		}
		$path = 'file:///'.substr($path, 6); //switch out file:/// for zip:// so we can use url_parse
		$url = parse_url($path);
		//open the zip file
		$filename = $url['path'];
		$this->zip = zip_open($filename);
		if (!is_resource($this->zip)) { return false; }

		//if entry name is given, find that entry   
		if (array_key_exists('query', $url) && $url['query']) {
			$path = $url['query'];
			do {
				$this->entry = zip_read($this->zip);
				if (!is_resource($this->entry)) { return false; }
			} while (zip_entry_name($this->entry) != $path);    
		} else { //otherwise get it by index (default to 0)
			$id = 0;
			if (array_key_exists('fragment', $url) && is_int($url['fragment']))
			$id = $url['fragment']*1;
			for ($i = 0; $i <= $id; $i++) {
				$this->entry = zip_read($this->zip);
				if (!is_resource($this->entry)) { return false; }
			}
		}
		//setup length and open the entry for reading
		$this->length = zip_entry_filesize($this->entry);
		$this->position = 0;
		zip_entry_open($this->zip, $this->entry, $mode);
		return true;
	} // end method stream_open

	// Closes the zip entry and file
	public function stream_close() { @zip_entry_close($this->entry); @zip_close($this->zip); }

	// Returns how many bytes have been read from the zip entry
	public function stream_tell() { return $this->position; }

	// Returns true if the end of the zip entry has been reached
	public function stream_eof() { return $this->position >= $this->length; }

	// Returns the stat array, only 'size' is filled in with the uncompressed zip entry size
	public function url_stat() { return array('dev'=>0, 'ino'=>0, 'mode'=>0, 'nlink'=>0, 'uid'=>0, 'gid'=>0, 'rdev'=>0, 'size'=>$this->length, 'atime'=>0, 'mtime'=>0, 'ctime'=>0, 'blksize'=>0, 'blocks'=>0); }

	// Reads the next $count bytes or until the end of the zip entry. Returns the data or false if no data was read.
	public function stream_read($count) {
		$this->position += $count;
		if ($this->position > $this->length)
		$this->position = $this->length;
		return zip_entry_read($this->entry, $count);
	}
}

// Register the zip stream handler
@stream_wrapper_register( 'zip', 'ZipStream' );

?>
