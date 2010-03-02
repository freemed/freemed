<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Api.PatientInterface'
		, 'mappedBy' => 'org.freemedsoftware.api.PatientInterface'
		, 'methods' => array (

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
			  array (
				  'name' => 'CheckForDuplicatePatient'
				, 'mappedName' => 'CheckForDuplicatePatient'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					array (
						  'type' => 'java.util.HashMap'
						, 'typeCRC' => '962170901<2004016611,2004016611>'
					)
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'DxForPatient'
				, 'mappedName' => 'DxForPatient'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'EmrAttachmentsByPatient'
				, 'mappedName' => 'EmrAttachmentsByPatient'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'EmrAttachmentsByPatientTable'
				, 'mappedName' => 'EmrAttachmentsByPatientTable'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'EmrModules'
				, 'mappedName' => 'EmrModules'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'MoveEmrAttachments'
				, 'mappedName' => 'MoveEmrAttachments'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => '[Ljava.lang.Integer;' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'NumericSearch'
				, 'mappedName' => 'NumericSearch'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					array (
						  'type' => 'java.util.HashMap<java.lang.String,java.lang.String>'
						, 'typeCRC' => '962170901<2004016611,2004016611>'
					)
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'Search'
				, 'mappedName' => 'Search'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					array (
						  'type' => 'java.util.HashMap<java.lang.String,java.lang.String>'
						, 'typeCRC' => '962170901<2004016611,2004016611>'
					)
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'PatientInformation'
				, 'mappedName' => 'PatientInformation'
				, 'returnType' => 'java.util.HashMap'
				, 'returnTypeCRC' => '962170901'
				, 'params' => array (
					array (
						  'type' => 'java.lang.Integer'
						, 'typeCRC' => '3438268394'
					)
				)
				, 'throws' => array ( )
			)

			// Method: TotalInSystem
			//
			//	Get total number of active patients in the system.
			//
			// Returns:
			//
			//	Integer, number of active patients in the system.
			//
			, array (
				  'name' => 'TotalInSystem'
				, 'mappedName' => 'TotalInSystem'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: Picklist
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
			, array (
				  'name' => 'Picklist'
				, 'mappedName' => 'Picklist'
				, 'returnType' => 'java.util.HashMap<java.lang.Integer,java.lang.String>'
				, 'returnTypeCRC' => '962170901<3438268394,2004016611>'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( /*'type' => 'org.freemedsoftware.gwt.client.AuthenticationException'*/ )
			)
//- HashMap = 962170901
//- HashMap[] = 3558356060

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
			, array (
				  'name' => 'ProceduresToBill'
				, 'mappedName' => 'ProceduresToBill'
				, 'returnType' => '[Ljava.lang.Integer;'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

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
			, array (
				  'name' => 'ToText'
				, 'mappedName' => 'ToText'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
