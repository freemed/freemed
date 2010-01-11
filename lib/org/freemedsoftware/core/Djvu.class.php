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

// Class: org.freemedsoftware.core.Djvu
//
//	Wrapper class to handle Djvu documents.
//
class Djvu {

	protected $filename;
	protected $md5;

	// Constructor: Djvu
	//
	// Parameters:
	//
	//	$filename - Filename of source DjVu file.
	//
	public function __construct ( $filename ) {
		$this->filename = $filename;
		if (!file_exists($filename)) { die ("Djvu: file does not exist \"$filename\""); }
		$this->md5 = $this->MD5Checksum();
	} // end constructor

	// Method: MD5Checksum
	//
	//	Get MD5 checksum for the current file.
	//
	// Returns:
	//
	//	32 character MD5 hash
	//
	protected function MD5Checksum ( ) {
		return substr( exec( "md5sum " . escapeshellarg( $this->filename ) ), 0, 32 );
	} // end method MD5Checksum

	// Method: NumberOfPages
	//
	// Returns:
	//
	//	Number of pages in the current Djvu document.
	//
	public function NumberOfPages ( ) {
		$filename = $this->filename;

		// Get basic info dump from file
		$info = `djvudump "$filename" | grep "Document directory"`;

		// If no info, one page
		if (empty($info)) { return 1; }

		// Split down to page number
		$_h1 = explode(", ", $info);
		$_h2 = explode(" ", trim($_h1[1]));

		// Return next to last segment
		return $_h2[count($_h2)-2];
	} // end method NumberOfPages

	// Method: GetPage
	//
	//	Get page image
	//
	// Parameters:
	//
	//	$page - Page number to return
	//
	//	$contents - (optional) Boolean, return the contents instead of the
	//	filename. Defaults to false.
	//
	//	$force_ps - (optional) Boolean, force no JPEG conversion. Defaults
	//	to false.
	//
	//	$force_rotate - (optional) Boolean, force 90 degree rotation
	//
	// Returns:
	//
	//	Either JPEG image of file in string or name of temporary file.
	//
	public function GetPage ( $page, $contents = false, $force_ps = false, $force_rotation = true ) {
		$filename = $this->filename;
		$cache_name = PHYSICAL_LOCATION . '/data/cache/djvu/' . $this->md5 . '.' . $page . '.' . ( $force_rotation ? 'rotated.' : '' ) . ( $force_ps ? 'ps' : 'jpg' );

		if ( ! file_exists( $cache_name ) ) {
			$temp = tempnam('/tmp', 'djvu');
			$rotate = $force_rotation ? " 90 " : " 0 ";
			//$command = "djvups -page=" . ($page+0) . " " . escapeshellarg($filename) . " | /usr/bin/convert - ${rotate} " . escapeshellarg( $cache_name );
			$command = "ddjvu -format=pnm -scale=100 -page=" . ($page+0) . " " . escapeshellarg($filename) . " " . escapeshellarg($temp) . " ; cat " . escapeshellarg($temp) . " | /usr/bin/pnmrotate ${rotate} | /usr/bin/pnmtojpeg > " . escapeshellarg( $cache_name );

			exec( $command );
			unlink( $temp );
		} else {
			// Touch it to avoid reaping if it has been accessed
			exec( "touch " . escapeshellarg( $cache_name ) );
		}

		if ($contents) {
			ob_start( );
			readfile( $cache_name );
			$c = ob_get_contents( );
			ob_end_clean( );
			return $c;
		} else {
			return $cache_name;
		}
	} // end method GetPage

	// Method: GetPageSized
	//
	//	Get page image with a particular target page size.
	//
	// Parameters:
	//
	//	$page - Page number to return
	//
	//	$w - Width
	//
	//	$h - Height
	//
	//	$contents - (optional) Boolean, return the contents instead of the
	//	filename. Defaults to false.
	//
	//	$force_ps - (optional) Boolean, force no JPEG conversion. Defaults
	//	to false.
	//
	//	$force_rotate - (optional) Boolean, force 90 degree rotation
	//
	// Returns:
	//
	//	Either JPEG image of file in string or name of temporary file.
	//
	public function GetPageSized ( $page, $w, $h, $contents = false, $force_ps = false, $force_rotation = true ) {
		$filename = $this->filename;
		$cache_name = PHYSICAL_LOCATION . '/data/cache/djvu/' . $this->md5 . '.' . $page . '.' . ( $force_rotation ? 'rotated.' : '' ) . ( $force_ps ? 'ps' : 'jpg' );

		if ( ! file_exists( $cache_name ) ) {
			$temp = tempnam('/tmp', 'djvu');
			$rotate = $force_rotation ? " 90 " : " 0 ";
			$command = "ddjvu -format=pnm -size=" . escapeshellarg($w . "x" . $h) . " -page=" . ($page+0) . " " . escapeshellarg($filename) . " " . escapeshellarg($temp) . " ; cat " . escapeshellarg($temp) . " | /usr/bin/pnmrotate ${rotate} | /usr/bin/pnmtojpeg > " . escapeshellarg( $cache_name );

			exec( $command );
			unlink( $temp );
		} else {
			// Touch it to avoid reaping if it has been accessed
			exec( "touch " . escapeshellarg( $cache_name ) );
		}

		if ($contents) {
			ob_start( );
			readfile( $cache_name );
			$c = ob_get_contents( );
			ob_end_clean( );
			return $c;
		} else {
			return $cache_name;
		}
	} // end method GetPageSized

	// Method: GetPageThumbnail
	//
	//	Get textual content of a page thumbnail.
	//
	// Parameters:
	//
	//	$page - Page number
	//
	//	$size - (optional) Maximum dimension of thumbnail. Defaults to 300 (px).
	//
	// Returns:
	//
	//	String containing JPEG thumbnail of specified page.
	//
	public function GetPageThumbnail ( $page, $size=300 ) {
		$filename = $this->filename;
		$cache_name = PHYSICAL_LOCATION . '/data/cache/djvu/' . $this->md5 . '.' . $page . '.' . $size . '.jpg';

		$s = $size."x".$size;

		if ( ! file_exists( $cache_name ) ) {
			$temp = `djvups -page=$page "$filename" | convert - -scale $s "${cache_name}"`;
		}

		ob_start();
		readfile( $cache_name );
		$contents = ob_get_contents( );
		ob_end_clean( );

		return $contents;
	} // end method GetPageThumbnail

	// Method: StoredChunks
	//
	//	Get list of chunks contained in the parent DjVu file.
	//
	// Returns:
	//
	//	Array of chunk names.
	//
	protected function StoredChunks ( ) {
		$filename = $this->filename;

		$raw = `djvm -l "$filename"`;

		// deal by line
		$lines = explode("\n", $raw);

		foreach ($lines as $__garbage => $line) {
			if (eregi('PAGE #', $line)) {
				// Split out contents ....
				$_h1 = explode('#', $line);
				$_h2 = explode(' ', trim($_h1[1]));

				$pages[] = trim($_h2[count($_h2) - 1]);
			}
		}
		return $pages;
	} // end method StoredChunks

	// Method: ToPDF
	//
	//	Convert DjVu document to a PDF document.
	//
	// Parameters:
	//
	//	$to_file - (optional) Boolean, for the function to dump
	//	contents to a file, then return the file name. If false,
	//	returns a string containing the PDF file data. Defaults
	//	to false.
	//
	// Returns:
	//
	//	PDF file data or file name, depending on parameters
	//
	public function ToPDF ( $to_file = false ) {
		$filename = $this->filename;

		$t = tempnam('/tmp', 'fmdjvu');
		for ($p=1; $p<=$this->NumberOfPages(); $p++) {
			// No conversion ...
			$temp_cmd = "djvups -page=$p \"$filename\" > \"".$t.".page".$p.".ps\"";
			$out = `$temp_cmd`;
			//syslog(LOG_INFO, "Djvu debug | $temp_cmd");
			
			$params[] = $t.'.page'.$p.'.ps';
		}
	
		// Merge with psmerge
		//$merge_temp_cmd = "psmerge -o".$t.".ps ".join(" ", $params);
		$merge_temp_cmd = "cat ".join(" ", $params)." > ".$t.".ps";
		$out = `$merge_temp_cmd`;
		//syslog(LOG_INFO, "Djvu debug | $merge_temp_cmd");

		// Convert to PDF
		$convert_temp_cmd = "ps2pdf $t.ps $t.pdf";
		$out = `$convert_temp_cmd`;
		//syslog(LOG_INFO, "Djvu debug | $convert_temp_cmd");

		// If this is sent to a file, skip the rest of the logic
		if ($to_file) {
			unlink ($t);
			unlink ($t.'.ps');
			foreach ($params AS $f) { unlink($f); }
			return $t.'.pdf';
		}

		ob_start();
		readfile($t.'.pdf');
		$c = ob_get_contents();
		ob_end_clean();

		unlink ($t);
		unlink ($t.'.ps');
		unlink ($t.'.pdf');
		foreach ($params AS $f) {
			unlink($f);
		}
		
		return $c;
	} // end method ToPDF

} // end class Djvu

?>
