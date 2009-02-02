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
		  'className' => 'org.freemedsoftware.gwt.client.Module.UnfiledDocuments'
		, 'mappedBy' => 'org.freemedsoftware.module.UnfiledDocuments'
		, 'methods' => array (

			// Method: NumberOfPages
			//
			//	Expose the number of pages of a Djvu document
			//
			// Parameters:
			//
			//	$id - Table record id
			//
			// Returns:
			//
			//	Integer, number of pages in the specified document
			//
			  array (
				  'name' => 'NumberOfPages'
				, 'mappedName' => 'NumberOfPages'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
				)
				, 'throws' => array ( )
			)

			// Method: batchSplit
			//
			//	Split multiple faxed documents.
			//
			// Parameters:
			//
			//	$id - Record id
			//
			//	$splitafter - Array of "splits"
			//
			, array (
				  'name' => 'BatchSplit'
				, 'mappedName' => 'BatchSplit'
				, 'returnType' => TypeSignatures::$NULL
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => '[Ljava.lang.Integer;' )
				)
				, 'throws' => array ( )
			)

			// Method: GetAll
			//
			//	Get all records.
			//
			// Returns:
			//
			//	Array of hashes.
			//
			, array (
				  'name' => 'GetAll'
				, 'mappedName' => 'GetAll'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

			// Method: Faxback
			//
			// Parameters:
			//
			//	$id - Record id
			//
			//	$faxback - Fax number to send faxback to
			//
			, array (
				  'name' => 'Faxback'
				, 'mappedName' => 'faxback'
				, 'returnType' => TypeSignatures::$NULL
				, 'params' => array (
					  array ( 'type' => 'java.lang.Integer' )
					, array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

			// Method: GetCount
			//
			//	Retrieve number of unfiled documents in the system.
			//
			// Returns:
			//
			//	Current number of unfiled documents in the system.
			//
			, array (
				  'name' => 'GetCount'
				, 'mappedName' => 'GetCount'
				, 'returnType' => 'java.lang.Integer'
				, 'params' => array ( )
				, 'throws' => array ( )
			)

		)
	)
);

?>
