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

class Allergies extends EMRModule {

	var $MODULE_NAME = "Allergies";
	var $MODULE_VERSION = "0.2.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "e58a3f17-817f-4444-b573-c8827fa38a16";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Allergies";
	var $table_name = 'allergies';
	var $patient_field = 'patient';
	var $date_field = 'reviewed';
	var $widget_hash = '##allergy## (##severity##)';

	public function __construct ( ) {
		// __("Allergies")

		$this->variables = array (
			'allergy',
			'severity',
			'patient',
			'reviewed' => SQL__NOW
		);

		$this->summary_vars = array (
			__("Allergy") => 'allergy',
			__("Reaction") => 'severity',
			__("Reviewed") => '_reviewed'
		);
		$this->summary_query = array (
			"DATE_FORMAT(reviewed, '%m/%d/%Y') AS _reviewed"
		);
		$this->summary_options = SUMMARY_DELETE;

		// call parent constructor
		parent::__construct();
	} // end constructor Allergies

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->date_field." DESC";
		$res = $GLOBALS['sql']->queryAll( $query );
		foreach ( $res AS $r ) {
			$m[] = trim($r['allergy']).' ('.trim($r['severity']).')';
		}
		return @join(', ', $m);
	} // end method recent_text

} // end class Allergies

register_module ("Allergies");

?>
