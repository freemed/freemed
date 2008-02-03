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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.ApiAuthorizations'
		, 'mappedBy' => 'org.freemedsoftware.api.Authorizations'
		, 'methods' => array (

			// Method: FindByCoverage
			//
			//	Find authorizations based on a coverage id
			//
			// Parameters:
			//
			//	$coverage - Coverage id key
			//
			// Returns:
			//
			//	Array of authorization keys, or false if it cannot
			//	find any.
			//
			  array (
				  'name' => 'FindByCoverage'
				, 'mappedName' => 'find_by_coverage'
				, 'returnType' => '[java.lang.Integer'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetAuthorization
			//
			//	Gets the SQL record associated with an authorization.
			//
			// Parameters:
			//
			//	$auth - Authorization id key
			//
			// Returns:
			//
			//	Associative array.
			//
			, array (
				  'name' => 'GetAuthorization'
				, 'mappedName' => 'get_authorization'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: Replace
			//
			//	Puts an authorization back to pre-appointment status.
			//	This has the exact opposite effect as <use_authorization>,
			//	as it increases the number of visits remaining on an
			//	authorization.
			//
			// Parameters:
			//
			//	$auth - Authorization key id
			//
			// Returns:
			//
			//	Boolean, successful
			//
			, array (
				  'name' => 'Replace'
				, 'mappedName' => 'Replace'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: UseAuthorization
			//
			//	"Use" an authorization. This computes remaining visits
			//	and other information for the authorization. This has
			//	the exact opposite effect as <replace_authorization>.
			//
			// Parameters:
			//
			//	$auth - Authorization key id
			//
			// Returns:
			//
			//	Boolean, successful
			//
			, array (
				  'name' => 'UseAuthorization'
				, 'mappedName' => 'use_authorization'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: valid
			//
			//	Determine if an authorization is valid, based on the
			//	date given.
			//
			// Parameters:
			//
			//	$auth - Authorization id key
			//
			//	$date - (optional) Date for the comparison. Defaults to
			//	the current date if none is provided.
			//
			// Returns:
			//
			//	Boolean, if authorization is currently valid.
			//
			, array (
				  'name' => 'Valid'
				, 'mappedName' => 'Valid'
				, 'returnType' => ''
				, 'params' => array (
				)
				, 'throws' => array ( )
			)

			// Method: ValidSet
			//
			//	Find set of valid authorizations from a set of
			//	unvalidated authorization keys.
			//
			// Parameters:
			//
			//	$set - Array of unvalidated authorization keys
			//
			//	$date - (optional) Date to use for range comparison.
			//	Defaults to the current date.
			//
			// Returns:
			//
			//	Array of valid authorization keys, or NULL array if
			//	none exist.
			//
			, array (
				  'name' => 'ValidSet'
				, 'mappedName' => 'valid_set'
				, 'returnType' => '[java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => '[java.lang.Integer' )
					, array ( 'type' => 'java.lang.Date' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
