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

	// Constructor: Agata7
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

		$report = $this->api->getReport();
		$merge = $this->DetermineMergedFormat ( $report );

		if (!$merge) {
			$ok = $this->api->generateReport ( );
		} else {
			$ok = $this->api->generateDocument ( );
		}

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

	// Method: CreateForm
	//
	//	Create HTML_QuickForm object to represent parameters for a report.
	//
	// Parameters:
	//
	//	$report - Name of report
	//
	// Returns:
	//
	//	HTML_QuickForm object
	//
	function CreateForm ( $report ) {
		$form = CreateObject('PEAR.HTML_QuickForm', 'form', 'get');
                freemed::quickform_i18n(&$form);
		$form->addElement('hidden', 'module', $_REQUEST['module']);
		$form->addElement('hidden', 'report', $_REQUEST['report']);
		$form->addElement('hidden', 'action', 'view');
		$form->setDefaults(array('action' => 'view'));

		// Make sure module cache is loaded, just in case
		$_cache = freemed::module_cache();

		// Get meta-information from the report
		$this->api->setReportPath(dirname(dirname(__FILE__)).'/data/report/'.$report.'.report');
		$report = $this->api->getReport();

		// Display the header if one exists
		if ($report['Report']['Properties']['Description']) {
			$form->addElement( 'header', '', $report['Report']['Properties']['Description'] );
		}

		$merged = $this->DetermineMergedFormat($report);

		//if (!is_array($report['Report']['Parameters'])) { return NULL; }
		foreach ($report['Report']['Parameters'] AS $k => $v) {
			if ($k == 'module') { next; }
			list( $desc, $type, $detail ) = explode(':', $v['value']);
			switch ($type) {
				case 'date':
				$form->addElement('static', $k, $desc, fm_date_entry($k));
				break; // date

				case 'module':
				$form->addElement('static', $k, $desc, module_function($detail, 'widget', $k));
				break; // module

				case 'patient':
				$form->addElement('static', $k, $desc, freemed::patient_widget($k));
				break; // patient

				case 'select':
				$form->addElement('select', $k, $desc, explode(',', $detail));
				break; // select

				case 'text':
				$form->addElement('text', $k, $desc);
				break; // text

				default:
				break;
			}
		}

		// Show format selection
		if (!$merged) {
			$form->addElement('select', 'format', __("Report Format"), array (
				'csv' => 'CSV',
				'html' => 'HTML',
				'pdf' => 'PDF',
				'ps' => 'Postscript',
				'txt' => 'Plain Text'
			)); 
		} else {
			$form->addElement('select', 'format', __("Report Format"), array (
				'pdf' => 'PDF',
			));
		}

		$submit_group[] = &HTML_QuickForm::createElement( 'submit', 'submit_action', __("Generate") );
		$submit_group[] = &HTML_QuickForm::createElement( 'submit', 'submit_action', __("Cancel") );
		$form->addGroup( $submit_group, null, null, '&nbsp;' );
		return $form;
	} // end method CreateForm

	// Method: DetermineMergedFormat
	//
	//	Figure out if a report is supposed to be an Agata "Merge"
	//	report or not
	//
	// Parameters:
	//
	//	$report - Array, passed by reference, which contains the
	//	representation of a report's XML format.
	//
	// Returns:
	//
	//	Boolean, true if merged report, false if not.
	//
	function DetermineMergedFormat ( &$report ) {
		if (strlen($report['Report']['Merge']['ReportHeader']) > 10) {
			return true;
		} else {
			return false;
		}
	} // end method DetermineMergedFormat

	// Method: GetReports
	//
	//	Get array of report information for reports available
	//	to the system.
	//
	function GetReports ( ) {
		if (! ($d = dir(dirname(dirname(__FILE__)).'/data/report/')) ) {
			DIE(get_class($this)." :: could not open directory '".dirname(__FILE__)."/data/report/'");
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
                // Get meta-information from the report
                $this->api->setReportPath(dirname(dirname(__FILE__)).'/data/report/'.$report);
                $report = $this->api->getReport();

		return $report['Report']['Properties'];
	} // end method _ReadMetaInformation

} // end class Agata

?>
