<?php
 // $Id$
 // desc: form rendering functions
 // lic : GPL, v2

include_once ("lib/freemed.php");

if (!defined ("__RENDER_FORMS_PHP__")) {

define ('__RENDER_FORMS_PHP__', true);

// ****************************************
// ********* FIXED FORMS SECTION **********
// ****************************************

// User defined sort function for rows and columns
function objbyrowcol ( $a, $b ) {
	// If the rows are the same, use the columns for comparison
	if ($a->row == $b->row)
		return ( ($a->col < $b->col) ? -1 : 1 );
	// Otherwise sort by the rows
	return ( ($a->row < $b->row) ? -1 : 1 );
} // end function objbyrowcol

class fixedFormEntry {
	// internal vars
	var $row;
	var $col;
	var $len;
	var $data;
	var $format;
	var $comment;

	// constructor fixedFormEntry
	function fixedFormEntry ($row, $col, $len, $data, $format, $comment) {
		$this->row     = $row;
		$this->col     = $col;
		$this->len     = $len;
		$this->data    = stripslashes($data);
		$this->format  = $format;
		$this->comment = $comment;
	} // end constructor fixedFormEntry
} // end class fixedFormEntry

	// function swap_fixedFormEntry
function swap_fixedFormEntry (&$ff1, &$ff2) {
	global $display_buffer;

	// move #1 into temp
	$fft = new fixedFormEntry ($ff1->row, $ff1->col, $ff1->len, $ff1->data,
		$ff1->format, $ff1->comment);
	// move #2 into #1
	$ff1->row     = $ff2->row;
	$ff1->col     = $ff2->col;
	$ff1->len     = $ff2->len;
	$ff1->data    = $ff2->data;
	$ff1->format  = $ff2->format;
	$ff1->comment = $ff2->comment;

	// move temp into #2
	$ff2->row     = $fft->row;
	$ff2->col     = $fft->col;
	$ff2->len     = $fft->len;
	$ff2->data    = $fft->data;
	$ff2->format  = $fft->format;
	$ff2->comment = $fft->comment;
} // end function swap_fixedFormEntry

// function to render single item, proper length
function render_FixedFormEntry ($formentry) {
	global $display_buffer;
	global $debug;

	if ($formentry->len < 1) return "";
	if ($debug) $display_buffer .= "\norig = $formentry->data <BR>\n";
	flush();
	$this_evalled = ( (strpos ($formentry->data, "\$") >=0) ?
		fm_eval ($formentry->data) :
		$formentry->data );

	if ($debug) $display_buffer .= "\nnew = $this_evalled <BR>\n";
	flush();
	if (strlen ($this_evalled) > $formentry->len) {
		// Cut out superfluous characters
		$length_adjusted = substr ($this_evalled, 0, $formentry->len);
	} elseif (strlen ($this_evalled) < $formentry->len) {
		// Add spaces, depending on padding
		if ($formentry->format == "N") {
			// Fill zeros to left 
			$this_evalled = str_pad($this_evalled,
				$formentry->len,
				"0",
				STR_PAD_LEFT
			);
		} else {
			// Pad spaces to right
			$this_evalled = str_pad(
				$this_evalled,
				$formentry->len
			);
		}
		$length_adjusted = $this_evalled;
	} else { // no change neccesary
		$length_adjusted = $this_evalled;
	} // end of checking length
	return $length_adjusted;
} // end function render_FixedFormEntry

function render_fixedForm ($id) {
	global $display_buffer;
	global $debug;

	if ($debug) $display_buffer .= "\nEntered render_fixedForm<BR>\n";
	flush ();

	// get record
	$this_form  = freemed::get_link_rec ($id, "fixedform");
	$pagelength = $this_form ["ffpagelength"];
	$rows       = fm_split_into_array ($this_form["ffrow"    ]);
	$cols       = fm_split_into_array ($this_form["ffcol"    ]);
	$lens       = fm_split_into_array ($this_form["fflength" ]);
	$datas      = fm_split_into_array ($this_form["ffdata"   ]);
	$formats    = fm_split_into_array ($this_form["ffformat" ]);
	$comments   = fm_split_into_array ($this_form["ffcomment"]);
	$number_of_entries = count ($rows);

	if ($debug) $display_buffer .= "\nnumber of entries = ".
		"$number_of_entries<BR>\n";
	flush();

	// import entries into array
	for ($i=0;$i<$number_of_entries;$i++) {
		$form_entry [$i] = new fixedFormEntry (
			$rows[$i],
			$cols[$i],
			$lens[$i],
			$datas[$i],
			$formats[$i],
			$comments[$i]
		);
	} // end for loop

	// Sort by using user defined sort function
	usort($form_entry, "objbyrowcol");     

	$cur_row    = 1;  // reset row
	$cur_col    = 1;  // reset col
	$cur_entry  = 0;  // start with the first entry
	$buffer     = ""; // clear buffer

	// loop through all entries
	while ($cur_entry < $number_of_entries) {
		// Import current entry item
		$form_item = $form_entry [$cur_entry];

		if ($debug) $display_buffer .= "\n$cur_entry out of ".
			"$number_of_entries <BR>\n";
		flush();

		// first, move to proper row if not there
		if (($form_item->row + $line_off) > $cur_row) {
			// How many CRs do we need?
			$num_crs = (($form_item->row + $line_off) - $cur_row);

			// Add appropriate CRs to skip lines
			for ($lc=0;$lc<$num_crs;$lc++) $buffer .= "\n";

			// Reset to the beginning of the next row
			$cur_col = 1;
		} // end of checking for current row status

		// Move to neccesary column if not there yet
		if ($form_item->col > $cur_col) {
			$num_spc = (($form_item->col) - $cur_col);
			for ($lc=0;$lc<$num_spc;$lc++) $buffer .= " "; 
		} // end of checking for current row status

		// Actually write the rendered item to the buffer...
		$cur_row = ($form_item->row + $line_off);
		$cur_col = $form_item->col + $form_item->len;
		if ($debug) $display_buffer .= "\nRendering entry <BR>\n";
		flush();
		$buffer .= render_fixedFormEntry ($form_item);
		if ($debug) $display_buffer .= "\nRendering finished <BR>\n";
		flush();
		$cur_entry++; // increment the counter!
	} // while there are more entries, loop

	// Add trailing CR to buffer (for end of last line)
	$buffer .= "\n";
	$cur_row ++;

	// Pad page to proper length with CRs
	if ($cur_row < $pagelength)
		for ($i=0;$i<($pagelength - $cur_row);$i++)
			$buffer .= "\n";

	// Send the buffer back to the calling routine
	return $buffer."\n";
} // end function render_fixedForm


function render_fixedRecord ($id,$rectype="") {
	global $display_buffer;
	global $debug;

	if ($debug) $display_buffer .= "\nEntered render_fixedForm<BR>\n";
	flush ();

	if (empty($rectype)) return "";

	$this_form  = freemed::get_link_rec ($id, "fixedform"); // get record
	$linelength = $this_form ["fflinelength"];
	$pagelength = $this_form ["ffpagelength"];
	$rows       = fm_split_into_array ($this_form["ffrow"    ]);
	$cols       = fm_split_into_array ($this_form["ffcol"    ]);
	$lens       = fm_split_into_array ($this_form["fflength" ]);
	$datas      = fm_split_into_array ($this_form["ffdata"   ]);
	$formats    = fm_split_into_array ($this_form["ffformat" ]);
	$comments   = fm_split_into_array ($this_form["ffcomment"]);
	$number_of_entries = count ($rows);

	if ($debug) $display_buffer .= "\nnumber of entries = $number_of_entries<BR>\n";
	flush();

	$x=0;
	// import entries into array
	for ($i=0;$i<$number_of_entries;$i++) {
		if ($rows[$i] != $rectype)
			continue;
	    	$form_entry [$x] = new fixedFormEntry (
			$rows[$i],
			$cols[$i],
			$lens[$i],
			$datas[$i],
			$formats[$i],
			$comments[$i]
		);
		$x++;
	} // end for loop

	// Sort by row and column using user-defined function
	usort($form_entry, "objbyrowcol");

	$cur_row    = 1;  // reset row
	$cur_col    = 1;  // reset col
	$cur_entry  = 0;  // start with the first entry
	$buffer     = ""; // clear buffer

	$number_of_entries = count($form_entry);
	// loop through all entries
	while ($cur_entry < $number_of_entries) {
		// Import current entry item
		$form_item = $form_entry [$cur_entry];

		if ($form_item->col > $cur_col) {
			$fillcnt = $form_item->col - $cur_col;
			$paddat = "";
			$paddat = str_pad($paddat,$fillcnt);
			$buffer .= $paddat;
			$cur_col = $form_item->col;
		}	

		$buffer .= render_fixedFormEntry ($form_item);
		$cur_col = $form_item->col + $form_item->len;
		$cur_entry++; // increment the counter!
		//$display_buffer .= "cur_col $cur_col<BR>";
	  } // while there are more entries, loop

	// add trailing CR to buffer
	$buffer = str_pad($buffer,$linelength);
	$buffer .= "\n";
	//$display_buffer .= "$buffer<BR>";
	return $buffer;
} // end function render_fixedRecord

} // end checking for __RENDER_FORMS_PHP__

?>
