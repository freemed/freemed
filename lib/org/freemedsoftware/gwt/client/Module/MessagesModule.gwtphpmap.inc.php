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

$gwtphpmap = array (
	array (
		  'className' => 'org.freemedsoftware.gwt.client.Module.MessagesModule'
		, 'mappedBy' => 'org.freemedsoftware.module.MessagesModule'
		, 'methods' => array (

			// Method: GetAllByTag
			//
			//	Grab hash of messages based on tags.
			//
			// Parameters:
			//
			//	$tag - (optional) Tag to search for, defaults to none.
			//
			//	$all - (optional) Get all messages, not just unread, defaults to false
			//
			// Returns:
			//
			//	Array of hashes.
			//
			  array (
				  'name' => 'GetAllByTag'
				, 'mappedName' => 'GetAllByTag'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

			// Method: MessageTags
			//
			//	List of all message tags associated with a user.
			//
			// Returns:
			//
			//	Array of tags
			//
			, array (
				  'name' => 'MessageTags'
				, 'mappedName' => 'MessageTags'
				, 'returnType' => '[[Ljava.lang.String;'
				, 'returnTypeCRC' => '392769419[2364883620[L2004016611;'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: UnreadMessages
			//
			//	Number of unread messages.
			//
			// Parameters:
			//
			//	$ts - (optional) Timestamp to use as marker.
			//
			//	$all - (optional) Show *all* messages, not just unread. Defaults to false.
			//
			// Returns:
			//
			//	Number of unread messages for the current user
			//
			, array (
				  'name' => 'UnreadMessages'
				, 'mappedName' => 'UnreadMessages'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Boolean' )
				)
				, 'throws' => array ( )
			)

			// Method: DeleteMultiple
			//
			//	Remove multiple messages by id.
			//
			// Parameters:
			//
			//	$m - Array of message ids
			//
			, array (
				  'name' => 'DeleteMultiple'
				, 'mappedName' => 'DeleteMultiple'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					array ( 'type' => '[java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
