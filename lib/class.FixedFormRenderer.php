<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.FormRenderer');

class FixedFormRenderer extends FormRenderer {

	function FixedFormRenderer ( ) {
		// Load form definitions
		$this->DefineForm();

		// Call parent constructor
		$this->FormRenderer();
	} // end constructor FixedFormRenderer

	function DefineForm ( ) {
		die("You must define a form to use this class. (".__FILE__." / ".__LINE__.")");
	} // end method DefineForm

	function RenderToBuffer ( ) {
		global $display_buffer, $debug;
		if ($debug) { print "<b>RenderToBuffer</b> called<br/>\n"; }

		// For now, no line offsets
		$this->line_offset = 0;

		// Split form into arrays
		unset ($rows);
		unset ($cols);
		unset ($lens);
		unset ($datas);
		unset ($formats);
		unset ($comments);
		$number_of_entries = 0;
		foreach ($this->form as $k => $v) {
			list (
				$rows[$number_of_entries],
				$cols[$number_of_entries],
				$lens[$number_of_entries],
				$datas[$number_of_entries],
				$formats[$number_of_entries],
				$comments[$number_of_entries]
			) = $v;
			$number_of_entries++;
		}

		if ($debug) print "number of entries = $number_of_entries\n";

		// Import entries into form_entry array
		for ($i=0; $i<$number_of_entries; $i++) {
			if ($rows[$i] == '') continue;
			$form_entry [$i] = CreateObject (
				'FreeMED.FixedFormEntry',
				$rows[$i],
				$cols[$i],
				$lens[$i],
				$datas[$i],
				$formats[$i],
				$comments[$i]
			);
		}

		// Reset all form rendering information
		$cur_row = 1; $cur_col = 1;
		$cur_entry = 0;
		$buffer = '';

		$number_of_entries = count($form_entry);

		while ($cur_entry < $number_of_entries) {
			// Import current entry item
			$form_item = $form_entry[$cur_entry];

			// Move to proper row if not there
			if (($form_item->row + $this->line_offset) > $cur_row) {
				// Determine number of linefeeds
				$num_crs = (($form_item->row + $this->line_offset) - $cur_row);

				// Add appropriate CRs
				for ($lc=0; $lc<$num_crs; $lc++) $buffer .= "\n";

				// Reset to the beginning of the next row
				$cur_col = 1;
			} // end checking row

			// Move to necessary column if not there
			if ($form_item->col > $cur_col) {
				$num_spc = (($form_item->col) - $cur_col);
				for ($lc=0; $lc<$num_spc; $lc++) $buffer .= " ";
			} // end moving to proper column

			// Write the rendered item to the buffer
			$cur_row = $form_item->row + $this->line_offset;
			$cur_col = $form_item->col + $form_item->len;
			$_entry = $this->_RenderEntry($form_item);
			$buffer .= $_entry;
			if ($debug) print "<b>buffer :</b>\n".$buffer."<hr/>\n";

			// Move to next entry
			$cur_entry++;
		} // end rendering loop

		// Add a trailing CR for end of last line
		$buffer .= "\n";
		$cur_row++;

		// Pad page to proper length
		if ($cur_row < $this->page_length) {
			for ($i=0; $i<($this->page_length - $cur_row); $i++) {
				$buffer .= "\n";
			}
		}

		// Send the buffer back to the calling routine
		$buffer .= "\n";
		return $buffer;
	} // end method RenderToBuffer

	//----- Internal Functions

	function _DateInRange ($checkdate, $dtbegin, $dtend) {
		// split all dates into component parts
		$begin_y = substr ($dtbegin,   0, 4);
		$begin_m = substr ($dtbegin,   5, 2);
		$begin_d = substr ($dtbegin,   8, 2);
		$end_y   = substr ($dtend,     0, 4);
		$end_m   = substr ($dtend,     5, 2);
		$end_d   = substr ($dtend,     8, 2);
		$cur_y   = substr ($checkdate, 0, 4);
		$cur_m   = substr ($checkdate, 5, 2);
		$cur_d   = substr ($checkdate, 8, 2);

		$end = $end_y;
		$end .= $end_m;
		$end .= $end_d;
		$start = $begin_y;
		$start .= $begin_m;
		$start .= $begin_d;
		$current = $cur_y;
		$current .= $cur_m;
		$current .= $cur_d;

		if ( ($current >= $begin) AND ($current <= $end) ) {
			return true;
		} else {
			return false;
		}
	} // end method _DateInRange

	function _GetCoverages ( $patient = 0 ) {
		if ($patient < 1) {
			trigger_error ("_GetCoverages() should not be called without a patient.");
		}
		$query = "SELECT DISTINCT proccurcovid FROM procrec WHERE ".
			"(procbalcurrent > '0' AND ".
			"procpatient = ".addslashes($patient)." AND ".
			"procbillable = '0' AND procbilled = '0' ) ".
			"ORDER BY procpos,procphysician,procrefdoc";
		$result = $GLOBALS['sql']->query($query);
		if (!$GLOBALS['sql']->results($result)) {
			return 0;
		} else {
			$covs = array ();
			while ($r = $GLOBALS['sql']->fetch_array($result)) {
				$covs[] = $r['proccurcovid'];
			}
			return $covs;
		}
	} // end method _GetCoverages

	function _GetPatientsToInsuranceBill ( $req = -1 ) {
		$query = "SELECT DISTINCT procpatient FROM procrec WHERE ".
			( ($req != -1) ? 
			"proccurcovtp='".addslashes($req)."' AND " : "" ).
			"procbalcurrent > 0";
		$result = $GLOBALS['sql']->query($query);

		// Send back a false if there are no patients to bill
		if (!$GLOBALS['sql']->results($result)) { return false; }

		// Aggregate into an array
		$pats = array ();
		while ($row = $GLOBALS['sql']->fetch_array($result)) {
			$pats[] = $row['procpatient'];
		}
		return $pats;
	} // end method _GetPatientsToInsuranceBill

	function _RenderEntry ( $entry ) {
		global $display_buffer, $debug;

		// Handle zero length entries
		if ($entry->len < 1) return '';

		// Debug
		if ($debug) $display_buffer .= "\norig = $entry->data<br/>\n";

		// Speed hack: only "evaluate" if it contains a "$"
		$this_evalled = ( (strpos($entry->data, '$') !== false) ?
			fm_eval ($entry->data) :
			$entry->data );

		// Debug
		if ($debug) print "old = $entry->data, new = $this_evalled\n";

		if (strlen($this_evalled) > $entry->len) {
			// Remove extra characters
			return substr($this_evalled, 0, $entry->len);
		} elseif (strlen($this_evalled) < $entry->len) {
			// Add spaces, depending on padding
			if ($entry->format == 'N') {
				// Fill zeroes to left
				return str_pad (
					$this_evalled,
					$entry->len,
					'0',
					STR_PAD_LEFT
				);
			} else {
				// Pad spaces to the right
				return str_pad (
					$this_evalled,
					$entry->len
				);
			}
		} else {
			// No change necessary
			return $this_evalled;
		}
	} // end method _RenderEntry

} // end class FixedFormRenderer

?>
