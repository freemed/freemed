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
		  'className' => 'org.freemedsoftware.gwt.client.Module.Reporting'
		, 'mappedBy' => 'org.freemedsoftware.module.Reporting'
		, 'methods' => array (

			// Method: GetReports
			//
			//	Get list of reports.
			//
			// Parameters:
			//
			//	$locale - (optional) Locale of reports to look up. Defaults to
			//	DEFAULT_LANGUAGE as defined in lib/settings.php
			//
			// Returns:
			//
			//	Array of hashes containing:
			//	* report_name
			//	* report_desc
			//	* report_uuid
			//
			  array (
				  'name' => 'GetReports'
				, 'mappedName' => 'GetReports'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)


			// Method: GetReportParameters
			//
			//	Get information on this report, including parameters.
			//
			// Parameters:
			//
			//	$uuid - UUID of designated report
			//
			// Returns:
			//
			//	Array of hashes
			//
			, array (
				  'name' => 'GetReportParameters'
				, 'mappedName' => 'GetReportParameters'
				, 'returnType' => '[Ljava.util.HashMap;'
				, 'returnTypeCRC' => '3558356060[L962170901;'
				, 'params' => array (
					  array ( 'type' => 'java.lang.String' )
				)
				, 'throws' => array ( )
			)

		)
	)
);

?>
