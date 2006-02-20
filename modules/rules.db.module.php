<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class RulesModule extends MaintenanceModule {

	var $MODULE_NAME    = "Rules";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Rules";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.1';

	var $record_name    = "Rules";
	var $table_name     = "rules";
	var $widget_hash    = "##rule_descrip## [##rule_prio##]";
	var $order_fields   = "rule_type, rule_prio, rule_descrip";

	function RulesModule () {
		// __("Rules")

		// Table definition
		$this->table_definition = array (
			'rule_created' => SQL__TIMESTAMP(16),
			'rule_descrip' => SQL__VARCHAR(150),
			'rule_prio' => SQL__INT_UNSIGNED(0),
			'rule_type' => SQL__VARCHAR(150),
			'rule_clause_if' => SQL__TEXT,
			'rule_clause_then' => SQL__TEXT,
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor RulesModule

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions(true)." ".
				"ORDER BY ".$this->order_fields
			),
			$this->page_name,
			array (
				__("Description") => 'rule_descrip',
				__("Priority") => 'rule_prio',
				__("Type") => 'rule_type'
			),
			array ("")
		);
	} // end method view

	function addform ( ) {
		if (!$_REQUEST['type']) {
			$this->addform_requesttype();
			return false;
		}

		$_REQUEST['type'] = ereg_replace('[^A-Za-z0-9_ ]', '', $_REQUEST['type']);

		// Get the interface for the if and then clauses
		$ifclause = module_function($_REQUEST['type'], freemed::module_get_meta($_REQUEST['type'], 'RuleInterface'), array('if'));
		$thenclause = module_function($_REQUEST['type'], freemed::module_get_meta($_REQUEST['type'], 'RuleInterface'), array('then'));

		foreach ($ifclause AS $item) {
			// Format of each array item:
			// 0 => field_name, 1 => equivalence selections array, 2 => widget, 3 => descrip
			$value = "<tr><td valign=\"middle\" NOWRAP><input type=\"checkbox\" name=\"check_if_${item[0]}\" value=\"1\" />";
			$value .= $item[3];
			$value .= "</td><td valign=\"middle\">";
			$value .= html_form::select_widget('equivalence_if_'.$item[0], $item[1]);
			$value .= "</td><td valign=\"middle\" NOWRAP>";
			$value .= '&nbsp; '.$item[2];
			$value .= "</td></tr>";
			$ifform[] = $value;
		}

		foreach ($thenclause AS $item) {
			// Format of each array item:
			// 0 => field_name, 1 => assignment selections array, 2 => widget, 3 => descrip
			$value = "<tr><td valign=\"middle\" NOWRAP><input type=\"checkbox\" name=\"check_then_${item[0]}\" value=\"1\" />";
			$value .= $item[3];
			$value .= "</td><td valign=\"middle\">";
			$value .= html_form::select_widget('assign_then_'.$item[0], $item[1]);
			$value .= "</td><td valign=\"middle\" NOWRAP>";
			$value .= '&nbsp; '.$item[2];
			$value .= "</td></tr>";
			$thenform[] = $value;
		}
		$GLOBALS['display_buffer'] .= "
		<form method=\"POST\">
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\" />
		<input type=\"hidden\" name=\"return\" value=\"".prepare($_REQUEST['return'])."\" />
		<input type=\"hidden\" name=\"type\" value=\"".prepare($_REQUEST['type'])."\" />
		<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"add\" />
		".html_form::form_table(array(
			__("Description") =>
			html_form::text_widget('rule_descrip', 150),

			__("Priority") .
			'('.__("lower numbers have higher priority").')' =>
			html_form::number_pulldown('rule_prio', 1, 30),
		))."

		<br/><br/>

		<div align=\"left\"><b>".__("IF")."</b></div>
		<table>".join('', $ifform)."</table>

		<br/><br/>

		<div align=\"left\"><b>".__("THEN")."</b></div>
		<table>".join('', $thenform)."</table>
		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Add")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		</form>
		";
	} // end method addform

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
		$rules_result = $GLOBALS['sql']->query( $rules_query );

		// If there are no rules in the system, return uninterpolated data
		if (!$GLOBALS['sql']->results( $rules_result )) {
			return $data;
		}

		// Pull a "local scope" copy so we can modify
		$d = $data;

		// Create index of what we have touched yet, so rules only affect something *once*
		foreach ($d AS $k => $v) { $touched[$k] = 0; }

		// Loop through all rules
		while ($rule = $GLOBALS['sql']->fetch_array( $rules_result )) {
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

	function addform_requesttype ( ) {
		$assoc = $this->_GetAssociations();
		if (!is_array($assoc)) {
			trigger_error(__("No modules are currently defining rule types."), E_USER_ERROR);
		}
		
		foreach ($assoc AS $item) {
			// Get information from item name
			$name = freemed::module_get_meta($item, 'RuleType');
			$choices[$name] = $item;
		}

		$GLOBALS['display_buffer'] .= "
		<form method=\"POST\">
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\" />
		<input type=\"hidden\" name=\"return\" value=\"".prepare($_REQUEST['return'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"addform\" />

		<div align=\"center\">
		".__("Choose Type")." : 
		".html_form::select_widget('type', $choices)."
		</div>

		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Choose")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		</form>
		";
	} // end method addform_requesttype

	function add ( ) {
		$_REQUEST['type'] = ereg_replace('[^A-Za-z0-9_ ]', '', $_REQUEST['type']);
		
		// Get the interface for the if and then clauses
		$ifclause = module_function($_REQUEST['type'], freemed::module_get_meta($_REQUEST['type'], 'RuleInterface'), array('if'));
		$thenclause = module_function($_REQUEST['type'], freemed::module_get_meta($_REQUEST['type'], 'RuleInterface'), array('then'));

		// Determine what we're doing
		foreach ($ifclause AS $if) {
			// Determine if it is active
			//print "looping for $if[0] <br/>\n";
			if ($_REQUEST['check_if_'.$if[0]] == 1) {
				//print "REQUEST if[0] = ".$_REQUEST[$if[0]]."<br/>\n";
				$clause['if'][] = array (
					'field' => $if[0],
					'equivalence' => $_REQUEST['equivalence_if_'.$if[0]],
					'value' => $_REQUEST[$if[0]]
				);
			}
		}

		if (!is_array($clause['if'])) {
			trigger_error(__("No IF clause was defined."), E_USER_ERROR);
		}
		foreach ($thenclause AS $then) {
			// Determine if it is active
			if ($_REQUEST['check_then_'.$then[0]] == 1) {
				$clause['then'][] = array (
					'field' => $then[0],
					'assignment' => $_REQUEST['assign_then_'.$then[0]],
					'value' => $_REQUEST[$then[0]]
				);
			}
		}
		if (!is_array($clause['then'])) {
			trigger_error(__("No THEN clause was defined."), E_USER_ERROR);
		}

		// Set global if/the clause in "variables"
		$this->variables = array (
			'rule_created' => SQL__NOW,
			'rule_descrip' => $_REQUEST['rule_descrip'],
			'rule_prio' => $_REQUEST['rule_prio'] + 0,
			'rule_type' => $_REQUEST['type'],
			'rule_clause_if' => serialize($clause['if']),
			'rule_clause_then' => serialize($clause['then'])
		);

		//print "<pre>"; print_r($this->variables); print "</pre>\n"; die();

		// Call add stuff from superclass
		$this->_add();
	} // end method add

	function modform ( ) {
		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		foreach ($rec AS $k => $v) { $_REQUEST[$k] = $v; }

		$template = CreateObject('_FreeMED.FormTemplate', $rec['fr_template']);
		$controls = $template->GetControls();
		$template->LoadData($_REQUEST['id']);

		foreach ($controls AS $k => $v) {
			// Decide if we have defined the control
			if (method_exists($this, 'control_'.$v['type'])) {
				// Set default value
				//$_REQUEST['variable_'.$v['variable']]
				$v['default'] = $template->FetchDataElement($v['variable']);

				// Call the appropriate method
				$widget = call_user_func(
					array(&$this, 'control_'.$v['type']),
					'widget',
					$v
				);

				// Push into form
				$form[$v['name']] = $widget;
			}
		} // end foreach controls

		$GLOBALS['display_buffer'] .= "
		<form method=\"POST\">
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\" />
		<input type=\"hidden\" name=\"return\" value=\"".prepare($_REQUEST['return'])."\" />
		<input type=\"hidden\" name=\"type\" value=\"".prepare($rec['type'])."\" />
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\" />
		<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />
		<input type=\"hidden\" name=\"action\" value=\"mod\" />

		".html_form::form_table($form)."

		<div align=\"center\">
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Modify")."\" />
		<input type=\"submit\" class=\"button\" name=\"__submit\" value=\"".__("Cancel")."\" />
		</div>
		</form>
		";
	} // end method modform

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

	function del ( $_id = NULL ) {
		$id = $_id ? $_id : $_REQUEST['id'];

		// Delete all attached pieces
		$q = "DELETE FROM form_record WHERE fr_id = '".addslashes($id)."'";
		$GLOBALS['sql']->query($q);

		// Stock deletion routine
		$this->_del();
	} // end method del

} // end class RulesModule

register_module ("RulesModule");

?>
