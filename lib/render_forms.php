<?php
 // $Id$
 // desc: form rendering functions
 // lic : GPL, v2

 include ("lib/freemed.php");

if (!defined ("__RENDER_FORMS_PHP__")) {

define (__RENDER_FORMS_PHP__, true);

// ****************************************
// ********* FIXED FORMS SECTION **********
// ****************************************

class diagnosisSet {
  var $internal_stack;      // internal "stack" of codes
  var $stack_size;          // number currently in queue
  var $maximum_stack_size;  // maximum number in stack

  // constructor diagnosisSet
  function diagnosisSet ($max_size = 4) {
    $this->maximum_stack_size = $max_size;    // set maximum size
    $this->stack_size         = 0;            // empty the stack
  } // end constructor diagnosisSet

  // function diagnosisSet->getStack
  // -- returns the actual codes in an array
  function getStack ($null_value = "") {
    for ($i=1;$i<=($this->stack_size);$i++) 
      $figured_stack[$i] = ereg_replace ("[^A-Z0-9.]", "",
       freemed_get_link_field (
       $this->internal_stack[$i], "icd9", "icd9code"));
    return $figured_stack;
  } // end function getStack

  // function diagnosisSet->inStack
  // -- returns true or false, indicating whether value is in the stack
  function inStack ($val) {
    $found = false;
    for ($i=1;$i<=($this->stack_size);$i++) {
      if ($this->internal_stack[$i] == $val) $found = $i;
    }
    return $found;
  } // end function diagnosisSet->inStack

  // function diagnosisSet->numberUnique
  // -- sees how many unique values in an array are *NOT* in the stack
  function numberUnique ($newvals) {
    global $debug;

    // determine size of new array
    $size_of_new = count ($newvals);

    if ($debug) echo "numberUnique->sizeOfNew = $size_of_new <BR>\n";

    // if the stack is empty, they are all unique
    if ($this->stack_size==0) return $size_of_new;

    // if there are none, return 0
    if ($size_of_new < 1) return 0;

    // loop to find matches
    $current_uniques = 0;
    for ($i=0;$i<=$size_of_new;$i++) { // loop to count
      if ((!($this->inStack($newvals[$i]))) and
          ($newvals[$i] != 0) and
          (!empty($newvals[$i]))) $current_uniques++;
    } // end of loop to count...

    // return the current number of uniques
    return $current_uniques;
  } // end function diagnosisSet->numberUnique

  // function diagnosisSet->popValue
  // -- add value to the stack
  function popValue ($val) {
    global $debug;
    if (!($this->inStack ($val)) and ($val != 0)) {
      $this->stack_size++;                           // increment the stack size
      $this->internal_stack[$this->stack_size] = $val;
    }
    if ($debug) echo "stack size = $this->stack_size ($val) <BR>\n";
  } // end function diagnosisSet->popValue

  // function diagnosisSet->testAddSet
  // -- tests to see whether another "set" can be added to the stack
  //    and add it to the stack if there is room
  function testAddSet ($diag1=0, $diag2=0, $diag3=0, $diag4=0) {
    global $debug;
    unset ($diag);
    // first determine how many parameters
    $flag = 0;
    if ($diag1 > 0) { $flag++; $diag[$flag] = $diag1; }
    if ($diag2 > 0) { $flag++; $diag[$flag] = $diag2; }
    if ($diag3 > 0) { $flag++; $diag[$flag] = $diag3; }
    if ($diag4 > 0) { $flag++; $diag[$flag] = $diag4; }

    if ($debug) echo "\n$flag diagnoses in this charge<BR>\n";
    flush();

    // if there are no diagnoses, then return true
    if ($flag == 0) return true;

    // if there is enough room, return true and add
    if (($this->numberUnique ($diag)) <=
        (($this->maximum_stack_size) - ($this->stack_size))) {
      for ($i=1;$i<=$flag;$i++) $this->popValue($diag[$i]);
      return true;
    } // end of true return for add
    
    // as a last resort, return false
    return false;
  } // end function diagnosisSet->testAddSet

  // function diagnosisSet->xrefList
  // -- returns comma delimited list of referenced diag codes
  function xrefList ($diag1=0,$diag2=0,$diag3=0,$diag4=0) {
    global $debug;
    // first determine how many parameters
    $diag[1] = $diag1; $diag[2] = $diag2; $diag[3] = $diag3; $diag[4] = $diag4;
    $flag = 0;       // by default, none
    for ($i=1;$i<=4;$i++) {  // loop through diagnoses
      if (($diag[$i] != 0) and ($flag == ($i - 1))) $flag = $i;
    } // end of looping through diagnoses
    $num_found = 0; $found_array = "";
    for ($i=1;$i<=$flag;$i++) {
      if ($pos = $this->inStack ($diag[$i])) {
        $num_found++;                  // increment counter
        $found_array[$num_found] = $pos; // add reference
      } // end if found in stack
    } // end looping through the stack
    if ($debug) echo "\narray size = ".count($found_array)." <BR>\n";
    if ($num_found > 1) sort ($found_array);
    if ($num_found < 1) return "";
    if ($num_found == 1) return $found_array[1];
    return join ($found_array, ","); // join with commas and return    
  } // end function diagnosisSet->xrefList

} // end class diagnosisSet

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
  global $debug;

  if ($formentry->len < 1) return "";
  if ($debug) echo "\norig = $formentry->data <BR>\n";
  flush();
  $this_evalled = ( (strpos ($formentry->data, "\$") >=0) ?
                     fm_eval ($formentry->data)       :
                     $formentry->data                    );
  if ($debug) echo "\nnew = $this_evalled <BR>\n";
  flush();
  if (strlen ($this_evalled) > $formentry->len) {
    $length_adjusted = substr ($this_evalled, 0, $formentry->len);
  } elseif (strlen ($this_evalled) < $formentry->len) {
    $this_difference = ($formentry->len) - (strlen($this_evalled));
    for ($loop=0;$loop<$this_difference;$loop++)
      { $this_evalled .= " "; } //if ($debug) echo "\nSPACE<BR>\n"; }
    $length_adjusted = $this_evalled;
  } else { // no change neccesary
    $length_adjusted = $this_evalled;
  } // end of checking length
  return $length_adjusted;
} // end function render_FixedFormEntry

function render_fixedForm ($id) {
  global $debug;

  if ($debug) echo "\nEntered render_fixedForm<BR>\n";
  flush ();

  $this_form  = freemed_get_link_rec ($id, "fixedform"); // get record
  $pagelength = $this_form ["ffpagelength"];
  $rows       = fm_split_into_array ($this_form["ffrow"    ]);
  $cols       = fm_split_into_array ($this_form["ffcol"    ]);
  $lens       = fm_split_into_array ($this_form["fflength" ]);
  $datas      = fm_split_into_array ($this_form["ffdata"   ]);
  $formats    = fm_split_into_array ($this_form["ffformat" ]);
  $comments   = fm_split_into_array ($this_form["ffcomment"]);
  $number_of_entries = count ($rows);

  if ($debug) echo "\nnumber of entries = $number_of_entries<BR>\n";
  flush();

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
  $buffer     = ""; // clear buffer

  // loop through all entries
  while ($cur_entry < $number_of_entries) {
    $form_item = $form_entry [$cur_entry]; // import current entry item

    if ($debug) echo "\n$cur_entry out of $number_of_entries <BR>\n";
    flush();

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
    $cur_col = $form_item->col + $form_item->len;
    if ($debug) echo "\nRendering entry <BR>\n";
    flush();
    $buffer .= render_fixedFormEntry ($form_item);
    if ($debug) echo "\nRendering finished <BR>\n";
    flush();
    $cur_entry++; // increment the counter!
  } // while there are more entries, loop

  // add trailing CR to buffer
  $buffer .= "\n";
  $cur_row ++;

  // make sure page is at proper length
  if ($cur_row < $pagelength)
   for ($i=0;$i<($pagelength - $cur_row);$i++)
     $buffer .= "\n";

  // send the buffer back to the calling routine
  return $buffer."\n";
} // end function render_fixedForm

} // end checking for __RENDER_FORMS_PHP__

?>
