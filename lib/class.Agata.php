<?php
	// $Id$
	// $Author$

// Include the global configuration for Agata
include (dirname(__FILE__).'/agata/config.php');

// Class: FreeMED.Agata
//
//	Wrapper for internal version of Agata Reports (5 final, forked).
//
class Agata {

	function Agata ( ) { }

	// Method: CreateReport
	//
	//	Create a report and store the information in this object.
	//
	// Parameters:
	//
	//	$format - Rendering engine used to create the output.
	//	Valid values are: Pdf, Ps, Html, etc
	//
	//	$report - Name of the report file used to create this
	//	report.
	//
	//	$title - Title to be printed at the top of the report.
	//
	//	$parameters - (optional) Additional qualifiers as an
	//	associative array.
	//
	// Returns:
	//
	//	Boolean, successful
	//
	function CreateReport ( $format, $report, $title, $parameters = NULL ) {
		// Create temporary file name
		$output = tempnam('/tmp', 'fm_agata_');

		// Create the Agata core
		$core = CreateObject('Agata.AgataCore');

		// Set core database access information based on
		// stuff that FreeMED defines in lib/settings.php
		$db = array (
			'DbHost' => DB_HOST,
			'DbName' => DB_NAME,
			'DbUser' => DB_USER,
			'DbPass' => DB_PASSWORD,
			'DbType' => 'mysql' // hardcoded for now
		);
		
		$rpt = $core->ReadSqlFile ( dirname(__FILE__). 
			'/agata/report/'.$report.'.report' );

		if ($rpt) {
			list($block, $breaks, $__garbage) = $rpt;
			$sqldef = $core->BlockToSql($block);

			$connection = CreateObject('Agata.Connection');
			if ($connection->Open($db, false)) {
				$query = $core->CreateQuery(
					$db,
					$sqldef,
					$parameters,
					false
				);
				//print "query = $query<br/>\n";

				// Load configuration into local scope. Note
				// that you have to use require, not include.
				require(dirname(__FILE__).'/agata/config.php');

				// Create the proper report object
				$obj = CreateObject('Agata.Agata'.$format,
					$db,
					$agataConfig,
					$output,
					$query,
					$breaks,
					false,
					true,
					false,
					$title
				);
				if (!is_object($obj)) {
					unlink($output);
					return false;
				}
				$obj->Process ( );
				$connection->Close ( );

				// Read file into buffer
				$fp = fopen($output, 'r');
				if ($fp) {
					while (!feof($fp)) {
						$buffer .= fgets($fp, 4096);
					}
					fclose($fp);
					$this->report_format = $format;
					$this->report_file = $buffer;
					unlink($output);
					return true;
				} else {
					$this->report_format = NULL;
					$this->report_file = NULL;
					unlink($output);
					return false;
				}
			} else {
				die('Cannot connect to database');	
			} // end if connection
		} // end if rpt
	} // end method CreateReport

	// Method: ServeReport
	//
	//	Get report MIME type based on stored information
	//
	// Returns:
	//
	//	Content-type header MIME type
	//
	function ServeReport ( ) {
		switch ($this->report_format) {
			case 'Pdf':  $c = 'application/x-pdf'; break;
			case 'Html': $c = 'text/html'; break;
		}
		if ($c) { Header('Content-type: '.$c); }
		print $this->report_file;
		die();
	} // end method ServeReport

} // end class Agata

?>
