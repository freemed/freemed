<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

LoadObjectDependency('FreeMED.BaseModule');

class EMRModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Electronic Medical Record";
	var $CATEGORY_VERSION = "0.2";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;
	var $patient_field; // the field that links to the patient ID

	// contructor method
	function EMRModule () {
		// Check for patient, if so, then set _ref appropriately
		if ($GLOBALS['patient'] > 0) {
			$GLOBALS['_ref'] = "manage.php?id=".urlencode($GLOBALS['patient']);
		}
	
		// Call parent constructor
		$this->BaseModule();
	} // end function EMRModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module, $patient;
		if (!isset($module)) {
			trigger_error("No Module Defined", E_ERROR);
		}
		if ($patient < 1) {
			trigger_error( "No Patient Defined", E_ERROR);
		}
		// check access to patient
		if (!freemed::check_access_for_patient($patient)) {
			trigger_error("User not Authorized for this function", E_USER_ERROR);
		}
		return true;

	} // end function check_vars

	// function locked
	// - determines if record id is locked or not, and reacts accordingly
	//   (use like :  if ($this->locked($id)) return false;         )
	function locked ($id, $quiet = false) {
		global $sql, $display_buffer;
		static $locked;

		if (!isset($locked)) {
			$query = "SELECT * FROM ".$this->table_name." WHERE ".
				"id='".addslashes($id)."' AND (locked > 0)";
			$result = $sql->query($query);
			$locked = $sql->results($result);
		}

		if ($locked) {
			if (!$quiet) 
			$display_buffer .= "
			<div ALIGN=\"CENTER\">

			</div>

			<p/>

			<div ALIGN=\"CENTER\">
			".(
				($GLOBALS['return'] == "manage") ?
				"<a href=\"manage.php?id=".urlencode($GLOBALS['patient']).
					"\">"._("Manage Patient")."</a>" :
				"<a href=\"module_loader.php?module=".
					get_class($this)."\">"._("back")."</a>"
			)."
			</div>
			";
			return true;
		} else {
			return false;
		}
	} // end function locked

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $patient, $submit, $return;

		if (!isset($this->this_patient))
			$this->this_patient = CreateObject('FreeMED.Patient', $patient);
		if (!isset($this->this_user))
			$this->this_user    = CreateObject('FreeMED.User');

		// display universal patient box
		if (!$this->disable_patient_box) {
		$display_buffer .= freemed::patient_box($this->this_patient)."<p/>\n";
		}

		// Handle cancel action from submit
		if ($submit==_("Cancel")) {
			if ($return=="manage") {
			Header("Location: manage.php?".
				"id=".urlencode($patient));
			} else {
			Header("Location: ".$this->page_name.
				"?module=".urlencode($this->MODULE_CLASS).
				"&patient=".urlencode($patient));
			}
			die("");
		}

		switch ($action) {
			case "add":
				$this->add();
				break;

			case "addform":
				$this->addform();
				break;

			case "del":
			case "delete":
				$this->del();
				break;

			case "lock":
				$this->lock();
				break;

			case "mod":
			case "modify":
				$this->mod();
				break;

			case "modform":
				$this->modform();
				break;

			case "display";
				$this->display();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main
	
	function display_message () {
		global $display_buffer;
		if (isset($this->message)) {
			$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\">
			<b>".prepare($this->message)."</b>
			</div>
			";
		}
	} // end function display_message

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function add
	// - addition routine
	function add () { $this->_add(); }
	function _add () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global ${$k};

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) $this->message = _("Record added successfully.");
		 else $this->message = _("Record addition failed.");
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
	} // end function _add

	// function del
	// - delete function
	function del () { $this->_del(); }
	function _del () {
		global $display_buffer;
		global $id, $sql;

		// Check for record locking
		if ($this->locked($id)) return false;

		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) $this->message = _("Record deleted successfully.");
		 else $this->message = _("Record deletion failed.");
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
	} // end function _del

	// function mod
	// - modification function
	function mod () { $this->_mod(); }
	function _mod () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;

		// Check for record locking
		if ($this->locked($id)) return false;

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id" => $id
				)
			)
		);
		if ($result) $this->message = _("Record modified successfully.");
		 else $this->message = _("Record modification failed.");
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
	} // end function _mod

	// function add/modform
	// - wrappers for form
	function addform () { $this->form(); }
	function modform () { $this->form(); }

	// function display
	// by default, a wrapper for view
	function display () { $this->view(); }

	// function form
	// - add/mod form stub
	function form () {
		global $display_buffer;
		global $action, $id, $sql;

		if (is_array($this->form_vars)) {
			reset ($this->form_vars);
			while (list ($k, $v) = each ($this->form_vars)) global ${$v};
		} // end if is array

		switch ($action) {
			case "addform":
				break;

			case "modform":
				$result = $sql->query ("SELECT * FROM ".$this->table_name.
					" WHERE ( id = '".prepare($id)."' )");
				$r = $sql->fetch_array ($result);
				extract ($r);

				// Check for record locking
				if ($this->locked($id)) return false;

				break;
		} // end of switch action
		
	} // end function form

	// function lock
	// - locking function
	function lock () { $this->_lock(); }
	function _lock () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global ${$k};

		// Check for record locking
		if ($this->locked($id)) return false;

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				array (
					"locked" => $_SESSION['authdata']['user']
				),
				array (
					"id" => $id
				)
			)
		);
		if ($result) $this->message = _("Record locked successfully.");
			else $this->message = _("Record locking failed.");
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
		}
	} // end function _mod

	// function summary
	// - show summary view of last few items
	function summary ($patient, $items) {
		global $sql, $display_buffer, $patient;

		// get last $items results
		$query = "SELECT *".
			( (count($this->summary_query)>0) ? 
			",".join(",", $this->summary_query)." " : " " ).
			"FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY id DESC LIMIT ".addslashes($items);
		$result = $sql->query($query);

		// Check to see if there *are* any...
		if ($sql->num_rows($result) < 1) {
			// If not, let the world know
			$buffer .= "<b>"._("NONE")."</b>\n";
		} else { // checking for results
			// Or loop and display
			$buffer .= "
			<table WIDTH=\"100%\" CELLSPACING=\"0\"
			 CELLPADDING=\"2\" BORDER=\"0\">
			<TR>
			";
			foreach ($this->summary_vars AS $k => $v) {
				$buffer .= "
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>".prepare($k)."</b>
				</td>
				";
			} // end foreach summary_vars
			$buffer .= "
				<td VALIGN=\"MIDDLE\" CLASS=\"menubar_info\">
				<b>"._("Action")."</b>
				</td>
			</tr>
			";
			while ($r = $sql->fetch_array($result)) {
				// Pull out all variables
				extract ($r);

				// Use $this->summary_vars
				$buffer .= "
				<tr VALIGN=\"MIDDLE\">
				";
				foreach ($this->summary_vars AS $k => $v) {
					if (!(strpos($v, ":")===false)) {
						// Split it up
						list ($p1, $p2) = explode(":", $v);
					
						switch ($p2) {
						case "phy":
						case "physician":
						$p = CreateObject('FreeMED.Physician', ${$p1});
						${$v} = $p->fullName();
						break;

						default:
							${$v} = $p1;
							break;
						}
					}
					$buffer .= "
					<td VALIGN=\"MIDDLE\">
					<small>".prepare(${$v})."</small>
					</td>
					";
				} // end looping through summary vars
				$buffer .= "
				<td VALIGN=\"MIDDLE\">
				".( (!$r['locked'] > 0) ?
				template::summary_modify_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=modform&id=".$r['id']."&return=manage") : "" ).
				"\n".( ($this->summary_options & SUMMARY_VIEW) ?
				template::summary_view_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=display&id=".$r['id']."&return=manage",
				($this->summary_options & SUMMARY_VIEW_NEWWINDOW)) : "" ).

				// "Lock" link for quick locking from the menu
				
				"\n".( (($this->summary_options & SUMMARY_LOCK) and
				!($r['locked'] > 0)) ?
				template::summary_lock_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=lock&id=".$r['id']."&return=manage") : "" ).

				// Process a "locked" link, which does nothing other
				// than display that the record is locked
				
				"\n".( (($this->summary_options & SUMMARY_LOCK) and
				($r['locked'] > 0)) ?
				template::summary_locked_link($this) : "" )."
				</td>
				</tr>
				";
			} // end of loop and display
			$buffer .= "</table>\n";
		} // checking if there are any results

		// Send back the buffer
		return $buffer;
	} // end function summary

	// function summary_bar
	// - override this to kill the basic bar
	function summary_bar ($patient) {
		return "
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&return=manage\">"._("View/Manage")."</a> |
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&action=addform&return=manage\">"._("Add")."</a>
		";
	} // end function summary_bar

	// function view
	// - view stub
	function view () {
		global $display_buffer;
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		$display_buffer .= freemed_display_itemlist (
			$result,
			"module_loader.php",
			$this->form_vars,
			array ("", _("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

	// override _setup with create_table
	function _setup () {
		if (!$this->create_table()) return false;
		return freemed_import_stock_data ($this->record_name);
	} // end function _setup

	// function create_table
	// - used to initally create SQL table
	function create_table () {
		global $sql;

		if (!isset($this->table_definition)) return false;
		$query = $sql->create_table_query(
			$this->table_name,
			$this->table_definition,
			( is_array($this->table_keys) ?
				array_merge(array("id"), $this->table_keys) :
				array("id")
			)
		);
		$result = $sql->query($query);
		return !empty($query);
	} // end function create_table

	// this function exports XML for the entire patient record
	function xml_export () {
		global $display_buffer;
		global $patient;

		if (!isset($this->this_patient))
			$this->this_patient = CreateObject('FreeMED.Patient', $patient);

		return $this->xml_generate($this->this_patient);
	} // end function EMRModule->xml_export

	function xml_generate ($patient) { return ""; } // stub 

} // end class EMRModule

?>
