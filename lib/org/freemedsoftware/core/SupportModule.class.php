<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //      Alexandru Zbarcea <zbarcea.a@gmail.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

	// Variable: $this->additional_fields
	//
	//	Additional custom SQL fields.
	//
	// Example:
	//
	//	$this->additional_fields = array ( 'LENGTH(ptst) AS st', 'ISNULL(x) AS y' );
	//
	protected $additional_fields;

	// Variable: $this->table_join
	//
	//	Additional join fields, used in widgets, where the keys are
	//	the associated fields in the local table, and the values are
	//	the names of the foreign tables.
	//
	// Example:
	//
	//	$this->table_join = array ( 'covpatient' => 'patient' );
	//
	protected $table_join;

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

	// Variable: $this->acl_category
	//
	//	Category of ACL query to be performed by module functions.
	//	Defaults to 'support'.
	//
	var $acl_category = 'support';

	// contructor method
	public function __construct () {
		// Store the rpc map in the meta information
		$this->_SetMetaInformation('rpc_field_map', $this->rpc_field_map);
		$this->_SetMetaInformation('distinct_fields', $this->distinct_fields);
		$this->_SetMetaInformation('table_name', $this->table_name);
		if (!$this->MODULE_HIDDEN) {
			$this->_SetAssociation('SupportModule');
		}

		// Call parent constructor
		parent::__construct();
	} // end function SupportModule

	// override check_vars method
	public function check_vars ($nullvar = "") {
		return true;
	} // end function check_vars

	// Method: GetMaintenanceStructure
	//
	//	Get structure used in display of SupportData maintenance
	//	screens.
	//
	// Returns:
	//
	//	Hash.
	//	* key = Column name
	//	* val = sql name
	//
	public function GetMaintenanceStructure ( ) {
		return $this->list_view;
	} // end method GetMaintenanceStructure

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

	// Method: acl_access
	//
	//	Should be overridden by any module which needs different
	//	access checks.
	//
	protected function acl_access ( $type ) { 
		return freemed::acl($this->acl_category, $type);
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
	// SeeAlso:
	//	<add_pre>
	//	<add_post>
	//
	public function add ( $data ) {
		if (!$this->acl_access('add') and !$this->defeat_acl) {
			//trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
		}

		$ourdata = $this->prepare( (array) $data );
		$this->add_pre( &$ourdata );
		$GLOBALS['sql']->load_data( $ourdata );

		$query = $GLOBALS['sql']->insert_query (
			$this->table_name,
			$this->variables
		);
		$result = $GLOBALS['sql']->query ( $query );

		$new_id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );
		$this->add_post( $new_id, &$ourdata );
		return $new_id;
	} // end function add

	protected function add_pre ( $data ) { }
	protected function add_post ( $id, $data ) { }

	// Method: del
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
	//	<del_pre>
	//
	public function del ( $id ) {
		if ( !$this->acl_access( 'delete' ) ) {
			//trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
		}

		$this->del_pre( $id + 0 );
		$query = "DELETE FROM `".$this->table_name."` WHERE id = '".addslashes( $id+0 )."'";
		$result = $GLOBALS['sql']->query ( $query );
		return true;
	} // end function del

	protected function del_pre ( $id ) { }

	// Method: mod
	//
	//	Basic superclass modification routine.
	//
	// Parameters:
	//
	//	$data - Hash of data to pass.
	//
	// See Also:
	//	<mod_pre>
	//	<mod_post>
	//
	public function mod ( $data ) {
		if ( !$this->acl_access( 'modify' ) ) {
			//trigger_error(__("You don't have permission to do that."), E_USER_ERROR);
		}

		if ( is_array( $data ) ) {
			if ( !$data['id'] ) { return false; }
		} elseif ( is_object( $data ) ) {
			if ( ! $data->id ) { return false; }
		} else {
			return false;
		}

		$ourdata = $this->prepare( (array) $data );

		$this->mod_pre( &$ourdata );
		$GLOBALS['sql']->load_data( $ourdata );
		if ( is_array( $this->variables ) ) {
			$result = $GLOBALS['sql']->query (
				$GLOBALS['sql']->update_query (
					$this->table_name,
					$this->variables,
					array ( "id" => $data['id'] )
				)
			);
			if ( PEAR::isError( $result ) ) {
				$result = false;
			}
		} else {
			$result = true;
		}
		$this->mod_post( &$ourdata );

		return $result ? true : false;
	} // end function mod

	protected function mod_pre ( $data ) { }
	protected function mod_post ( $data ) { }

	// Method: GetRecord
	//
	//	Retrieve database record associated with this module's
	//	data.
	//
	// Parameters:
	//
	//	$id - Database ID
	//
	// Returns:
	//
	//	Hash.
	//
	public function GetRecord ( $id ) {
		return $GLOBALS['sql']->get_link( $this->table_name, $id );
	} // end method GetRecord

	// Method: GetRecords
	//
	//	Get list of records for this maintenance table.
	//
	// Parameters:
	//
	//	$limit - (optional) Limit to maximum number of records to return
	//
	// Return:
	//
	//	Array of hashes.
	//
	public function GetRecords ( $limit = 100, $criteria_field = NULL, $criteria = NULL ) {
		// TODO: Security, sanity check criteria_field variable

		// Check to make sure that if $criteria_field is declared that it's valid
		if ( $criteria_field ) {
			$found = false;
			foreach ( $this->variables AS $v ) {
				if ( $v == $criteria_field ) { $found = true; }
			}
			if ( ! $found ) {
				syslog( LOG_INFO, "GetRecords| invalid value ${criteria_field} attempted for indexing value" );
				return false;
			}
		}
		$q = "SELECT *,".( is_array( $this->additional_fields ) ? join(',', $this->additional_fields).',' : '' ).$this->table_name.".id AS id FROM `".$this->table_name."` ".$this->FormJoinClause()." ".( $criteria_field ? " WHERE ${criteria_field} LIKE '".$GLOBALS['sql']->escape( $criteria )."%' " : "" )." ".( $this->order_field != 'id' ? "ORDER BY ".$this->order_field : "" )." LIMIT ${limit}";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetRecords

	// Method: FormJoinClause
	//
	//	Internal protected method to form the SQL LEFT OUTER JOIN
	//	clause needed by the module if it is required.
	//
	// Returns:
	//
	//	SQL query fragment
	//
	protected function FormJoinClause ( ) {
		// Create join clause if there is one
		$join = '';
		if (is_array($this->table_join)) {
			$j = array();
			foreach ( $this->table_join AS $k => $v ) {
				if ( ($k+0) == 0 ) {
					$j[] = "LEFT OUTER JOIN ${v} ON ".$this->table_name.".${k} = ${v}.id";
				}
			}
			$join = join(' ', $j);
		}
		return $join;
	} // end method FormJoinClause

	// Method: picklist
	//
	//	Generic picklist.
	//
	// Parameters:
	//
	//	$criteria - (optional) String to narrow search.
	//
	// Returns:
	//
	//	Array of hashes
	//
	function picklist ( $criteria = NULL ) {
		if (!(strpos($this->widget_hash, "##") === false)) {
			$value = '';
			$hash_split = explode('##', $this->widget_hash);
			foreach ($hash_split AS $_k => $_v) {
				if ($_k & 1) {
					$c[] = "LOWER(". $_v .") LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%')";
				}
			}
		} else {
			$c[] = "LOWER(".$this->widget_hash.") LIKE LOWER('%".$GLOBALS['sql']->escape( $criteria )."%')";
		}

		$query = "SELECT * FROM ".$this->table_name.
			" ".$this->FormJoinClause()." ".
			( is_array($c) ? " WHERE ".join(' OR ',$c) : "" ).
			( $this->order_field ? " ORDER BY ".$this->order_field : "" ).
			" LIMIT 20";
		//syslog(LOG_INFO, $query);
		$result = $GLOBALS['sql']->queryAll($query);
		if (!count($result)) { return array(); }
		foreach ($result AS $r) {
			$return[$r['id']] = trim( $this->to_text( $r ) );
		}
		return $return;
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
	//	$id - Record id or array containing record hash
	//
	//	$field - (optional) Defaults to id for the identifying field.
	//
	// Returns:
	//
	//	Textual version of record
	//
	public function to_text ( $id, $field='id' ) {
		if (!$id) { return __("NO RECORD FOUND"); }
		if (is_array($id)) {
			$r = $id;
		} else {
			$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		}
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
	//	initial data import.
	//
	public function _setup () {
		//syslog(LOG_INFO, get_class($this)." : _setup()");
		if (!$this->create_table()) { return false; }
		//syslog(LOG_INFO, get_class($this)." : done with create_table");
		$c = $GLOBALS['sql']->queryOne( "SELECT COUNT(*) FROM ".$this->table_name );
		if ( $c > 0 ) { return false; }
		return CallMethod( 'org.freemedsoftware.api.TableMaintenance.ImportStockData', $this->table_name );
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
		//syslog(LOG_INFO, get_class($this)." : create_table");

		// Check to see if the current version exits
		$path = dirname(__FILE__).'/../../../../data/schema/mysql/'.$this->table_name.'.sql';
		if (file_exists( $path )) {
			$command = '"'.dirname(__FILE__).'/../../../../scripts/load_schema.sh" '.escapeshellarg('mysql').' '.escapeshellarg($this->table_name).' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME);
			system ( $command );
			return true;
		} else {
			//syslog(LOG_INFO, get_class($this)." : no definition found for ${path}");
			return false;
		}
	} // end function create_table

} // end class SupportModule

?>
