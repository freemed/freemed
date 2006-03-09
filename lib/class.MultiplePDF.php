<?php
	// $Id$
	// $Author$

// Class: FreeMED.MultiplePDF
//
//	Handle compositing of multiple PDF files
//
class MultiplePDF {

	var $stack;

	// Constructor: MultiplePDF
	function MultiplePDF ( ) {
	} // end method constructor

	// Method: Add
	//
	//	Add an additional PDF to the stack of PDFs to be composited.
	//
	// Parameters:
	//
	//	$pdffile - PDF file name
	//
	function Add ( $pdffile ) {
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
	function Composite ( ) {
		// If there is nothing on the stack, null filename
		if (count($this->stack) < 1) { return ''; }

		// Create temporary filename
		$tempfile ='/tmp/'.mktime().'.pdf';

		// Create command
		$cmd = "pdfjoin --paper letterpaper --outfile '${tempfile}' ";
		foreach ($this->stack AS $pdf) {
			$cmd .= " \"".escapeshellcmd($pdf)."\" ";
		}

		// Execute the composite command
		$garbage_collector = exec( $cmd );

		// Return the temporary file name
		return $tempfile;
	} // end method Composite

} // end class MultiplePDF

?>
