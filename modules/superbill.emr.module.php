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

class SuperBill extends EMRModule {

	var $MODULE_NAME = "Superbill";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = '5ab0f71e-8b24-491d-a7a9-e509442384e0';

	var $PACKAGE_MINIMUM_VERSION = '0.8.3';

	var $record_name   = "Superbill";
	var $table_name    = "superbill";
	var $patient_field = "patient";
	var $widget_hash   = "##pnotesdt## ##pnotesdescrip##";

	var $print_template = 'superbill';

	var $variables = array (		
		'dateofservice',
		'enteredby',
		'patient',
		'note',
		'procs',
		'dx',
		'reviewed'
	);

	public function __construct ( ) {
		// __("Superbill")
		$this->summary_vars = array (
			__("Date") => "dateofservice",
			__("Note") => "note",
			__("Reviewed") => "reviewed_text"
		);
		$this->summary_options |= SUMMARY_VIEW;
		$this->summary_query = array(
			"SUBSTR_COUNT( procs, ',' ) + 1 AS procs_count",
			"CASE reviewed WHEN 0 THEN 'not reviewed' ELSE 'reviewed' END AS reviewed_text"
		);

		// Set associations
		$this->acl = array ( 'emr', 'bill' );

		// Call parent constructor
		parent::__construct( );
	} // end constructor SuperBill

	// Protected internal methods

	protected function add_pre ( &$data ) {
		$s = CreateObject('org.freemedsoftware.api.Scheduler');
		$data['dateofservice'] = $s->ImportDate( $data['dateofservice'] ? $data['dateofservice'] : date('Y-m-d') );	

		$user = freemed::user_cache( );

		if (is_array($data['procs'])) { $data['procs'] = join(',', $data['procs']); }
		if (is_array($data['dx'])) { $data['dx'] = join(',', $data['dx']); }
		$data['reviewed'] = 0;
		$data['enteredby'] = $user->user_number;
	} // end add_pre

	protected function mod_pre ( &$data ) {
		$s = CreateObject('org.freemedsoftware.api.Scheduler');
		$data['dateofservice'] = $s->ImportDate( $data['dateofservice'] ? $data['dateofservice'] : date('Y-m-d') );	
		if (is_array($data['procs'])) { $data['procs'] = join(',', $data['procs']); }
		if (is_array($data['dx'])) { $data['dx'] = join(',', $data['dx']); }
	} // end mod_pre

	// Public methods

	// Method: GetForDates
	//
	//	Retrieve list of superbills for the specified date range.
	//
	// Parameters:
	//
	//	$dtbegin - Beginning date
	//
	//	$dtend - Ending date
	//
	//	$handled - (optional) Include handled status.
	//
	// Returns:
	//
	//	Array of hashes for superbills between the specified dates.
	//
	public function GetForDates( $dtbegin, $dtend, $handled = NULL ) {
		$s = CreateObject('org.freemedsoftware.api.Scheduler');
		$query = "SELECT s.id AS id, DATE_FORMAT(s.dateofservice, '%m/%d/%Y') AS dateofservice_mdy, s.dateofservice AS dateofservice, CONCAT(pt.ptlname, ', ', pt.ptfname, ' (', pt.ptid, ')') AS patient_name, CONCAT(pr.phylname, ', ', pr.phyfname) AS provider_name, pr.id AS provider_id, pt.id AS patient_id, s.reviewed AS reviewed, s.procs AS procs, SUBSTR_COUNT(s.procs, ',')+1 AS procs_count FROM superbill s LEFT OUTER JOIN patient pt ON pt.id=s.patient LEFT OUTER JOIN physician pr ON s.provider=pr.id ";
		$where = false;
		if ( $dtbegin != NULL ) {
			$query .= "WHERE s.dateofservice>=".$GLOBALS['sql']->quote( $s->ImportDate( $dtbegin ) )." AND s.dateofservice <=".$GLOBALS['sql']->quote( $s->ImportDate( $dtend ) );
			$where = true;
		}
		if ( $handled !== NULL ) {
			if ( $where ) {
				$query .= " AND s.reviewed = ".$GLOBALS['sql']->quote( $handled );
			} else {
				$query .= " WHERE s.reviewed = ".$GLOBALS['sql']->quote( $handled );
			}
		}
		$res = $GLOBALS['sql']->queryAll( $query );
		foreach ( $res AS $r ) {
			$p_query = "SELECT cptcode FROM cpt WHERE FIND_IN_SET(id, ".$GLOBALS['sql']->quote( $r['procs'] ).")";
			$p = $GLOBALS['sql']->queryCol( $p_query );
			$r['cpt'] = join(',', $p );
			$result[] = $r;
		}
		return is_array($result) ? $result : array();
	} // end method GetForDates

	// Method: MarkAsHandled
	//
	//	Mark a superbill as being handled
	//
	// Parameters:
	//
	//	$id - Record id of the superbill in question
	//
	// Returns:
	//
	//	Boolean, successful.
	//
	public function MarkAsHandled ( $id ) {
		$user = freemed::user_cache( );
		$query = $GLOBALS['sql']->update_query( 
			$this->table_name,
			array( 'reviewed' => $user->user_number ),
			array( 'id' => $id + 0 )
		);
		return $query ? true : false;
	} // end method MarkAsHandled

	// Method: GetSuperbill
	//
	//	Retrieve complete superbill.
	//
	// Parameters:
	//
	//	$id - Record ID of superbill.
	//
	// Returns:
	//
	//	Hash containing:
	//	* dx
	//	* px
	//
	public function GetSuperbill ( $id ) {
		$hash['dx'] = $GLOBALS['sql']->queryAll( "SELECT dx.icd9code AS code, dx.icd9descrip AS descrip, dx.id AS id FROM icd9 dx LEFT OUTER JOIN superbill s ON FIND_IN_SET( dx.id, s.dx ) WHERE s.id = " . $GLOBALS['sql']->quote( $id ) );
		$hash['px'] = $GLOBALS['sql']->queryAll( "SELECT px.cptcode AS code, px.cptnameint AS descrip, px.id AS id FROM cpt px LEFT OUTER JOIN superbill s ON FIND_IN_SET( px.id, s.procs ) WHERE s.id = " . $GLOBALS['sql']->quote( $id ) );
		return $hash;
	} // end method GetSuperbill

	// Method: ProcessSuperbills
	//
	//	Process superbills into procedure records.
	//
	// Parameters:
	//
	//	$superbills - Array of superbills to process, or '0' to process
	//	all reviewed superbills.
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function ProcessSuperbills ( $superbills = 0 ) {
		if ( $superbills == 0 ) {
			$query = "SELECT * FROM ".$this->table_name." WHERE processed = 0 AND reviewed > 0";
		} else {
			// Use enumerated superbill ids
			$query = "SELECT * FROM ".$this->table_name." WHERE FIND_IN_SET( id, ".$GLOBALS['sql']->quote( join( ',', $superbills ) )." )";
		}
		$s = $GLOBALS['sql']->queryAll( $query );
		foreach ( $s AS $bill ) {
			$dxs = explode( ',', $bill['dx'] );
			$pxs = explode( ',', $bill['procs'] );
			$detail = unserialize( $bill['detail'] );
			foreach ( $pxs AS $px ) {
				// Get current coverages

				// Calculate charges

				// Create database insert
				$ins = $GLOBALS['sql']->insert_query(
					'procrec',
					array(
						'procpatient' => $bill[ 'patient' ],
						'proccpt' => $px,
						'procdiag1' => $dx[0],
						'procdiag2' => $dx[1],
						'procdiag3' => $dx[2],
						'procdiag4' => $dx[3],
						'procbillable' => 1,
						'procbilled' => 0,
						'procamtpaid' => 0, // TODO: handle copays
					)
				);
				$result = $GLOBALS['sql']->query( $ins );
			} // end foreach procedure

			// Mark superbill as processed
			$query = $GLOBALS['sql']->update_query( 
				$this->table_name,
				array( 'processed' => freemed::user_cache()->user_number ),
				array( 'id' => $id + 0 )
			);

		} // end foreach superbill

		return true;
	} // end method ProcessSuperbills

} // end class SuperBill

register_module ("SuperBill");

?>
