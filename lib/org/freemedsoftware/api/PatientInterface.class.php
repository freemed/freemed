<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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
	//	* ptsuffix - Suffix
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
			( $criteria['ptmname'] ? "ptmname=".$GLOBALS['sql']->quote( $criteria['ptmname'] )." AND " : "" ).
			( $criteria['ptsuffix'] ? "ptsuffix=".$GLOBALS['sql']->quote( $criteria['ptsuffix'] )." AND " : "" ).
			( $criteria['ptdob'] ? "ptdob=".$GLOBALS['sql']->quote( $s->ImportDate($criteria['ptdob']) )." AND " : "" ).
			"ptarchive=0";
		$res = $GLOBALS['sql']->queryAll( $q );
		if ( count ( $res ) > 0 ) {
			return $res[0]['ptid'];
		}
	} // end method CheckForDuplicatePatient

	// Method: DxForPatient
	//
	//	Find all diagnoses associated with patients.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	// Returns:
	//
	//	Array of hashes.
	//	* id
	//	* code
	//	* description
	//
	public function DxForPatient( $patient ) {
		$q = "SELECT d.dx AS id, i.icd9code AS code, i.icd9descrip AS description FROM dxhistory d LEFT OUTER JOIN icd9 i ON d.dx=i.id WHERE d.patient = ".( $patient + 0 )." GROUP BY d.dx";
		return $GLOBALS['sql']->queryAll( $q );
	} // end method DxForPatient

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

	// Method: NumericSearch
	//
	//	Search for patients by numeric criteria.
	//
	// Parameters:
	//
	//	$criteria - Hash
	//	* last_name - Last name
	//	* first_name - First name
	//	* year_of_birth - Year for date of birth
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* ptlname - Patient last name
	//	* ptfname - Patient first name
	//	* ptid - Internal practice ID
	//	* id - Patient record ID
	//
	public function NumericSearch ( $criteria ) {
		$q = "SELECT p.ptlname, p.ptfname, p.ptid, p.id FROM patient_keypad_lookup k LEFT OUTER JOIN patient p ON k.patient = p.id WHERE k.archive = 0 AND last_name LIKE '". $GLOBALS['sql']->escape( $criteria['last_name'] )."%' AND first_name LIKE '". $GLOBALS['sql']->escape( $criteria['first_name'] ) ."%' AND year_of_birth = ".$GLOBALS['sql']->quote( $criteria['year_of_birth'] );
		return $GLOBALS['sql']->queryAll( $q );
	} // end method NumericSearch

	// Method: Search
	//
	//	Public patient search engine interface.
	//
	// Parameters:
	//
	//	$criteria - Hash containing one or more of the following qualifiers:
	//	* ptid - Patient ID
	//	* ssn - Social security number
	//	* age - Age in years
	//	* hphone - Home phone number
	//	* wphone - Work phone number
	//	* zip - Zip code
	//	* city - City name
	//	* dmv - Drivers license number
	//	* email - Email address
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function Search ( $criteria ) {
		if (!count($criteria)) { return array(); }

		foreach ($criteria AS $k => $v) {
			switch ($k) {
				case 'hphone':
				case 'wphone':
				case 'ssn':
				case 'dmv':
				case 'email':
				if ($v) { $c[] = "p.pt${k} LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'city':
				if ($v) { $c[] = "pa.city LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'zip':
				if ($v) { $c[] = "pa.postal LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'ptid':
				if ($v) { $c[] = "p.ptid LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'age':
				if ($v) { $c[] = "FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 ) = ".$GLOBALS['sql']->quote($v+0); }
				break;

				default: break;
			}
		} // end foreach

		// Only look for 
		if ( !isset( $criteria['archive'] ) ) { $c[] = "p.ptarchive = 0"; }

		$query = "SELECT p.ptlname AS last_name, p.ptfname AS first_name, p.ptmname AS middle_name, p.ptid AS patient_id, FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 ) AS age, p.ptdob AS date_of_birth, p.id AS id FROM patient p LEFT OUTER JOIN patient_address pa ON p.id = pa.patient WHERE ".join(' AND ', $c)." AND pa.active = 1 ORDER BY p.ptlname, p.ptfname, p.ptmname LIMIT 20";
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
	//	* address_line_1
	//	* address_line_2
	//	* csz
	//
	public function PatientInformation( $id ) {
		$q = "SELECT CONCAT( p.ptlname, ', ', p.ptfname, ' ', p.ptmname ) AS patient_name, p.ptid AS patient_id, p.ptdob AS date_of_birth, DATE_FORMAT(p.ptdob, '%m/%d/%Y') AS date_of_birth_mdy, FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365) AS age, pa.line1 AS address_line_1, pa.line2 AS address_line_2, CONCAT( pa.city, ', ', pa.stpr, ' ', pa.postal ) AS csz, p.* FROM patient p LEFT OUTER JOIN patient_address pa ON ( pa.patient = p.id AND pa.active = TRUE ) WHERE p.id = " . ( $id + 0 ) . " GROUP BY p.id";
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
                	$q[] = "ptid LIKE '".addslashes($first)."%'";
		} elseif ($last) {
                	$q[] = "ptlname LIKE '".addslashes($last)."%'";
                	$q[] = "ptid LIKE '".addslashes($last)."%'";
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
	//	$full - (optional) Boolean, full information string. If true then
	//	contains DOB and patient ID. Defaults to true.
	//
	// Returns:
	//
	//	String representation of patient.
	//
	public function ToText ( $patient, $full = true ) {
		$_obj = CreateObject('org.freemedsoftware.core.Patient', $patient);
		return ( $full ? $_obj->to_text( ) : $_obj->fullName( ) );
	} // end public function ToText

} // end class PatientInterface

?>
