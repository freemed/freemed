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
		  'className' => 'org.freemedsoftware.gwt.client.Module.Allergies'

		, 'mappedBy' => 'org.freemedsoftware.module.Allergies'
		, 'methods' => array (

			// Method: GetMostRecent
			//
			//	Get atoms for most recent set of allergies.
			//
			// Parameters:
			//
			//	$patient - Patient ID
			//
			// Returns:
			//
			//	Array of allergy "atoms".
			//
			// SeeAlso:
			//	<GetAtoms>
			//
			  array (
				  'name' => 'GetMostRecent'
				, 'mappedName' => 'GetMostRecent'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: GetAtoms
			//
			//	Get all atoms associated with an allergy record.
			//
			// Parameters:
			//
			//	$mid - Medications id
			//
			// Returns:
			//
			//	Array of hashes
			//
			, array (
				  'name' => 'GetAtoms'
				, 'mappedName' => 'GetAtoms'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: SetAtoms
			//
			// Parameters:
			//
			//	$patient - Patient id
			//
			//	$mid - Medications id
			//
			//	$atoms - Array of hashes
			//	* altered - Boolean flag to determine whether or not this entry has been altered.
			//	* id - 0 if new, otherwise the current id
			//
			// Returns:
			//
			//	Boolean, success.
			//
			, array (
				  'name' => 'SetAtoms'
				, 'mappedName' => 'SetAtoms'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => '[Ljava.util.HashMap;' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
