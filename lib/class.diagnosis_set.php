<?php
 // $Id$
 // $Author$
 // lic : GPL, v2

class diagnosis_set {
	var $internal_stack;      // internal "stack" of codes
	var $stack_size;          // number currently in queue
	var $maximum_stack_size;  // maximum number in stack

	// constructor diagnosis_set
	function diagnosis_set ($max_size = 4) {
		$this->maximum_stack_size = $max_size;    // set maximum size
		$this->stack_size         = 0;            // empty the stack
	} // end constructor diagnosis_set

	// function diagnosis_set->getStack
	// -- returns the actual codes in an array
	function getStack ($null_value = "") {
		for ($i=1;$i<=($this->stack_size);$i++) 
			$figured_stack[$i] = ereg_replace ("[^A-Z0-9.]", "",
				freemed::get_link_field (
					$this->internal_stack[$i],
					"icd9",
					"icd9code"
				)
			);
		return $figured_stack;
	} // end function getStack

	// function diagnosis_set->inStack
	// -- returns true or false, indicating whether value is in the stack
	function inStack ($val) {
		$found = false;
		for ($i=1;$i<=($this->stack_size);$i++) {
			if ($this->internal_stack[$i] == $val) $found = $i;
		} // end for (loop through stack
		return $found;
	} // end function diagnosis_set->inStack

	// function diagnosis_set->numberUnique
	// -- sees how many unique values in an array are *NOT* in the stack
	function numberUnique ($newvals) {
		global $display_buffer;
		global $debug;

		// determine size of new array
		$size_of_new = count ($newvals);

		if ($debug) $display_buffer .= "numberUnique->sizeOfNew = ".
			"$size_of_new <BR>\n";

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
	} // end function diagnosis_set->numberUnique

	// function diagnosis_set->popValue
	// -- add value to the stack
	function popValue ($val) {
		global $display_buffer;
		global $debug;
		if (!($this->inStack ($val)) and ($val != 0)) {
			// increment the stack size
			$this->stack_size++;
			$this->internal_stack[$this->stack_size] = $val;
		} // end checking for valid value not in stack
		if ($debug) $display_buffer .= "stack size = ".
			"$this->stack_size ($val) <BR>\n";
	} // end function diagnosis_set->popValue

	// function diagnosis_set->testAddSet
	// -- tests to see whether another "set" can be added to the stack
	//    and add it to the stack if there is room
	function testAddSet ($diag1=0, $diag2=0, $diag3=0, $diag4=0) {
		global $display_buffer;
		global $debug;
		unset ($diag);
		// first determine how many parameters
		$flag = 0;
		if ($diag1 > 0) { $flag++; $diag[$flag] = $diag1; }
		if ($diag2 > 0) { $flag++; $diag[$flag] = $diag2; }
		if ($diag3 > 0) { $flag++; $diag[$flag] = $diag3; }
		if ($diag4 > 0) { $flag++; $diag[$flag] = $diag4; }

		if ($debug) $display_buffer .= "\n$flag ".
			"diagnoses in this charge<BR>\n";
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
	} // end function diagnosis_set->testAddSet

	// function diagnosis_set->xrefList
	// -- returns comma delimited list of referenced diag codes
	function xrefList ($diag1=0,$diag2=0,$diag3=0,$diag4=0) {
		global $display_buffer;
		global $debug;

		// first determine how many parameters
		$diag[1] = $diag1; $diag[2] = $diag2;
		$diag[3] = $diag3; $diag[4] = $diag4;
		$flag = 0;       // by default, none
		for ($i=1;$i<=4;$i++) {  // loop through diagnoses
			if (($diag[$i] != 0) and ($flag == ($i - 1)))
				$flag = $i;
		} // end of looping through diagnoses
		$num_found = 0; $found_array = "";
		for ($i=1;$i<=$flag;$i++) {
			if ($pos = $this->inStack ($diag[$i])) {
				// increment counter
				$num_found++;
				// add reference
				$found_array[$num_found] = $pos;
			} // end if found in stack
		} // end looping through the stack
		if ($debug) $display_buffer .= "\narray size = ".
			count($found_array)." <BR>\n";
		if ($num_found > 1) sort ($found_array);
		if ($num_found < 1) return "";
		if ($num_found == 1) return $found_array[1];
		// Join with commas and return    
		return join ($found_array, ",");
	} // end function diagnosis_set->xrefList

} // end class diagnosis_set

?>
