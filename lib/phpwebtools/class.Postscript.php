<?php
	// $Id$
	// $Author$
	// Based on pslib 003 from 2000

// Quick check for proper GD functions
if (!function_exists('ImagePSLoadFont')) {
	die('You must install the PHP GD module to use the Postscript class!');
} // end gd functions check

// Class: PHP.Postscript
class Postscript {

	var $fp;
	var $filename;
	var $string = "";
	var $page = 1;

	// Variable: Postscript->spacing_multiplier
	//
	// Sets the percentage of line height that belongs to the font.
	// (example: if this is set to 0.8, 80% of the space is taken up
	// by the font, and the remaining 20% is space)
	//
	var $spacing_multiplier = 0.8;

	// Variable: Postscript->font_table
	//
	// This table defines the filenames that are associated with the
	// font descriptions. They are used so that PHP can correctly
	// load the fonts to determine what text line widths are. These
	// come in the Debian 'gsfonts' package.
	//
	var $font_table = array (
		'URWGothicL' => 'a010013l',
		'URWBookmanL-Light' => 'b018012l',
		'URWBookmanL-DemiBold' => 'b018015l',
		'URWBookmanL-LightItalic' => 'b018032l',
		'URWBookmanL-DemiBoldItalic' => 'b018035l',
		'CenturySchoolbookL-Roman' => 'c059013l',
		'CenturySchoolbookL-Bold' => 'c059016l',
		'CenturySchoolbookL-Italic' => 'c059033l',
		'CenturySchoolbookL-BoldItalic' => 'c059036l',
		'Dingbats' => 'd050000l',
		'Dingbats-Regular' => 'c050000l',
		'NimbusSansL' => 'n019003l',
		'NimbusSansL-Regular' => 'n019003l',
		'NimbusSansL-Bold' => 'n019004l',
		'NimbusSansL-Italic' => 'n019023l',
		'NimbusSansL-RegularItalic' => 'n019023l',
		'NimbusSansL-BoldItalic' => 'n019024l',
		'NimbusSansL-Condensed' => 'n019043l',
		'NimbusSansL-RegularCondensed' => 'n019043l',
		'NimbusSansL-BoldCondensed' => 'n019044l',
		'NimbusMonoL' => 'n022003l',
		'NimbusMonoL-Regular' => 'n022003l',
		'NimbusMonoL-Bold' => 'n022004l',
		'NimbusMonoL-Oblique' => 'n022023l',
		'NimbusMonoL-RegularOblique' => 'n022023l',
	);

	// Method: Postscript constructor
	//
	// Parameters:
	//
	//	$fname - File name for output
	//
	//	$_options - (optional) Associative array of optional
	//	parameters.
	//		* title - Title of generated page. Defaults to
	//		'Generated with PSLib'.
	//		* orientation - Orientation of generated page.
	//		Defaults to 'Portrait'.
	//		* paper - Paper size. Defaults to 'letter'.
	//		* author - Author name. Defaults to
	//		'phpwebtools/PSLib'.
	//
	function Postscript ( $fname = "", $_options = '' ) {
		// Check for default options
		if (!is_array($_options)) {
			$this->options['title'] = 'Generated with PSLib';
			$this->options['orientation'] = 'Portrait';
			$this->options['paper'] = 'Letter';
			$this->options['author'] = 'phpwebtools/PSLib';
		} else {
			$this->options = $_options;
		}

		// Defaults
		if (!isset($this->options['paper'])) {
			$this->options['paper'] = 'Letter';
		}
		
		//- A text string was requested: file name to create
		if($fname) {
			if(! $this->fp = fopen($fname,"w")) return(0);
		}
        
		$this->string .= "%!PS-Adobe-3.0 \n";
		$this->string .= '%%Creator: ' . $this->options['author'] . "\n";
		$this->string .= '%%CreationDate: ' . date("d/m/Y, H:i") . "\n";
		$this->string .= '%%Title: ' . $this->options['title'] . "\n";
		$this->string .= "%%PageOrder: Ascend \n";
		$this->string .= '%%Orientation: ' . $this->options['orientation'] . "\n";
		$this->string .= "%%EndComments \n";
		$this->string .= "%%BeginProlog \n";
		$this->string .= "%%BeginResource: definitions \n";

		// Section for Postscript macros
		$this->string .= "/inch  {72 mul} def \n";


		/* Comment this to disable support for international character encoding (or remove file acentos.ps)
		if (file_exists('acentos.ps')) {
			if($f = join('',file('acentos.ps'))) $this->string .= $f;
		}
		*/
		
		$this->string .= "%%EndResource \n";
		$this->string .= "%%EndProlog \n";

		// Reset last virtual bounding box
		$this->_last_vbbox_y = -1;

		return true;
	} // end constructor Postscript

	// Method: begin_page
	//
	//	Begin new page
	//
	// Parameters:
	//
	//	$page - Page number
	//
	function begin_page($page) {
		$this->string.= "%%Page: " . $page . ' ' . $page . "\n";
		$this->page_opened = true;
		return true;
	} // end method begin_page
    
	// Method: end_page
	//
	//	End current page
	//
	function end_page() {
		if ($this->page_opened) {
			$this->string .= "showpage \n";
			$this->page_opened = false;
			return true;
		} else {
			return false;
		}
	} // end method end_page

	// Method: center_xy
	//
	//	Show some centeredtext at specific coordinates 
	//
	// Parameters:
	//
	//	$text - Text to display
	//
	//	$xcoord - X coordinate
	//
	//	$ycoord - Y coordinate
	//
	function center_xy($text, $xcoord, $ycoord) {
		if(!$text || !$xcoord || !$ycoord) return(0);
       
		$this->moveto($xcoord, $ycoord);
		$this->string .=  '(' . $text  . ') dup stringwidth '.
				'pop 2 div neg 0 rmoveto show '."\n";
		$this->show($text);

		return true;
	} // end method center_xy

	// Method: center_xy_font
	//
	//	Show some centered text at specific coordinates
	//	with font settings
	//
	// Parameters:
	//
	//	$text - Text to display
	//
	//	$xcoord - X coordinate
	//
	//	$ycoord - Y coordinate
	//
	//	$font_name - Name of the font to be used
	//
	//	$font_size - Size of the font to be used
	//
	function center_xy_font($text, $xcoord, $ycoord, $font_name, $font_size) {
		if(!$text || !$xcoord || !$ycoord || !$font_name || !$font_size) return(0);

		$this->set_font($font_name, $font_size);
		$this->center_xy($text, $xcoord, $ycoord);

		return true;
	} // end method center_xy_font

	// Method: close
	//
	//	Close the postscript file
	//
	function close() {
		if ($this->page_opened) {
			$this->string .= "showpage \n";
		}
		if($this->fp) {
			fwrite($this->fp,$this->string);
			fclose($this->fp);
		}
		return($this->string);
	} // end method close

	// Method: inches_to_x
	//
	//	Determine "x" value by the absolute position from the
	//	upper left hand corner horizontally. Returns a point
	//	value.
	//
	// Parameters:
	//
	//	$i - Horizontal position from left side of the paper
	//	in inches.
	//
	// Returns:
	//
	//	X point value
	//
	function inches_to_x($i) {
		// Determine from page width
		switch($this->options['page']) {
			case 'Letter': default: $s = 8.5;
		} // end switch
		
		// Remember: Postscript renders from lower right (0,0) to
		// upper right (x,y). Width, therefore, goes from left
		// to right, and height goes from bottom to top.
		// ( pos ) x 72 ppi = absolute position
		return ($i) * 72;
	} // end method inches_to_x

	// Method: inches_to_y
	//
	//	Determine "y" value by the absolute position from the
	//	upper left hand corner vertically. Returns a point
	//	value.
	//
	// Parameters:
	//
	//	$i - Vertical position from top of the paper in inches.
	//
	// Returns:
	//
	//	Y point value
	//
	function inches_to_y($i) {
		// Determine from page height
		switch($this->options['page']) {
			case 'Letter': default: $s = 11;
		} // end switch
		
		// ( size - pos ) x 72 ppi = absolute position
		return ($s - $i) * 72;
	} // end method inches_to_y

	// Method: line
	//
	//	Draw a line
	//
	// Parameters:
	//
	//	$xcoord_from - Origin X position of the line
	//
	//	$ycoord_from - Origin Y position of the line
	//
	//	$xcoord_to - Destination X position of the line
	//
	//	$ycoord_to - Destination Y position of the line
	//
	//	$linewidth - Width of the line in points
	//
	function line($xcoord_from=0, $ycoord_from=0, $xcoord_to=0, $ycoord_to=0, $linewidth=0) {
		if(!$xcoord_from || !$ycoord_to || !$xcoord_to || !$ycoord_to || !$linewidth) return false;
        
		$this->string .= $linewidth . " setlinewidth  \n";
		$this->string .= $xcoord_from . ' ' . $ycoord_from  . " moveto \n";
		$this->string .= $xcoord_to . ' ' . $ycoord_to  . " lineto \n";
		$this->string .= "stroke \n";
        
		return true;
	} // end method line

	// Method: moveto
	//
	//	Move to coordinates
	//
	// Parameters:
	//
	//	$xcoord - Destination X coordinate
	//
	//	$ycoord - Destination Y coordinate
	//
	function moveto($xcoord, $ycoord) {
		if(empty($xcoord) || empty($ycoord)) return false;
		$this->string .= $xcoord . ' ' . $ycoord . " moveto \n";
		return true;
	} // end method moveto


	// Method: moveto_font
	//
	//	Move to coordinates and change the font
	//
	// Parameters:
	//
	//	$xcoord - Destination X coordinate
	//
	//	$ycoord - Destination Y coordinate
	//
	//	$font_name - Name of the font
	//
	//	$font_size - Point size of the font
	//
	function moveto_font($xcoord, $ycoord, $font_name, $font_size) {
		if(!$xcoord || !$ycoord || !$font_name || !$font_size) return false;
		$this->string .= $xcoord . ' ' . $ycoord . " moveto \n";
		$this->string .= '/' . $font_name . ' findfont ' . $font_size . " scalefont setfont \n";
		return true;
	} // end method moveto_font


	// Method: open_ps
	//
	//	Insert a PS file/image (remember to delete the
	//	information in the top of the file (source))
	//
	// Parameters:
	//
	//	$ps_file - Name of the file to join into the current
	//	rendering
	//
	function open_ps($ps_file="") {
		if(!$ps_file) return false;

		if($f = join('',file($ps_file))) {
			$this->string .= $f;
		} else {
			return false;
		}

		return true;
	} // end method open_ps

	// Method: rect
	//
	//	Draw a rectangle
	//
	// Parameters:
	//
	//	$xcoord_from - Origin X position of the upper left corner
	//
	//	$ycoord_from - Origin Y position of the upper left corner
	//
	//	$xcoord_to - Destination X position of the lower right
	//	corner
	//
	//	$ycoord_to - Destination Y position of the lower right
	//	corner
	//
	//	$linewidth - Width of the lines in points
	//
	function rect($xcoord_from, $ycoord_from, $xcoord_to, $ycoord_to, $linewidth) {
		if(!$xcoord_from || !$ycoord_from || !$xcoord_to || !$ycoord_to || !$linewidth) return false;

		$this->string .= $linewidth . " setlinewidth  \n";
		$this->string .= "newpath \n";
		$this->string .= $xcoord_from . ' ' . $ycoord_from  . " moveto \n";
		$this->string .= $xcoord_to . ' ' . $ycoord_from  . " lineto \n";
		$this->string .= $xcoord_to . ' ' . $ycoord_to  . " lineto \n";
		$this->string .= $xcoord_from . " " . $ycoord_to  . " lineto \n";
		$this->string .= "closepath \n";
		$this->string .= "stroke \n";

		return true;
	} // end method rect


	// Method: rect_fill
	//
	//	Draw and shade a rectangle
	//
	// Parameters:
	//
	//	$xcoord_from - Origin X position of the upper left corner
	//
	//	$ycoord_from - Origin Y position of the upper left corner
	//
	//	$xcoord_to - Destination X position of the lower right
	//	corner
	//
	//	$ycoord_to - Destination Y position of the lower right
	//	corner
	//
	//	$linewidth - Width of the lines in points
	//
	//	$darkness - Darkness, in 0 .. 1 value.
	//
	function rect_fill($xcoord_from, $ycoord_from, $xcoord_to,
			$ycoord_to, $linewidth, $darkness) {
		if(!$xcoord_from || !$ycoord_from || !$xcoord_to || !$ycoord_to || !$linewidth || !$darkness) return false;

		$this->string .= "newpath \n";
		$this->string .= $linewidth . " setlinewidth  \n";
		$this->string .= $xcoord_from . ' ' . $ycoord_from  . " moveto \n";
		$this->string .= $xcoord_to . ' ' . $ycoord_from  . " lineto \n";
		$this->string .= $xcoord_to . ' ' . $ycoord_to  . " lineto \n";
		$this->string .= $xcoord_from . ' ' . $ycoord_to  . " lineto \n";
		$this->string .= "closepath \n";
		$this->string .= "gsave \n";
		$this->string .= $darkness . " setgray  \n";
		$this->string .= "fill \n";
		$this->string .= "grestore \n";
		$this->string .= "stroke \n";

		return true;
	} // end method rect_fill

	// Method: rotate
	//
	//	Set rotation, use 0 or 360 to end rotation
	//
	// Parameters:
	//
	//	$degrees - Degrees to rotate the current drawing aspect
	//
	function rotate($degrees) {
		if(!$degrees) return false;

		if(($degrees == '0') or ($degrees == '360')) {
			$this->string .= "grestore \n";
		} else {
			$this->string .= "gsave \n";
			$this->string .= $degrees . " rotate \n";
		}
		return true;
	} // end method rotate

	// Method: set_font
	//
	//	Set the font to show
	//
	// Parameters:
	//
	//	$font_name - Name of the font
	//
	//	$font_size - Point size of the font
	//
	function set_font($font_name, $font_size) {
		if(!$font_name || !$font_size) return false;
		$this->string .=  '/' . $font_name . ' findfont ' . $font_size . " scalefont setfont \n";
		return true;
	} // end method set_font

	// Method: show
	//
	//	Show some text at the current coordinates
	//	(use 'moveto' to set coordinates)
	//
	// See Also:
	//	<moveto>
	//
	function show($text) {
		if(!$text) return false;
		$this->string .=  '(' . $text  . ") show \n";
		return true;
	} // end method show

	// Method: show_eval
	//	
	//	Evaluate the text and show it at the current coordinates
	//
	// Parameters:
	//
	//	$text - Text with php variable expressions
	//
	function show_eval($_text) {
		// If this is not done, $text is not writeable (nor do
		// you want to to be), so it has to be copied into the
		// internal scope. Bug with last PSLib release. - jeff
		$text = $_text;

		if(!$text) return false;
	       
		eval("\$text = \"$text\";");
		$this->string .=  '(' . $text  . ") show \n";
	       
		return true;
	} // end method show_eval

	// Method: show_xy
	//
	//	Show some text at specific coordinates 
	//
	// Parameters:
	//
	//	$text - Text to display
	//
	//	$xcoord - X coordinate of origin point
	//
	//	$ycoord - Y coordinate of origin point
	//
	function show_xy($text, $xcoord, $ycoord) {
		if(!$text || !$xcoord || !$ycoord) return(0);
       
		$this->moveto($xcoord, $ycoord);
		$this->show($text);

		return true;
	} // end method show_xy

	// Method: show_xy_font
	//
	//	Show some text at specific coordinates with font settings
	//
	// Parameters:
	//
	//	$text - Text to display
	//
	//	$xcoord - X coordinate of origin point
	//
	//	$ycoord - Y coordinate of origin point
	//
	//	$font_name - Name of the font
	//
	//	$font_size - Size of the font
	//
	function show_xy_font($text, $xcoord, $ycoord, $font_name, $font_size) {
		if(!$text || !$xcoord || !$ycoord || !$font_name || !$font_size) return(0);

		$this->set_font($font_name, $font_size);
		$this->show_xy($text, $xcoord, $ycoord);

		return true;
	} // end method show_xy_font

	// Method: text_vbbox
	//
	//	Creates a text virtual bounding box, to determine whether
	//	text will fit in a certain area
	//
	// Parameters:
	//
	//	$string - Text string to be displayed. Line breaks with
	//	line break characters are honored, otherwise text is
	//	wrapped at the edge of the box.
	//
	//	$font - Name of the font to be used
	//
	//	$size - Size of the font to be used
	//
	//	$tx - Top left X coordinate for the bounding box
	//
	//	$ty - Top left Y coordinate for the bounding box
	//
	//	$bx - Bottom right X coordinate for the bounding box
	//
	//	$by - (optional) Bottom right Y coordinate for the
	//	bounding box. If this is not set, this is not considered
	//	to be a fixed size bounding box, and the Y parameter
	//	will expand until either the page ends or the text is
	//	exhausted.
	//
	function text_vbbox ($string, $font, $size, $tx, $ty, $bx, $by=-1) {
		// Check to see which mode we're running in:
		if ($by==-1) {
			$fixed = false;
			//print "not fixed<br/>\n";
		} else {
			$fixed = true;
			//print "fixed<br/>\n";
		}

		// Determine width
		$width = $bx - $tx;
		if ($width <= 1) { die(__FILE__.":".__LINE__." - zero width"); }
		//print "width = $width<br/>\n";

		// Initially split by "\n"'s
		$orig = explode("\n", $string);

		foreach ($orig AS $__garbage => $this_string) {
			// Check for fitting *as is*
			//print "cur width = ".$this->_get_text_length ($font, ($size * $this->spacing_multiplier), $this_string)."<br/>\n";
			//print "this->_get_text_length (".$font.", (".$size." * ".$this->spacing_multiplier."), ".$this_string."<br/>\n";
			if (($cur_width = $this->_get_text_length ($font, ($size * $this->spacing_multiplier), $this_string)) <= $width) {
				if ($cur_width = 0) {
					die('current width cannot be 0');
				}
				//print "fits?<br/>\n";
				// If it fits, add the string
				$new[] = $this_string;
			} else { // end if fits as is
				// Loop through lines to find next split
				$split = $this->_get_string_split($font, ($size * $this->spacing_multiplier), $this_string, $width);
				//print "does not fit. initial 'split' is ".$split."<br/>\n";
				$fit = false;
				while ($split >= 2 and !$fit) {
					$new[] = trim(substr($this_string, 0, $split));
					$this_string = trim(substr($this_string, -(strlen($this_string) - $split)));
					//print "split = $split<br/>\n";
					//print "this_string = $this_string<br/>\n";
					$split = $this->_get_string_split($font, ($size * $this->spacing_multiplier), $this_string, $width);
					//print "split was returned as $split<br/>\n";
					if ($split == strlen($this_string)) {
						//print "fit = TRUE<br/>\n";
						$fit = true;
						$new[] = trim($this_string);
					}
					//print "<hr/>\n";
				}
			}
		} // end looping through fragments

		// If fixed size, determine how many lines can be shown.
		if ($fixed) {
			$num_lines = floor ( $width / $this->_get_text_height($font, ($size * $this->spacing_multiplier), $this_string) );
			// Transfer these
			$_bx = $bx; $_by = $by;

			// Unset current vbbox_y, since it isn't being used.
			unset($this->_last_vbbox_y);
		} else {
			// If not fixed size, figure out box scaling.
			// TODO: Make sure this doesn't go over the page ends
			$num_lines = count ($new);
			//print "num_lines = $num_lines<br/>\n";
			// Figure out bottom X and Y
			$_bx = $bx;
			$_by = $ty - ($num_lines * $size);

			// Transfer this to protected class variable.
			// (There *has* to be a better way to do this, even
			// though with the optional parameter, I can't
			// *really* use references...) - Jeff
			$this->_last_vbbox_y = $_by;
		}
		// Loop through correct number of lines
		for ($i=0; $i<$num_lines; $i++) {
			// Move to $tx, ($line * $size)
			$x = $tx; $y = ($ty - ($i * $size));
			// Display text
			$this->show_xy_font($new[$i], $x, $y, $font, ($size * $this->spacing_multiplier));
		}
	} // end method text_vbbox

	// Method: text_vbbox_bottom
	//
	//	Returns the last bottom Y coordinate of a virtual text
	//	bounding box.
	//
	// Returns:
	//
	//	Y coordinate, or false if there was no Y coordinate
	//	computed.
	//
	// See Also:
	//	<text_vbbox>
	//
	function text_vbbox_bottom() {
		return ( (isset($this->_last_vbbox_y)) ? $this->_last_vbbox_y : false );
	} // end method text_vbbox_bottom

	// Method: x_to_inches
	//
	//	Convert X coordinate to horizontal measurement
	//
	// Parameters:
	//
	//	$p - Position as X coordinate in points
	//
	// Returns:
	//
	//	Horizontal inches measurement from the top left of
	//	the page.
	//
	function x_to_inches($p) {
		// Determine from page width
		switch($this->options['page']) {
			case 'Letter': default: $s = 8.5;
		} // end switch
		
		// Check for 0
		if ($p == 0) return $s;
		
		// size - ( pos / 72 ppi ) = inches
		return $s - ($p / 72);
	} // end method x_to_inches

	// Method: y_to_inches
	//
	//	Convert Y coordinate to vertical measurement
	//
	// Parameters:
	//
	//	$p - Position as Y coordinate in points
	//
	// Returns:
	//
	//	Vertical inches measurement from the top left of
	//	the page.
	//
	function y_to_inches($p) {
		// Determine from page height
		switch($this->options['page']) {
			case 'Letter': default: $s = 11;
		} // end switch

		// Check for 0
		if ($p == 0) return $s;
		
		// size - ( pos / 72 ppi ) = inches
		return $s - ($p / 72);
	} // end method y_to_inches

	//------------------------------------------------------------
	// Internal Functions
	//------------------------------------------------------------

	// Method: _get_string_split
	//
	//	Internal function to determine the split of a string
	//	while still fitting within a virtual boundary
	//
	// Parameters:
	//
	//	$font - Font to use
	//
	//	$size - Size of the font to use
	//
	//	$_string - Text string to split
	//
	//	$width - Width of virtual bounding box in points
	//
	// Returns:
	//
	//	Position to split the string at.
	//
	function _get_string_split ($font, $size, $_string, $width) {
		$string = trim($_string);
		$pos = strlen($string);

		// Initial first case check for length compliance
		if ($this->_get_text_length($font, $size, trim(substr($string, 0, $pos))) <= $width) {
			return $pos;
		}

		// Check to see if we're at a space
		// If it isn't a space, backpedal until it is
		while ($pos > 0) {
			// Skip back to previous word ending
			while (($string[$pos] != ' ') and ($pos > 0)) {
				$pos -= 1;
				//print "pos is $pos<br/>\n";
			}
			// Check to see if we're successful
			if ($this->_get_text_length($font, $size, trim(substr($string, 0, $pos))) <= $width) {
				//print "found width at $pos<br/>\n";
				return $pos;
			} else {
				// Reverse over space for next loop, otherwise
				// we get stuck
				$pos -= 1;
			}
		}
		die('_get_string_split(): Should not be here!');
	} // end method _get_string_split

	// Method: _get_text_height
	//
	//	Internal method to determine maximum height of a
	//	string in the designated font and size.
	//
	// Parameters:
	//
	//	$font - Font to be used
	//
	//	$size - Size of the font to be used
	//
	//	$string - Text string
	//
	// Returns:
	//
	//	Height in points.
	//
	function _get_text_height ($font, $size, $string) {
		// Check for a cached font, if not, load it and get a number
//		if (!isset($this->_fonts[$font])) {
//			$this->_fonts[$font] = ImagePSLoadFont('/usr/share/fonts/type1/gsfonts/'.$this->font_table[$font].'.pfb');
//		}

		// Get the bounding box from PHP
//		list ($lx, $ly, $rx, $ry) = ImagePSBBox($string, $this->_fonts[$font], $size, 0, 0, 0);
		list ($lx, $ly, $rx, $ry) = ImagePSBBox($string, ImagePSLoadFont('/usr/share/fonts/type1/gsfonts/'.$this->font_table[$font].'.pfb'), $size, 0, 0, 0);

		// Get the width and return in points
		return ($ry - $ly);
	} // end method _get_text_height

	// Method: _get_text_length
	//
	//	Internal method to determine length of a string in
	//	the designated font and size.
	//
	// Parameters:
	//
	//	$font - Font to be used
	//
	//	$size - Size of the font to be used
	//
	//	$string - Text string
	//
	// Returns:
	//
	//	Length in points
	//
	function _get_text_length ($font, $size, $string) {
		static $_fonts;
		//print "font = $font, size = $size, string = $string<br/>\n";

		// Check for a cached font, if not, load it and get a number
		if (!isset($this->_fonts[$font])) {
			$this->_fonts[$font] = ImagePSLoadFont('/usr/share/fonts/type1/gsfonts/'.$this->font_table[$font].'.pfb');
		}

		// Get the bounding box from PHP
		//print "ImagePSBBox(".$string.", ".$this->_fonts[$font].", ".$size.", 0, 0, 0);<br/>\n";
		list ($lx, $ly, $rx, $ry) = ImagePSBBox($string, $this->_fonts[$font], $size, 0, 0, 0);
		//print "lx = $lx, ly = $ly, rx = $rx, ry = $ry<br/>\n";

		// Get the width and return in points
		//print "should be ".($rx - $lx)."<br/>\n";
		return ($rx - $lx);
	} // end method _get_text_length

} // end class Postscript

?>
