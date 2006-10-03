<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.BaseModule');

// Class: org.freemedsoftware.core.EMRModule
//
//	Electronic Medical Record module superclass. It is descended from
//	<BaseModule>.
//
class EMRModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Electronic Medical Record";
	var $CATEGORY_VERSION = "0.4";

	// Variable: $this->widget_hash
	//
	//	Specifies the format of the <widget> method. This is
	//	formatted by having SQL field names surrounded by '##'s.
	//
	// Example:
	//
	//	$this->widget_hash = '##phylname##, ##phyfname##';
	//	
	var $widget_hash;

	// vars to be passed from child modules
	var $order_fields;
	var $form_vars;
	var $table_name;

	// Variable: disable_patient_box
	//
	//	Whether or not to disable the patient box display at
	//	the top of the screen. Defaults to false.
	//
	var $disable_patient_box = false;

	// Variable: date_field
	//
	//	Field name that contains the date pertaining to the
	//	EMR record fragment in question. This is used by
	//	certain routines in FreeMED to guess as to the most
	//	accurate piece of information for a particular
	//	query.
	//
	// Example:
	//
	//	$this->date_field = 'rxdtfrom';
	//
	var $date_field;

	// Variable: patient_field
	//
	//	Field name that describes the patient. This is used by
	//	FreeMED's module handler to determine whether a record
	//	should be displayed in the EMR summary screen.
	//
	// Example:
	//
	//	$this->patient_field = 'eocpatient';
	//
	var $patient_field; // the field that links to the patient ID

	// Variable: display_format
	//
	//	Hash describing the format which is used to display the
	//	current record by default. It needs to be overridden by
	//	child classes. It uses '##' seperated values to signify
	//	variables.
	//
	// Example:
	//
	//	$this->display_format = '##phylname##, ##phyfname##';
	//
	var $display_format;

	// Variable: summary_conditional
	//
	//	An SQL logical phrase which is used to pare down the
	//	results from a summary view query.
	//
	// Example:
	//
	//	$this->summary_conditional = "ptsex = 'm'";
	var $summary_conditional = '';

	// Variable: summary_query_link
	//
	//	Allows another table (or more) to be "linked" via a
	//	WHERE clause.
	//
	// Example:
	//
	//	$this->summary_query_link = array ('payrecproc', 'procrec');
	//
	// See Also:
	//	<summary>
	//
	var $summary_query_link = false;

	// Variable: summary_order_by
	//
	//	The order in which the EMR summary items are displayed.
	//	This is passed verbatim to an SQL "ORDER BY" clause, so
	//	DESC can be used. Defaults to 'id' if nothing else is
	//	specified.
	//
	// Example:
	//
	//	$this->summary_order_by = 'eocdtlastsimilar, eocstate DESC';
	//
	// See Also:
	//	<summary>
	//
	var $summary_order_by = 'id';

	// Variable: $this->rpc_field_map
	//
	//	Specifies the format of the XML-RPC structures returned by
	//	the FreeMED.DynamicModule.picklist method. These are
	//	passed as key => value, where key is the target name of the
	//	structure item and value is the name of the SQL field. "id"
	//	is passed as "id" by default, as <$this->patient_field> is
	//	passed as "patient". If this is not defined,
	//	FreeMED.DynamicModule.picklist will fail for the target
	//	module.
	//
	// Example:
	//
	//	$this->rpc_field_map = array ( 'last_name' => 'ptlname' );
	//
	var $rpc_field_map;

	// contructor method
	function EMRModule () {
		// Check for patient, if so, then set _ref appropriately
		if ($GLOBALS['patient'] > 0) {
			$GLOBALS['_ref'] = "manage.php?id=".urlencode($GLOBALS['patient']);
		}

		// Add meta information for patient_field, if it exists
		if (isset($this->record_name)) {
			$this->_SetMetaInformation('record_name', $this->record_name);
		}
		if (isset($this->date_field)) {
			$this->_SetMetaInformation('date_field', $this->date_field);
		}
		if (isset($this->patient_field)) {
			$this->_SetMetaInformation('patient_field', $this->patient_field);
		}
		if (isset($this->table_name)) {
			$this->_SetMetaInformation('table_name', $this->table_name);
		}
		if (!empty($this->widget_hash)) {
			$this->_SetMetaInformation('widget_hash', $this->widget_hash);
		}
		if (!empty($this->rpc_field_map)) {
			$this->_SetMetaInformation('rpc_field_map', $this->rpc_field_map);
		}

		// Call parent constructor
		parent::__construct();
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

	// Method: locked
	//
	// 	Determines if record id is locked or not.
	//
	// Parameters:
	//
	//	$id - Record id to be checked
	//
	//	$quiet - (optional) Boolean. If set to true, this value
	//	will cause a denial screen to be displayed. Defaults to
	//	false.
	//
	// Returns:
	//
	//	Boolean, whether the record is locked or not.
	//
	// Example:
	//
	//	if ($this->locked($id)) return false;
	//
	function locked ($id, $quiet = false) {
		global $display_buffer;
		static $locked;

		// If there is no table_name, we can skip this altogether
		if (empty($this->table_name)) { return false; }

		if (!isset($locked)) {
			$query = "SELECT COUNT(*) AS lock_count ".
				"FROM ".$this->table_name." WHERE ".
				"id='".addslashes($id)."' AND (locked > 0)";
			$result = $GLOBALS['sql']->queryOne( $query );
			$locked = ($result['lock_count'] > 0);
		}

		if ($locked) {
			return true;
		} else {
			return false;
		}
	} // end function locked

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $patient, $__submit, $return;

		if ($action=='print') {
			$this->disable_patient_box = true;
		}

		// Pull current patient from session if needed
		if (!isset($patient)) {
			$patient = $_SESSION['current_patient'];
		}

		if (!isset($this->this_patient))
			$this->this_patient = CreateObject('org.freemedsoftware.core.Patient', $patient);
		if (!isset($this->this_user))
			$this->this_user    = CreateObject('org.freemedsoftware.core.User');

		// display universal patient box
		if (!$this->disable_patient_box) {
			$display_buffer .= freemed::patient_box($this->this_patient)."<p/>\n";
		}

		// Kludge for older "submit" actions
		if (!isset($__submit)) { $__submit = $GLOBALS['submit']; }

		// Handle cancel action from __submit
		if ($__submit==__("Cancel")) {
			// Unlock record, if it is locked
			$__lock = CreateObject('org.freemedsoftware.core.RecordLock', $this->table_name);
			$__lock->UnlockRow ( $_REQUEST['id'] );

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
				if (!$this->acl_access('add', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->add();
				break;

			case "addform":
				if (!$this->acl_access('add', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->addform();
				break;

			case "del":
			case "delete":
				if (!$this->acl_access('delete', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->in_use($_REQUEST['id']);
				$this->del();
				break;

			case "lock":
				if (!$this->acl_access('lock', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->lock();
				break;

			case "mod":
			case "modify":
				if (!$this->acl_access('modify', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->in_use($_REQUEST['id']);
				$this->mod();
				break;

			case "modform":
				if (!$this->acl_access('modify', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->in_use($_REQUEST['id']);
				$this->modform();
				break;

			case "print":
				$this->printaction();
				break;

			case "display";
				if (!$this->acl_access('view', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->display();
				break;

			case "view":
			default:
				if (!$this->acl_access('view', $patient)) {
					trigger_error(__("You do not have access to do that."), E_USER_ERROR);
				}
				$this->view();
				break;
		} // end switch action
	} // end function main

	// Method: additional_move
	//
	//	Stub function. Define additional EMR movement functionality
	//	per module. Note that this function does *not* perform the
	//	actual move, but instead moves support files, et cetera.
	//
	// Parameters:
	//
	//	$id - Id of the record in question
	//
	//	$from - Original patient
	//
	//	$to - Destination patient
	//
	function additional_move ($id, $from, $to) { }

	// Method: acl_access
	//
	//	Should be overridden by any module which needs different
	//	access checks.
	//
	function acl_access ( $type, $patient ) {
		if (!is_array($this->acl)) {
			return freemed::acl_patient('emr', $type, $patient);
		} else {
			$this_user = CreateObject('org.freemedsoftware.core.User');
			$xs = explode(',', $this_user->local_record['userlevel']);
			foreach ($xs AS $x) {
				// Admin has universal access
				if ($x == 'admin') { return true; }
				foreach ($this->acl AS $acl) {
					if ($acl == $x) { return true; }
				}
			}
		}
		return false;
	} // end method acl_access
	
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

	// Method: form_table
	//
	//	Builds the table used by the add/mod form methods, and
	//	returns it as an associative array which is passed to
	//	<html_form::form_table>. By default this returns NULL
	//	and needs to be overridden by child classes. It is only
	//	used if the default <form> method is used.
	//
	// Returns:
	//
	//	Associative array describing form.
	//
	// See Also:
	//	<form>
	//
	function form_table () {
		return NULL;
	} // end function form_table

	function in_use ( $id ) {
		// Check for record locking
		$lock = CreateObject('org.freemedsoftware.core.RecordLock', $this->table_name);
		if ($lock->IsLocked( $id )) {
			trigger_error(__("This record is currently in use."), E_USER_ERROR);
		} else {
			// Add record lock
			$lock->LockRow( $id );
		}
	} // end method in_use

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function add
	// - addition routine
	function add () { $this->_add(); }
	function _add ($_param = NULL) {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global ${$k};

		if (is_array($_param)) {
			foreach ($_param AS $k => $v) {
				global ${$k}; ${$k} = $v;
				$_REQUEST[$k] = $v;
			}
		}

		$this->_preadd($_param);
		$this->{get_class($this)}();

		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->insert_query (
				$this->table_name,
				$this->variables
			)
		);
		$new_id = $GLOBALS['sql']->last_record($result, $this->table_name);

		if ($result) {
			$this->message = __("Record added successfully.");
			if (is_array($_param)) { return $new_id; }
		} else {
			$this->message = __("Record addition failed.");
			if (is_array($_param)) { return false; }
		}
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
			Header('Location: '.$refresh); die();
		}

		// If called without 'return', send back new id
		return $new_id;
	} // end function _add
	function _preadd ( $param = NULL ) { }

	// function del
	// - delete function
	function del () { $this->_del(); }
	function _del ($_id = -1) {
		global $display_buffer;
		global $id, $sql;

		// Pull from parameter, if given
		if ($_id > 0) { $id = $_id; }

		// Check for record locking

		// If there is an override ...
		if (!freemed::lock_override()) {
			if ($this->locked($id)) return false;
		}

		$query = "DELETE FROM ".$this->table_name." ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) {
			$this->message = __("Record deleted successfully.");
			if ($_id > 0) { return true; }
		} else {
			$this->message = __("Record deletion failed.");
			if ($_id > 0) { return false; }
		}
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
			Header('Location: '.$refresh); die();
		}
	} // end function _del

	// function mod
	// - modification function
	function mod () { $this->_mod(); }
	function _mod ($_param = NULL) {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;

		if (is_array($_param)) {
			foreach ($_param AS $k => $v) {
				global ${$k};
				${$k} = $v;
			}
		}

		// Check for record locking
		if (!freemed::lock_override()) {
			if ($this->locked($id)) return false;
		}
		$__lock = CreateObject('org.freemedsoftware.core.RecordLock', $this->table_name);
		if ($__lock->IsLocked($id)) {
			$this->message = __("Record modification failed due to record lock.");
			if (is_array($_param)) { return false; }
			$this->view(); $this->display_message();
		}

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id" => $id
				)
			)
		);

		// Unlock row, since update is done
		$__lock->UnlockRow( $id );

		if ($result) {
			$this->message = __("Record modified successfully.");
			if (is_array($_param)) { return true; }
		} else {
			$this->message = __("Record modification failed.");
			if (is_array($_param)) { return false; }
		}
		$this->view(); $this->display_message();

		// Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
			Header('Location: '.$refresh);
			die();
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
		global $display_buffer, $module, $action, $id, $sql, $patient;

		if (is_array($this->form_vars)) {
			reset ($this->form_vars);
			while (list ($k, $v) = each ($this->form_vars)) global ${$v};
		} // end if is array

		// Check for record locking
		if ($this->locked($id)) return false;

		// Handle additional hidden variables
		$this->form_hidden[] = $this->patient_field;
		if ($this->patient_field != 'id') {
			$_REQUEST[$this->patient_field] = $_REQUEST['patient'];
		}
		foreach ($this->form_hidden AS $k => $v) {
			if ( (($k+0)>0) or empty($k)) {
				$k = $v;
				$v = $_REQUEST[$v];
			}
			// TODO: should handle arrays, etc
			$form_hidden .= "<input type=\"hidden\" ".
				"name=\"".prepare($k)."\" ".
				"value=\"".prepare($v)."\" />\n";
		}

		switch ($action) {
			case "addform":
				break;

			case "modform":
				if ($this->table_name) {
					$r = freemed::get_link_rec($id, $this->table_name);
					foreach ($r as $k => $v) {
						global ${$k};
						${$k} = $v;
					}
				} // end checking for table name
				break;
		} // end of switch action
		
		$display_buffer .= "
		<div align=\"center\">
		<form action=\"".$this->page_name."\" method=\"post\">
		<input type=\"hidden\" name=\"module\" value=\"".
			prepare($module)."\"/>
		<input type=\"hidden\" name=\"return\" value=\"".
			prepare($GLOBALS['return'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"".
			( $action=="addform" ? "add" : "mod" )."\"/>
		<input type=\"hidden\" name=\"patient\" value=\"".
			prepare($patient)."\"/>
		<input type=\"hidden\" name=\"id\" value=\"".
			prepare($id)."\"/>
		".$form_hidden."
		".html_form::form_table($this->form_table())."
		<p/>
		<input type=\"submit\" name=\"__submit\" value=\"".
			 ( ($action=="addform") ? __("Add") : __("Modify") )."\" ".
			 "class=\"button\" />
		<input type=\"submit\" name=\"__submit\" value=\"".
			__("Cancel")."\" class=\"button\" />
		</form>
		</div>
		";
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
		if ($result) $this->message = __("Record locked successfully.");
			else $this->message = __("Record locking failed.");
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

		if (is_array($this->summary_query_link)) {
			foreach ($this->summary_query_link AS $my_k => $my_v) {
				// Format: field => table_name
				$_from[] = "LEFT OUTER JOIN ${my_v} ON ${my_v}.id = ".$this->table_name.'.'.$my_k;
			}
			$this->summary_query[] = $this->table_name.'.id AS __actual_id';
		}

		// get last $items results
		$query = "SELECT *".
			( (count($this->summary_query)>0) ? 
			",".join(",", $this->summary_query)." " : " " ).
			"FROM ".$this->table_name." ".
			( is_array($this->summary_query_link) ? " ".join(',',$_from).' ' : ' ' ).
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			($this->summary_conditional ? 'AND '.$this->summary_conditional.' ' : '' ).
			"ORDER BY ".( (is_array($this->summary_query_link) and $this->summary_order_by == 'id') ? $this->table_name.'.' : '' ).$this->summary_order_by." DESC LIMIT ".addslashes($items);
		//if ($this->summary_query_link) { die ($query); }
		$result = $sql->query($query);

		// Check to see if there *are* any...
		if ($sql->num_rows($result) < 1) {
			// If not, let the world know
			$buffer .= "<b>".__("No data entered.")."</b>\n";
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
				<b>".__("Action")."</b>
				</td>
			</tr>
			";
			while ($r = $sql->fetch_array($result)) {
				// Deal with record.id for query_link
				if (is_array($this->summary_query_link)) {
					$r['id'] = $r['__actual_id'];
				}

				// Pull out all variables
				extract ($r);

				// Check for annotations
				if ($_anno = module_function('Annotations', 'getAnnotations', array (get_class($this), $id))) {
					$use_anno = true;
					$_anno = module_function('Annotations', 'outputAnnotations', array ($_anno));
				} else {
					$use_anno = false;
				}

				// Use $this->summary_vars
				$buffer .= "
				<tr VALIGN=\"MIDDLE\">
				";
				$first = true;
				foreach ($this->summary_vars AS $k => $v) {
					if (!(strpos($v, ":")===false)) {
						// Split it up
						list ($p1, $p2, $p3) = explode(":", $v);
					
						switch ($p2) {
						case "phy":
						case "physician":
						$p = CreateObject('org.freemedsoftware.core.Physician', ${$p1});
						${$v} = $p->fullName();
						break;

						case "user":
						$__u = CreateObject('org.freemedsoftware.core.User', ${$p1});
						${$v} = $__u->getDescription();
						break;

						default:
						// use fields ...
						${$v} = freemed::get_link_field(${$p1}, $p2, $p3);
						break;
						}
					}
					$buffer .= "
					<td VALIGN=\"MIDDLE\">
					<small>".
					( ($use_anno and $first) ?
						"<span style=\"text-decoration: underline;\" ".
						"onMouseOver=\"tooltip('".module_function('Annotations', 'prepareAnnotation', array($_anno))."');\" ".
						"onMouseOut=\"hidetooltip();\">" : "" ).
					prepare(${$v}).
					( ($use_anno and $first) ? "</span>" : "" ).
					"</small>
					</td>
					";
					$first = false;
				} // end looping through summary vars
				$buffer .= "
				<td VALIGN=\"MIDDLE\">".
				( ((!$r['locked'] > 0) or freemed::lock_override()) ?
				"\n".template::summary_modify_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=modform&id=".$r['id']."&return=manage") : "" ).
				// Delete option
				( (((!$r['locked'] > 0) or freemed::lock_override()) and ($this->summary_options & SUMMARY_DELETE)) ?
				"\n".template::summary_delete_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=del&id=".$r['id']."&return=manage") : "" ).
				( ($this->summary_options & SUMMARY_VIEW) ?
				"\n".template::summary_view_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=display&id=".$r['id']."&return=manage",
				($this->summary_options & SUMMARY_VIEW_NEWWINDOW)) : "" ).

				// "Lock" link for quick locking from the menu
				
				( (($this->summary_options & SUMMARY_LOCK) and
				!($r['locked'] > 0)) ?
				"\n".template::summary_lock_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
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
				get_class($this)."&patient=$patient&".
				"action=print&id=".$r['id']) : "" ).

				// Annotations
				( !($this->summary_options & SUMMARY_NOANNOTATE) ?
				"\n".template::summary_annotate_link($this,
				"module_loader.php?module=annotations&".
				"atable=".$this->table_name."&".
				"amodule=".get_class($this)."&".
				"patient=$patient&action=addform&".
				"aid=".$r['id']."&return=manage") : "" ).
				// Additional summary icon callback
				$this->additional_summary_icons ( $patient, $r['id'] ).
				"</td>
				</tr>
				";
			} // end of loop and display
			$buffer .= "</table>\n";
		} // checking if there are any results

		// Send back the buffer
		return $buffer;
	} // end function summary

	// Method: additional_summary_icons
	//
	//	Callback to allow additional summary icons to be added.
	//	Each icon should be prefixed with a linefeed (\n)
	//	character to be equally spaced in the output. By default
	//	this is just a stub.
	//
	// Parameters:
	//
	//	$patient - Patient record id
	//
	//	$id - Record id
	//
	// Returns:
	//
	//	HTML code for additional icons
	//
	function additional_summary_icons ( $patient, $id ) { return ''; }

	// Method: summary_bar
	//
	//	Produces the text for the EMR summary bar menu. By
	//	default it produces View/Manage and Add links. Override
	//	this function to change the basic EMR summary bar menu.
	//
	// Parameters:
	//
	//	$patient - Id of current patient
	//
	// Returns:
	//
	//	XHTML formatted text links.
	//
	function summary_bar ($patient) {
		return "
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&return=manage\">".__("View/Manage")."</a> |
		<a HREF=\"module_loader.php?module=".
		get_class($this)."&patient=".urlencode($patient).
		"&action=addform&return=manage\">".__("Add")."</a>
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
			array ("", __("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

	// override _setup with create_table
	function _setup () {
		if (!$this->create_table()) return false;
		return freemed_import_stock_data ($this->table_name);
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
			$this->this_patient = CreateObject('org.freemedsoftware.core.Patient', $patient);

		return $this->xml_generate($this->this_patient);
	} // end function EMRModule->xml_export

	function xml_generate ($patient) { return ""; } // stub 

	// ----- XML-RPC Functions -----------------------------------------

	// Method: picklist
	//
	// Parameters:
	//
	//	$criteria - Hash of criteria information. If this is to
	//	be patient specific, 'patient' must be passed with the
	//	patient id number.
	//
	// Returns:
	//
	//	Array of hashes
	//
	// See Also:
	//	<$this->rpc_field_map>
	//
	function picklist ($criteria) {
		global $sql;

		if (is_array($criteria) and $criteria['patient']>0) {
			$patient = $criteria['patient'];
		}

		if (!is_array($criteria)) {
			$c[] = $this->patient_field." = '".addslashes($criteria)."'";
			// Check for access violation from user
			$user_id = $GLOBALS['__freemed']['basic_auth_id'];
	
			if (!freemed::check_for_access($patient, $user_id)) {
				// TODO: Set to return XML-RPC error
				return false;
			}
		} else {
			foreach ($criteria AS $k => $v) {
				if ($k == 'patient') {
					$c[] = $this->patient_field." = '".addslashes($v)."'";
				} else {
					$c[] = "LOWER(".addslashes($k).") LIKE '%'".addslashes($v)."%'";
				}
			}
		}

		$result = $sql->query(
			"SELECT * FROM ".$this->table_name." ".
			"WHERE ".join(' AND ', $c)." ".
			( $this->order_fields ? "ORDER BY ".$this->order_fields : "" )
		);
		
		if (!$GLOBALS['sql']->results($result)) {
			return CreateObject('PHP.xmlrpcresp',
				CreateObject('PHP.xmlrpcval', 'none', 'string')
			);
		}

		return rpc_generate_sql_hash (
			$this->table_name,
			array_merge (
				$this->rpc_field_map,
				array (
					'id' => 'id',
					'patient' => $this->patient_field
				)
			),
			" WHERE ".join(' AND ', $c)." ".
			( $this->order_fields ? "ORDER BY ".$this->order_fields : "" )
		);
	} // end method picklist

	// Method: widget
	//
	//	Generic widget code to allow a picklist-based widget for
	//	simple modules. Should be overridden for more complex tasks.
	//
	//	This function uses $this->widget_hash, which contains field
	//	names surrounded by '##'s.
	//
	// Parameters:
	//
	//	$varname - Name of the variable that the widget's data is
	//	passed in.
	//
	//	$patient - Id of patient this deals with.
	//
	//	$conditions - (optional) Additional clauses for SQL WHERE.
	//	defaults to none.
	//
	// Returns:
	//
	//	XHTML-compliant picklist widget.
	//
	function widget ( $varname, $patient, $conditions = false ) {
		$query = "SELECT * FROM ".$this->table_name." WHERE ".
			"( ".$this->patient_field.
				" = '".addslashes($patient)."') ".
			( $conditions ? " AND ( ".$conditions." ) " : "" ).
			( $this->order_fields ? "ORDER BY ".$this->order_fields : "" );
		$result = $GLOBALS['sql']->query($query);
		$return[__("NONE SELECTED")] = "";
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			if (!(strpos($this->widget_hash, "##") === false)) {
				$key = '';
				$hash_split = explode('##', $this->widget_hash);
				foreach ($hash_split AS $_k => $_v) {
					if (!($_k & 1)) {
						$key .= prepare($_v);
					} else {
						$key .= prepare($r[$_v]);
					}
				}
			} else {
				$key = $this->widget_hash;
			}
			$return[$key] = $r['id'];
		}
		return html_form::select_widget($varname, $return);
	} // end method widget

	// Method: _recent_record
	//
	//	Return most recent record, possibly qualified by a
	//	particular date.
	//
	// Parameters:
	//
	//	$patient - Id of patient record
	//
	//	$recent_date - (optional) Date to qualify this by
	//
	// Returns:
	//
	//	Associative array (hash) of record
	//
	function _recent_record ( $patient, $recent_date = NULL ) {
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field." = '".addslashes($patient)."' ".
			( $recent_date ? " AND ".$this->date_field." <= '".addslashes($recent_date)."' " : "" ).
			"ORDER BY ".$this->date_field." DESC, id DESC";
		$res = $GLOBALS['sql']->query($query);
		return $GLOBALS['sql']->fetch_array($res);
	} // end method _recent_record

	//------ Internal Printing ----------------------------------------

	// Method: _RenderToPDF
	//
	//	Render internal record for printing directly to a PDF file.
	//
	// Parameters:
	//
	//	$record - Record id
	//
	// Returns:
	//
	//	File name of destination PDF file
	//
	function _RenderToPDF ( $record ) {
		// Handle render
		if ($render = $this->print_override($record)) {
			// Handle this elsewhere
		} else {
			// Create TeX object for patient
			$TeX = CreateObject('_FreeMED.TeX', array());

			// Actual renderer for formatting array
			if ($this->patient_field) {
				// If this is an EMR module with additional
				// fields, import them
				$query = "SELECT *".
					( (count($this->summary_query)>0) ? 
					",".join(",", $this->summary_query)." " : " " ).
					"FROM ".$this->table_name." ".
					"WHERE id='".addslashes($record)."'";
					$result = $GLOBALS['sql']->query($query);
					$rec = $GLOBALS['sql']->fetch_array($result);
			} else {
				$rec = freemed::get_link_rec($record, $t);
			} // end checking for summary_query
			$TeX->_buffer = $TeX->RenderFromTemplate(
				$this->print_template,
				$rec
			);
			$render = $TeX->RenderToPDF(!(empty($this->print_template)));
		} // end render if/else

		// Return the file name
		return $render;
	} // end method _RenderToPDF

	// Method: _RenderTeX
	//
	//	Internal TeX renderer for the record. By default this
	//	uses the <print_format> class variable to determine the
	//	proper format. If another format is to be used, override
	//	this class.
	//
	// Parameters:
	//
	//	$TeX - <FreeMED.TeX> object reference. Must be prefixed by
	//	an amphersand, otherwise changes will be lost!
	//
	//	$id - Record id to be printed
	//
	// Example:
	//
	//	$this->_RenderTeX ( &$TeX, $id );
	//
	// See Also:
	//	<_RenderField>
	//
	function _RenderTeX ( $TeX, $id ) {
		if (is_array($id)) {
			foreach ($id AS $k => $v) {
				$buffer .= _RenderTeX ( &$TeX, $v );
			}
			return $buffer;
		} else {
			if (!$id) return false;
			die('here');

			// Determine template
			if ($_REQUEST['print_template']) {
				$my_template = $_REQUEST['print_template'];
			} else {
				$my_template = $this->print_template;
			}

			// Handle templating elsewhere
			return $TeX->RenderFromTemplate(
				$my_template,
				$this->_print_mapping($TeX, $id)
			);
		}
	} // end method _RenderTeX

	// Method: _RenderField
	//
	//	Render out ##a:b@c## or ##a## type fields. "a" stands for
	//	the record, "b" stands for the target field, and "c" stands
	//	for the target table.
	//
	// Parameters:
	//
	//	$arg - Formatted field
	//
	//	$r - Associative array containing record
	//
	// Returns:
	//
	//	Rendered field
	//
	// Example:
	//
	//	$return = $this->_RenderField ( '##eocpatient:ptlname@patient##' );
	//
	function _RenderField ( $arg, $r = NULL ) {
		if (!(strpos($arg, '##') === false)) {
			// We need to deal with the content
			$displayed = '';
			$f_split = explode ('##', $arg);
			foreach ($f_split AS $f_k => $f_v) {
				if (!($f_k & 1)) {
					// Outside of '##'s
					$displayed .= $f_v;
				} else {
					// Inside ... process
					if (!(strpos($f_v, ':') === false)) {
						// Process as ##a:b@c##
						list ($a, $_b) = explode (':', $f_v);
						list ($b, $c) = explode ('@', $_b);
						$f_q = $GLOBALS['sql']->query(
							'SELECT '.$c.'.'.$b.' AS result '.
							'FROM '.$c.', '.$this->table_name.' '.
							'WHERE '.$this->table_name.'.'.$a.' = '.$c.'.id AND '.
							$this->table_name.'.id = '.$_REQUEST['id']
						);
						$f_r = $GLOBALS['sql']->fetch_array($f_q);
						$displayed .= $f_r['result'];
					} else {
						// Simple field replacement
						$displayed .= $r[$f_v];
					}
				}
			}
			return $displayed;
		} else {
			// No processing required. Return as is.
			return $arg;
		}
	} // end method _RenderField

	// Method: _TeX_Information
	//
	//	Callback to provide information to the TeX renderer about
	//	formatting.
	//
	// Returns:
	//
	//	Array ( title, heading, physician )
	//
	function _TeX_Information ( ) {
		// abstract
		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		$patient = CreateObject('FreeMED.Patient', $_REQUEST['patient']);
		$user = CreateObject('FreeMED.User');
		if ($user->isPhysician()) {
			$phy = $user->getPhysician();
		} else {
			$phy = $patient->local_record['patphy'];
		}
		$physician_object = CreateObject('FreeMED.Physician', $phy);
		$title = __($this->record_name);
		$heading = $patient->fullName().' ('.$patient->local_record['ptid'].')';
		$physician = $physician_object->fullName();
		return array ($title, $heading, $physician);
		return array ($title, $heading, $physician);
	} // end method _TeX_Information

} // end class EMRModule

?>
