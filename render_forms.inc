<?php
 # file: render_forms.inc
 # desc: form rendering functions
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 if (!$__global_var__)        include ("global.var.inc");
 if (!$__freemed_functions__) include ("freemed-functions.inc");

// ****************************************
// ********* FIXED FORMS SECTION **********
// ****************************************

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
    $this->data    = $data;
    $this->format  = $format;
    $this->comment = $comment;
  } // end constructor fixedFormEntry
} // end class fixedFormEntry

// function swap_fixedFormEntry
function swap_fixedFormEntry (&$ff1, &$ff2) {
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
  $this_evalled = fm_eval ($formentry->data);
  if (strlen ($this_evalled) > $formentry->len) {
    $length_adjusted = substr ($this_evalled, 0, $formentry->len);
  } elseif (strlen ($this_evalled) < $formentry->len) {
    for ($loop=0;$loop<($this_evalled - ($formentry->len));$loop++)
      $this_evalled .= " ";
    $length_adjusted = $this_evalled;
  } else { // no change neccesary
    $length_adjusted = $this_evalled;
  } // end of checking length
  return $length_adjusted;
} // end function render_FixedFormEntry

function render_fixedForm ($id, $offset=0) {
  global $loop;

  $this_form  = freemed_get_link_rec ($id, "fixedform"); // get record
  $pagelength = $this_form ["ffpagelength"];
  $rows       = fm_split_into_array ($this_form["ffrow"    ]);
  $cols       = fm_split_into_array ($this_form["ffcol"    ]);
  $lens       = fm_split_into_array ($this_form["fflength" ]);
  $datas      = fm_split_into_array ($this_form["ffdata"   ]);
  $formats    = fm_split_into_array ($this_form["ffformat" ]);
  $comments   = fm_split_into_array ($this_form["ffcomment"]);
  $number_of_entries = count ($rows);

   // import entries into array
  for ($i=0;$i<$number_of_entries;$i++) {
    $form_entry [$i] = new fixedFormEntry ($rows[$i],    $cols[$i],
                                           $lens[$i],    $datas[$i],
                                           $formats[$i], $comments[$i]);
  } // end for loop

  /* THIS DOESN'T WORK !!! SOMEONE DO AN INSERTION SORT!!!
     BEGIN FUBAR'D CODE -----------------------------------
  // bubble sort so that everything else works
  for ($i=($number_of_entries-1);$i>=0;$i--) {
   for ($j=1;$j<=$i;$j++) {
    $a = $form_entry [$i-1]; $b = $form_entry [$i];
    if ( ($a->rows > $b->rows) or
         (($a->rows <= $b->rows) and ($a->cols > $b->cols)) )
     swap_fixedFormEntry ($form_entry [$i-1], $form_entry [$i]);
   } // end inner bubble sort routine
  } // end outer bubble sort routine
     END OF HUGE BLOCK OF FUBAR'D CODE -------------------- */

  $cur_row    = 1;  // reset row
  $cur_col    = 1;  // reset col
  $cur_entry  = 0;  // start with the first entry
  $recursive  = 0;  // recursive counter off (NOT ENABLED YET)
  $loop_start = 0;  // mark the start of the loop
  $in_loop    = false; // we aren't in a loop yet
  $buffer     = ""; // clear buffer

  // loop through all entries
  while ($cur_entry < $number_of_entries) {
    $form_item = $form_entry [$cur_entry]; // import current entry item
    $next_item = $form_entry [$cur_entry + 1];
    $skip_to   = $cur_entry + 1;           // next entry is...
    $inc_loop  = false;                    // don't increment loop
    $loop      = $recursive + $offset;     // figure current loop position
    $line_off  = 0;                        // line offset = 0

    // determine where we are in the loop
    if (strpos($form_item->format, "L")) {
      if (!$in_loop) {
        $loop_start = $cur_entry;
        $in_loop    = true; // set in loop
        $recursive  = 1; // set to first of loop
      } // end of if not in loop

      // calculate line offset
      if ($recursive > 0) {
        $line_off = ($recursive - 1) * $this_form[ffloopoffset];
      } else {
        $line_off = 0;
      } // end of checking for recursive

    } elseif ($in_loop and (!strpos($next_item->format, "L"))) {
      // if we are in the loop, but have reached the end...
      // if we have not reached the count, skip back
      if ($recursive < $this_form[ffloopnum]) {
        $skip_to = $loop_start; // skip back to beginning of loop
        $inc_loop = true;       // increment the loop counter
      } // end if recursive < ffloopnum
    } // end if loop

     // first, move to proper row if not there
    if (($form_item->row + $line_off) > $cur_row) {
      $num_crs = (($form_item->row + $line_off) - $cur_row);
      for ($lc=0;$lc<$num_crs;$lc++) $buffer .= "\n";
      $cur_col = 1; // reset to the beginning of the row
    } // end of checking for current row status
     // now move to proper column if not there
    if ($form_item->col > $cur_col) {
      $num_spc = (($form_item->col) - $cur_col);
      for ($lc=0;$lc<$num_spc;$lc++) $buffer .= " "; 
    } // end of checking for current row status
     // actually write the rendered item to the buffer...
    $cur_row = ($form_item->row + $line_off); 
    $cur_col = $form_item->col;
    $buffer .= render_fixedFormEntry ($form_item);
    $cur_entry = $skip_to; // increment the counter!
    if ($inc_loop) $recursive++; // increment loop counter
    if ($recursive == $this_form[ffloopnum]) $in_loop = false;
  } // while there are more entries, loop

  // add trailing CR to buffer
  $buffer .= "\n";
  $cur_row ++;

  // make sure page is at proper length
  if ($cur_row < $pagelength)
   for ($i=0;$i<($pagelength - $cur_row);$i++)
     $buffer .= "\n";

  // send the buffer back to the calling routine
  return $buffer;
} // end function render_fixedForm

?>
