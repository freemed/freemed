<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class PatientACL extends EMRModule {

	var $MODULE_NAME = "Patient ACL";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "8cd616c5-241f-4b12-97cf-847c5d5ddb0e";

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	public function __construct ( ) {
		// Call parent constructor
		parent::__construct ( );
	} // end constructor

	// Method: GetList
	//
	// Parameters:
	//
	//	$patient - Patient id.
	//
	// Return:
	//
	//	Array of hashes.
	//
	function GetList ( $patient ) {
		$acls = $this->get_acl_for_patient($patient);
		if (count($acls) < 1) { return array (); }

		// Generate actual ACL display for modification, et cetera
		foreach ($acls AS $this_acl) {
			$r[] = array (
				'aro' => join(', ', $this->get_object_for_acl ($this_acl, 'aro')),
				'aco' => join(', ', $this->get_object_for_acl ($this_acl, 'aco')),
				'id' => $this_acl
			);
		}
		
		return $r;
	} // end method GetList

	/*
		// FIXME: implement add function
		$result = $this->add_acl(
			$_REQUEST['patient'], 
			$_REQUEST['aco'], 
			$_REQUEST['aro']
		);
	*/

	public function del ( $patient, $id ) {
		$result = $this->delete_acl ( $patient + 0, $id + 0 );
		return $result;
	}

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
	protected function add_acl ( $pid, $aco, $aro, $obj=NULL ) {
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
	protected function delete_acl ( $pid, $acl_id ) {
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
	public function get_acl_for_patient ( $pid ) {
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
		
		$res = $GLOBALS['sql']->queryAll($query);
		$return = array();
		foreach ( $res AS $r ) {
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
	public function get_aco ( ) {
		$query = "SELECT * FROM acl_aco WHERE section_value='emr' ".
			"ORDER BY order_value";
		$res = $GLOBALS['sql']->queryAll( $query );
		$return = array ( );
		foreach ( $res AS $r ) {
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
	public function get_aro ( ) {
		$query = "SELECT * FROM acl_aro WHERE section_value='individual' ".
			"ORDER BY order_value";
		$res = $GLOBALS['sql']->queryAll( $query );
		$return = array ( );
		foreach ( $res AS $r ) {
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
	public function get_object_for_acl ( $acl_id, $type ) {
		if ($type != 'aro' and $type != 'aco') {
			trigger_error(__("Invalid object type specified"), E_USER_ERROR);
		}
		$query = "
			SELECT  a.acl_id AS acl_id,o.name AS o_name,s.name AS s_name
			FROM    acl_".$type."_map a
			INNER JOIN      acl_".$type." o ON (o.section_value=a.section_value AND o.value=a.value)
			INNER JOIN      acl_".$type."_sections s ON s.value=a.section_value
			WHERE   a.acl_id IN (".$acl_id.")
		";
		$res = $GLOBALS['sql']->queryAll($query);
		$return = array();
		foreach ( $res AS $r ) {
			//$return[] = $r['s_name'] . " / " . $r['o_name'];
			$return[] = $r['o_name'];
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
	public function get_axo_for_patient ( $pid ) {
		$query = "SELECT id FROM acl_axo ".
			"WHERE value='patient_".addslashes($pid)."'";
		$res = $GLOBALS['sql']->queryOne($query);
		return $res;
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
	public function get_object_section ( $type, $value ) {
		$query = "SELECT id FROM acl_".$type."_sections ".
			"WHERE value='".addslashes($value)."'";
		$res = $GLOBALS['sql']->queryOne($query);
		return $res;
	} // end method get_object_section

} // end class PatientACL

register_module ("PatientACL");

?>
