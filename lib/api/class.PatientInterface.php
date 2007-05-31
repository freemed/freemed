<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.api.PatientInterface
//
//	Class to access patient functions.
//
class PatientInterface {

	public function __constructor ( ) { }

	// Method: CheckForDuplicatePatient
	//
	//	Check for duplicate patients existing based on provided criteria.
	//
	// Parameters:
	//
	//	$criteria - Hash.
	//	* ptlname - Last name
	//	* ptfname - First name
	//	* ptmname - Middle name
	//	* ptdob - Date of birth
	//
	// Returns:
	//
	//	False if there are no matches, the patient id if there are.
	//
	public function CheckForDuplicatePatient ( $criteria ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		$q = "SELECT * FROM patient p WHERE ".
			"ptlname=".$GLOBALS['sql']->quote( $criteria['ptlname'] )." AND ".
			"ptfname=".$GLOBALS['sql']->quote( $criteria['ptfname'] )." AND ".
			"ptmname=".$GLOBALS['sql']->quote( $criteria['ptmname'] )." AND ".
			"ptdob=".$GLOBALS['sql']->quote( $s->ImportDate($criteria['ptdob']) )." AND ".
			"ptarchive=0";
		$res = $GLOBALS['sql']->queryAll( $q );
		if ( count ( $res ) > 0 ) {
			return $res[0]['ptid'];
		}
	} // end method CheckForDuplicatePatient

	// Method: EmrAttachmentsByPatient
	//
	//	Get all patient attachments. Has support for caching.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	// Returns:
	//
	//	Array of hashes.
	//	* patient
	//	* module
	//	* oid
	//	* annotation
	//	* summary
	//	* stamp
	//	* date_mdy
	//	* type
	//	* module_namespace
	//	* locked
	//	* id
	//
	public function EmrAttachmentsByPatient ( $patient ) {
		static $_cache;
		if ( !isset( $_cache[$patient] ) ) {
			$query = "SELECT p.patient AS patient, p.module AS module, p.oid AS oid, p.annotation AS annotation, p.summary AS summary, p.stamp AS stamp, DATE_FORMAT(p.stamp, '%m/%d/%Y') AS date_mdy, m.module_name AS type, m.module_class AS module_namespace, p.locked AS locked, p.id AS id FROM patient_emr p LEFT OUTER JOIN modules m ON m.module_table = p.module WHERE p.patient = ".$GLOBALS['sql']->quote( $patient )." AND m.module_hidden = 0";
			$_cache[$patient] = $GLOBALS['sql']->queryAll( $query );
		}
		return $_cache[$patient];
	} // end method EmrAttachmentsByPatient

	// Method: EmrAttachmentsByPatientTable
	//
	//	Get all patient EMR attachments by table name.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$table - Table name
	//
	// Returns:
	//
	//	Array of hashes.
	//
	// SeeAlso:
	//
	//	<EmrAttachmentsByPatient>
	//
	public function EmrAttachmentsByPatientTable ( $patient, $table ) {
		$raw = EmrAttachmentsByPatient ( $patient );
		foreach ( $raw AS $r ) {
			if ( $r['module'] == $table ) {
				$result[] = $r;
			}
		}
		return $result;
	} // end method EmrAttachmentsByPatientTable

	// Method: EmrModules
	//
	//	Form list of presentable EMR modules.
	//
	// Parameters:
	//
	//	$part - Piece of name, to be used in completion pick widgets.
	//
	//	$same - (optional) Boolean, whether key and value should be the same,
	//	defaults to false.
	//
	// Returns:
	//
	//	Hash of values.
	//	* module_name - Textual name of a module
	//	* module_class - Class of the module in question
	//
	public function EmrModules ( $part, $same = false ) {
		$query = "SELECT module_name, module_class FROM modules WHERE FIND_IN_SET( module_handlers, 'EmrSummary') AND module_hidden = 0 ".( $part ? " AND module_name LIKE '%".$GLOBALS['sql']->escape($part)."%'" : '' )." ORDER BY module_name";
		foreach ( $GLOBALS['sql']->queryAll( $query ) AS $r ) {
			//$return[$r['module_class']] = $r['module_name'];
			$return[] = $same ? array ( $r['module_name'], $r['module_name'] ) : $return[] = array ( $r['module_name'], $r['module_class'] );
		}
		return $return;
	} // end method EmrModules

	// Method: Search
	//
	// Parameters:
	//
	//	$criteria - Hash containing one or more of the following qualifiers:
	//	* ssn - Social security number
	//	* age - Age in years
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function Search ( $criteria ) {
		if (!count($criteria)) { return array(); }

		foreach ($criteria AS $k => $v) {
			switch ($k) {
				case 'ssn':
				if ($v) { $c[] = "p.ptssn LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'age':
				if ($v) { $c[] = "CAST( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 AS UNSIGNED INTEGER) = ".$GLOBALS['sql']->quote($v+0); }
				break;

				default: break;
			}
		} // end foreach

		if (!count($c)) { return array(); }
		$query = "SELECT p.ptlname AS last_name, p.ptfname AS first_name, p.ptmname AS middle_name, p.ptid AS patient_id, CAST( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 AS UNSIGNED INTEGER) AS age, p.ptdob AS date_of_birth, p.id AS id FROM patient p WHERE ".join(' AND ', $c)." ORDER BY p.ptlname, p.ptfname, p.ptmname LIMIT 20";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method Search

	// Method: PatientInformation
	//
	//	Basic patient information for a single patient. Useful for summary
	//	screens and other informational displays.
	//
	// Parameters:
	//
	//	$id - Patient id
	//
	// Returns:
	//
	//	Hash. Contains:
	//	* patient_name
	//	* patient_id
	//	* date_of_birth
	//	* date_of_birth_mdy
	//	* age
	//	* csz
	//
	public function PatientInformation( $id ) {
		$q = "SELECT CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname ) AS patient_name, p.ptid AS patient_id, p.ptdob AS date_of_birth, DATE_FORMAT(p.ptdob, '%m/%d/%Y') AS date_of_birth_mdy, CAST( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 AS UNSIGNED INTEGER) AS age, CONCAT( p.ptcity, ', ', p.ptstate, ' ', p.ptzip ) AS csz, p.* FROM patient p WHERE p.id = " . ( $id + 0 );
		return $GLOBALS['sql']->queryRow( $q );
	} // end method PatientInformation

	// Method: TotalInSystem
	//
	//	Get total number of active patients in the system.
	//
	// Returns:
	//
	//	Integer, number of active patients in the system.
	//
	public function TotalInSystem ( ) {
		return $GLOBALS['sql']->queryOne("SELECT COUNT(*) FROM patient WHERE ptarchive=0");
	} // end method TotalInSystem

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
