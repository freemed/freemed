<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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
	var $MODULE_VERSION = "0.4";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "11644a0c-9efb-4db2-857f-3e4d86b1b2ea";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Medications";
	var $table_name = 'medications';
	var $patient_field = 'mpatient';
	var $date_field = 'mdate';
	var $atomic_keys = array (
		'mid',
		'mdrug',
		'mdosage',
		'mroute',
		'mpatient',
		'mdate',
		'minterval',
		'mprescriber',
		'user'
	);

	public function __construct ( ) {
		// __("Medications")

		$this->variables = array (
			'mdate',
			'mpatient',
			'user'
		);

		$this->summary_vars = array (
			__("Date") => 'mdate',
			__("Drugs") => 'mdrugs',
		);
		$this->summary_options = SUMMARY_DELETE;
		$this->summary_order_by = 'mdate';
		$this->_SetAssociation( 'EmrModule' );

		// call parent constructor
		parent::__construct( );
	} // end constructor Medications

	protected function add_pre ( &$data ) {
		$data['mdate'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		unset($data['mdate']);
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: GetMostRecent
	//
	//	Get atoms for most recent set of medications.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	// Returns:
	//
	//	Array of medication "atoms".
	//
	// SeeAlso:
	//	<GetAtoms>
	//
	public function GetMostRecent ( $patient ) {
		$q = "SELECT id FROM ".$this->table_name." WHERE mpatient = ".$GLOBALS['sql']->escape( $patient )." ORDER BY mdate DESC LIMIT 1";
		$mid = $GLOBALS['sql']->queryOne( $q );
		if ( ($mid + 0) < 1 ) { return array(); }
		return $this->GetAtoms( $mid );
	} // end method GetMostRecent

	// Method: GetAtoms
	//
	//	Get all atoms associated with a medication record.
	//
	// Parameters:
	//
	//	$mid - Medications id
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetAtoms( $mid ) {
		$q = "SELECT ma.*, CONCAT(p.phylname, ', ', p.phyfname, ' ', p.phymname) AS prescriber FROM medications_atomic ma LEFT OUTER JOIN physician p ON ma.mprescriber = p.id WHERE ma.mid = ". ( $mid + 0 );
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAtoms

	// Method: SetAtoms
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$mid - Medications id
	//
	//	$atoms - Array of hashes
	//	* altered - Boolean flag to determine whether or not this entry has been altered.
	//	* id - 0 if new, otherwise the current id
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function SetAtoms ( $patient, $mid, $atoms ) {
		$as = (array) $atoms;

		// Get current atoms
		//$current = $GLOBALS['sql']->queryCol( "SELECT id FROM medications_atomic WHERE mid = ".$GLOBALS['sql']->quote( $mid ) );
		foreach ( $as AS $a ) {
			$a = (array) $a;
			if ( ( $a['id'] + 0 ) > 0 ) {
				$newkeys[] = ( $a['id'] + 0 );
			}
		}

		// Remove everything not here anymore
		if ( count( $newkeys ) > 0 ) {
			$remove = $GLOBALS['sql']->query( "DELETE FROM medications_atomic WHERE mid = ".$GLOBALS['sql']->quote( $mid )." AND NOT FIND_IN_SET( id, ".$GLOBALS['sql']->quote( join( ',', $newkeys ) )." )" );
		}

		foreach ( $as AS $a ) {
			// Force as an array
			$a = (array) $a;

			// Preprocessing
			$a['mid'] = $mid;
			$a['mpatient'] = $patient;

			// If id = 0, process as new entry
			if ( ( (int) $a['id'] ) == 0 ) {
				syslog( LOG_DEBUG, "SetAtoms: adding new atom for $patient, mid = $mid" );
				$GLOBALS['sql']->load_data( $a );
				$query = $GLOBALS['sql']->insert_query(
					'medications_atomic',
					$this->atomic_keys
				);
				$GLOBALS['sql']->query( $query );
			} else {
				if ( $a['altered'] ) {
					syslog( LOG_DEBUG, "SetAtoms: modifying atomic medication for $patient, mid = $mid, id = ".$a['id'] );
					$GLOBALS['sql']->load_data( $a );
					$query = $GLOBALS['sql']->update_query(
						'medications_atomic',
						$this->atomic_keys,
						array( 'id' => $a['id'] )
					);
					$GLOBALS['sql']->query( $query );
				}
			}
		}
		return true;
	} // end method SetAtoms

} // end class Medications

register_module ("Medications");

?>
