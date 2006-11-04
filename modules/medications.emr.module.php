<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class Medications extends EMRModule {

	var $MODULE_NAME = "Medications";
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

	protected function add_pre ( &$data ) {
		$data['mdate'] = date('Y-m-d');
	}

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

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		// Version 0.3
		//
		//	Migrated to seperate table ...
		//
		if (!version_check($version, '0.3')) {
			// Create new table
			$sql->query($sql->create_table_query($this->table_name, $this->table_definition, array('id')));
			// Migrate old entries
			$q = $sql->query("SELECT ptquickmeds,id FROM patient WHERE LENGTH(ptquickmeds) > 3");
			if ($sql->results($q)) {
				while ($r = $sql->fetch_array($q)) {
					$e = sql_expand($r['ptquickmeds']);
					foreach ($e AS $a) {
						$sql->query($sql->insert_query(
							$this->table_name,
							array(
								'mdrug' => $a,
								'mdosage' => '',
								'mroute' => '',
								'mpatient' => $r['id']	
							)
						));
					} // end foreach entry
				} // end loop through patient entries
			} // end checking for results
		}	
	} // end method _update

} // end class Medications

register_module ("Medications");

?>
