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
		$return['report_sp'] = $r['report_sp'];
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

	// Method: GenerateReport
	//
	//	Actual reporting generation routine.
	//
	// Parameters:
	//
	//	$uuid - Report UUID
	//
	//	$format - Format to return the report in
	//
	//	$param - Array of parameters
	//
	// Returns:
	//
	//	Report
	//
	public function GenerateReport ( $uuid, $format, $param ) {
		$report = $this->GetReportParameters( $uuid );

		// Sanity checking
		if (!$report['report_name']) { return false; }

		$s = CreateObject('org.freemedsoftware.api.Scheduler');
		foreach ($report['params'] AS $k => $v) {
			if ( !$v['optional'] and !$param[$k] ) {
				syslog(LOG_INFO, get_class($this)."| parameter $k failed for report $uuid");
				return false;
			}

			switch ($v['type']) {
				case 'Date':
				$pass[] = $GLOBALS['sql']->quote( $s->ImportDate( $param[$k] ) );
				break;

				default:
				$pass[] = $GLOBALS['sql']->quote( $param[$k] );
				break;
			}
		}

		// Form query
		$query = "CALL ".$report['report_sp']." ( ". @join( ', ', $pass )." ); ";
		//print_r($result); die();

		switch ( strtolower( $format ) ) {
			case 'csv':
			$csv = CreateObject( 'org.freemedsoftware.core.CSV' );
			$csv->ImportSQLQuery( $query );
			$csv->Export();
			break; // csv

			case 'html':
			$result = $GLOBALS['sql']->queryAll( $query );
			$buf = "<html><head><title>".htmlentities( $report['report_name'] )."</title></head>\n";
			$buf .= "<body>";
			$buf .= "<h1>".htmlentities( $report['report_name'] )."</h1>\n";
			$buf .= "<table>\n";
			$buf .= "\t<thead>\n";
			$buf .= "\t\t<tr>\n";
			foreach ( $result[0] AS $k => $v ) {
				$buf .= "\t\t\t<th>".htmlentities( $k )."</th>\n";
			}
			$buf .= "\t\t</tr>\n";
			$buf .= "\t</thead>\n";
			$buf .= "\t<tbody>\n";
			foreach ( $result AS $v ) {
				$buf .= "\t\t<tr>\n";
				foreach ( $v AS $val ) {
					$buf .= "\t\t\t<td>".htmlentities( $val )."</td>\n";
				}
				$buf .= "\t\t</tr>\n";
			}
			$buf .= "\t</tbody>\n";
			$buf .= "</table>\n";
			$buf .= "</body></html>";
			die ( $buf );
			break; // html

			case 'xml':
			$result = $GLOBALS['sql']->queryAll( $query );
			$xml = new SimpleXMLElement("<Report Timestamp=\"".mktime()."\" Name=\"".htmlentities( $report['report_name'] )."\"></Report>");
			foreach ($result AS $r ) {
				$row = $xml->addChild( 'Record' );
				foreach ( $r AS $column => $value ) {
					$column = ereg_replace( '[^A-Za-z0-9]+', '', $column );
					$row->$column = "$value";
				}
			}
			die( $xml->asXML( ) );
			break; // xml
	
			default:
			return $result;
			break;
		}
	} // end method GenerateReport

} // end class Reporting

register_module("Reporting");

?>
