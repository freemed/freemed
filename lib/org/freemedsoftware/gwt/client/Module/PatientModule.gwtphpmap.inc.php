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
		  'className' => 'org.freemedsoftware.gwt.client.Module.PatientModule'
		, 'mappedBy' => 'org.freemedsoftware.module.PatientModule'
		, 'methods' => array (

			// Method: GetAddresses
			//
			//	Get all addresses associated with a patient.
			//
			// Parameters:
			//
			//	$patient - Patient id
			//
			// Returns:
			//
			//	Array of hashes
			//
			  array (
				  'name' => 'GetAddresses'
				, 'mappedName' => 'GetAddresses'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: SetAddresses
			//
			// Parameters:
			//
			//	$patient - Patient id
			//
			//	$addresses - Array of hashes
			//	* type - 2 character abbreviation
			//	* active - Boolean active flag
			//	* relate - 2 character abbreviation
			//	* line1 - Address line 1
			//	* line2 - Address line 2
			//	* csz - City state zip country hash
			//	* altered - Boolean flag to determine whether or not this entry has been altered.
			//	* id - 0 if new, otherwise the current id
			//
			// Returns:
			//
			//	Boolean, success.
			//
			, array (
				  'name' => 'SetAddresses'
				, 'mappedName' => 'SetAddresses'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => '[Ljava.util.HashMap;' )
				)
				, 'throws' => array ( )
			)


		)
	)
);

?>
