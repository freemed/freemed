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

// Class: org.freemedsoftware.core.SupportModule
//
//	Database table maintenance module superclass. This is descended
//	from <BaseModule>.
//
class SupportModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Support Data";
	var $CATEGORY_VERSION = "0.2.1";

	// Variable: $this->order_field
	//
	//	Defines the ORDER BY clause for built-in functions. This
	//	should be present in all child modules. It is set to 'id'
	//	by default, which is horrible behavior, and should be
	//	overridden in all child classes.
	//
	var $order_field = 'id';

	// Variable: $this->form_vars
	//
	//	List of form variables which need to be used in the
	//	add or modify forms.
	//
	// Example:
	//
	//	$this->form_vars = array ( 'ptfname', 'ptlname' );
	//
	// See Also:
	//	<form>
	//
	var $form_vars;

	// Variable: $this->defeat_acl
	//
	//	Turns of ACL checking for 'support' modules. This is
	//	useful for modules that are used with their own internal
	//	access controls. Defaults to false.
	//
	// Example:
	//
	//	$this->defeat_acl = true;
	//
	var $defeat_acl = false;

	// Variable: $this->table_name
	//
	//	Defines the name of the SQL table used and/or defined
	//	by this module. Must be defined for the module to
	//	function properly if a table definition is used.
	//
	// Example:
	//
	//	$this->table_name = 'facility';
	//
	var $table_name;

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

	// Variable: $this->list_view
	//
	//	Describe the "listing" view of the format : name => field
	//
	// Example:
	//
	//	$this->list_view = array ( __("CPT Code") => 'cptcode', __("Description") => 'cptnameint' );
	//
	protected $list_view = NULL;

	// Variable: $this->rpc_field_map
	//
	//	Specifies the format of the XML-RPC structures returned by
	//	the FreeMED.DynamicModule.picklist method. These are passed
	//	as key => value, where key is the target name of the
	//	structure item and value is the name of the SQL field. "id"
	//	is passed as "id" by default. If this array is not
	//	defined, FreeMED.DynamicModule.picklist will fail for the
	//	target module.
	//
	// Example:
	//
	//	$this->rpc_field_map = array ( 'last_name' => 'ptlname' );
	//
	var $rpc_field_map;

	// Variable: $this->distinct_fields
	//
	//	Specifies the field names which are allowed to have
	//	distinct value queries against them.
	//
	// Example:
	//
	//	$this->distinct_fields = array ( "assignedto" );
	//
	var $distinct_fields;

	// contructor method
	public function __construct () {
		// Store the rpc map in the meta information
		$this->_SetMetaInformation('rpc_field_map', $this->rpc_field_map);
		$this->_SetMetaInformation('distinct_fields', $this->distinct_fields);
		$this->_SetMetaInformation('table_name', $this->table_name);

		// Call parent constructor
		parent::__construct();
	} // end function SupportModule

	// override check_vars method
	public function check_vars ($nullvar = "") {
		return true;
	} // end function check_vars

	// Method: prepare
	//
	//	Prepare data for insertion into SQL using variables mapping. Must be
	//	overridden per module.
	//
	// Parameters:
	//
	//	$data - Hash of input data
	//
	// Returns:
	//
	//	$hash - Hash of sanitized output data
	//
	protected function prepare ( $data ) {
		return $data;
	} // end protected function prepare

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $__submit;

		// Handle a "Cancel" button being pushed
		if ($__submit==__("Cancel")) {
			$action = "";
			$this->view();
			return NULL;
		}

		switch ($action) {
			case "add":
				if (!$this->acl_access('add') and !$this->defeat_acl) {
					trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
				}
				$this->add();
				break;

			case "addform":
				if (!$this->acl_access('add') and !$this->defeat_acl) {
					trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
				}
				if ($_REQUEST['return'] == 'close') {
					$GLOBALS['__freemed']['no_template_display'] = true;
				}
				$this->addform();
				break;

			case "del":
			case "delete":
				if (!$this->acl_access('delete') and !$this->defeat_acl) {
					trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
				}
				$this->del();
				break;

			case "mod":
			case "modify":
				if (!$this->acl_access('modify') and !$this->defeat_acl) {
					trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
				}
				$this->mod();
				break;

			case "modform":
				if (!$this->acl_access('modify') and !$this->defeat_acl) {
					trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
				}
				global $id;
				if (empty($id) or ($id<1)) {
					template_display();
				}
				$this->modform();
				break;

			case "view":
			default:
				if (!$this->acl_access('view') and !$this->defeat_acl) {
					trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
				}
				$action = "";
				$this->view();
				break;
		} // end switch action
	} // end function main

	// Method: acl_access
	//
	//	Should be overridden by any module which needs different
	//	access checks.
	//
	protected function acl_access ( $type ) { 
		return freemed::acl_patient('support', $type);
	} // end method acl_access
	
	// Method: add
	//
	//	Basic superclass addition routine.
	//
	// Parameters:
	//
	//	$_param - (optional) Associative array of values. If
	//	specified, _add will run quiet. The associative array
	//	is in the format of sql_name => sql_value.
	//
	// Returns:
	//
	//	Nothing if there are no parameters. If $_param is
	//	specified, _add will return the id number if successful
	//	or false if unsuccessful.
	//
	public function add ( $data ) {
		if (!$this->acl_access('add') and !$this->defeat_acl) {
			trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
		}

		$GLOBALS['sql']->load_data( $this->prepare ( $data ) );

		$this->add_pre( &$data );
		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		$new_id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );
		$this->add_post( $new_id );
		return $new_id;
	} // end function add

	protected function add_pre ( $data ) { }
	protected function add_post ( $id ) { }

	// Method: _del
	//
	//	Basic superclass deletion routine.
	//
	// Parameters:
	//
	//	$_param - (optional) Id number for the record to
	//	be deleted. 
	//
	// Returns:
	//
	//	Nothing if there are no parameters. If $_param is
	//	specified, _del will return boolean true or false
	//	depending on whether it is successful.
	//
	// See Also:
	//	<predel>
	//
	public function del ( $id ) {
		if ( !$this->acl_access( 'delete' ) ) {
			trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
		}

		$this->del_pre( $id + 0 );
		$query = "DELETE FROM `".$this->table_name."` WHERE id = '".addslashes( $id+0 )."'";
		$result = $GLOBALS['sql']->query ( $query );
	} // end function del

	protected function del_pre ( $id ) { }

	// Method: mod
	//
	//	Basic superclass modification routine.
	//
	// Parameters:
	//
	//	$data - 
	//
	// See Also:
	//	<premod>
	//	<postmod>
	//
	public function mod ( $data ) {
		if ( !$this->acl_access( 'modify' ) ) {
			trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
		}

		if ( !$data['id'] ) { return false; }

		$this->mod_pre( &$data );
		$GLOBALS['sql']->load_data( $this->prepare( $data ) );
		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->update_query (
				$this->table_name,
				$this->variables,
				array ( "id" => $id )
			)
		);
		$this->mod_post( &$data );

		return $result ? true : false;
	} // end function mod

	private function mod_pre ( $data ) { }
	private function mod_post ( $data ) { }

	// Method: picklist
	//
	//	Generic picklist for XML-RPC.
	//
	// Parameters:
	//
	//	$criteria - (optional) Hash of criteria fields to narrow
	//	the search
	//
	// Returns:
	//
	//	Array of hashes
	//
	function picklist ( $criteria = NULL ) {
		// Do not execute if there is no field map
		if (!is_array($this->rpc_field_map)) {
			return false;
		}

		// Map criteria from rpc_field_map
		if (is_array($criteria)) {
			foreach ($criteria AS $k => $v) {
				if (!empty($this->rpc_field_map[$k])) {
					$c[] = "LOWER(".$this->rpc_field_map[$k].") LIKE '%".addslashes(strtolower($v))."%'";
				}
			}
		}

		$query = "SELECT * FROM ".$this->table_name.
			( is_array($c) ? " WHERE ".join(' AND ',$c) : "" ).
			( $this->order_field ? " ORDER BY ".$this->order_field : "" );
		//syslog(LOG_INFO, $query);
		$result = $GLOBALS['sql']->query($query);
		if (!$GLOBALS['sql']->results($result)) {
			return '';
		}
		return rpc_generate_sql_hash(
			$this->table_name,
			array_merge(
				$this->rpc_field_map,
				array ( 'id' => 'id' )
			),
			( is_array($c) ? " WHERE ".join(' AND ',$c) : "" ).
			' ORDER BY '.$this->order_field
		);
	} // end method picklist

	// Method: distinct
	//
	//	Provide a list of distinct values for a particular field.
	//
	// Parameters:
	//
	//	$field - Name of field to provide distinct values for.
	//
	// Returns:
	//
	//	Array of distinct values, or false if the field name is
	//	invalid.
	//
	function distinct ( $field ) {
		$found = false;
		foreach ($this->distinct_fields AS $v) {
			if ($v == $field) { $found = true; }
		}
		if (!$found) { return false; }

		// Parse distinct_values and return an array
		$r = $GLOBALS['sql']->distinct_values($this->table_name, $field);
		return $r;
	} // end method distinct

	// Method: get_field
	//
	//	Get single support data field.
	//
	// Parameters:
	//
	//	$id - Record id
	//
	//	$field - Field name
	//
	// Returns:
	//
	//	Textual version of field
	//
	protected function get_field ( $id, $field ) {
		if (!$id) { return __("NO RECORD FOUND"); }
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		return $r[$field];
	} // end method get_field

	// Method: to_text
	//
	//	Convert id to text, based on <$this->widget_hash>
	//
	// Parameters:
	//
	//	$id - Record id
	//
	//	$field - (optional) Defaults to id for the identifying field.
	//
	// Returns:
	//
	//	Textual version of record
	//
	public function to_text ( $id, $field='id' ) {
		if (!$id) { return __("NO RECORD FOUND"); }
		$r = $GLOBALS['sql']->get_link( $this->table_name, $rec );
		if (!(strpos($this->widget_hash, "##") === false)) {
			$value = '';
			$hash_split = explode('##', $this->widget_hash);
			foreach ($hash_split AS $_k => $_v) {
				if (!($_k & 1)) {
					$value .= stripslashes($_v);
				} else {
					$value .= stripslashes($r[$_v]);
				}
			}
		} else {
			$value = $r[$this->widget_hash];
		}
		return $value;
	} // end method to_text

	// Method: _setup
	//
	//	Internal method called by the module superclass, which
	//	executes initial table creation from <create_table> and
	//	initial data import from <freemed_import_stock_data>.
	//
	public function _setup () {
		global $display_buffer;
		if (!$this->create_table()) { return false; }
		return freemed_import_stock_data ($this->table_name);
	} // end function _setup

	// Method: create_table
	//
	//	Creates the initial table definition required by this
	//	module to function properly. Relies on the existance of
	//	data/schema/VERSION/TABLENAME.sql
	//
	// Returns:
	//
	//	Boolean, false if failed.
	//
	protected function create_table ( ) {
		// Check for data/schema/(version)/(table_name).sql
		$path = dirname(__FILE__).'/../../data/schema/'.VERSION.'/'.$this->table_name.'.sql';
		if ( file_exists ( $path ) ) {
			$query = $GLOBALS['sql']->query ( readfile ( $path ) );
			return $query ? true : false;
		} else {
			return false;
		}
	} // end function create_table

} // end class SupportModule

?>
