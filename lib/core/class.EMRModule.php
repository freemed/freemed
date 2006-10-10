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
//	<org.freemedsoftware.core.BaseModule>.
//
class EMRModule extends BaseModule {

	// override variables
	public $CATEGORY_NAME = "Electronic Medical Record";
	public $CATEGORY_VERSION = "0.4";

	// Variable: $this->widget_hash
	//
	//	Specifies the format of the <widget> method. This is
	//	formatted by having SQL field names surrounded by '##'s.
	//
	// Example:
	//
	//	$this->widget_hash = '##phylname##, ##phyfname##';
	//	
	public $widget_hash;

	// vars to be passed from child modules
	public $order_fields;
	public $form_vars;
	public $table_name;

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
	public $date_field;

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
	public $patient_field; // the field that links to the patient ID

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

	public function __construct () {
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
	} // end constructor

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

		return $locked ? true : false;
	} // end function locked

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
	protected function additional_move ($id, $from, $to) { }

	// Method: acl_access
	//
	//	Should be overridden by any module which needs different
	//	access checks.
	//
	protected function acl_access ( $type, $patient ) {
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
	
	// Method: in_use
	//
	// Parameters:
	//
	//	 $id -
	//
	public function in_use ( $id ) {
		// Check for record locking
		$lock = CreateObject('org.freemedsoftware.core.RecordLock', $this->table_name);
		if ($lock->IsLocked( $id )) {
			trigger_error(__("This record is currently in use."), E_USER_ERROR);
		} else {
			// Add record lock
			$lock->LockRow( $id );
		}
	} // end method in_use

	// Method: add
	//
	// Parameters:
	//
	//	$data -
	//
	public function add ( $data ) {
		if ( !$this->acl_access( 'add', $patient ) ) {
			trigger_error(__("You do not have access to do that."), E_USER_ERROR);
		}

		$GLOBALS['sql']->load_data( $this->prepare ( $data ) );
		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->insert_query (
				$this->table_name,
				$this->variables
			)
		);
		$new_id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );

		return $new_id;
	} // end public function add

	// Function: del
	//
	// Parameters:
	//
	//	$id - Record id
	//
	public function del ( $id ) {
		if ( !$this->acl_access( 'delete', $patient ) ) {
			trigger_error(__("You do not have access to do that."), E_USER_ERROR);
		}

		// If there is an override ...
		if (!freemed::lock_override()) {
			if ($this->locked($id)) return false;
		}

		$query = "DELETE FROM `".$this->table_name."` WHERE id = '".addslashes( $id )."'";
		$result = $GLOBALS['sql']->query( $query );
		return $result ? true : false;
	} // end public function del

	// Method: mod
	public function mod ( $data ) {
		if ( !$this->acl_access( 'modify', $patient ) ) {
			trigger_error(__("You do not have access to do that."), E_USER_ERROR);
		}

		if (!$data['id']) { return false; }

		// Check for modification locking
		if (!freemed::lock_override()) {
			if ($this->locked($data['id'])) { return false; }
		}

		// Handle row-level locking mechanism
		$lock = CreateObject('org.freemedsoftware.core.RecordLock', $this->table_name);
		if ( $lock->IsLocked( $data['id'] ) ) {
			return false;
		} else {
			$lock->LockRock( $data['id'] );
		}

		$GLOBALS['sql']->load_data( $this->prepare ( $data ) );
		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id" => $data['id']
				)
			)
		);

		// Unlock row, since update is done
		$lock->UnlockRow( $data['id'] );

		return $result ? true : false;
	} // end public function mod

	// Method: lock
	//
	// Parameters:
	//
	//	$id - Record id
	//
	public function lock ( $id ) {
		if ( !$this->acl_access( 'lock', $patient ) ) {
			trigger_error(__("You do not have access to do that."), E_USER_ERROR);
		}

		// Check for record locking
		if ($this->locked( $id )) { return false; }

		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->update_query (
				$this->table_name,
				array (
					"locked" => $this->this_user->user_number
				),
				array (
					"id" => $id
				)
			)
		);

		return $result ? true : false;
	} // end public function lock

	// Method: _setup
	public function _setup ( ) {
		if (!$this->create_table()) { return false; }
		return freemed_import_stock_data ( $this->table_name );
	} // end function _setup

	// Method: create_table
	//
	// Returns:
	//
	//	Boolean, success status.
	//
	protected function create_table () {
		// Check to see if the current version exits
		$path = BASE_PATH.'/data/schema/'.VERSION.'/'.$this->table_name.'.sql';
		if (file_exists( $path )) {
			$result = $GLOBALS['sql']->query( file_get_contents( $path ) );
			return $result ? true : false;
		} else {
			return false;
		}
		return !empty($query);
	} // end function create_table

	// Method: picklist
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
	public function picklist ( $varname, $patient, $conditions = false ) {
		// TODO: sanitize conditions or disable entirely ... perhaps select from a list of possibles defined by the module?
		$query = "SELECT * FROM `".$this->table_name."` WHERE ".
			"( `".$this->patient_field.
				"` = '".addslashes($patient)."') ".
			( $conditions ? " AND ( ".$conditions." ) " : "" ).
			( $this->order_fields ? "ORDER BY ".$this->order_fields : "" );
		$result = $GLOBALS['sql']->queryAll( $query );
		foreach ( $result AS $r ) {
			if (!(strpos($this->widget_hash, "##") === false)) {
				$key = '';
				$hash_split = explode('##', $this->widget_hash);
				foreach ($hash_split AS $_k => $_v) {
					if (!($_k & 1)) {
						$key .= stripslashes($_v);
					} else {
						$key .= stripslashes($r[$_v]);
					}
				}
			} else {
				$key = $this->widget_hash;
			}
			$return[$key] = $r['id'];
		}
		return $return;
	} // end method picklist

	protected function qualified_query ( $patient, $items = NULL ) {
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
			"ORDER BY ".( (is_array($this->summary_query_link) and $this->summary_order_by == 'id') ? $this->table_name.'.' : '' ).$this->summary_order_by." DESC ".
			( $items ? "LIMIT ".addslashes($items) : '' );

		return $query;
	} // end protected function qualified_query

	// Method: recent_record
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
	public function recent_record ( $patient, $recent_date = NULL ) {
		$query = "SELECT * FROM `".$this->table_name."` ".
			"WHERE `".$this->patient_field."` = '".addslashes($patient)."' ".
			( $recent_date ? " AND `".$this->date_field."` <= '".addslashes($recent_date)."' " : "" ).
			"ORDER BY ".$this->date_field." DESC, id DESC";
		$res = $GLOBALS['sql']->queryOne( $query );
		return $res;
	} // end public function recent_record

	// Method: RenderToPDF
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
	public function RenderToPDF ( $record ) {
		// Sanity checking
		if (!is_integer($record)) { return false; }

		// Handle render
		if ($render = $this->print_override($record)) {
			// Handle this elsewhere
		} else {
			// Create TeX object for patient
			$TeX = CreateObject('org.freemedsoftware.core.TeX', array());

			// Actual renderer for formatting array
			if ($this->patient_field) {
				// If this is an EMR module with additional
				// fields, import them
				$query = "SELECT *".
					( (count($this->summary_query)>0) ? 
					",".join(",", $this->summary_query)." " : " " ).
					"FROM ".$this->table_name." ".
					"WHERE id='".addslashes($record)."'";
				$rec = $GLOBALS['sql']->queryOne($query);
			} else {
				$rec = $GLOBALS['sql']->get_link( $t, $record );
			} // end checking for summary_query

			$TeX->_buffer = $TeX->RenderFromTemplate(
				$this->print_template,
				$rec
			);
			$render = $TeX->RenderToPDF( );
		} // end render if/else

		// Return the file name
		return $render;
	} // end method RenderToPDF

	// Method: RenderTeX
	//
	//	Internal TeX renderer for the record. By default this
	//	uses the <print_format> class variable to determine the
	//	proper format. If another format is to be used, override
	//	this class.
	//
	// Parameters:
	//
	//	$TeX - <org.freemedsoftware.core.TeX> object reference. Must
	//	be prefixed by an amphersand, otherwise changes will be lost!
	//
	//	$id - Record id to be printed
	//
	// Example:
	//
	//	$this->RenderTeX ( &$TeX, $id );
	//
	public function RenderTeX ( $TeX, $id, $template = false ) {
		if (is_array($id)) {
			foreach ($id AS $k => $v) {
				$buffer .= $this->RenderTeX ( &$TeX, $v );
			}
			return $buffer;
		} else {
			if (!$id) { return false; }

			// Determine template
			if ($template) {
				$my_template = $template;
			} else {
				$my_template = $this->print_template;
			}

			$query = "SELECT *".
				( (count($this->summary_query)>0) ? 
				",".join(",", $this->summary_query)." " : " " ).
				"FROM ".$this->table_name." ".
				"WHERE id='".addslashes($record)."'";
			$rec = $GLOBALS['sql']->queryOne($query);

			// Handle templating elsewhere
			return $TeX->RenderFromTemplate( $my_template, $rec );
		}
	} // end method RenderTeX

} // end class EMRModule

?>
