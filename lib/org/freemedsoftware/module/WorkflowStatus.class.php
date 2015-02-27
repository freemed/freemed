<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class WorkflowStatus extends SupportModule {

	var $MODULE_NAME = "Workflow Status";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "f106983a-dc3e-4e10-8668-f379b0288f6e";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Workflow Status";
	var $table_name  = "workflow_status";
	var $order_field = "stamp";

	var $widget_hash = "stamp";
	
	var $variables = array (
		"stamp",
		"patient",
		"user",
		"status_type",
		"status_completed"
	);

	public function __construct ( ) {
		// __("Workflow Status")

		$this->_SetHandler( 'Dashboard', get_class( $this ) );
	
		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$date['stamp'] = '';
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: StatusMapForDate
	//
	//	Form a status "map" for the date requested
	//
	// Parameters:
	//
	//	$date - Date in question
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function StatusMapForDate( $date ) {
		freemed::acl_enforce( 'scheduling', 'read' );
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$u = freemed::user_cache();
		$q = "CALL patientWorkflowStatusByDate( ". $GLOBALS['sql']->quote( $s->ImportDate( $date ) ) .", " . $GLOBALS['sql']->quote( $u->getManageConfig( 'workflow_status_age' ) + 0 ) . " )";
		return $GLOBALS['sql']->queryAllStoredProc( $q );
	} // end method StatusMapForDate

	// Method: OverallStatusforDate
	//
	//	Create single status indicator for the day, so that it is possible
	//	to gauge whether any items still need to be completed for the day.
	//
	// Parameters:
	//
	//	$date - Date ( defaults to today )
	//
	// Returns:
	//
	//	Boolean.
	//
	public function OverallStatusForDate( $date ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$dt = $s->ImportDate( $date );
		$map = $this->StatusMapForDate( $dt ? $dt : date('Y-m-d') );
		$status = true;
		foreach ( $map AS $e ) {
			if ( $e['patient_id'] > 0 ) {
				foreach ( $e AS $k => $v ) {
					switch ( $k ) {
						case 'patient': case 'patient_id': case 'date_of':
						break;
	
						default:
						if ( ! $v ) { $status = false; }
						break;
					}
				}
			}
		}
		return $status;
	} // end method OverallStatusForDate

	// Method: SetStatus
	//
	//	Set workflow status for a patient, module and date
	//
	// Parameters:
	//
	//	$patient - Patient id.
	//
	//	$date - Applicable date.
	//
	//	$module - Module class name.
	//
	//	$status - Boolean, true or false.
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function SetStatus ( $patient, $date, $module, $status ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$dt = $s->ImportDate( $date );
		$q = "CALL patientWorkflowUpdateStatus ( ".
			$GLOBALS['sql']->quote( $patient + 0 ). ", ".
			$GLOBALS['sql']->quote( $dt ).", ".
			$GLOBALS['sql']->quote( $module ).", ".
			$GLOBALS['sql']->quote( $status ).", ".
			$GLOBALS['sql']->quote( freemed::user_cache()->user_number )." );";
		$result = $GLOBALS['sql']->query( $q );
		return (bool) $result;
	} // end method SetStatus

} // end class WorkflowStatus

register_module ("WorkflowStatus");

?>
