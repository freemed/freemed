<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

class PatientInterface {

	public function __constructor ( ) { }

	// Method: picklist
	//
	//	Generate associative array of patient table id to patient
	//	text based on criteria given.
	//
	// Parameters:
	//
	//	$string - String containing text parameters.
	//
	//	$limit - (optional) Limit number of results. Defaults to 10.
	//
	//	$inputlimit - (optional) Lower limit number of digits which
	//	have to be entered in order for this routine to return a
	//	valid value. Defaults to 2.
	//
	// Returns:
	//
	//	Associative array.
	//	* key - Patient table id key
	//	* value - Text representing patient record identifying info.
	//
	public function picklist ( $string, $limit = 10, $inputlimit = 2 ) {
		if (strlen($string) < $inputlimit) { return false; }

		$criteria = addslashes( $string );
		if (!(strpos($criteria, ',') === false)) {
			list ($last, $first) = explode( ',', $criteria);
		} else {
			if (!(strpos($criteria, ' ') === false)) {
				list ($first, $last) = explode( ' ', $criteria );
			} else {
				$either = $criteria;
			}
		}
		$last = trim( $last );
		$first = trim( $first );
		$either = trim( $either );

		if ($first and $last) {
			$q[] = "( ptlname LIKE '".addslashes($last)."%' AND ".
				" ptfname LIKE '".addslashes($first)."%' )";
		} elseif ($first) {
                	$q[] = "ptfname LIKE '".addslashes($first)."%'";
		} elseif ($last) {
                	$q[] = "ptlname LIKE '".addslashes($last)."%'";
		} else {
			$q[] = "ptfname LIKE '".addslashes($either)."%'";
			$q[] = "ptlname LIKE '".addslashes($either)."%'";
			$q[] = "ptid LIKE '".addslashes($either)."%'";
		}

		$query = "SELECT * FROM patient WHERE ( ".join(' OR ', $q)." ) ".
			"AND ( ISNULL(ptarchive) OR ptarchive=0 )";
		syslog(LOG_INFO, "PICK| $query");
		$result = $GLOBALS['sql']->queryAll( $query );
		if (count($result) < 1) { return array (); }
		$count = 0;
		foreach ($result AS $r) {
			$count++;
			if ($count < $limit) {
				$_obj = CreateObject('org.freemedsoftware.core.Patient', $r);
				$return[$r['id']] = trim(stripslashes($_obj->to_text()));

			}
		}
		return $return;
	} // end public function picklist

	// Method: ProceduresToBill
	//
	//	Determine list of procedures to bill, optionally by patient.
	//
	// Parameters:
	//
	//	$patient - (optional) Patient id to get, otherwise does not qualify
	//
	// Return:
	//
	//	Array of procedure ids
	//
	public function ProceduresToBill ( $patient = 0 ) {
		$_obj = CreateObject('org.freemedsoftware.core.Patient', $patient+0);
		return $_obj->get_procedures_to_bill ( $patient ? true : false );
	} // end public function ProceduresToBill

	// Method: ToText
	//
	//	Get a textual representation of a patient
	//
	// Parameters:
	//
	//	$patient - Database id of patient
	//
	// Returns:
	//
	//	String representation of patient.
	//
	public function ToText ( $patient ) {
		$_obj = CreateObject('org.freemedsoftware.core.Patient', $patient);
		return $_obj->to_text( );
	} // end public function ToText

} // end class PatientInterface
