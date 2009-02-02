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

class Prescription extends EMRModule {

	var $MODULE_NAME    = "Prescription";
	var $MODULE_VERSION = "0.4.0";
	var $MODULE_DESCRIPTION = "The prescription module allows prescriptions to be written for patients from any drug in the local formulary or in the Multum drug database (if access to that database is available.";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "956beba2-9fbe-4674-93d1-c38ad3e6f9f1";

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
			"rxdrugmultum",
			"rxsize",
			"rxform",
			"rxdosage",
			"rxquantity",
			"rxquantityqual",
			"rxunit",
			"rxinterval",
			"rxpatient",
			"rxsubstitute",
			"rxrefills",
			"rxrefillinterval",
			"rxperrefill",
			"rxdx",
			"rxcovstatus",
			"rxnote",
			"rxsig",
			"rxorigrx",
			"locked",
			"user"
		);
		$this->_SetAssociation( 'EmrModule' );
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$data['rxdtadd'] = date('Y-m-d');
		$data['rxdtmod'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
		$data['locked'] = 0;
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

	// Method: GetDistinctRx
	//
	//	Retrieve all prescriptions for a patient withoout duplicates.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	// Returns:
	//
	//	Array of hashes:
	//	* rx - RX description
	//	* sig - RX signature
	//	* often - RX interval
	//	* id - RX id
	//
	public function GetDistinctRx ( $patient ) {
		$q = "SELECT CONCAT( r.rxdrug, ' ', r.rxform, ' ', r.rxdosage, ' ', IFNULL(ps.product_strength_description, '') ) AS rx, r.rxsig AS sig, r.rxinterval AS often, r.id AS id FROM rx r LEFT OUTER JOIN multum_product_strength ps ON ps.product_strength_code = r.rxunit WHERE r.rxpatient = ".$GLOBALS['sql']->quote( $patient )." AND ( r.rxorigrx = 0 OR ISNULL( r.rxorigrx ) )";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetDistinctRx

} // end class Prescription

register_module ("Prescription");

?>
