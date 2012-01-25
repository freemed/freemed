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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Api.UserInterface'
		, 'mappedBy' => 'org.freemedsoftware.api.UserInterface'
		, 'methods' => array (

			// Method: GetCurrentUsername
			//
			//	Determine the username for the current user.
			//
			// Returns:
			//
			//	String.
			//
			  array (
				  'name' => 'GetCurrentUsername'
				, 'mappedName' => 'GetCurrentUsername'
				, 'returnType' => 'java.lang.String'
				, 'params' => array ( )
				, 'throws' => array ( )
			)
		

			// Method: GetUsers
			//
			//	Get picklist formatted user information.
			//
			// Parameters:
			//
			//	$param - Substring to search for. Defaults to ''.
			//
			// Returns:
			//
			//	Array of arrays containing ( user description, id ).
			//
			, array (
				  'name' => 'GetUsers'
				, 'mappedName' => 'GetCurrentUsername'
				, 'returnType' => '[[java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			, array (
				  'name' => 'GetNewMessages'
				, 'mappedName' => 'GetNewMessages'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: SetConfigValue
			//
			//	Set user configurable variable.
			//
			// Parameters:
			//
			//	$key - Configuration key
			//
			//	$value - Configuration value
			//
			, array (
				  'name' => 'SetConfigValue'
				, 'mappedName' => 'SetConfigValue'
				, 'returnType' => TypeSignatures::$VOID
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: GetRecord
			//
			//	Get user record.
			//
			// Parameters:
			//
			//	$id - User record id
			//
			// Returns:
			//
			//	Associative array
			//
			, array (
				  'name' => 'GetRecord'
				, 'mappedName' => 'GetRecord'
				, 'returnType' => '[[java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetRecords
			//
			//	Get list of records for the user table.
			//
			// Parameters:
			//
			//	$limit - (optional) Limit to maximum number of records to return
			//
			// Return:
			//
			//	Array of hashes.
			//
			, array (
				  'name' => 'GetRecord'
				, 'mappedName' => 'GetRecord'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: add
			//
			//	User addition routine.
			//
			// Parameters:
			//
			//	$_param - (optional) Associative array of values. If
			//	specified, _add will run quiet. The associative array
			//	is in the format of sql_name => sql_value.
			//
			// Returns:
			//
			//	Nothing if there are no parameters. If $_param is
			//	specified, _add will return the id number if successful
			//	or false if unsuccessful.
			//
			, array (
				  'name' => 'GetRecord'
				, 'mappedName' => 'GetRecord'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: del
			//
			//	User deletion id
			//
			// Parameters:
			//
			//	$_param - (optional) Id number for the record to
			//	be deleted. 
			//
			// Returns:
			//
			//	Nothing if there are no parameters. If $_param is
			//	specified, _del will return boolean true or false
			//	depending on whether it is successful.
			//
			, array (
				  'name' => 'del'
				, 'mappedName' => 'del'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: mod
			//
			//	User modification routine
			//
			// Parameters:
			//
			//	$data - Hash of data to pass.
			//
			, array (
				  'name' => 'mod'
				, 'mappedName' => 'mod'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
