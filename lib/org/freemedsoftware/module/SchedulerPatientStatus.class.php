<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

class SchedulerPatientStatus extends EMRModule {

	var $MODULE_NAME = "Scheduler Patient Status";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "f58a3945-4b47-42de-b74c-6e43608dd98e";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Scheduler Patient Status";
	var $table_name = 'scheduler_status';
	var $patient_field = 'cspatient';
	var $variables = array (
		'cspatient',
		'csstatus',
		'csnote',
		'csuser',
		'csappt'
	);

	public function __construct ( ) {
		// __("Scheduler Patient Status")
		$this->summary_vars = array (
			__("Date/Time") => 'csstamp',
			__("Status") => 'sname',
			__("Note") => 'csnote'
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_DELETE | SUMMARY_NOANNOTATE;
		$this->summary_query_link = array ( 'csstatus' => 'schedulerstatustype' );

		// call parent constructor
		parent::__construct( );
	} // end constructor SchedulerPatientStatus

	protected function add_pre ( &$data ) {
		if (!is_object($GLOBALS['this_user'])) { $GLOBALS['this_user'] = CreateObject('org.freemedsoftware.core.User'); }
		$data['csstamp'] = SQL__NOW;
		$data['csuser'] = $GLOBALS['this_user']->user_number;
	}

	// Method: getPatientStatus
	//
	//	Get current patient status.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$appt - Appointment id
	//
	// Returns:
	//
	//	Array (
	//		Numeric id describing current status
	//		Age in seconds
	//	)
	//
	public function getPatientStatus ( $patient, $appt) {
		static $_cache;

		if (!isset($_cache[$appt])) {
			$q = "SELECT *,(NOW()-csstamp) AS age FROM ".$this->table_name." WHERE cspatient = '".addslashes($patient)."' AND csappt = '".addslashes($appt)."' ORDER BY csstamp DESC LIMIT 1";
			$res = $GLOBALS['sql']->queryRow($q);
			if (!count($res)) {
				$_cache[$appt] = false;
			}
			$_cache[$appt] = $res;;
		}

		return array ( $_cache[$appt]['csstatus'], $_cache[$appt]['age'] );
	} // end method getPatientStatus

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		//if (!version_check($version, '0.2')) {
		//}	
	} // end method _update

} // end class SchedulerPatientStatus

register_module ("SchedulerPatientStatus");

?>
