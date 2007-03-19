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

class PrescriptionModule extends EMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_VERSION = "0.4.0";
	var $MODULE_DESCRIPTION = "
		The prescription module allows prescriptions to be written 
		for patients from any drug in the local formulary or in the 
		Multum drug database (if access to that database is 
		available.";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID = "956beba2-9fbe-4674-93d1-c38ad3e6f9f1";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Prescription";
	var $table_name     = "rx";
	var $patient_field  = "rxpatient";
	var $date_field	    = "rxdtfrom";
	var $widget_hash    = "##rxdtfrom## ##rxdrug## ##rxform## (##rxrefills##)";

	var $print_template = 'rx';

	public function __construct () {
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW |
			SUMMARY_LOCK | SUMMARY_PRINT | SUMMARY_DELETE;

		$this->summary_vars = array (
			__("Date From") => "rxdtfrom",
			__("Drug") => "_drug",
			//__("Dosage") => "_dosage",
			__("Disp") => "_dispensed",
			//__("Dispensed") => "_dispensed",
			__("Sig")  => "rxdosage",
			__("By")   => "rxphy:physician",
			__("Refills") => "_refills",
			//"Crypto Key" => "rxmd5"
		);
		// Specialized query bits
		$this->summary_query = array (
			"MD5(id) AS rxmd5",
			"CASE rxsize WHEN 0 THEN CONCAT(rxform, ' ', rxdrug) ELSE CASE rxform WHEN 'Spray' THEN CONCAT(rxform, ' ', rxdrug) WHEN 'Unit' THEN rxdrug ELSE CONCAT(rxform, ' ', rxdrug, ' ', rxsize, ' ', rxunit) END END AS _drug",
			"CONCAT(rxsize, ' ', rxunit, ' ', rxinterval) AS _dosage",
			//"CASE rxform WHEN 'Unit' THEN rxquantity WHEN 'Tablets' THEN CONCAT(rxquantity, ' tablets') WHEN 'Spray' THEN CONCAT(rxquantity, ' ', rxunit) WHEN 'Capsules' THEN CONCAT(rxquantity, ' capsules') WHEN 'Container' THEN CONCAT(rxquantity, ' container') WHEN 'Cannister' THEN CONCAT(rxquantity, ' cannister') WHEN 'Bottle' THEN CONCAT(rxquantity, ' bottle') WHEN 'Tube' THEN CONCAT(rxquantity, ' tube') ELSE CONCAT(rxquantity, ' ', IF(rxunit LIKE '%cc%', 'cc', rxunit)) END AS _dispensed",
			"CONCAT(rxquantity, ' ', LCASE(rxform)) AS _dispensed",
			"CASE rxrefills WHEN 99 THEN 'p.r.n' ELSE rxrefills END AS _refills"
		);

		$this->list_view = array (
			__("Date") => "rxdtfrom",
			__("Drug") => "rxdrug",
			__("Dosage") => "_dosage"
		);

		$this->variables = array (
			"rxdtfrom",
			"rxphy",
			"rxdrug",
			"rxsize",
			"rxform",
			"rxdosage",
			"rxquantity",
			"rxunit",
			"rxinterval",
			"rxpatient",
			"rxsubstitute",
			"rxrefills",
			"rxperrefill",
			"rxnote",
			"rxorigrx",
			"locked" => '0',
			"user"
		);
		$this->acl = array ('emr');
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$data['rxdtadd'] = date('Y-m-d');
		$data['rxdtmod'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	} // end method add_pre

	protected function mod_pre ( &$data ) {
		$data['rxdtmod'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	} // end method mod_pre

	function fax_widget ( $varname, $id ) {
		global $sql, ${$varname};
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$p = $GLOBALS['sql']->get_link( 'patient', $r[$this->patient_field] );
		$pharmacy = $GLOBALS['sql']->get_link( 'pharmacy', $p['ptpharmacy'] );
		${$varname} = $pharmacy['phfax'];
		return module_function('pharmacymaintenance',
			'widget',
			array ( $varname, false, 'phfax' )
		);
	} // end method fax_widget

	function recent_text ( $patient, $recent_date = NULL ) {
		// skip recent; need all for this one
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE ".$this->patient_field."='".addslashes($patient)."' ".
			"ORDER BY ".$this->date_field." DESC";
		$res = $GLOBALS['sql']->queryAll($query);
		foreach ( $res AS $r ) {
			$m[] = trim($r['rxdrug'].' '.$r['rxdosage'].' '.$r['rxroute']);
		}
		return @join(', ', $m);
	} // end method recent_text

	// Updates
	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN rxphy INT UNSIGNED AFTER rxdtfrom');
		}
		// Version 0.3.3
		//
		//	Add prescription locking
		//
		if (!version_check($version, '0.3.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN locked INT UNSIGNED AFTER rxnote');
			// Patch existing data to be unlocked
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'locked = \'0\'');
		}

		// Version 0.3.4
		//
		//	Add extra intervals
		//
		if (!version_check($version, '0.3.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxinterval rxinterval ENUM (
				"b.i.d.",
				"t.i.d.",
				"q.i.d.",
				"q. 3h",
				"q. 4h",
				"q. 5h",
				"q. 6h",
				"q. 8h",
				"q.d.",
				"h.s.",
				"q.h.s.",
				"q.A.M.",
				"q.P.M.",
				"a.c.",
				"p.c.",
				"p.r.n."
			)');
		}

		// Version 0.3.5
		//
		//	Change prescription format
		//
		if (!version_check($version, '0.3.5')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxform rxform VARCHAR(32)');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxunit rxunit VARCHAR(32)');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxsize rxsize REAL');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN rxdosage rxdosage VARCHAR(128)');
			$sql->query('UPDATE '.$this->table_name.' '.
				'SET rxdosage = concat(rxdosage, \' \', rxinterval)');
		}

		// Version 0.4.0
		//
		//	Allow repeats of prescriptions ...
		//
		if (!version_check($version, '0.4.0')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN rxorigrx INT UNSIGNED AFTER rxperrefill');
		}
	} // end method _update

} // end class PrescriptionModule

register_module ("PrescriptionModule");

?>
