<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Module.PatientTag'
		, 'mappedBy' => 'org.freemedsoftware.module.PatientTag'
		, 'methods' => array (

			// Method: ListTags
			//
			//	Get list of tags based on criteria.
			//
			// Parameters:
			//
			//	$criteria - Criteria
			//
			// Returns:
			//
			//	Array of key = value hashes.
			//
			  array (
				  'name' => 'ListTags'
				, 'mappedName' => 'ListTags'
				, 'returnType' => '[Ljava.lang.String;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: CreateTag
			//
			//	Attach a new tag to a patient
			//
			// Parameters:
			//
			//	$patient - Patient record id.
			//
			//	$tag - Textual name of tag
			//
			// Returns:
			//
			//	Boolean, success.
			//
			, array (
				  'name' => 'CreateTag'
				, 'mappedName' => 'CreateTag'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: ExpireTag
			//
			//	Force tag to expire for specified patient and tag.
			//
			// Parameters:
			//
			//	$patient - Patient record id.
			//
			//	$tag - Textual name of tag in question.
			//
			// Returns:
			//
			//	Boolean, success.
			//
			, array (
				  'name' => 'ExpireTag'
				, 'mappedName' => 'ExpireTag'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: TagsForPatient
			//
			//	Get list of all tags associated with a patient.
			//
			// Parameters:
			//
			//	$patient - Patient record id.
			//
			// Returns:
			//
			//	Array of tags.
			//
			, array (
				  'name' => 'TagsForPatient'
				, 'mappedName' => 'TagsForPatient'
				, 'returnType' => '[Ljava.lang.String;'
				, 'returnTypeCRC' => '2364883620[L2004016611;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: SimpleTagSearch
			//
			//	Tag search function.
			//
			// Parameters:
			//
			//	$tag - Name of tag to search for.
			//
			//	$include_inactive - (optional) Boolean, include inactive tags.
			//	Defaults to false.
			//
			// Returns:
			//
			//	Array of hashes. (See <SearchEngine> output)
			//
			// SeeAlso:
			//	<AdvancedTagSearch>
			//	<SearchEngine>
			//
			, array (
				  'name' => 'SimpleTagSearch'
				, 'mappedName' => 'SimpleTagSearch'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

			// Method: AdvancedTagSearch
			//
			//	Advanced tag searching function, allowing simple boolean searching.
			//
			// Parameters:
			//
			//	$tag - Name of primary tag to search for.
			//
			//	$clauses - Array of hashes like this:
			//	* tag - Name of tag
			//	* operator - 'AND' or 'OR'
			//
			//	$include_inactive - (optional) Boolean, include inactive tags.
			//	Defaults to false.
			//
			// Returns:
			//
			//	Array of hashes. (See <SearchEngine> output)
			//
			// SeeAlso:
			//	<SimpleTagSearch>
			//	<SearchEngine>
			//
			, array (
				  'name' => 'AdvancedTagSearch'
				, 'mappedName' => 'AdvancedTagSearch'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.util.HashMap' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
