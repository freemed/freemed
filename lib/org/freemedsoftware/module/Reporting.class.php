<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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
	var $MODULE_VERSION = "0.1.1";
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
	//	$rpt_cat - (optional) Reporting category.
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* report_name
	//	* report_desc
	//	* report_uuid
	//
	public function GetReports ( $locale = NULL, $rpt_cat = NULL ) {
		freemed::acl_enforce( 'reporting', 'menu' );
		$query = "SELECT report_name, report_desc, report_uuid FROM reporting WHERE report_locale=". $GLOBALS['sql']->quote( $locale == NULL ? DEFAULT_LANGUAGE : $locale ). " " . ( $rpt_cat != NULL ? " AND report_category=".$GLOBALS['sql']->quote( $rpt_cat ) : "" ) . " ORDER BY report_name";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetReports

	// Method: GetReportByName
	//
	//	Get report against provided name.
	//
	// Parameters:
	//
	//	$reportName - name of the report to be searched

	//
	// Returns:
	//
	//	* report_uuid
	//
	public function GetReport ( $reportName ) {
		freemed::acl_enforce( 'reporting', 'menu' );
		$query = "SELECT report_uuid FROM reporting WHERE report_name =". $GLOBALS['sql']->quote( $reportName);
		$result = $GLOBALS['sql']->queryrow( $query );
		return $result['report_uuid'];
	} // end method GetReports

	// Method: GetReportParameters
	//
	//	Get information on this report, including parameters.
	//
	// Parameters:
	//
	//	$uuid - UUID of designated report
	//
	//	$flattened - Flatten results (default true)
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetReportParameters ( $uuid, $flatten = true ) {
		freemed::acl_enforce( 'reporting', 'generate' );
		$query = "SELECT * FROM reporting WHERE report_uuid=".$GLOBALS['sql']->quote( $uuid );
		$r = $GLOBALS['sql']->queryRow( $query );
		$return = array ();
		$return['report_name'] = $r['report_name'];
		$return['report_desc'] = $r['report_desc'];
		$return['report_type'] = $r['report_type'];
		$return['report_sp'] = $r['report_sp'];
		$return['report_formatting'] = $r['report_formatting'];
		$return['report_param_count'] = $r['report_param_count'];
		if ($r['report_param_count'] == 0) {
			if ( ! ( defined('FORCE_CAST_TO_PHP_PRIMITIVE_TYPES') || $flatten ) ) {
				$return['params'] = array();
			}
		} else {
			$names = explode( ',', $r['report_param_names'] );
			$types = explode( ',', $r['report_param_types'] );
			$options = explode( ',', $r['report_param_options'] );
			$optional = explode( ',', $r['report_param_optional'] );
			for ( $p = 0; $p < $r['report_param_count'] ; $p++ ) {
				if ( defined('FORCE_CAST_TO_PHP_PRIMITIVE_TYPES') || $flatten ) {
					// Force flattening of output for GWT
					$return['report_param_name_'.$p] = $names[$p];
					$return['report_param_type_'.$p] = $types[$p];
					$return['report_param_options_'.$p] = $options[$p];
					$return['report_param_optional_'.$p] = ( $optional[$p] ? true : false );
				} else {
					$return['params'][$p] = array (
						'name' => $names[$p],
						'type' => $types[$p],
						'options' => $options[$p],
						'optional' => ( $optional[$p] ? true : false )
					);
				}
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
		freemed::acl_enforce( 'reporting', 'generate' );
		
		$report = $this->GetReportParameters( $uuid, false );
		
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
				
				case 'Facility':
				$pass[] = $GLOBALS['sql']->quote( ((int) HTTP_Session2::get('facility_id')));
				break;
				
				case 'BottleID':
				case 'TestStatus':
				case 'EMRModule':
				case 'SupportModule':
				$pass[] = $param[$k]+0;
				break;

				case 'User':
				$pass[] = (int) freemed::user_cache()->user_number;
				break;

				
				default:
				$pass[] = $GLOBALS['sql']->quote( $param[$k] );
				break;
			}
		}
		// Form query
		$query = "CALL ".$report['report_sp']." ( ". @join( ', ', $pass )." ); ";
		
		//print_r($result); die();

		// Handle graphing, or at least non-standard, reports
		if ( $report['report_type'] != 'standard' ) {
			return call_user_func_array( array( &$this, 'GenerateReport_'.ucfirst($report['report_type']) ), array( $report, $format, $query, $pass ) );
		}

		switch ( strtolower( $format ) ) {
			case 'csv':
			$csv = CreateObject( 'org.freemedsoftware.core.CSV' );
			$csv->ImportSQLQuery( $query );
			$csv->Export();
			break; // csv

			case 'html':
			$result = $GLOBALS['sql']->queryAllStoredProc( $query );
			
			$buf = "<html><head><title>".htmlentities( $report['report_name'] )."</title></head>\n";
			$buf .= "<body>";
			$buf .= "<h1>".htmlentities( $report['report_name'] )."</h1>\n";
			$buf .= "<h3>". __("Printed on") . " " . date('r') . "</h3>\n";
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

			case 'pdf':
			$pdf = CreateObject( 'org.freemedsoftware.core.FPDF_Report' );
			$pdf->LoadData( $report['report_name'], $query );
			$pdf->Export();
			break; // pdf

			case 'xml':
			$result = $GLOBALS['sql']->queryAllStoredProc( $query );
			$xml = new SimpleXMLElement("<Report Timestamp=\"".mktime()."\" Name=\"".htmlentities( $report['report_name'] )."\"></Report>");
			foreach ($result AS $r ) {
				$row = $xml->addChild( 'Record' );
				foreach ( $r AS $column => $value ) {
					$column = ereg_replace( '[^A-Za-z0-9]+', '', $column );
					$row->$column = "$value";
				}
			}
			Header('Content-type: text/xml');
			die( $xml->asXML( ) );
			break; // xml
	
			default:
			return $result;
			break;
		}
	} // end method GenerateReport
	
	
	//----- Pluggable methods go below -----

	// Method: GenerateReport_Graph
	//
	//	Internal method used to generate graphs.
	//
	// Parameters:
	//
	//	$param - Hash of report information, as returned by 
	//	<GetReportParameters>
	//
	//	$format - Format of the report
	//
	//	$query - SQL query as created by <GenerateReport>
	//
	protected function GenerateReport_Graph ( $param, $format, $query, $params = NULL ) {
		freemed::acl_enforce( 'reporting', 'generate' );
		// Execute query
		$res = $GLOBALS['sql']->queryAllStoredProc( $query );

		// Get keys
		foreach ( $res[0] AS $k => $v ) { if (!is_integer($k)) { $ks[] = $k; } }
		$primary_key = $ks[0]; unset( $ks[0] );
		foreach ( $ks AS $v ) { $keys[] = $v; } 

		// Create Image_Graph
		LoadObjectDependency( 'net.php.pear.Image_Graph' );
		$g =& Image_Graph::factory( 'graph', array( 800, 600 ) );
		$f =& $g->addNew( 'ttf_font', dirname(__FILE__).'/../data/fonts/FreeSans.ttf' );
		$f->setSize( 10 );
		$g->setFont( $f );
		$g->add(
			Image_Graph::vertical(
				Image_Graph::vertical(
					Image_Graph::factory( 'title', array( $param['report_name'], 12 ) ),
					Image_Graph::factory( 'title', array( __('Generated').' '.date('r'), 8 ) ),
					80
				),
				Image_Graph::vertical(
					$plotarea = Image_Graph::factory( 'plotarea' ),
					$legend = Image_Graph::factory( 'legend' ),
					85
				),
				5
			)
		);
		$legend->setPlotarea( $plotarea );

		// Fancy background stuff
		$FillArray =& Image_Graph::factory( 'Image_Graph_Fill_Array' );
		$FillArray->addColor('blue@0.2');
		$FillArray->addColor('yellow@0.2');
		$FillArray->addColor('green@0.2');
		$plotarea->setFillStyle( $FillArray );
		$plotarea->setFillColor('silver@0.3'); 
		$Grid =& $plotarea->addNew( 'line_grid', IMAGE_GRAPH_AXIS_X );
		$Grid->setLineColor( 'silver');

		foreach ( $keys AS $k => $v ) {	
			$data[ $k ] =& Image_Graph::factory('dataset');
			$data[ $k ]->setName( $v );

			// Load data
			foreach ( $res AS $r ) {
				$data[ $k ]->addPoint( $r[ $primary_key ], $r[ $v ] );
			}
			$plot[$k] =& $plotarea->addNew( 'line', array( &$data[ $k ] ) );
			//$plot[$k] =& $plotarea->addNew( 'Image_Graph_Plot_Smoothed_Area', array( &$data[ $k ] ) );
		}

		// Check plot colors
		$colors = array ( 'red', 'blue', 'green', 'yellow', 'purple', 'black', 'orange' );
		foreach ( $plot AS $k => $v ) {
			$plot[ $k ]->setLineColor( $colors[ $k ] );
			//$plot[ $k ]->setFillColor( $colors[ $k ].'@0.3' );
		}

		$g->setPadding( 10 );
		$g->done( );
	} // end method GenerateReport_Graph

	// Method: GenerateReport_Rlib
	//
	//	Internal method used to generate rlib-based reports
	//
	// Parameters:
	//
	//	$param - Hash of report information, as returned by 
	//	<GetReportParameters>
	//
	//	$format - Format of the report
	//
	//	$query - SQL query as created by <GenerateReport>
	//
	protected function GenerateReport_Rlib ( $param, $format, $query, $params = NULL ) {
		freemed::acl_enforce( 'reporting', 'generate' );
		switch ( $format ) {
			case 'html':	$outformat = 'html';	$ext = 'html';	break;
			case 'csv':	$outformat = 'csv';	$ext = 'csv';	break;
			case 'text':	$outformat = 'text';	$ext = 'txt';	break;
			case 'pdf':	$outformat = 'pdf';	$ext = 'pdf';	break;
			default:	$outformat = 'pdf';	$ext = 'pdf';	break;
		} // end switch format

		@dl( "rlib.so" );
		if ( ! function_exists( 'rlib_init' ) ) {
			syslog( LOG_ERR, get_class($this)."| rlib PHP extension not found" );
		}

		// Global scope things to be passed into m.* namespace
		$GLOBALS['installation'] = INSTALLATION;
		$GLOBALS['generated_on'] = date( 'r' );

		$rlib = rlib_init( );
		rlib_add_datasource_mysql( $rlib, "local_mysql", DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		rlib_add_query_as( $rlib, "local_mysql", $query, "result" );
		rlib_add_report_from_buffer( $rlib, $param['report_formatting'] );
		rlib_set_output_format_from_text( $rlib, $outformat );
		rlib_execute( $rlib );
		switch ( $outformat ) {
			case 'pdf':
			Header( 'Content-type: application/pdf' );
			break;

			default:
			Header( rlib_get_content_type( $rlib ) );
			break;
		}
		Header ("Content-Disposition: inline; filename=\"".mktime().".${ext}\"");
		rlib_spool( $rlib );
		rlib_free( $rlib );
		die();
	} // end method GenerateReport_Rlib

	// Method: GenerateReport_Jasper
	//
	//	Internal method used to generate JasperReports-based reports
	//
	// Parameters:
	//
	//	$param - Hash of report information, as returned by 
	//	<GetReportParameters>
	//
	//	$format - Format of the report
	//
	//	$query - SQL query as created by <GenerateReport>
	//
	//	$params - Optional array of parameters to pass to the report.
	//
	protected function GenerateReport_Jasper ( $param, $format, $query, $params = NULL ) {
		//return $params;
		freemed::acl_enforce( 'reporting', 'generate' );
		switch ( $format ) {
			case 'html':	$outformat = 'HTML';	$ext = 'html';	break;
			case 'xml':	$outformat = 'XML';	$ext = 'xml';	break;
			case 'pdf':	$outformat = 'PDF';	$ext = 'pdf';	break;
			case 'xls':	$outformat = 'XLS';	$ext = 'xls';	break;
			default:	$outformat = 'PDF';	$ext = 'pdf';	break;
		} // end switch format

		// Create connection string
		$jdbc_url = "jdbc:mysql://" . DB_HOST . ":3306/" . DB_NAME;

		// Prepare parameters
		$parameters = "";
		if ($params != NULL && count($params) > 0) {
			foreach ($params AS $k => $p) {
				$parameters .= " --param=" . $p;
				switch ($param['params'][$k]['type']) {
					case 'Date':
					$parameters .= " --paramformat=date";
					break;

					case 'Facility':
					case 'User':
					case 'BottleID':
					case 'TestStatus':
					case 'EMRModule':
					case 'SupportModule':
					$parameters .= " --paramformat=int";
					break;

					default:
					$parameters .= " --paramformat=string";
					break;
				}
			}
		}

		$reportprefix = $param['report_formatting'] . "." . mktime();

		// Wrap and generate
		$cmd = "java -jar " . PHYSICAL_LOCATION . "/scripts/jasper/JasperWrapper.jar --dburl=" . escapeshellarg( $jdbc_url ) . " --dbuser=" . escapeshellarg( DB_USER) . " --dbpass=" . escapeshellarg( DB_PASSWORD ) . " --ipath=" . escapeshellarg( PHYSICAL_LOCATION . '/data/report/' ) . " --opath=" . escapeshellarg( PHYSICAL_LOCATION . '/data/cache/' ) . " --oprefix=" . escapeshellarg( $reportprefix ) . " --format=" . escapeshellarg( $outformat ) . " --report=" . escapeshellarg( $param['report_formatting'] . ".jrxml" ) . " " . $parameters;
		syslog(LOG_INFO, "Jasper cmd = $cmd");
		// Execute actual report generation
		`$cmd 2>&1 | logger -t JasperWrapper`;
		$output_file = PHYSICAL_LOCATION . "/data/cache/" . $reportprefix . "." . $ext;
		
		switch ( $format ) {
			case 'xls':
			Header( 'Content-type: application/x-ms-excel' );
			break;

			case 'html':
			Header( 'Content-type: text/html' );
			break;

			case 'xml':
			Header( 'Content-type: text/xml' );
			break;

			case 'pdf':
			default:
			Header( 'Content-type: application/pdf' );
			break;
		}
		
		Header ("Content-Transfer-Encoding:­binary"); 
		Header ("Content-Disposition: inline; filename=\"" . $param['report_formatting'] . ".${ext}\"");
		Header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		Header ("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		readfile( $output_file );		
		@unlink( $output_file );
		@unlink( $output_file . '_files' );
		die();
	} // end method GenerateReport_Jasper

} // end class Reporting

register_module("Reporting");

?>
