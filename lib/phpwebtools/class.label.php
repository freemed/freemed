<?php
 // $Id$
 // code: jeff b <jeff@ourexchange.net>
 // lic : GPL

if (function_exists("pdf_open")) {

define ( '__CLASS_LABEL_PHP__', true );

	// multiplier for units to points
define ( 'LABEL_UNIT_MULTIPLIER',	72 );	// is this right?

class label {
	var $rows, $cols;
	var $offset_v, $offset_h;
	var $page_count = 0;
	var $paper_size;

	// "state" variables
	var $cur_row, $cur_col;

	// file handle(s)
	var $pdf_file;
	var $pdf_file_handle;

	function label ($paper_size, $offset_v, $offset_h, $rows, $cols) {
		$this->offset_v 	= $offset_v	* LABEL_UNIT_MULTIPLIER;
		$this->offset_h 	= $offset_h	* LABEL_UNIT_MULTIPLIER;
		$this->rows     	= $rows;
		$this->cols    		= $cols;
		$this->paper_size	= $paper_size;

			// initially set position to row=1, col=0 (so it increments to 1)
		$this->cur_row		= 1;
		$this->cur_col		= 0;

			// open initial pdf file
		$this->pdf_file_handle = fopen ( "php://stdout", "a" );
		if (!$this->pdf_file_handle) die ("class.label.php :: couldn't open pipe");
		 else Header ("Content-type: application/pdf");
		$this->pdf_file = pdf_open ( $this->pdf_file_handle );
			// set initial information
		pdf_set_info ( $this->pdf_file, "Creator", "PHP" );
			// create initial page
		$this->page_count++;
			// set font information
		//pdf_findfont ( $this->pdf_file, "Courier", "builtin", 1 );
		//pdf_set_value ( $this->pdf_file, "textrendering", 1 );
			// page header
		switch ($paper_size) {
			case LABEL_PAPER_LETTER:	// 8 1/2 x 11 "
				$width	=	8.5 * LABEL_UNIT_MULTIPLIER;
				$height	=	11  * LABEL_UNIT_MULTIPLIER;
				break; // end LABEL_PAPER_LETTER

			case LABEL_PAPER_TABLOID:	// 11 x 17 "
				$width	=	11  * LABEL_UNIT_MULTIPLIER;
				$height	=	17  * LABEL_UNIT_MULTIPLIER;
				break; // end LABEL_PAPER_TABLOID
		} // end paper size
		pdf_begin_page ( $this->pdf_file, $height, $width );
		pdf_add_outline ( $this->pdf_file, "Page ".$this->page_count );
	} // end constructor label

	function add ($text) {
			// determine whether we have to advance lines or not
		if ( ($this->cur_col) >= $cols ) {
				// reset to first in the columns
			$this->cur_col = 1;
				// now determine if we have to skip pages...
			if ( ($this->cur_row) >= $rows ) {
				$this->page_advance();
					// reset everything to 1 (first)
				$this->cur_row = 1;
				$this->cur_col = 1;
			} else {
				// if not, just increment row
				$this->cur_row++;
			} // end checking for skipping pages
		} else {
			// if we don't advance lines, we advance columns
			$this->cur_col++;
		} // end checking for advancing columns

		// get box information
		list ( $top_h, $top_v, $bottom_h, $bottom_v ) =
			$this->determine_position ( $this->cur_row, $this->cur_col );

		// use pdf_show_boxed to display text
		return pdf_show_boxed (
			$this->pdf_file,			// handle
			$text,
			$top_h,
			$top_v,
			($bottom_h - $top_h),		// width
			($bottom_v - $top_v),		// height
			"left"						// justify mode
		);

	} // end function label->add()

	function determine_position ( $row, $col ) {
		$d_width = $d_height = 0; // reset height and width
		switch ($this->page_size) {

			case LABEL_PAPER_LETTER: // 8 1/2 x 11 "
				$d_width	=	8.5	* LABEL_UNIT_MULTIPLIER;
				$d_height	=	11	* LABEL_UNIT_MULTIPLIER;
				break; // end LABEL_PAPER_LETTER

			case LABEL_PAPER_TABLOID: // 11 x 17 "
				$d_width	=	11	* LABEL_UNIT_MULTIPLIER;
				$d_height	=	17	* LABEL_UNIT_MULTIPLIER;
				break; // end LABEL_PAPER_TABLOID

		} // end switch page_size

			// determine actual size box/divisions
			// (assume offset on both sides)
		$whole_height	= $d_height - ( 2 * $this->offset_h );
		$whole_width	= $d_width  - ( 2 * $this->offset_w );

			// determine size of subdivisons
		$label_height	= $whole_height / $this->rows;
		$label_width	= $whole_width  / $this->cols; 

			// determine, with offset, the upper left corner...
			// ( minus 1 to account for first box)
		$top_v			= $this->offset_h + ( ( $row - 1 ) * $label_height );
		$top_h			= $this->offset_w + ( ( $col - 1 ) * $label_width  );

			// calculate bottom corner
		$bottom_v		= $top_v + $label_height;
		$bottom_h		= $top_h + $label_width ;

			// return them in a array/list
		return array ( $top_h, $top_v, $bottom_h, $bottom_v ); 

	} // end function label->determine_position()

	function display () {
			// close the last page
		pdf_end_page ( $this->pdf_file );
			// actually closes file handles...
		pdf_close ( $this->pdf_file );
		fclose ( $this->pdf_file_handle );
	} // end function label->display()

	function page_advance () {
			// close the last page
		pdf_end_page ( $this->pdf_file );
			// open new page
		pdf_begin_page ( $this->pdf_file, 595, 842 );
			// outline
		pdf_begin_page ( $this->pdf_file, "Page ".$this->page_count );
	} // end function label->page_advance()

} // end class label

} // end if no pdf functions

?>
