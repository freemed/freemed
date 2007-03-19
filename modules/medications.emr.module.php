<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

class Medications extends EMRModule {

	var $MODULE_NAME = "Medication";
	var $MODULE_VERSION = "0.3";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "11644a0c-9efb-4db2-857f-3e4d86b1b2ea";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Medications";
	var $table_name = 'medications';
	var $patient_field = 'mpatient';
	var $date_field = 'mdate';

	public function __construct ( ) {
		// __("Medications")

		$this->variables = array (
			'mdrug',
			'mdosage',
			'mroute',
			'mpatient',
			'mdate',
			'user'
		);

		$this->summary_vars = array (
			__("Drug") => 'mdrug',
			__("Dosage") => 'mdosage'
		);
		$this->summary_options = SUMMARY_DELETE;
		$this->summary_order_by = 'mdrug';

		// call parent constructor
		parent::__construct( );
	} // end constructor Medications

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->date_field." DESC";
		$res = $GLOBALS['sql']->queryAll($query);
	        $m[] = "\n\nMEDICATIONS:\n";
		foreach ( $res AS $r ) {
			$m[] = trim($r['mdrug'].' '.$r['mdosage'].' '.$r['mroute']);
		}
		return @join("\n", $m);
	} // end method recent_text

	protected function add_pre ( &$data ) {
		$data['mdate'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class Medications

register_module ("Medications");

?>
