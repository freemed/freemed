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
			//print_r($rpt);
			list($block, $breaks, $merge, $subquery) = $rpt;
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
				if ($format != 'Merge') {
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
					$obj->Process ( );
				} else {
					$obj = CreateObject('Agata.AgataMerge',
						$db,
						$agataConfig,
						$output, // filename
						$query, // currentquery
						null, // posaction
						'10', // left margin
						'20', // top margin
						'10', // spacing
						false // paging
					);
					$obj->MergePs( 
						join("\n", $merge),
						$subquery
					);
				}
				if (!is_object($obj)) {
					unlink($output);
					return false;
				}
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

	// Method: GetReports
	//
	//	Get array of report information for reports available
	//	to the system.
	//
	function GetReports ( ) {
		if (! ($d = dir(dirname(__FILE__).'/agata/report/')) ) {
			DIE(get_class($this)." :: could not open directory '".dirname(__FILE__)."/agata/report/'");
		}
		while ($entry = $d->read()) {
			if (eregi('\.report$', $entry)) {
				//print "dir entry = $entry\n";
				$reports[str_replace('.report', '', basename($entry))] = $this->_ReadMetaInformation(basename($entry));
			} // end checking file name match
		} // end while
		return $reports;
	} // end method GetReports

	// Method: ReportToFile
	//
	//	Moves a completed report to a specified filename.
	//
	// Parameters:
	//
	//	$filename - Target file name
	//
	// Returns:
	//
	//	Boolean, if successful
	//
	function ReportToFile ( $filename ) {
		if (!$this->report_file) { return false; }
		$fp = fopen ( $filename, 'w' );
		if (!$fp) { return $fp; }
		fwrite($fp, $this->report_file);
		fclose($fp);
		return true;
	} // end method ReportToFile

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
			case 'Csv':  $c = 'text/csv'; break;
			case 'Pdf':  $c = 'application/x-pdf'; break;
			case 'Ps':   $c = 'application/x-ps'; break;
			case 'Html': $c = 'text/html'; break;
			case 'Txt':  $c = 'text/plain'; break;

			// Merge outputs Postscript for now
			case 'Merge': $c = 'application/x-ps'; break;
		}
		if ($c) { Header('Content-type: '.$c); }
		print $this->report_file;
		die();
	} // end method ServeReport

	// Method: ServeMergeAsPDF
	//
	//	Serves a merge file (which natively outputs in Postscript)
	//	as a PDF.
	//
	// Returns:
	//
	//	Content-type header MIME type
	//
	function ServeMergeAsPDF ( ) {
		Header('Content-type: application/x-pdf');
		// Convert using ps2pdf
		$tmp = tempnam('/tmp', 'fm_agata');
		$this->ReportToFile($tmp);
		system("ps2pdf $tmp $tmp.pdf");
		readfile($tmp.'.pdf');
		unlink ($tmp);
		unlink ($tmp.'.pdf');
		die();
	} // end method ServeMergeAsPDF

	// Method: _ReadMetaInformation
	//
	//	Get report meta-information
	//
	// Returns:
	//
	//	Array containing an associative array containing the
	//	meta-information.
	//
	function _ReadMetaInformation ( $report ) {
		//print "checking $report\n";
		$fp = fopen(dirname(__FILE__).'/agata/report/'.$report, 'r');
		if (!$fp) { DIE(get_class($this).' :: could not open '.$report); }
		while (!feof($fp)) { $buffer .= fgets($fp, 1024); }
		fclose($fp);
		$lines = explode("\n", $buffer);
		foreach ($lines AS $_garbage => $line) {
			if (eregi('##[A-Za-z=\., ]*##', $line)) {
				// Process meta line
				//print "meta line = $line\n";
				$chunks = explode('##', $line);
				$meta = explode(',', $chunks[1]);
				foreach ($meta AS $garbage => $info) {
					list ($k, $v) = explode('=', $info);
					$return[$k] = $v;
				}
				return $return;
			}
		}
	} // end method _ReadMetaInformation

} // end class Agata

?>
