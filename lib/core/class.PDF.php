<?php
 // $Id$
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

class PDF {

	var $cache_dir;
	var $file;
	var $page_count;
	var $uid;

	function __constructor ( $file ) { $this->PDF($file); }

	function PDF ( $file ) {
		$this->file = $file;
		$this->page_count = $this->NumberOfPages();
		$this->uid = md5_file($this->file);
		$this->cache_dir = dirname(dirname(__FILE__)).'/data/cache/pdf/'.$this->uid.'/';

		// Create cache directories (with parent)
		@mkdir(dirname(dirname(__FILE__)).'/data/cache/pdf/');
		@mkdir($this->cache_dir);
	} // end constructor

	// Method: CachedPageName
	//
	//	Determine file name for cached page
	//
	// Parameters:
	//
	//	$page - Page number
	//
	// Returns:
	//
	//	Absolute file name for cached page
	//
	function CachedPageName ( $page ) {
		return $this->cache_dir . $page . '.png';
	} // end method CachedPageName

	// Method: GetPage
	//
	//	Get PNG image (in string) for a particular page of the current PDF.
	//
	// Parameters:
	//
	//	$page - Page number to retrieve
	//
	// Returns:
	//
	//	String containing PNG image of the page in question.
	//
	function GetPage ( $page ) {
		if (($page + 0) == 0) { return false; }
		if (($page + 0) > $this->NumberOfPages()) { return false; }
		$tmp = $this->CachedPageName($page);

		// If it has not been cached, cache it
		if (!file_exists($tmp)) {
			system("pdftops -q -f $page -l $page \"".$this->file."\" - | convert - \"".$tmp."\"");
		}

		// Send page back to the browser
		ob_start();
		readfile($tmp);
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	} // end method GetPage

	// Method: NumberOfPages
	//
	//	Determine number of pages in current document, caching information
	//
	// Returns:
	//
	//	Integer number of pages in current document
	//
	function NumberOfPages ( ) {
		if (!isset($this->page_count)) {
			ob_start();
			$this->page_count = exec ("pdfinfo -meta \"".$this->file."\" | grep ^Pages: | awk '{ print \$2; }'") + 0;
			ob_end_clean();
		}
		return $this->page_count;
	} // end method NumberOfPages

} // end class PDF

?>
