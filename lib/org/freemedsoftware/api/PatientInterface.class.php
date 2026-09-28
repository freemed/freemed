<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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
LoadObjectDependency('org.freemedsoftware.core.SqlIdent');
LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist');

class PatientInterface {

	public function __constructor ( ) { }

	// Method: _allowlist_gate
	//
	//	Resolve the concrete relay method string that a module_function()
	//	dispatch in this class is about to perform and run it through the
	//	relay's shared decision point.
	//
	//	Same mechanism and same scope as ModuleInterface::_allowlist_gate
	//	(Task 2.8 fix round 2, R32): module_function($module, $literal)
	//	performs `org.freemedsoftware.module.<$module>.<$literal>`, which is
	//	exactly the relay method string that would reach the same method, so
	//	the string the gate sees IS the string the relay would have seen. The
	//	inner scope (third argument TRUE) is the scope the pre-2.8 code used
	//	for re-dispatched calls; it is inert in practice here (no `module.*`
	//	string can be inside the shipped never-allow namespace
	//	'org.freemedsoftware.core.'), but the class name is caller-chosen, so
	//	it is the honest scope.
	//
	//	Reachability (measured, fix round 3): the ONE dispatch this gate
	//	covers is MoveEmrAttachments' additional_move call. In this tree that
	//	dispatch is NOT reachable, for two pre-existing reasons that are
	//	independent of the gate and are recorded for the follow-on work
	//	(Task 4.3), not fixed here:
	//	  1. `$patient` in MoveEmrAttachments is an undefined local, so the
	//	     resolve query is `... WHERE p.patient = NULL` and never matches a
	//	     row (quote(NULL) is 'NULL'; `x = NULL` is never TRUE). $resolve is
	//	     empty, SqlIdent::name() refuses the statement and the loop
	//	     `continue`s BEFORE the dispatch. Measured, shipped data:
	//	     syslog 'PatientInterface::MoveEmrAttachments| refusing update:
	//	     module_table=NULL patient_field=NULL are not valid SQL identifiers'.
	//	  2. Even with a matching row, freemed::module_get_meta($class,
	//	     'patient_field') returns false for every registered module (it
	//	     reads $row['MODULE_CLASS'] / $row['META_INFORMATION'], and
	//	     module_cache() rows carry neither), so the SqlIdent refusal fires
	//	     for the same reason.
	//	The gate is therefore defence in depth: it is what this dispatch needs
	//	the day either blocker is repaired, and it is inert (it cannot refuse
	//	anything that reaches it today) until then. Its both-stage behaviour
	//	once reached is proved by ablation in
	//	tests/security/evidence/relay-allowlist.txt, and its decision is pinned
	//	at runtime by the hermetic suite (Reflection, fix round 4).
	//
	//	Placement (fix round 4): the call site is BEFORE the first write in
	//	the loop, because the class name it gates comes from the resolve
	//	query, which only reads. That makes a refusal atomic -- nothing of
	//	this attachment is written when the hook is refused. It was placed
	//	after the two UPDATEs in fix round 3, which left a refused call with
	//	the record already moved; the move is why the residue is gone.
	//
	// Parameters:
	//
	//	$module - The module class name, as the resolve query supplied it.
	//
	//	$method - The literal method name this class dispatches.
	//
	// Returns:
	//
	//	Boolean. TRUE means the dispatch may proceed, FALSE means it must be
	//	refused (and the caller must not dispatch).
	private function _allowlist_gate ( $module, $method ) {
		// class_exists() is the M1 degradation path: a missing class file must
		// not fatal the request.
		if ( ! class_exists ( 'Relay_Allowlist' ) ) { return true; }
		// A non-string argument cannot dispatch anyway; building the string
		// this way keeps that from emitting a PHP conversion warning, and the
		// resulting string is never listed, so enforcement refuses it.
		$concrete = 'org.freemedsoftware.module.'
			. ( is_string ( $module ) ? $module : '' ) . '.'
			. ( is_string ( $method ) ? $method : '' );
		return ! Relay_Allowlist::refuse ( $concrete, NULL, true );
	} // end method _allowlist_gate

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
		// Category A + F4 (2.6f): the date of birth is one of the criteria, so it
		// is validated as Y-m-d (ImportDate() plus a shape check) and the query is
		// refused with a log line rather than quoting ImportDate()'s boolean
		// false - quote(false) is a bare 0, which compares equal to MySQL's
		// zero-date ('0000-00-00'), i.e. a row this search never asked for.
		// Refused rather than dropped: dropping the date would widen a duplicate
		// match to name-only and invent duplicates.
		$dob = NULL;
		if ( $criteria['ptdob'] ) {
			$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
			$parsed = $s->ImportDate( $criteria['ptdob'] );
			if ( !preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', (string) $parsed ) ) {
				syslog( LOG_ERR, get_class($this).'::CheckForDuplicatePatient| refusing non-Y-m-d ptdob '.var_export($criteria['ptdob'], true) );
				return false; // this method's empty answer ("no matches")
			}
			$dob = $parsed;
		}
		$q = "SELECT * FROM patient p WHERE ".
			"ptlname=".$GLOBALS['sql']->quote( $criteria['ptlname'] )." AND ".
			"ptfname=".$GLOBALS['sql']->quote( $criteria['ptfname'] )." AND ".
			( $criteria['ptmname'] ? "ptmname=".$GLOBALS['sql']->quote( $criteria['ptmname'] )." AND " : "" ).
			( $criteria['ptsuffix'] ? "ptsuffix=".$GLOBALS['sql']->quote( $criteria['ptsuffix'] )." AND " : "" ).
			( $dob !== NULL ? "ptdob=".$GLOBALS['sql']->quote( $dob )." AND " : "" ).
			"ptarchive=0";
		$res = $GLOBALS['sql']->queryAll( $q );
		if ( count ( $res ) > 0 ) {
			return $res[0]['ptid'];
		}
	} // end method CheckForDuplicatePatient

	// Method: GetDuplicatePatients
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
	//	array of hashes.
	//
	public function GetDuplicatePatients ( $criteria ) {
		// Category A + F4 (2.6f): as CheckForDuplicatePatient - the date of birth
		// is validated as Y-m-d and an unparseable value refuses the query
		// (logged, empty answer) instead of being quoted as a bare 0 that would
		// match a zero-date row.
		$dob = NULL;
		if ( $criteria['ptdob'] ) {
			$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
			$parsed = $s->ImportDate( $criteria['ptdob'] );
			if ( !preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', (string) $parsed ) ) {
				syslog( LOG_ERR, get_class($this).'::GetDuplicatePatients| refusing non-Y-m-d ptdob '.var_export($criteria['ptdob'], true) );
				return array(); // this method's empty answer
			}
			$dob = $parsed;
		}
		$q = "SELECT * FROM patient p WHERE ".
			"ptlname=".$GLOBALS['sql']->quote( $criteria['ptlname'] )." AND ".
			"ptfname=".$GLOBALS['sql']->quote( $criteria['ptfname'] )." AND ".
			( $criteria['ptmname'] ? "ptmname=".$GLOBALS['sql']->quote( $criteria['ptmname'] )." AND " : "" ).
			( $criteria['ptsuffix'] ? "ptsuffix=".$GLOBALS['sql']->quote( $criteria['ptsuffix'] )." AND " : "" ).
			( $dob !== NULL ? "ptdob=".$GLOBALS['sql']->quote( $dob )." AND " : "" ).
			"ptarchive=0";
		$res = $GLOBALS['sql']->queryAll( $q );
		foreach( $res AS $r) {
			$_obj = CreateObject('org.freemedsoftware.core.Patient', $r);
			$return[(int)$r['id']] = trim(stripslashes($_obj->to_text()));
		}
		return 	$return;
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

	// Method: MoveEmrAttachments
	//
	//	Move EMR attachments from one patient to another.
	//
	// Parameters:
	//
	//	$patientFrom - Source patient id number
	//
	//	$patientTo - Destination patient id number
	//
	//	$attachments - Array of patient_emr table ids
	//
	// Return:
	//
	//	Boolean, success
	//
	public function MoveEmrAttachments ( $patientFrom, $patientTo, $attachments ) {
		// Go through all records, make changes
		if ( !is_array( $attachments ) ) { return false; }
		$success = true;
		foreach ( $attachments AS $attachment ) {
			// Resolve original id and table
			$resolve = $GLOBALS['sql']->queryRow( "SELECT m.module_table AS 'table', m.module_class AS 'class', p.oid AS oid FROM patient_emr p LEFT OUTER JOIN modules m ON m.module_table = p.module WHERE p.patient = ".$GLOBALS['sql']->quote( $patient )." AND p.id = " . ( (int) $patientFrom ) );

			// Get patient field from meta data. Both the field name and the
			// table name arrive from the module registry (a database row), so
			// they are NOT trusted code: validate them as identifiers and, on
			// failure, log and refuse this statement rather than aborting the
			// request (ruling R12 — a fatal in a clinical EMR caused by a data
			// value is worse than a skipped attachment move).
			$patient_field = freemed::module_get_meta( $resolve['class'], 'patient_field' );
			$table_q        = SqlIdent::name( $resolve['table'] );
			$field_q        = SqlIdent::name( $patient_field );
			if ( $table_q === false or $field_q === false ) {
				syslog( LOG_ERR, get_class($this).'::MoveEmrAttachments| refusing update: '
					. 'module_table=' . var_export($resolve['table'], true)
					. ' patient_field=' . var_export($patient_field, true)
					. ' are not valid SQL identifiers' );
				$success = false;
				continue;
			}

			// Anything additional
			// (Task 2.8, R32): the resolved module class is a database value,
			// but the CALLER chooses which `patient_emr` rows by id, so it is
			// the same CLASS axis the print wrappers were gated for -- the
			// literal 'additional_move' is fixed here and the class is not.
			// Gate the CONCRETE string BEFORE EVERY WRITE, so that a refusal
			// is ATOMIC with respect to this attachment: the record is not
			// moved, no annotation is moved and no hook runs, and the refusal
			// is reported through $success the way the SqlIdent refusal above
			// refuses one statement instead of aborting the request (a fatal
			// in a clinical EMR caused by a data value is worse than a skipped
			// attachment move). Fix round 3 placed this gate after both
			// UPDATEs, so a refused call had already moved the record -- a
			// partial application; fix round 4 moved it here, and the class it
			// needs comes from the resolve query above, which only READS, so
			// nothing had to be written first. In the shipped log-only stage
			// the gate returns TRUE on a miss (the miss is logged), so shipped
			// behaviour is unchanged.
			if ( ! $this->_allowlist_gate ( $resolve['class'], 'additional_move' ) ) {
				$success = false;
				continue;
			}

			// Move actual record
			$result = $GLOBALS['sql']->query( "UPDATE " . $table_q . " SET " . $field_q . " = " . $GLOBALS['sql']->quote( (int) $patientTo ) . " WHERE id = " . $GLOBALS['sql']->quote( (int) $resolve['oid'] ) );
			$success &= (boolean) $result;

			// Move any annotations, if they exist
			$result = $GLOBALS['sql']->query( "UPDATE annotations SET apatient = " . $GLOBALS['sql']->quote( (int) $patientTo ) . " WHERE apatient = " . $GLOBALS['sql']->quote( (int) $patientFrom ) . " AND atable = " . $GLOBALS['sql']->quote( $resolve['table'] ) . " AND aid = " . $GLOBALS['sql']->quote( (int) $resolve['oid'] ) );
			$success &= (boolean) $result;

			module_function(
				  $resolve['class']
				, 'additional_move'
				, array (
					  $resolve['oid']
					, $patientFrom
					, $patientTo
				)
			);
		}
		return $success;
	}

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
/*	public function Search ( $_criteria ) {
		$criteria = (array) $_criteria;
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

*/

				
	public function Search ( $_criteria ) {
		freemed::acl_enforce( 'emr', 'read' );
		$criteria = (array) $_criteria;
		if (!count($criteria)) { return array(); }

		foreach ($criteria AS $k => $v) {
			switch ($k) {
				
				
				case 'ptssn':
				if ($v) { $c[] = "p.ptssn LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;
				
				
				case 'ptdmv':
				if ($v) { $c[] = "p.ptdmv LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;
				case 'ptemail':
				if ($v) { $c[] = "p.ptemail LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;
				
				
				case 'ptwphone':
				if ($v) { $c[] = "p.ptwphone LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;
				
				case 'pthphone':
				if ($v) { $c[] = "p.pthphone LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;
				
				case 'city':
				if ($v) { $c[] = "pa.city LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'ptzip':
				if ($v) { $c[] = "p.ptzip LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'ptid':
				if ($v) { $c[] = "p.ptid LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				case 'age':
				if ($v) { $c[] = "FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 ) = ".$GLOBALS['sql']->quote($v+0); }
				break;
				
				case 'ptfname':
				if ($v) { $c[] = "p.ptfname LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;
				
				case 'ptlname':
				if ($v) { $c[] = "p.ptlname LIKE '%".$GLOBALS['sql']->escape( $v )."%'"; }
				break;

				default: break;
			}
		} // end foreach

		// Only look for 
		if ( !isset( $criteria['archive'] ) ) { $c[] = "p.ptarchive = 0"; }

		$query = "SELECT distinct p.ptlname AS last_name, p.ptfname AS first_name, p.ptmname AS middle_name, p.ptid AS patient_id, FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365 ) AS age, p.ptdob AS date_of_birth, p.id AS id FROM patient p LEFT OUTER JOIN patient_address pa ON p.id = pa.patient WHERE ".join(' AND ', $c)." AND pa.active = 1 ORDER BY p.ptlname, p.ptfname, p.ptmname LIMIT 20";
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
	//	* city
	//	* state
	//	* postal
	//	* hasallergy
	//	* facility
	//	* pharmacy
	//	* pcp
	//
	public function PatientInformation( $id ) {
		syslog(LOG_INFO, (int)$id);
		$q = "SELECT "
		."CONCAT( p.ptlname, ', ', p.ptfname, IF(NOT ISNULL(p.ptmname), CONCAT(' ', p.ptmname), '') ) AS patient_name"
		.", p.ptid AS patient_id"
		.", p.ptdob AS date_of_birth"
		.", p.ptprimarylanguage AS language"
		.", DATE_FORMAT(p.ptdob, '%m/%d/%Y') AS date_of_birth_mdy"
		.", CASE WHEN ( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365) >= 2 THEN CONCAT(FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 365),' years') ELSE CONCAT(FLOOR( ( TO_DAYS(NOW()) - TO_DAYS(p.ptdob) ) / 30),' months') END AS age"
		.", pa.line1 AS address_line_1"
		.", pa.line2 AS address_line_2"
		.", pa.city AS city"
		.", pa.stpr AS state"
		.", pa.postal AS postal"
		.", CONCAT( pa.city, ', ', pa.stpr, ' ', pa.postal ) AS csz"
		.", CASE WHEN p.id IN ( SELECT al.patient FROM allergies al WHERE al.patient=".$GLOBALS['sql']->quote( $id )." AND active = 'active' ) THEN 'true' ELSE 'false' END AS hasallergy, p.* "
		.", CONCAT( phy.phylname, ', ', phy.phyfname, ' ', phy.phymname ) AS pcp"
		.", CONCAT( fac.psrname, ' (', fac.psrcity, ', ', fac.psrstate,')' ) AS facility"
		.", CONCAT( ph.phname, ' (', ph.phcity, ', ', ph.phstate,')' ) AS pharmacy "
		."FROM patient p "
		."LEFT OUTER JOIN patient_address pa ON ( pa.patient = p.id AND pa.active = TRUE ) "
		."LEFT OUTER JOIN physician phy ON ( phy.id = p.ptpcp) "
		."LEFT OUTER JOIN facility fac ON ( fac.id = p.ptprimaryfacility) "
		."LEFT OUTER JOIN pharmacy ph ON ( ph.id = p.ptpharmacy) "
		."WHERE p.id = " . $GLOBALS['sql']->quote( $id ). " GROUP BY p.id";
		syslog(LOG_INFO, $q);
		return $GLOBALS['sql']->queryRow( $q );
	} // end method PatientInformation

	// Method: PatientEMRView
	//
	//	detailed patient information for a single patient including coverages & authorizations data
	//
	// Parameters:
	//
	//	$id - Patient id
	//
	// Returns:
	//
	//	Hash. Contains:
	//	* all personal Information
	//	* Coverages data
	//	* authorization data
	//
	public function PatientEMRView( $id ) {
		syslog(LOG_INFO, (int)$id);
		$patient = $GLOBALS['sql']->quote( (int) $id );
		$q = "SELECT "
			. "  p.id "
			. ", p.ptmarital "
			. ", p.ptempl "
			. ", p.ptssn "
			. ", p.ptrace "
			. ", p.ptreligion "
			. ", p.ptbilltype "
			. ", p.ptbudg "
			. " FROM patient p "
			. " WHERE p.id = " . intval( $id );
		syslog(LOG_INFO, $q);
		$return = $GLOBALS['sql']->queryRow( $q );
		$pt_info['ptinfo'] = array($return);
		//Coverages Info
		$pt_coverages_obj = CreateObject('org.freemedsoftware.module.PatientCoverages');
		$pt_coverages     = $pt_coverages_obj->GetAllCoveragesWithDetail($patient);
		if($pt_coverages)
			$pt_info['ptcoverages'] = $pt_coverages;
		//Authorizations Info
		$pt_auth_obj = CreateObject('org.freemedsoftware.module.Authorizations');
		$pt_auth     = $pt_auth_obj->GetAllAuthorizationsWithDetail($patient);
		if($pt_auth)
		 	$pt_info['ptauth'] = $pt_auth;
		return 	$pt_info;
	} // end method PatientInformation

	// Method: PatientEMRViewWithIntake
	//
	//	detailed patient information for a single patient including coverages & authorizations data
	//
	// Parameters:
	//
	//	$id - Patient id
	//
	// Returns:
	//
	//	Hash. Contains:
	//	* all personal Information
	//	* Coverages data
	//	* authorization data
	//
	public function PatientEMRViewWithIntake( $id ) {
		syslog(LOG_INFO, (int)$id);
		$return = $this->PatientEMRView($id);
		$initialIntake = CreateObject('org.freemedsoftware.module.TreatmentInitialIntake');
		$intakeData = $initialIntake->GetPatientAdmitDateWithProgram($id);
		if($intakeData)
			$return['ptinfo'] = array(array_merge($return['ptinfo'][0],$intakeData));
			
		return $return;
	} // end method PatientInformation

	// Method: TrackView
	//
	//	Track patient view. (Drop breadcrumb for history, etc.)
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$view - (optional) Part of the EMR to track view for.
	//
	public function TrackView( $patient, $view = 'EMR' ) {
		$this_user = freemed::user_cache();
		$GLOBALS['sql']->query(
			  "INSERT INTO patient_view_history "
			. " ( user, patient, viewed ) "
			. " VALUES ( "
			       . $GLOBALS['sql']->quote( $this_user->user_number )
			. ", " . $GLOBALS['sql']->quote( (int) $patient )
			. ", " . $GLOBALS['sql']->quote( $view )
			. " ); "
		);
	} // end method TrackView

	// Method: GetTrackHistory
	//
	//	Get patient tracking history.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$view - (optional) Part of the EMR to track view for.
	//
	public function GetTrackHistory( $patient, $view = 'EMR' ) {
		$this_user = freemed::user_cache();
		return $GLOBALS['sql']->queryAll(
			  "SELECT "
			. " patient, stamp "
			. " FROM patient_view_history "
			. " WHERE "
				. " user = " . $GLOBALS['sql']->quote( $this_user->user_number )
				. " AND viewed = " . $GLOBALS['sql']->quote( $view )
			. " ORDER BY stamp DESC ; "
		);
	} // end method GetTrackHistory

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
	public function picklist ( $string, $_limit = 10, $inputlimit = 2 ) {
		freemed::acl_enforce( 'emr', 'read' );
		$limit = ($_limit < 10) ? 10 : $_limit;
		if (strlen($string) < $inputlimit) {
			syslog(LOG_INFO, "under $inputlimit");
			return false;
		}

		// Category A (2.6b): the tokeniser input is not a query value - every
		// predicate below is driver-quoted where it is composed (quote()). The
		// addslashes() that used to sit here double-escaped the value, so a
		// search for O'Brien looked for the literal O\'Brien.
		$criteria = (string) $string;
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

		// Category A (2.6b): each predicate is driver-quoted where it is
		// composed - quote() supplies the surrounding quotes, so the hand-written
		// ones are gone (never both) and the pattern text is unchanged.
		if ($first and $last) {
			$q[] = "ptfname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
			$q[] = "ptlname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
			$q[] = "( ptlname LIKE ".$GLOBALS['sql']->quote( $last.'%' )." AND ".
				" ptfname LIKE ".$GLOBALS['sql']->quote( $first.'%' )." )";
		} elseif ($first) {
			$q[] = "ptfname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
			$q[] = "ptlname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
                	$q[] = "ptfname LIKE ".$GLOBALS['sql']->quote( $first.'%' );
                	$q[] = "ptid LIKE ".$GLOBALS['sql']->quote( '%'.$first.'%' );
		} elseif ($last) {
			$q[] = "ptfname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
			$q[] = "ptlname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
                	$q[] = "ptlname LIKE ".$GLOBALS['sql']->quote( $last.'%' );
                	$q[] = "ptid LIKE ".$GLOBALS['sql']->quote( '%'.$last.'%' );
		} else {
			$q[] = "ptfname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
			$q[] = "ptlname LIKE ".$GLOBALS['sql']->quote( $either.'%' );
			$q[] = "ptid LIKE ".$GLOBALS['sql']->quote( '%'.$either.'%' );
		}

		// Category C: $q is a join of driver-quoted predicates built just above,
		// so the assembly carries no data; the LIMIT is cast (Category A - see
		// UserInterface::GetRecords for the relay-reachable write primitive).
		$query = sprintf(
			'SELECT * FROM patient WHERE ( %s ) AND ( ISNULL(ptarchive) OR ptarchive=0 ) LIMIT %d',
			join(' OR ', $q), intval($limit) );
		syslog(LOG_INFO, "PICK| $query");
		$result = $GLOBALS['sql']->queryAll( $query );
		if (count($result) < 1) { return array (); }
		$count = 0;
		foreach ($result AS $r) {
			$_obj = CreateObject('org.freemedsoftware.core.Patient', $r);
			$return[(int)$r['id']] = trim(stripslashes($_obj->to_text()));
		}
		syslog(LOG_INFO, "picklist| found ".count($return)." results returned");
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
