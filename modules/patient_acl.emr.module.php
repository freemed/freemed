<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class PatientACL extends EMRModule {

	var $MODULE_NAME = "Patient ACL";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function PatientACL () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor

	// The EMR box; probably the most important part of this module
	function summary ($patient, $dummy_items) {
		// Get patient object from global scope (if it exists)
		if (isset($GLOBALS['this_patient'])) {
			global $this_patient;
		} else {
			$this_patient = CreateObject('FreeMED.Patient', $patient);
		}

		// Check to see if we *can* generate a statement for this
		// patient
		$acls = $this->get_acl_for_patient($patient);
		if (count($acls) < 1) {
			$buffer .= "
			<div align=\"center\">
			".__("There are no ACL rules for this patient.")."
			</div>
			";
			return $buffer;
		}

		$buffer .= "
		<table WIDTH=\"100%\" cellspacing=\"0\" cellpadding=\"2\"
		 border=\"0\">
		<tr>
			<td><b>ARO</b></td>
			<td><b>ACO</b></td>
			<td><b>".__("Action")."</b></td>
		</tr>
		";

		// Generate actual ACL display for modification, et cetera
		foreach ($acls AS $this_acl) {
			$buffer .= "
			<tr>
				<td>".join(', ', $this->get_object_for_acl ($this_acl, 'aro'))."</td>
				<td>".join(', ', $this->get_object_for_acl ($this_acl, 'aco'))."</td>
				<td>".
				template::summary_delete_link($this,
				"module_loader.php?module=".
				get_class($this)."&patient=$patient&".
				"action=del&id=".urlencode($this_acl).
				"&return=manage")."
				</td>
			</tr>
			";
		}
		$buffer .= "</table>\n";
		
		return $buffer;
	} // end method summary

	function addform ( ) {
		global $display_buffer;

		$display_buffer .= "<form>\n";
		$display_buffer .= "
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\" />
		<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"".prepare($_REQUEST['manage'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"add\" />
		";
		$display_buffer .= html_form::form_table(array(
			__("Users") =>
			$this->_multiple_select('aro', $this->get_aro()),

			__("Properties") =>
			$this->_multiple_select('aco', $this->get_aco())
		));
		$display_buffer .= "
		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Add")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		";
		$display_buffer .= "</form>\n";
	} // end method addform

	function add ( ) {
		global $display_buffer;
	
		// Lint checking
		if (count($_REQUEST['aco']) < 1 or count($_REQUEST['aro']) < 1) {
			trigger_error(__("At least one user and property must be selected."), E_USER_ERROR);
		}
		if ($_REQUEST['patient'] < 1) {
			trigger_error(__("A valid patient must be selected."), E_USER_ERROR);
		}
		$display_buffer .= __("Adding")." ... ";
		$result = $this->add_acl(
			$_REQUEST['patient'], 
			$_REQUEST['aco'], 
			$_REQUEST['aro']
		);
		if (!$result) {
			$display_buffer .= __("failed");
		} else {
			$display_buffer .= __("done");
		}

		if ($_REQUEST['return'] == 'manage') {
			$GLOBALS['refresh'] = 'manage.php?id='.$_REQUEST['patient'];
		}
	} // end method add

	function del ( ) {
		global $display_buffer;
		$display_buffer .= __("Deleting") . " ... ";
		$result = $this->delete_acl ( $_REQUEST['patient'], $_REQUEST['id'] );
		if ($result) {
			$display_buffer .= __("done");
		} else {
			$display_buffer .= __("failed");
		}

		if ($_REQUEST['return'] == 'manage') {
			$GLOBALS['refresh'] = 'manage.php?id='.$_REQUEST['patient'];
		}

		return false;
	} // end method del

	function view ( ) {
	} // end method view

	// ----- HELPER FUNCTIONS -------------------------------------------

	// Method: add_acl
	//
	//	Adds an ACL for a patient AXO. Currently is not "smart"
	//	in that it does not search for existing ACLs with the same
	//	objects to add users to, but rather adds another ACL to
	//	the list. Probably needs to be fixed in the long run?
	//
	// Parameters:
	//
	//	$pid - Patient record id
	//
	//	$aco - Array of ACO object ids (add, view, modify, etc)
	//
	//	$aro - Array of ARO object ids (user_1, user_2, etc)
	//
	//	$obj - (optional) ACL object
	//
	// Returns:
	//
	//	Boolean, success
	//
	function add_acl ( $pid, $aco, $aro, $obj=NULL ) {
		// Get cached gacl_api object from ACL module
		if (!is_object($obj)) {
			$acl = module_function('ACL', '_acl_object');
		} else {
			$acl = &$obj;
		}

		return $acl->add_acl(
			array ('emr' => $aco ),
			array ('individual' => $aro ),
			NULL, // aro groups
			array ('patient' => array ( 'patient_'.$pid ) ),
			NULL, // axo groups
			1, // allow
			1, // enable
			NULL, // return value
			NULL, // note
			'user' // section_value
		);
	} // end method add_acl

	// Method: delete_acl
	//
	//	Removes specified patient ACL. Checks to see whether the ACL
	//	belongs to the patient before removing.
	//
	// Parameters:
	//
	//	$pid - Patient record id
	//
	//	$acl_id - ACL record id
	//
	// Returns:
	//
	//	Boolean, success.
	//
	function delete_acl ( $pid, $acl_id ) {
		// Make sure that we are deleting the correct thing, otherwise
		// this is a HUGE security hole...
		$valid_acl = $this->get_acl_for_patient($pid);
		if (!is_array($valid_acl)) { return false; }

		$found = false;
		foreach ($valid_acl AS $this_acl) {
			if ($this_acl == $acl_id) { $found = true; }
		}
		// Send out error if attack is attempted
		if (!$found) {
			trigger_error(__("An attempt was made to remove an ACL which does not belong to this patient!"), E_USER_ERROR);
		}

		// Actual deletion routine --------------------------------

		// Get cached gacl_api object from ACL module
		$acl = module_function('ACL', '_acl_object');
		return $acl->del_acl($acl_id);
	} // end method delete_acl

	// Method: get_acl_for_patient
	//
	//	Get ACLs for patient AXO object
	//
	// Parameters:
	//
	//	$pid - Patient record id
	//
	// Returns:
	//
	//	Array of ACL ids, or empty array if none are found.
	//
	function get_acl_for_patient ( $pid ) {
		$query = "
			SELECT
			DISTINCT a.id
			FROM            acl_acl a
			LEFT JOIN       acl_aco_map ac ON ac.acl_id=a.id
			LEFT JOIN       acl_aro_map ar ON ar.acl_id=a.id
			LEFT JOIN       acl_axo_map ax ON ax.acl_id=a.id
		";

		// AXO item
		$query .= "
			LEFT JOIN       acl_axo x ON (x.section_value=ax.section_value AND x.value=ax.value)
		";
		$where[] = " (lower(x.value) LIKE 'patient_".addslashes($pid)."') ";
		$query .= " WHERE ".join(' AND ', $where);
		
		$res = $GLOBALS['sql']->query($query);
		$return = array();
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$return[] = $r['id'];
		}
		return $return;
	} // end method get_acl_for_patient

	// Method: get_aco
	//
	//	Retrieve list of valid ACO object as an associative array,
	//	meant to be used in a selection widget.
	//
	// Returns:
	//
	//	Associative array with keys representing the names of the
	//	ACOs in question and the values containing their IDs.
	//
	function get_aco ( ) {
		$query = "SELECT * FROM acl_aco WHERE section_value='emr' ".
			"ORDER BY order_value";
		$res = $GLOBALS['sql']->query($query);
		$return = array ( );
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$return[stripslashes($r['name'])] = $r['value'];
		}
		return $return;
	} // end method get_aco

	// Method: get_aro
	//
	//	Retrieve list of valid ARO object as an associative array,
	//	meant to be used in a selection widget.
	//
	// Returns:
	//
	//	Associative array with keys representing the names of the
	//	AROs in question and the values containing their IDs.
	//
	function get_aro ( ) {
		$query = "SELECT * FROM acl_aro WHERE section_value='individual' ".
			"ORDER BY order_value";
		$res = $GLOBALS['sql']->query($query);
		$return = array ( );
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			$return[stripslashes($r['name'])] = $r['value'];
		}
		return $return;
	} // end method get_aro

	// Method: get_object_for_acl
	//
	//	Get ACL objects (ACO, ARO) which pertain to specified ACL
	//
	// Parameters:
	//
	//	$acl_id - Record ID of the ACL in question
	//
	//	$type - aco or aro
	//
	function get_object_for_acl ( $acl_id, $type ) {
		if ($type != 'aro' and $type != 'aco') {
			trigger_error(__("Invalid object type specified"), E_USER_ERROR);
		}
		$query = "
			SELECT  a.acl_id,o.name,s.name
			FROM    acl_".$type."_map a
			INNER JOIN      acl_".$type." o ON (o.section_value=a.section_value AND o.value=a.value)
			INNER JOIN      acl_".$type."_sections s ON s.value=a.section_value
			WHERE   a.acl_id IN (".$acl_id.")
		";
		$res = $GLOBALS['sql']->query($query);
		$return = array();
		while ($r = $GLOBALS['sql']->fetch_array($res)) {
			//$return[] = $r[2] . " / " . $r[1];
			$return[] = $r[1];
		}
		return $return;
	} // end method get_object_for_acl

	// Method: get_axo_for_patient
	//
	//	Resolve the AXO id for a particular patient record ID
	//
	// Parameters:
	//
	//	$pid - Patient record ID
	//
	// Returns:
	//
	//	ACL AXO record id
	//
	function get_axo_for_patient ( $pid ) {
		$query = "SELECT * FROM acl_axo ".
			"WHERE value='patient_".addslashes($pid)."'";
		$res = $GLOBALS['sql']->query($query);
		$r = $GLOBALS['sql']->fetch_array($res);
		return $r['id'];
	} // end method get_axo_for_patient

	// Method: get_object_section
	//
	//	Get the section ID for an AXO, ARO, ACO, et cetera
	//
	// Parameters:
	//
	//	$type - axo, aro, aco
	//
	//	$value - Section value in question
	//
	// Returns:
	//
	//	ACL id for section
	//
	function get_object_section ( $type, $value ) {
		$query = "SELECT * FROM acl_".$type."_sections ".
			"WHERE value='".addslashes($value)."'";
		$res = $GLOBALS['sql']->query($query);
		$r = $GLOBALS['sql']->fetch_array($res);
		return $r['id'];
	} // end method get_object_section

	function _multiple_select ( $name, $values ) {
		$buffer .= "<select name=\"".prepare($name)."[]\" ".
			"size=\"6\" multiple=\"multiple\">\n";
		foreach ($values AS $k => $v) {
			$buffer .= html_form::select_option (
				$name,
				$v,
				$k
			);
		}
		$buffer .= "</select>\n";
		return $buffer;
	} // end method _multiple_choice

} // end class PatientACL

register_module ("PatientACL");

?>
