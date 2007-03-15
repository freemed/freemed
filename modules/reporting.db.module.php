<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class Reporting extends SupportModule {

	var $MODULE_NAME = "Reporting";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "689d7681-2ea3-40b1-b3be-b56790c6e075";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "reporting";

	public function __construct () {
		// Call parent constructor
		parent::__construct();
	} // end constructor


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
	public function GetReports ( $locale = NULL ) {
		$query = "SELECT report_name, report_desc, report_uuid FROM reporting WHERE report_locale=". $GLOBALS['sql']->quote( $locale == NULL ? DEFAULT_LANGUAGE : $locale ). " ORDER BY report_name";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetReports

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
	public function GetReportParameters ( $uuid ) {
		$query = "SELECT * FROM reporting WHERE report_uuid=".$GLOBALS['sql']->quote( $uuid );
		$r = $GLOBALS['sql']->queryRow( $query );
		$return = array ();
		$return['report_name'] = $r['report_name'];
		$return['report_desc'] = $r['report_desc'];
		if ($r['report_param_count'] == 0) {
			$return['params'] = array();
		} else {
			$names = explode( ',', $r['report_param_names'] );
			$types = explode( ',', $r['report_param_types'] );
			$optional = explode( ',', $r['report_param_optional'] );
			for ( $p = 0; $p < $r['report_param_count'] ; $p++ ) {
				$return['params'][$p] = array (
					'name' => $names[$p],
					'types' => $types[$p],
					'optional' => ( $optional[$p] ? true : false )
				);
			}
		}
		return $return;
	} // end method GetReportParameters

} // end class Reporting

register_module("Reporting");

?>
