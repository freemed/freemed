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
		  'className' => 'org.freemedsoftware.gwt.client.Api.Messages'
		, 'mappedBy' => 'org.freemedsoftware.api.Messages'
		, 'methods' => array (

			// Method: get
			//
			//	Retrieve message by message id key
			//
			// Parameters:
			//
			//	$message - Message id key
			//
			// Returns:
			//
			//	Associative array containing message information.
			//
			  array (
				  'name' => 'Get'
				, 'mappedBy' => 'Get'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: recipients_to_text
			//
			//	Convert a recipients field (comma delimited users) into
			//	a list of user names.
			//
			// Parameters:
			//
			//	$recip - 'msgrecip' field
			//
			// Returns:
			//
			//	Comma-delimited list of user names
			//
			, array (
				  'name' => 'RecipentsToText'
				, 'mappedBy' => 'recipients_to_text'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: remove
			//
			//	Remove a message from the system by its id key
			//
			// Parameters:
			//
			//	$message_id - Message id key
			//
			// Returns:
			//
			//	Boolean, successful
			//
			, array (
				  'name' => 'Remove'
				, 'mappedBy' => 'Remove'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: ListOfUsers
			//
			//	Create list of users in the system.
			//
			// Returns:
			//
			//	Array of hashes containing:
			//	* id - User id
			//	* username - Name of the user in question
			//
			, array (
				  'name' => 'ListOfUsers'
				, 'mappedBy' => 'ListOfUsers'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: send
			//
			//	Send a message using the FreeMED messaging system
			//
			// Parameters:
			//
			//	$message - Associative array containing information describing the message.
			//	* system - Boolean, if system message
			//	* user - Destination user id
			//	* group - User group id, optional
			//	* for - Array of destination users (instad of 'user')
			//	* text - Message body
			//	* patient - Patient id
			//	* person - Person name whom the message is regarding
			//	* subject - Textual subject line
			//	* urgency - Urgency ( 1 to 5 )
			//	* tag - Tag under which to file message. Defaults to ''.
			//
			// Returns:
			//
			//	Boolean, successful
			//
			, array (
				  'name' => 'Send'
				, 'mappedBy' => 'Send'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => 'java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: TagModify
			//
			//	Change the tagging associated with a message.
			//
			// Parameters:
			//
			//	$message - Message table ID
			//
			//	$tag - Tag to associate with this message
			//
			// Returns:
			//
			//	Boolean, successful
			//
			, array (
				  'name' => 'TagModify'
				, 'mappedBy' => 'TagModify'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: view_per_patient
			//
			//	Get all messages associated with a patient
			//
			// Parameters:
			//
			//	$patient - Patient id key
			//
			//	$unread_only - (optional) Boolean, restrict to unread
			//	messages only. Defaults to false.
			//
			// Returns:
			//
			//	Array of associative arrays containing message information.
			//
			// See Also:
			//	<view>
			//	<view_per_user>
			//
			, array (
				  'name' => 'ViewPerPatient'
				, 'mappedBy' => 'view_per_patient'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

			// Method: view_per_user
			//
			//	Get all messages associated with the current user
			//
			// Parameters:
			//
			//	$unread_only - (optional) Boolean, restrict to unread
			//	messages only. Defaults to false.
			//
			// Returns:
			//
			//	Array of associative arrays containing message information.
			//
			// See Also:
			//	<view>
			//	<view_per_patient>
			//
			, array (
				  'name' => 'ViewPerUser'
				, 'mappedBy' => 'view_per_user'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
