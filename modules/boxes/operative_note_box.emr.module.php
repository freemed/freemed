<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class OperativeNoteBox extends EMRModule {

	var $MODULE_NAME = "Operative Report Box";
	var $MODULE_VERSION = "0.1";

	var $MODULE_FILE = __FILE__;

	function OperativeNoteBox () {
		$this->summary_options |= SUMMARY_VIEW | SUMMARY_DELETE | SUMMARY_PRINT;
	
		// Call parent constructor
		$this->EMRModule();
	} // end constructor OperativeNoteBox

	function summary ($patient, $items) {
		global $sql, $display_buffer, $patient;

		// get last $items results
		$query = "SELECT *,DATE_FORMAT(opnotedt, '%m/%d/%Y') AS my_date ".
			"FROM opnote ".
			"WHERE opnotepat='".addslashes($patient)."' ".
			"ORDER BY opnotedt DESC, id DESC";
		$result = $sql->query($query);
		while ($r = $sql->fetch_array($result)) {
			$key = $r['opnotedt'] . '-' . $r['id'] . '-1';
			$r['module'] = 'opnotemodule';
			$r['type'] = __("Written");
			$map[$key] = $r;
		}
		$query = "SELECT *,DATE_FORMAT(imagedt, '%m/%d/%Y') AS my_date, ".
			"CASE imagereviewed WHEN 0 THEN 'no' ELSE 'yes' END AS reviewed ".
			"FROM images ".
			"WHERE imagepat='".addslashes($patient)."' ".
			"AND imagetype='op_report' ".
			"ORDER BY imagedt DESC, id DESC";
		$result = $sql->query($query);
		while ($r = $sql->fetch_array($result)) {
			$key = $r['imagedt'] . '-' . $r['id'] . '-2';
			$r['module'] = 'scanneddocuments';
			$r['type'] = __("Scanned");
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
				<b>".__("Provider")."</b>
				</td>
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Desc")."</b>
				</td>
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".__("Reviewed")."</b>
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
						'my_date',
						'imagephy',
						'imagedesc',
						'reviewed'
					);
					break;

					case 'opnotemodule':
					$fields = array (
						'my_date',
						'opnotedoc',
						'opnotedescrip',
						'XYZHZHZHZ' // crap here
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
				$phy = CreateObject('_FreeMED.Physician', $r[$fields[1]]);
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
					".prepare($phy->to_text())."
					</small>
					</td>
					<td VALIGN=\"MIDDLE\">
					<small>
					".prepare($r[$fields[2]])."
					</small>
					</td>
					<td VALIGN=\"MIDDLE\">
					<small>
					".prepare($r[$fields[3]])."
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
				( (((!$r['locked'] > 0) or freemed::lock_override()) and ($this->summary_options & SUMMARY_DELETE)) ?
				"\n".template::summary_delete_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=del&id=".$r['id']."&return=manage") : "" ).
				( ($this->summary_options & SUMMARY_VIEW) ?
				"\n".template::summary_view_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=display&id=".$r['id']."&return=manage",
				($this->summary_options & SUMMARY_VIEW_NEWWINDOW)) : "" ).

				// "Lock" link for quick locking from the menu
				
				( (($this->summary_options & SUMMARY_LOCK) and
				!($r['locked'] > 0)) ?
				"\n".template::summary_lock_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=lock&id=".$r['id']."&return=manage") : "" ).

				// Process a "locked" link, which does nothing other
				// than display that the record is locked
				
				( (($this->summary_options & SUMMARY_LOCK) and
				($r['locked'] > 0)) ?
				"\n".template::summary_locked_link($this) : "" ).

				// Printing stuff
				( ($this->summary_options & SUMMARY_PRINT) ?
				"\n".template::summary_print_link($this,
				"module_loader.php?module=".
				$r['module']."&patient=$patient&".
				"action=print&id=".$r['id']) : "" ).

				// Annotations
				( !($this->summary_options & SUMMARY_NOANNOTATE) ?
				"\n".template::summary_annotate_link($this,
				"module_loader.php?module=annotations&".
				"atable=".$this->table_name."&".
				"amodule=".urlencode($r['module'])."&".
				"patient=$patient&action=addform&".
				"aid=".$r['id']."&return=manage") : "" ).
				// Additional summary icon callback
				$this->additional_summary_icons ( $patient, $r['id'] ).
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
		Header('Location: module_loader.php?module=opnotemodule&'.
			'id='.urlencode($_REQUEST['id']).'&'.
			'action='.urlencode($_REQUEST['action']).'&'.
			'patient='.urlencode($_REQUEST['patient']).'&'.
			'return='.urlencode($_REQUEST['return'])
		);
		die();
	}

	function _redirect () {
		module_function('OpnoteModule', $_REQUEST['action']);
	}

	// function view
	// - view stub
	/*
	function view () {
		global $display_buffer;
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		$display_buffer .= freemed_display_itemlist (
			$result,
			"module_loader.php",
			$this->form_vars,
			array ("", __("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view
	*/

} // end module operativenotebox

register_module('OperativeNoteBox');

?>
