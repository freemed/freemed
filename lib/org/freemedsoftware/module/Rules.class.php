<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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
	var $variables = array (
		  'rule_descrip'
		, 'rule_prio'
		, 'rule_type'
		, 'rule_clause_if_facility_eq'
		, 'rule_clause_if_facility'
		, 'rule_clause_if_cpt_eq'
		, 'rule_clause_if_cpt'
		, 'rule_clause_if_cptmod_eq'
		, 'rule_clause_if_cptmod'
		, 'rule_clause_then_charges'
		, 'rule_clause_then_tos'
	);

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

			$check = array (
				  'procpos' => 'facility'
				, 'proccpt' => 'cpt'
				, 'proccptmod' => 'cptmod'
				, 'proccptmod2' => 'cptmod'
				, 'proccptmod3' => 'cptmod'
			);
			$found = array ( );

			foreach ($check AS $k => $c) {
				// For each variable clause, determine if we are "matching" criteria
				$values = explode( ",", $rule['rule_clause_if_'.$c] );
				switch ($rule['rule_clause_if_' . $c . '_eq']) {
					case 'EQ':
					if (in_array($data[$k], $values)) {
						$if_clause_met = true;
						$found[$c] = true;
					} else {
						$if_clause_met = false;
					}
					break;	

					case 'NE':
					if (in_array($data[$k], $values)) {
						$if_clause_met = false;
					} else {
						$if_clause_met = true;
						$found[$c] = true;
					}
					break;

					default:
					break;
				} // end switch equivalence

				// Override to stop duplicate field checks from bombing out
				if ($found[$c]) { $if_clause_met = true; }
			}

			// If the "IF" clause is met, execute then
			if ($if_clause_met) {
				if ($r['rule_clause_then_charges'] != '') {
					$d['proccharges'] = $r['rule_clause_then_charges'];
				}
				if ($r['rule_clause_then_tos'] > 0) {
					$d['proctos'] = $r['rule_clause_then_tos'];
				}
			} // end if if_clause_met
		} // end rules result loop

		// Return interpreted data array
		return $d;
	} // end method interpreter

} // end class Rules

register_module ("Rules");

?>
