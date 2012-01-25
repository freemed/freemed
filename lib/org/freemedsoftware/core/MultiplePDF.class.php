<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.MultiplePDF
//
//	Handle compositing of multiple PDF files
//
class MultiplePDF {

	protected $stack;
	protected $internalFile;

	// Constructor: MultiplePDF
	public function __construct ( ) {
	} // end method constructor

	// Method: Add
	//
	//	Add an additional PDF to the stack of PDFs to be composited.
	//
	// Parameters:
	//
	//	$pdffile - PDF file name
	//
	public function Add ( $pdffile ) {
		if (file_exists($pdffile)) { $this->stack[] = $pdffile; }
	} // end method Add

	// Method: Composite
	//
	//	Composite all PDF files in the stack to a single PDF file
	//
	// Returns:
	//
	//	Name of temporary file containing all PDF information.
	//
	public function Composite ( ) {
		// If there is nothing on the stack, null filename
		if (count($this->stack) < 1) { return ''; }

		// Create temporary filename
		$tempfile ='/tmp/'.mktime().'.pdf';

		// Create command
		$cmd = "pdfjoin --paper letterpaper --outfile '${tempfile}' ";
		foreach ($this->stack AS $pdf) {
			$cmd .= " ".escapeshellarg($pdf)." ";
		}

		// Execute the composite command
		$garbage_collector = exec( $cmd );

		// Return the temporary file name
		$internalFile = $tempfile;
		return $tempfile;
	} // end method Composite

	public function __destruct() {
		// Clean up the temporary file
		@unlink( $internalFile );
	}

} // end class MultiplePDF

?>
