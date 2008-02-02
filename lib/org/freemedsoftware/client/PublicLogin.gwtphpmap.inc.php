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
		  'className' => 'org.freemedsoftware.client.PublicLogin'
		, 'mappedBy' => 'org.freemedsoftware.public.Login'
		, 'methods' => array (
			// Method: LoggedIn
			//
			//	Gets present session status to determine if user has logged in.
			//
			// Returns:
			//
			//	Boolean.
			//
			  array (
				  'name' => 'LoggedIn'
				, 'mappedName' => 'LoggedIn'
				, 'returnType' => TypeSignatures::$BOOLEAN
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: Logout
			//
			//	Log out of the current session.
			//
			// Returns:
			//
			//	Success, boolean.
			//
			, array (
				  'name' => 'Logout'
				, 'mappedName' => 'Logout'
				, 'returnType' => TypeSignatures::$BOOLEAN
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: Validate
			//
			//	Validate a new session with the provided credentials.
			//
			// Parameters:
			//	
			//	$username - Username
			//
			//	$password - Plain text password
			//
			// Returns:
			//
			//	Boolean, login status.
			//
			, array (
				  'name' => 'Validate'
				, 'mappedName' => 'Validate'
				, 'returnType' => TypeSignatures::$BOOLEAN
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: SessionPopulate
			//
			//	Populate / repopulate session data with user information. Requires
			//	valid $_SESSION['authdata']['user'] variable.
			//
			// Returns:
			//
			//	True on success.
			//
			, array (
				  'name' => 'SessionPopulate'
				, 'mappedName' => 'SessionPopulate'
				, 'returnType' => TypeSignatures::$BOOLEAN
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: GetLocations
			//
			//	Populate location selection.
			//
			// Returns:
			//
			//	Array of arrays:
			//	* [ language, abbrev ]
			//
			, array (
				  'name' => 'GetLocations'
				, 'mappedName' => 'GetLocations'
				, 'returnType' => '[[java.lang.String'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: GetLanguages
			//
			//	Populate language selection.
			//
			// Returns:
			//
			//	Array of arrays:
			//	* [ language, abbrev ]
			//
			, array (
				  'name' => 'GetLanguages'
				, 'mappedName' => 'GetLanguages'
				, 'returnType' => '[[java.lang.String'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

		)
	)
);

?>
