<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //      Alexandru Zbarcea <zbarcea.a@gmail.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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
	public $date_variables = NULL;

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

	// Variable: $this->loinc_mapping
	//
	//	LOINC data point for this module
	//
	// Example:
	//
	//	$this->loinc_mapping = '11369-6';
	//
	protected $loinc_mapping;

	// Variable: $this->loinc_display
	//
	//	HL7 CDA mapping
	//
	// Example:
	//
	//	$this->loinc_display = array (
	//		# column name => SQL field
	//		"Immunization" => 'immunization',
	//		"Date" => 'dateof'
	//	);
	//
	protected $loinc_display;

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
		if (! $this->MODULE_HIDDEN ) {
			$this->_SetHandler( 'EmrSummary', null );
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
	// Returns:
	//
	//	Boolean, whether the record is locked or not.
	//
	// Example:
	//
	//	if ($this->locked($id)) return false;
	//
	function locked ($id, $quiet = false) {
		static $locked;

		// If there is no table_name, we can skip this altogether
		if (empty($this->table_name)) { return false; }

		if (!isset($locked['id_'.$id])) {
			$query = "SELECT COUNT(*) AS lock_count FROM ".$this->table_name." WHERE id='".addslashes($id)."' AND (locked > 0)";
			$result = $GLOBALS['sql']->queryOne( $query );
			$locked['id_'.$id] = ($result > 0) && !( is_a( $result, 'DB_Error' ) );
		}

		return $locked['id_'.$id] ? true : false;
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
			return true;
		}
		return false;
	} // end method acl_access
	
	// Method: in_use
	//
	//	Determine if the record is locked, and therefore "in use".
	//
	// Parameters:
	//
	//	$id - Record ID.
	//
	// Returns:
	//
	//	Boolean, locked status.
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
	//	Publically accessible record insertion routine.
	//
	// Parameters:
	//
	//	$data - Hash of values to be inserted into the record.
	//
	// Returns:
	//
	//	'id' field of newly created record.
	//
	// SeeAlso:
	//	<add_pre>
	//	<add_post>
	// 
	public function add ( $data ) {
		freemed::acl_enforce( 'emr', 'write' );

		$ourdata = $this->prepare( (array) $data );
		$this->add_pre( $ourdata );
		$GLOBALS['sql']->load_data( $ourdata );
		$query = $GLOBALS['sql']->insert_query (
			$this->table_name,
			$this->variables,
			$this->date_variables
		);
		$result = $GLOBALS['sql']->query ( $query );
		$new_id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );
		$this->moduleFieldCheck(get_class($this),$new_id,$data);
		$this->add_post( $new_id, $ourdata );

		return $new_id;
	} // end public function add
	
	//Mthod: moduleFieldCheck
	//
	//      This method will check whether this module belongs to work flow and also evaluate the complete/uncomplete status of this module form
	//
	// Parameters:
	//
	//	$moduleClass - module class name of called module
	//	$data        - form data to be saved
	protected function moduleFieldCheck ( $moduleClass,$new_id,$data ) {
		$moduleFieldCheckerTypeObj = createObject("org.freemedsoftware.module.ModuleFieldCheckerType");
		$result = $moduleFieldCheckerTypeObj->getModuleInfo( $moduleClass );
		$module_fields = "";
		if($result){
		        $fields= explode(',',$result['fields']);
			
			for ( $counter = 0; $counter < count($fields); $counter++)
			{
				
				if($data[$fields[$counter]]=="")
				{
					$module_fields=$module_fields.$fields[$counter].",";
					//break;
				}
			}
			if(strlen($module_fields)>0){
				$moduleFieldCheckerObj = createObject("org.freemedsoftware.module.ModuleFieldChecker");
				$moduleData['stamp'] = date('Y-m-d H:i:s');
				$moduleData['patient'] = $data[$this->patient_field];
				$moduleData['user'] = freemed::user_cache()->user_number;
				$moduleData['module_type'] = $result['id'];
				$moduleData['module_fields'] = $module_fields;
				$moduleData['module_record'] = $new_id;
				$moduleFieldCheckerObj->add($moduleData);
			}
		}
		
	} // end function moduleFieldCheck
	
	protected function add_pre( $data ) { }
	protected function add_post( $id, $data ) { }

	// Function: del
	//
	// Parameters:
	//
	//	$id - Record id
	//
	public function del ( $id ) {
		freemed::acl_enforce( 'emr', 'modify' );

		// If there is an override ...
		if (!freemed::lock_override()) {
			if ($this->locked($id)) return false;
		}

		$this->del_pre( $id );
		$query = "DELETE FROM `".$this->table_name."` WHERE id = '".addslashes( $id )."'";
		$result = $GLOBALS['sql']->query( $query );
		return $result ? true : false;
	} // end public function del

	protected function del_pre( $id ) { }

	// Method: mod
	//
	//	Modify EMR record segment
	//
	// Parameters:
	//
	//	$data - Hash of data
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function mod ( $data ) {
		freemed::acl_enforce( 'emr', 'modify' );

		if ( !is_array($data) and !is_object($data) ) {
			syslog(LOG_INFO, get_class($this)."| no data presented");
			return false;
		}
		$ourdata = (array) $data;

		if (!$ourdata['id']) { syslog(LOG_INFO, get_class($this)."| no id presented"); return false; }

		// Check for modification locking
		if (!freemed::lock_override()) {
			if ($this->locked($ourdata['id'])) { return false; }
		}

		// Handle row-level locking mechanism
		$lock = CreateObject('org.freemedsoftware.core.RecordLock', $this->table_name);
		if ( $lock->IsLocked( $ourdata['id'] ) ) {
			return false;
		} else {
			$lock->LockRow( $ourdata['id'] );
		}

		$ourdata = $this->prepare( $ourdata );
		$this->mod_pre( $ourdata );
		$GLOBALS['sql']->load_data( $ourdata );
		$result = $GLOBALS['sql']->query (
			$GLOBALS['sql']->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id" => $data['id']
				),
				$this->date_variables
			)
		);
		$this->mod_post( $ourdata );

		$this->moduleFieldCheck(get_class($this),$data['id'],$data);

		// Unlock row, since update is done
		$lock->UnlockRow( $data['id'] );

		return $result ? true : false;
	} // end public function mod

	protected function mod_pre ( $data ) { }
	protected function mod_post ( $data ) { }

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

		$this_user = freemed::user_cache();
		$query = $GLOBALS['sql']->update_query (
			$this->table_name,
			array (
				"locked" => $this_user->user_number
			),
			array ( "id" => $id )
		);
		$result = $GLOBALS['sql']->query ( $query );

		return $result ? true : false;
	} // end public function lock

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

	// Method: _setup
	public function _setup ( ) {
		if (!$this->create_table()) { return false; }
		$c = $GLOBALS['sql']->queryOne( "SELECT COUNT(*) FROM ".$this->table_name );
		if ( $c > 0 ) { return false; }
		return CallMethod('org.freemedsoftware.api.TableMaintenance.ImportStockData', $this->table_name );
	} // end function _setup

	// Method: create_table
	//
	// Returns:
	//
	//	Boolean, success status.
	//
	protected function create_table () {
		// Check to see if the current version exits
		$path = dirname(__FILE__).'/../../../../data/schema/mysql/'.$this->table_name.'.sql';
		if (file_exists( $path )) {
			if (DB_HOST === "localhost" || DB_HOST === "127.0.0.1") {
				$command = dirname(__FILE__).'/../../../../scripts/load_schema.sh '.escapeshellarg('mysql').' '.escapeshellarg($this->table_name).' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME);
			} else {
				$command = dirname(__FILE__).'/../../../../scripts/load_schema.sh '.escapeshellarg('mysql').' '.escapeshellarg($this->table_name).' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME).' 0 '.escapeshellarg(DB_HOST);
			}
			system ( $command );
			return true;
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

	// Method: CdaComponent
	//
	//	Generate HL7 CDA component mapping from internal information.
	//
	// Parameters:
	//
	//	$patient - Patient id number
	//
	// Returns:
	//
	//	XML text, meant to be concatenated into the component section
	//	of a CDA document
	//
	// SeeAlso:
	//	<GetList>
	//
	public function CdaComponent ( $patient ) {
		$set = $this->GetList( $patient, NULL );
		if ( !is_array ( $set ) ) { return NULL; }
		if ( !isset ( $this->loinc_mapping ) ) { return NULL; };

		// Grab LOINC record
		$loinc = $GLOBALS['sql']->queryRow( "SELECT * FROM loinc WHERE loinc_num=" . $GLOBALS['sql']->quote( $this->loinc_mapping ) );

		// Create CDA component header
                $buffer = '<component><section><code code="' . htmlentities( $loinc['loinc_num'] )  . '" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC" displayName="' . htmlentities( $loinc['component'] ) . '" /><title>' . htmlentities( $loinc['component'] ) . '</title><text><table border="1"><tbody><tr>';

		// Form heading
		// <th>Immunization</th><th>Date</th></tr>';
		foreach ( $this->loinc_display AS $k => $v ) {
			$buffer .= '<th>' . htmlentities( $k ) . '</th>';
		}
		$buffer .= '</tr>';

		// Form values
		foreach ( $set AS $r ) {
			$buffer .= '<tr>';
			foreach ( $this->loinc_display AS $k => $v ) {
				$buffer .= '<td>' . htmlentities( $r[$v] ) . '</td>';
			}
			$buffer .= '</tr>';
		}

		// Create CDA component footer
		$buffer .= '</section></component>';
		return $buffer;
	} // end method CdaComponent

	// Method: GetList
	//
	//	ACL controlled wrapper around <qualified_query> which allows lists of
	//	records to be pulled for the current component.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$items - (optional) Limit on number of items returned. Defaults to 10.
	//
	//	$conditional - (optional) Hash of key => value conditions for the
	//	query.
	//
	// Returns:
	//
	//	Array of hashes containing records.
	//
	// SeeAlso:
	//	<qualified_query>
	//
	public function GetList ( $patient, $items = 10, $conditional = NULL ) {
		// ACL
		//if (!$this->acl_access ( 'view', $patient ) ) {
		//	syslog(LOG_INFO, "ACL: Access denied for $patient (".get_class($this).")");
		//	return false;
		//}

		// Wrapper
		return $this->qualified_query ( $patient, $items, $conditional );
	} // end method GetList

	// Method: qualified_query
	//
	//	Internal method allowing queries based on the "summary" view. This
	//	method is protected, and cannot be called externally.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$items - (optional) Maximum number of items to return.
	//
	//	$conditional - (optional) Hash of additional items to check for. Key
	//	is field name, value is value to look for.
	//
	// Return:
	//
	//	Array of hashes.
	//
	protected function qualified_query ( $patient, $items = NULL, $conditional = NULL ) {
		if (is_array($this->summary_query_link)) {
			foreach ($this->summary_query_link AS $my_k => $my_v) {
				// Format: field => table_name
				$_from[] = "LEFT OUTER JOIN ${my_v} ON ${my_v}.id = ".$this->table_name.'.'.$my_k;
			}
			$this->summary_query[] = $this->table_name.'.id AS __actual_id';
		}

		// Form conditional clause, if it exists
		if ( is_array ($conditional) ) {
			foreach ($conditional AS $k => $v) {
				$c[] = "`".$GLOBALS['sql']->escape($k)."` = ".$GLOBALS['sql']->quote($v);
			}
			$conditional_clause = join ( ' AND ', $c );
		}

		// get last $items results
		$query = "SELECT *".
			( (count($this->summary_query)>0) ? 
			",".join(",", $this->summary_query)." " : " " ).
			"FROM ".$this->table_name." ".
			( is_array($this->summary_query_link) ? " ".join(',',$_from).' ' : ' ' ).
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			($this->summary_conditional ? 'AND '.$this->summary_conditional.' ' : '' ).
			($conditional ? 'AND ( '.$conditional_clause.' ) ' : '' ).
			"ORDER BY ".( (is_array($this->summary_query_link) and $this->summary_order_by == 'id') ? $this->table_name.'.' : '' ).$this->summary_order_by." DESC ".
			( $items ? "LIMIT ".addslashes($items) : '' );

		// Return full hash
		return $GLOBALS['sql']->queryAll( $query );
	} // end protected function qualified_query

	// Method: GetRecentRecord
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
	public function GetRecentRecord ( $patient, $recent_date = NULL ) {
		if ( $recent_date ) {
			$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
			$rDate = $s->ImportDate( $recent_date );
		} else {
			$rDate = NULL;
		}
		$query = "SELECT * FROM `".$this->table_name."` ".
			"WHERE `".$this->patient_field."` = '".addslashes($patient)."' ".
			( $rDate ? " AND `".$this->date_field."` <= '".addslashes($rDate)."' " : "" ).
			"ORDER BY ".$this->date_field." DESC, id DESC";
		$res = $GLOBALS['sql']->queryRow( $query );
		return $res;
	} // end method GetRecentRecord

	// Method: RecentDates
	//
	//	List of previous date fields for this distinct record date.
	//
	// Parameters:
	//
	//	$patient - Id of patient record
	//
	//	$param - (optional) Qualifying parameter
	//
	// Returns:
	//
	//	Array of arrays, [ k, v ]
	//
	public function RecentDates ( $patient, $param = NULL ) {
		$query = "SELECT DISTINCT(".$this->date_field.") AS dt, p.summary AS summary FROM ".$this->table_name." t LEFT OUTER JOIN patient_emr p ON ( p.module = ".$GLOBALS['sql']->quote( $this->table_name )." AND p.oid = t.id ) WHERE t.".$this->patient_field." = ".$GLOBALS['sql']->quote( $patient )." ORDER BY ".$this->date_field." DESC LIMIT 10";
		$res = $GLOBALS['sql']->queryAll( $query ); 
		foreach ( $res AS $r ) {
			$result[] = array ( "$r[dt] - $r[summary]", $r[dt] );
		}
		return $result;
	} // end method RecentDates

	// Method: PrintSinglePDF
	//
	//	Print a single PDF file to a printer.
	//
	// Parameters:
	//
	//	$id - Record id
	//
	//	$printer - String, name of printer
	//
	public function PrintSinglePDF ( $id, $printer ) {
		$render = $this->RenderToPDF( $id );
		$p = CreateObject( 'org.freemedsoftware.core.PrinterWrapper' );
		$p->driver->PrintFile( $printer, $render );
		unlink( $render );
		return true;
	} // end method PrintSinglePDF

	// Method: RenderHtmlView
	//
	// Parameters:
	//
	//	$id - ID for the record associated with the current module.
	//
	public function RenderHtmlView( $id ) {
		// Sanity checking
		if ((int) $id <= 0) { return false; }

		// Actual renderer for formatting array
		if ($this->patient_field) {
			// If this is an EMR module with additional
			// fields, import them
			$query = "SELECT *".
				( (count($this->summary_query)>0) ? 
				",".join(",", $this->summary_query)." " : " " ).
				"FROM ".$this->table_name." ".
				"WHERE id='".addslashes($id)."'";
			$rec = $GLOBALS['sql']->queryRow($query);
		} else {
			$rec = $GLOBALS['sql']->get_link( $t, $id );
		} // end checking for summary_query

		// Make sure we import everything but id from patient
		$pat = $GLOBALS['sql']->get_link( 'patient', $rec[$this->patient_field] );
		unset ($pat['id']);
		foreach ($pat AS $k => $v) {
			if (!isset($rec[$k])) { $rec[$k] = $v; }
		}

		$template = $this->table_name;
		$basedir = PHYSICAL_LOCATION;
		if (!file_exists("$basedir/data/emrview/${template}.tpl")) {
			print "$basedir/data/emrview/$template.tpl<br/>\n";
			die("Could not load $template template.");
		}

		// Initialize Smarty engine, with caching
		if (!is_object($this->smarty)) {
			$this->smarty = CreateObject( 'net.php.smarty.Smarty' );
			$this->smarty->setTemplateDir( "$basedir/data/emrview/" );
			$this->smarty->setCompileDir( "$basedir/data/cache/smarty/templates_c/" );
			$this->smarty->setCacheDir( "$basedir/data/cache/smarty/cache/" );
			$this->smarty->left_delimiter = '<!--{';
			$this->smarty->right_delimiter = '}-->';
		}

		// Load rec data
		$this->smarty->assign( 'rec', $rec );
		if (is_array($rec)) {
			foreach ($rec AS $k => $v) {
				$this->smarty->assign( $k, $v );
			}
		}

		// Render
		$this->smarty->display( "${template}.tpl" );
		die();
	} // end method RenderHtmlView

	// Method: RenderSinglePDF
	//
	//	Creates a single record PDF which is returned with headers to
	//	the browser.
	//
	// Parameters:
	//
	//	$id - ID for the record associated with the current module.
	//
	// Returns:
	//
	//	PDF document with headers et al
	//
	public function RenderSinglePDF ( $id ) {
		Header('Content-type: application/x-freemed-print-pdf');
		Header('Content-Disposition: inline; filename="'.mktime().'.pdf"');
		$file = $this->RenderToPDF( $id );
		if (!file_exists($file)) { die ('no file'); }
		readfile ( $file );
		unlink ( $file );
		die();
	} // end method RenderSinglePDF
	
	// Method: RenderToPDF
	//
	//	Render internal record for printing directly to a PDF file.
	//
	// Parameters:
	//
	//	$record - Record id
	//
	//	$texonly - (optional) Boolean, whether to generate only the TeX source
	//	instead of the destination PDF. Defaults to false.
	//
	// Returns:
	//
	//	File name of destination PDF file
	//
	public function RenderToPDF ( $record, $texonly = false ) {
		// Sanity checking
		if ($record+0 <= 0) { return false; }

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
				$rec = $GLOBALS['sql']->queryRow($query);
			} else {
				$rec = $GLOBALS['sql']->get_link( $t, $record );
			} // end checking for summary_query

			// Make sure we import everything but id from patient
			$pat = $GLOBALS['sql']->get_link( 'patient', $rec[$this->patient_field] );
			unset ($pat['id']);
			foreach ($pat AS $k => $v) {
				if (!isset($rec[$k])) { $rec[$k] = $v; }
			}

			if ($texonly) {
				return $TeX->RenderFromTemplate(
					$this->print_template,
					$rec
				);
			}
			$TeX->SetBuffer($TeX->RenderFromTemplate(
				$this->print_template,
				$rec
			));
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
				$buffer .= $this->RenderTeX ( $TeX, $v );
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
			$rec = $GLOBALS['sql']->queryRow($query);

			// Handle templating elsewhere
			return $TeX->RenderFromTemplate( $my_template, $rec );
		}
	} // end method RenderTeX

} // end class EMRModule

?>
