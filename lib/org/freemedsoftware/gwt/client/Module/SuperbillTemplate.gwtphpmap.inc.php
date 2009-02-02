<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

			// Method: GetTemplate
			//
			//	Retrieve complete superbill template.
			//
			// Parameters:
			//
			//	$id - Record ID of superbill template.
			//
			//	$patient - Patient ID
			//
			// Returns:
			//
			//	Hash containing:
			//	* dx
			//	* px
			//
			  array (
				  'name' => 'GetTemplate'
				, 'mappedName' => 'GetTemplate'
				, 'returnType' => 'java.lang.HashMap<java.lang.String,[Ljava.lang.HashMap<java.lang.String,java.lang.String>>;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
