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

class CurrentProblems extends EMRModule {

	var $MODULE_NAME = "Current Problems";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "435a3c8a-3a10-4212-b3fc-cd6a531cf583";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Current Problems";
	var $patient_field = "ppatient";

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );
	var $date_field = 'id';
	var $table_name = 'current_problems';
	var $order_fields = 'pdate,problem';
	var $widget_hash = '##pdate## ##problem##';

	public function __construct ( ) {
		// __("Current Problems")
		$this->variables = array (
			'problem',
			'ppatient',
			'pdate'
		);
		
		// call parent constructor
		parent::__construct( );
	} // end constructor CurrentProblems

	protected function add_pre ( &$data ) {
		$data['pdate'] = date('Y-m-d');
	}

	protected function mod_pre ( &$data ) {
		$data['pdate'] = date('Y-m-d');
	}

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->order_fields;
		$res = $GLOBALS['sql']->queryAll($query);

		// Get problems, and extract to an array
		$m[] ="\n\nCURRENT PROBLEMS:\n";
		foreach ( $res AS $r ) {
			$m[] = trim($r['junkpdate'].' '.$r['problem']);
		}
		return @join("\n", $m);
	} // end method recent_text

	function _update ( ) {
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.2
		//
		//	Migrated to separate table
		//
		if (!version_check($version, '0.2')) {
			// Create table
			$GLOBALS['sql']->query($GLOBALS['sql']->create_table_query($this->table_name, $this->table_definition, array('id')));

			// Migrate old entries
			$q = $GLOBALS['sql']->query('SELECT ptproblems,id FROM patient WHERE LENGTH(ptproblems) > 3');
			while ($r = $GLOBALS['sql']->fetch_array($q)) {
				$e = sql_expand($r['ptproblems']);
				if (!is_array($e)) { $e = array ( $e ); }
				foreach ($e AS $a) {
					$GLOBALS['sql']->query(
						$GLOBALS['sql']->insert_query(
							$this->table_name,
							array(
								'ppatient' => $r['id'],
								'problem' => $a
							)
						)
					); // end query
				} // end foreach $e
			} // end while
		}
	} // end method _update

} // end class CurrentProblems

register_module ("CurrentProblems");

?>
