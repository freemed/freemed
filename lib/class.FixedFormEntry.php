<?php
	// $Id$
	// $Author$

class FixedFormEntry {
	// Internal variables
	var $row;
	var $col;
	var $len;
	var $data;
	var $format;
	var $comment;

	// constructor FixedFormEntry
	function FixedFormEntry ($row, $col, $len, $data, $format, $comment) {
		$this->row     = $row;
		$this->col     = $col;
		$this->len     = $len;
		$this->data    = stripslashes($data);
		$this->format  = $format;
		$this->comment = $comment;
	} // end constructor FixedFormEntry
} // end class FixedFormEntry

?>
