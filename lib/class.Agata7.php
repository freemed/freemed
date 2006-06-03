<?php
	// $Id$
	// $Author$

// Include the AgataAPI class
include_once (dirname(__FILE__).'/agata7/classes/core/AgataAPI.class');

// Class: FreeMED.Agata7
//
//	Wrapper for Agata Reports 7.x (official version)
//
class Agata7 {

	function Agata7 ( ) {
		$this->api = new AgataAPI ( );
		// Set defaults
		$this->api->setLanguage('en'); // FIXME : pull from actual language
	}

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
	//	$parameters - (optional) Additional qualifiers as an
	//	associative array.
	//
	// Returns:
	//
	//	Boolean, successful
	//
	function CreateReport ( $format, $report, $parameters = NULL ) {
		// Create temporary file name
		$output = tempnam('/tmp', 'fm_agata_');

		$this->api->setReportPath(dirname(dirname(__FILE__)).'/data/report/'.$report.'.report');

		// Set core database access information based on
		// stuff that FreeMED defines in lib/settings.php
		$db = array (
			'host' => DB_HOST,
			'name' => DB_NAME,
			'user' => DB_USER,
			'pass' => DB_PASSWORD,
			'type' => 'mysql' // hardcoded for now
		);
		$this->api->setProject($db);

		$this->api->setOutputPath($output);
		$this->api->setFormat(strtolower($format));
		if (strtolower($format) == 'pdf') { $this->api->setLayout('default-PDF'); }
		if (is_array($parameters)) {
			foreach ($parameters AS $k => $v) {
				$this->api->setParameter($k, $v);
			}
		}

		$ok = $this->api->generateReport ( );

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
	} // end method CreateReport

	// Method: GetReports
	//
	//	Get array of report information for reports available
	//	to the system.
	//
	function GetReports ( ) {
		if (! ($d = dir(dirname(dirname(__FILE__)).'/data/report/')) ) {
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
		switch (strtolower($this->report_format)) {
			case 'csv':  $c = 'text/csv'; $e = 'csv'; break;
			case 'pdf':  $c = 'application/x-pdf'; $e = 'pdf'; break;
			case 'ps':   $c = 'application/x-ps'; $e = 'ps'; break;
			case 'html': $c = 'text/html'; break;
			case 'txt':  $c = 'text/plain'; break;
			default:     $c = 'application/x-pdf'; $e = 'pdf'; break;
		}
		if ($e) { Header('Content-Disposition: inline; filename="'.mktime().'.'.$e.'"'); }
		if ($c) { Header('Content-type: '.$c); }
		print $this->report_file;
		die();
	} // end method ServeReport

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
			if (eregi('##[A-Za-z=\.\/, ]*##', $line)) {
				// Process meta line
				//print "meta line = $line\n";
				$chunks = explode('##', $line);
				$meta = explode(',', $chunks[1]);
				foreach ($meta AS $garbage => $info) {
					list ($k, $v) = explode('=', $info);
					if (strpos($v, '/') !== false) {
						$return[$k] = explode('/', $v);
					} else {
						$return[$k] = $v;
					}
				}
				return $return;
			}
		}
	} // end method _ReadMetaInformation

} // end class Agata

?>
