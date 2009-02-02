<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class ProgressNotes extends EMRModule {

	var $MODULE_NAME = "Progress Notes";
	var $MODULE_VERSION = "0.3.1";
	var $MODULE_DESCRIPTION = "FreeMED Progress Notes allow physicians and providers to track patient activity through SOAPIER style notes.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "33cd25ad-48e2-4d5a-9652-6b8104fceeb2";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name   = "Progress Notes";
	var $table_name    = "pnotes";
	var $patient_field = "pnotespat";
	var $widget_hash   = "##pnotesdt## ##pnotesdescrip##";
	var $date_field    = 'pnotesdt';

	var $print_template = 'progress_notes';

	var $variables = array (
		'pnotesdt',
		'pnotesdtadd',
		'pnotesdtmod',
		'pnotespat',
		'pnotesdescrip',
		'pnotesdoc',
		'pnoteseoc',
		'pnotes_S',
		'pnotes_O',
		'pnotes_A',
		'pnotes_P',
		'pnotes_I',
		'pnotes_E',
		'pnotes_R',
		'pnotessbp',
		'pnotesdbp',
		'pnotestemp',
		'pnotesheartrate',
		'pnotesresprate',
		'pnotesweight',
		'pnotesheight',
		'pnotesbmi',
		'iso',
		'user',
		'locked'
	);

	public function __construct ( ) {
		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Provider")    =>	"pnotesdoc:physician",
			__("Description") =>	"pnotesdescrip"
		);
		$this->summary_options |= SUMMARY_VIEW | SUMMARY_LOCK | SUMMARY_PRINT | SUMMARY_DELETE;
		$this->summary_query = array("DATE_FORMAT(pnotesdt, '%m/%d/%Y') AS my_date");
		$this->summary_order_by = 'pnotesdt DESC,id';

		$this->list_view = array (
			__("Date")        => "pnotesdt",
			__("Description") => "pnotesdescrip"
		);

		// Set associations
		$this->_SetAssociation('EmrModule');
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'pnoteseoc');

		$this->acl = array ( 'emr' );

		// Call parent constructor
		parent::__construct( );
	} // end constructor ProgressNotes

	// Method: CalculateBMI
	public function CalculateBMI ( $height, $weight ) {
		if ($height > 0) {
			// English is ( W / H^2 ) * 703
			// Metric  is ( W / H^2 )
			$bmi = ( $weight / ( pow( $height, 2 ) ) ) * 703;

			// And we'll round off to two decimal places
			$bmi = bcadd($bmi, 0, 2);
			return $bmi;
		}
	} // end method CalculateBMI

	// Method: NoteForDate
	//
	//	Determines if a progress note was entered for a particular
	//	appointment.
	//
	// Parameters:
	//
	//	$patient - ID for patient record
	//
	//	$date - Date to be queried
	//
	// Returns:
	//
	//	Boolean, whether or not a note exists.
	//
	public function NoteForDate ( $patient, $date ) {
		$q = "SELECT COUNT(id) AS my_count ".
			"FROM ".$this->table_name." WHERE ".
			"pnotespat = '".addslashes($patient)."' AND ".
			"pnotesdt = '".addslashes($date)."'";
		$my_count = $GLOBALS['sql']->queryOne($q);
		if ($my_count > 0) {
			return true;
		} else {
			return false;
		}
	} // end method NoteForDate

	protected function add_pre ( &$data ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$data['pnotesdt'] = $s->ImportDate( $data['pnotesdt'] );
        	$data['pnotesdtadd'] = date('Y-m-d');
        	$data['pnotesdtmod'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$data['pnotesdt'] = $s->ImportDate( $data['pnotesdt'] );
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end of class ProgressNotes

register_module ("ProgressNotes");

?>
