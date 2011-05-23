<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Module.RemittBillingTransport'
		, 'mappedBy' => 'org.freemedsoftware.module.RemittBillingTransport'
		, 'methods' => array (

			array (
				  'name' => 'GetRebillList'
				, 'mappedName' => 'GetRebillList'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			, array (
				  'name' => 'GetReport'
				, 'mappedName' => 'GetReport'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			, array (
				  'name' => 'ProcessStatement'
				, 'mappedName' => 'ProcessStatement'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => '[java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetStatus
			//
			//	Get current status by REMITT unique identifiers
			//
			// Parameters:
			//
			//	$uniques - Array of REMITT unique identifiers
			//
			// Returns:
			//
			//	Hash with key being the unique identifier and value being the REMITT
			//	return code.
			//
			, array (
				  'name' => 'GetStatus'
				, 'mappedName' => 'GetStatus'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array (
					array ( 'type' => '[java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: MarkAsBilled
			//
			//	Mark a list of billkeys as being billed.
			//
			// Parameters:
			//
			//	$billkeys - Array of billkeys
			//
			// Returns:
			//
			//	Boolean, success.
			//
			, array (
				  'name' => 'MarkAsBilled'
				, 'mappedName' => 'MarkAsBilled'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => '[java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: PatientsToBill
			//
			//	Get list of all patients to bill with claims.
			//
			// Returns:
			//
			//	Hash containing:
			//	* patient_id - Internal FreeMED record ID for this patient
			//	* claim_count - Number of claims for this patient
			//	* claims - Array of claim ids for this patient
			//	* patient - Human readable patient name
			//	* date_of_birth - Date of birth in YYYY-MM-DD format
			//	* date_of_birth_mdy - Date of birth in MM/DD/YYYY format
			//
			, array (
				  'name' => 'PatientsToBill'
				, 'mappedName' => 'PatientsToBill'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: GetClaimInformation
			//
			//	Resolve additional bill information for a list of claim ids.
			//
			// Parameters:
			//
			//	$claims - Array of claim ids
			//
			// Returns:
			//
			//	Array of hashes containing:
			//	* claim
			//	* claim_date
			//	* claim_date_mdy
			//	* output_format
			//	* paper_format
			//	* paper_target
			//	* electronic_format
			//	* electronic_target
			//	* cpt_code
			//	* cpt_description
			//
			, array (
				  'name' => 'GetClaimInformation'
				, 'mappedName' => 'GetClaimInformation'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					array ( 'type' => '[java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: ProcessClaims
			//
			//	Input jobs into the claims queue for REMITT.
			//
			// Parameters:
			//
			//	$patients - Array of patients for whom billing is enabled.
			//
			//	$claims - Array of claims for which we are billing
			//
			//	$overrides (optional) - Array of media changes and other exceptions (TODO)
			//
			// Returns:
			//
			//	Array of hashes containing:
			//	* Billkey number
			//	* Number of claims in billkey
			//
			, array (
				  'name' => 'ProcessClaims'
				, 'mappedName' => 'ProcessClaims'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => '[java.lang.Integer' )
					, array ( 'type' => '[java.lang.Integer' )
					, array ( 'type' => '[java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
