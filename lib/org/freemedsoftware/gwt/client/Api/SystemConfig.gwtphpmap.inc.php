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
		  'className' => 'org.freemedsoftware.gwt.client.Api.SystemConfig'
		, 'mappedBy' => 'org.freemedsoftware.api.SystemConfig'
		, 'methods' => array (

			// Method: GetAll
			//
			//	Get entire list of configuration slots for building a configuration
			//	interface.
			//
			// Returns:
			//
			//	Array of hashes.
			//
			  array (
				  'name' => 'GetAll'
				, 'mappedName' => 'GetAll'
				, 'returnType' => 'java.util.HashMap'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: GetValue
			//
			// Parameters:
			//
			//	$config - Configuration key
			//
			// Returns:
			//
			//	Configuration value.
			//
			, array (
				  'name' => 'GetValue'
				, 'mappedName' => 'GetValue'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: GetConfigSections
			//
			//	Get list of configuration sections
			//
			// Returns:
			//
			//	Array of configuration sections.
			//
			, array (
				  'name' => 'SetConfigSections'
				, 'mappedName' => 'SetConfigSections'
				, 'returnType' => '[java.lang.String'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: SetValue
			//
			//	Set global configuration value
			//
			// Parameters:
			//
			//	$var - Configuration key
			//
			//	$val - Configuration value
			//
			// Returns:
			//
			//	Boolean, success
			//
			, array (
				  'name' => 'SetValue'
				, 'mappedName' => 'SetValue'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: SetValues
			//
			//	Batch set configuration values.
			//
			// Parameters:
			//
			//	$hash - Hash of configuration values.
			//
			// Returns:
			//
			//	Boolean, success.
			//
			, array (
				  'name' => 'SetValues'
				, 'mappedName' => 'SetValues'
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
