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
		  'className' => 'org.freemedsoftware.gwt.client.Module.Annotations'
		, 'mappedBy' => 'org.freemedsoftware.module.Annotations'
		, 'methods' => array (

			// Method: NewAnnotation
			//
			//	Append an annotation on an EMR attachment.
			//
			// Parameters:
			//
			//	$id - patient_emr table id for this EMR attachment
			//
			//	$text - Text of the annotation to be added.
			//
			// Returns:
			//
			//	Boolean, success.
			//
			  array (
				  'name' => 'NewAnnotation'
				, 'mappedName' => 'NewAnnotation'
				, 'returnType' => 'java.lang.Boolean'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: GetAnnotations
			//
			//	Get annotations, if present.
			//
			// Parameters:
			//
			//	$id - ID number
			//
			// Returns:
			//
			//	Array of annotations, otherwise false.
			//
			, array (
				  'name' => 'GetAnnotations'
				, 'mappedName' => 'GetAnnotations'
				, 'returnType' => '[java.util.HashMap'
				, 'params' => array (
					array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: outputAnnotations
			//
			//	Produce tooltip-friendly annotations from the output
			//	of <getAnnotations>.
			//
			// Parameters:
			//
			//	$annotations - Array of annotations
			//
			// Returns:
			//
			//	XHTML-formatted annotation string
			//
			, array (
				  'name' => 'OutputAnnotations'
				, 'mappedName' => 'OutputAnnotations'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					array ( 'type' => '[java.util.HashMap' )
				)
				, 'throws' => array ( )
			)

			// Method: prepareAnnotation
			//
			//	Prepare an annotation for being embedded in a Javascript
			//	string.
			//
			// Parameters:
			//
			//	$a - Annotation text.
			//
			// Returns:
			//
			//	Javascript string formatted text.
			//
			, array (
				  'name' => 'PrepareAnnotation'
				, 'mappedName' => 'PrepareAnnotation'
				, 'returnType' => 'java.lang.String'
				, 'params' => array (
					array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: lookupPatient
			//
			//	Get patient from other information given.
			//
			// Parameters:
			//
			//	$module - Module name
			//
			//	$id - Record id
			//
			// Returns:
			//
			//	Patient id number
			//
			, array (
				  'name' => 'LookupPatient'
				, 'mappedName' => 'LookupPatient'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
					, array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
