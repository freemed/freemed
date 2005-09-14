<?php
	// $Id$
	// $Author$

class PDF {

	var $file;

	function __constructor ( $file ) { $this->PDF($file); }

	function PDF ( $file ) {
		$this->file = $file;
	} // end constructor

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
		$tmp = tempnam('/tmp', 'pdf');
		system("pdftops -q -f $page -l $page \"".$this->file."\" - | convert - ".$tmp.".png");
		ob_start();
		readfile($tmp.'.png');
		$buffer = ob_get_contents();
		ob_end_clean();
		unlink ($tmp);
		unlink ($tmp.'.png');
		return $buffer;
	} // end method GetPage

} // end class PDF

?>
