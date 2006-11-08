<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class Rules extends SupportModule {

	var $MODULE_NAME    = "Rules";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Rules";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "ba28dc75-a4eb-41b4-9f11-b36b5d7e4a6a";

	var $PACKAGE_MINIMUM_VERSION = '0.8.1';

	var $record_name    = "Rules";
	var $table_name     = "rules";
	var $widget_hash    = "##rule_descrip## [##rule_prio##]";
	var $order_fields   = "rule_type, rule_prio, rule_descrip";

	public function __construct ( ) {
		// __("Rules")

		$this->list_view = array (
			__("Description") => 'rule_descrip',
			__("Priority") => 'rule_prio',
			__("Type") => 'rule_type'
		);

		// Run parent constructor
		parent::__construct ( );
	} // end constructor Rules

	// Method: interpreter
	//
	//	Rule interpreter, taking array of data and returning array of result information.
	//
	// Parameters:
	//
	//	$type - Type of rule
	//
	//	$data - Associative array of input data.
	//
	// Results:
	//
	//	Associative array of result information based on rules
	//
	function interpreter ( $type, $data ) {
		// Get all rules in the system which match this
		$rules_query = "SELECT * FROM ".$this->table_name." WHERE rule_type='".addslashes($type)."' ORDER BY rule_prio";
		$rules_result = $GLOBALS['sql']->queryAll( $rules_query );

		// If there are no rules in the system, return uninterpolated data
		if ( ! count ( $rules_result ) ) {
			return $data;
		}

		// Pull a "local scope" copy so we can modify
		$d = $data;

		// Create index of what we have touched yet, so rules only affect something *once*
		foreach ($d AS $k => $v) { $touched[$k] = 0; }

		// Loop through all rules
		foreach ( $rules_result AS $rule ) {
			// Unfold if and then clauses
			$if = unserialize( $rule['rule_clause_if'] );
			$then = unserialize( $rule['rule_clause_then'] );

			// Determine if "if clause" is met (assume so at outset, then disprove)
			$if_clause_met = true;
			foreach ($if AS $r) {
				// For each variable clause, determine if we are "matching" criteria
				switch ($r['equivalence']) {
					case '=':
					if ($d[$r['field']] != $r['value']) { $if_clause_met = false; }
					break;	

					case '!=':
					if ($d[$r['field']] == $r['value']) { $if_clause_met = false; }
					break;

					default:
					trigger_error(__("Rule set encountered unknown equivalence operator."), E_USER_ERROR);
					break;
				} // end switch equivalence
			}

			// If the "IF" clause is met, execute then
			if ($if_clause_met) {
				foreach ($then AS $r) {
					// Switch by assignment, only touch if not touched
					switch ($r['assignment']) {
						case '=':
						if (!$touched[$r['field']]) {
							$d[$r['field']] = $r['value'];
							$touched[$r['field']] = 1;
						}
						break;

						case '*=':
						if (!$touched[$r['field']]) {
							$d[$r['field']] *= $r['value'];
							$touched[$r['field']] = 1;
						}
						break;
					} // end switch assignment
				} // end each then element
			} // end if if_clause_met
		} // end rules result loop

		// Return interpreted data array
		return $d;
	} // end method interpreter

	protected function add_pre ( ) {
		$data['type'] = ereg_replace('[^A-Za-z0-9_ ]', '', $data['type']);
		
		// Get the interface for the if and then clauses
		$ifclause = module_function($data['type'], freemed::module_get_meta($data['type'], 'RuleInterface'), array('if'));
		$thenclause = module_function($data['type'], freemed::module_get_meta($data['type'], 'RuleInterface'), array('then'));

		// Determine what we're doing
		foreach ($ifclause AS $if) {
			// Determine if it is active
			//print "looping for $if[0] <br/>\n";
			if ($data['check_if_'.$if[0]] == 1) {
				//print "REQUEST if[0] = ".$_REQUEST[$if[0]]."<br/>\n";
				$clause['if'][] = array (
					'field' => $if[0],
					'equivalence' => $data['equivalence_if_'.$if[0]],
					'value' => $data[$if[0]]
				);
			}
		}

		if (!is_array($clause['if'])) {
			trigger_error(__("No IF clause was defined."), E_USER_ERROR);
		}
		foreach ($thenclause AS $then) {
			// Determine if it is active
			if ($data['check_then_'.$then[0]] == 1) {
				$clause['then'][] = array (
					'field' => $then[0],
					'assignment' => $data['assign_then_'.$then[0]],
					'value' => $data[$then[0]]
				);
			}
		}
		if (!is_array($clause['then'])) {
			trigger_error(__("No THEN clause was defined."), E_USER_ERROR);
		}

		$data['rule_created'] = SQL__NOW;
		$data['rule_clause_if'] = serialize($clause['if']);
		$data['rule_clause_then'] = serialize($clause['then']);
	}

	/*
	// FIXME FIXME FIXME
	function mod ( ) {
		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		
		$template = CreateObject('_FreeMED.FormTemplate', $rec['fr_template']);
		$information = $template->GetInformation();
		$controls = $template->GetControls();

		// Only update timestamp on master record
		$fr_query = $GLOBALS['sql']->update_query(
			$this->table_name,
			array (
				'fr_timestamp' => SQL__NOW,
			),
			array ( 'id' => $_REQUEST['id'] )
		);
		$fr_result = $GLOBALS['sql']->query ( $fr_query );

		// Get id for association
		$fid = $_REQUEST['id'];

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Call the appropriate method
				$value = call_user_func(
					array(&$this, 'control_'.$v['type']),
					'serialize',
					$v
				);

				// Build UPDATE query
				$query = "UPDATE form_record SET fr_value = '".addslashes($value)."' WHERE fr_id='".addslashes($fid)."' AND fr_uuid='".$v['uuid']."'";
				$result = $GLOBALS['sql']->query ( $query );
			}
		} // end foreach controls

		// Return to where we came from:
		if ($_REQUEST['return'] == 'manage') {
			$GLOBALS['refresh'] = "manage.php?id=".urlencode($_REQUEST['patient']);
		} else {
			$GLOBALS['refresh'] = "module_loader.php?module=".urlencode(get_class($this));
		}
	} // end method mod
	*/

	protected function del_pre ( $id ) {
		// Delete all attached pieces
		$q = "DELETE FROM form_record WHERE fr_id = '".addslashes($id)."'";
		$GLOBALS['sql']->query($q);
	}

} // end class Rules

register_module ("Rules");

?>
