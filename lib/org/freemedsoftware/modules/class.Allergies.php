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

	var $MODULE_NAME = "Allergy";
	var $MODULE_VERSION = "0.2.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "e58a3f17-817f-4444-b573-c8827fa38a16";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Allergies";
	var $table_name = 'allergies';
	var $patient_field = 'patient';
	var $date_field = 'reviewed';
	var $widget_hash = '##allergies##';
	var $atomic_keys = array (
		'aid',
		'allergy',
		'reaction',
		'severity',
		'patient',
		'reviewed',
		'user'
	);

	public function __construct ( ) {
		// __("Allergies")

		$this->variables = array (
			'allergies',
			'patient',
			'reviewed' => SQL__NOW,
			'user'
		);

		$this->summary_vars = array (
			__("Allergies") => 'allergies',
			__("Reviewed") => '_reviewed'
		);
		$this->summary_query = array (
			"DATE_FORMAT(reviewed, '%m/%d/%Y') AS _reviewed"
		);
		$this->summary_options = SUMMARY_DELETE;
		$this->_SetAssociation( 'EmrModule' );

		// call parent constructor
		parent::__construct();
	} // end constructor Allergies

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: GetMostRecent
	//
	//	Get atoms for most recent set of allergies.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	// Returns:
	//
	//	Array of allergy "atoms".
	//
	// SeeAlso:
	//	<GetAtoms>
	//
	public function GetMostRecent ( $patient ) {
		$q = "SELECT id FROM ".$this->table_name." WHERE patient = ".$GLOBALS['sql']->escape( $patient )." ORDER BY reviewed DESC LIMIT 1";
		$aid = $GLOBALS['sql']->queryOne( $q );
		if ( ($aid + 0) < 1 ) { return array(); }
		return $this->GetAtoms( $aid );
	} // end method GetMostRecent

	// Method: GetAtoms
	//
	//	Get all atoms associated with a allergies record.
	//
	// Parameters:
	//
	//	$aid - Allergies id
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetAtoms( $aid ) {
		$q = "SELECT * FROM allergies_atomic WHERE aid = ". ( $aid + 0 );
		return $GLOBALS['sql']->queryAll( $q );
	} // end method GetAtoms

	// Method: SetAtoms
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	//	$aid - Allergies id
	//
	//	$atoms - Array of hashes
	//	* altered - Boolean flag to determine whether or not this entry has been altered.
	//	* id - 0 if new, otherwise the current id
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function SetAtoms ( $patient, $aid, $atoms ) {
		$as = (array) $atoms;

		// Get current atoms
		//$current = $GLOBALS['sql']->queryCol( "SELECT id FROM allergies_atomic WHERE aid = ".$GLOBALS['sql']->quote( $aid ) );
		foreach ( $as AS $a ) {
			$a = (array) $a;
			if ( ( $a['id'] + 0 ) > 0 ) {
				$newkeys[] = ( $a['id'] + 0 );
			}
		}

		// Remove everything not here anymore
		if ( count( $newkeys ) > 0 ) {
			$remove = $GLOBALS['sql']->query( "DELETE FROM allergies_atomic WHERE aid = ".$GLOBALS['sql']->quote( $aid )." AND NOT FIND_IN_SET( id, ".$GLOBALS['sql']->quote( join( ',', $newkeys ) )." )" );
		}

		foreach ( $as AS $a ) {
			// Force as an array
			$a = (array) $a;

			// Preprocessing
			$a['aid'] = $aid;
			$a['patient'] = $patient;

			// If id = 0, process as new entry
			if ( ( (int) $a['id'] ) == 0 ) {
				syslog( LOG_DEBUG, "SetAtoms: adding new atom for $patient, aid = $aid" );
				$GLOBALS['sql']->load_data( $a );
				$query = $GLOBALS['sql']->insert_query(
					'allergies_atomic',
					$this->atomic_keys
				);
				$GLOBALS['sql']->query( $query );
			} else {
				if ( $a['altered'] ) {
					syslog( LOG_DEBUG, "SetAtoms: modifying atomic allergy for $patient, aid = $aid, id = ".$a['id'] );
					$GLOBALS['sql']->load_data( $a );
					$query = $GLOBALS['sql']->update_query(
						'allergies_atomic',
						$this->atomic_keys,
						array( 'id' => $a['id'] )
					);
					$GLOBALS['sql']->query( $query );
				}
			}
		}
		return true;
	} // end method SetAtoms

} // end class Allergies

register_module ("Allergies");

?>
