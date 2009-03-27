<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class InsuranceAndFinancialBox extends EMRModule {

	var $MODULE_NAME = "Insurance and Financial Box";
	var $MODULE_VERSION = "0.1";

	var $MODULE_FILE = __FILE__;

	function InsuranceAndFinancialBox () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor InsuranceAndFinancialBox

	function summary ($patient, $items) {
		global $sql, $display_buffer, $patient;

		// get last $items results
		$query = "SELECT *,DATE_FORMAT(letterdt, '%m/%d/%Y') AS my_date ".
			"FROM insfinancial ".
			"WHERE letterpatient='".addslashes($patient)."' ".
			"ORDER BY letterdt DESC, id DESC";
		$result = $sql->query($query);
		while ($r = $sql->fetch_array($result)) {
			$key = $r['letterdt'] . '-' . $r['id'] . '-1';
			$r['module'] = 'insuranceandfinancial';
			$r['type'] = $r['lettersubject'];
			$map[$key] = $r;
		}
		$query = "SELECT *,DATE_FORMAT(imagedt, '%m/%d/%Y') AS my_date ".
			"FROM images ".
			"WHERE imagepat='".addslashes($patient)."' ".
			"AND imagetype='financial' ".
			"ORDER BY imagedt DESC, id DESC";
		$result = $sql->query($query);
		while ($r = $sql->fetch_array($result)) {
			$key = $r['imagedt'] . '-' . $r['id'] . '-2';
			//print_r($key); print "<br/>\n";
			$r['module'] = 'scanneddocuments';
			$r['type'] = $r['imagedesc'];
			$map[$key] = $r;
		}

		// Sort everything properly
		krsort($map);

		$overflow = false;
		$count = 1;
		foreach ($map AS $k => $v) {
			if ($count > $items) { $overflow = true; }
			$count++;
		}

		// Check to see if there *are* any...
		if (count($map) < 1) {
			// If not, let the world know
			$buffer .= "<b>".__("No data entered.")."</b>\n";
		} else { // checking for results
			// Or loop and display
			if ($overflow) { $buffer .= "<div style=\"overflow: auto; height: 200px;\">\n"; }
			$buffer .= "
			<table WIDTH=\"100%\" CELLSPACING=\"0\"
			 CELLPADDING=\"2\" BORDER=\"0\">
			<TR>
			";

			$buffer .= "
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Date")."</b>
				</td>
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Description")."</b>
				</td>
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Action")."</b>
				</td>
			</tr>
			";
			foreach ($map AS $r) {
				// Pull out all variables
				extract ($r);

				switch ($r['module']) {
					case 'scanneddocuments':
					$fields = array (
						'my_date'
					);
					break;

					case 'insuranceandfinancial':
					$fields = array (
						'my_date'
					);
					break;
				}

				// Check for annotations
				if ($_anno = module_function('Annotations', 'getAnnotations', array ($r['module'], $id))) {
					$use_anno = true;
					$_anno = module_function('Annotations', 'outputAnnotations', array ($_anno));
				} else {
					$use_anno = false;
				}

				// Use $this->summary_vars
				$buffer .= "
				<tr VALIGN=\"MIDDLE\">
					<td VALIGN=\"MIDDLE\">
					<small>".
					( $use_anno ?
						"<span style=\"text-decoration: underline;\" ".
						"onMouseOver=\"tooltip('".module_function('Annotations', 'prepareAnnotation', array($_anno))."');\" ".
						"onMouseOut=\"hidetooltip();\">" : "" ).
					prepare($r[$fields[0]]).
					( $use_anno ? "</span>" : "" ).
					"</small>
					</td>
					<td VALIGN=\"MIDDLE\">
					<small>
					".prepare($r['type'])."
					</small>
					</td>
				";
				$first = false;
				$buffer .= "
				<td VALIGN=\"MIDDLE\">".
				( ((!$r['locked'] > 0) or freemed::lock_override()) ?
				"\n".template::summary_modify_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=modform&id=".$r['id']."&return=manage") : "" ).
				// Delete option
				( (((!$r['locked'] > 0) or freemed::lock_override())) ?
				"\n".template::summary_delete_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=del&id=".$r['id']."&return=manage") : "" ).
				"\n".template::summary_view_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=display&id=".$r['id']."&return=manage").

				// "Lock" link for quick locking from the menu
				
				( (($r['module'] == 'insuranceandfinancial') and
				!($r['locked'] > 0)) ?
				"\n".template::summary_lock_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=lock&id=".$r['id']."&return=manage") : "" ).

				// Process a "locked" link, which does nothing other
				// than display that the record is locked
				
				( (($r['module'] == 'insuranceandfinancial') and
				($r['locked'] > 0)) ?
				"\n".template::summary_locked_link($this) : "" ).

				// Printing stuff
				"\n".template::summary_print_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=print&id=".$r['id']).

				// Annotations
				( !($this->summary_options & SUMMARY_NOANNOTATE) ?
				"\n".template::summary_annotate_link($this,
				"module_loader.php?module=annotations&".
				"atable=".$this->table_name."&".
				"amodule=".urlencode($r['module'])."&".
				"patient=$patient&action=addform&".
				"aid=".$r['id']."&return=manage") : "" ).
				// Additional summary icon callback
				( $r['module'] == 'insuranceandfinancial' ?
				module_function('insuranceandfinancial', 'additional_summary_icons', array ( $patient, $r['id'] )) : '' ).
				"</td>
				</tr>
				";
			} // end of loop and display
			$buffer .= "</table>\n";
			if ($overflow) { $buffer .= "</div>\n"; }
		} // checking if there are any results

		// Send back the buffer
		return $buffer;
	} // end function summary

	function addform() { $this->_redirect(); }
	function modform() { $this->_redirect(); }
	function add() { $this->_redirect(); }
	function mod() { $this->_redirect(); }
	function view() {
		Header('Location: module_loader.php?module=insuranceandfinancial&'.
			'id='.urlencode($_REQUEST['id']).'&'.
			'action='.urlencode($_REQUEST['action']).'&'.
			'patient='.urlencode($_REQUEST['patient']).'&'.
			'return='.urlencode($_REQUEST['return'])
		);
		die();
	}

	function _redirect ($force = NULL) {
		module_function('InsuranceAndFinancial', $force ? $force : $_REQUEST['action']);
	}

} // end module InsuranceAndFinancialBox

register_module('InsuranceAndFinancialBox');

?>
