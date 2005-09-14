<?php
	// $Id$
	// $Author$

// Class: PHP.PrinterLPD
class PrinterLPD {

	function PrinterLPD ( ) {
		// Currently no functionality in the constructor
	} // end constructor PrinterCUPS

	// Method: PrinterLPD->GetPrinters
	//
	//	Get list of printers available to system.
	//
	// Returns:
	//
	//	Array of available printers.
	//
	function GetPrinters ( ) {
		// Read in the printcap file
		$fp = fopen('/etc/printcap', 'r');
		if (!$fp) {
			if ($this->debug) {
				print "NO PRINTCAP AVAILABLE<br/>\n";
			}
			return false;
		}
		while (!feof($fp)) { $printcap .= fgets($fp, 4096); }
		fclose($fp);

		// Create lines (exploding by linebreak
		$lines = explode ("\n", $printcap);

		// Remove comments and null lines
		foreach ($lines AS $k => $v) {
			// Remove comments
			if (substr($v, 0, 1) == '#') {
				unset ($lines[$k]);
			} elseif (strlen($v) < 5) {
				unset ($lines[$k]);
			}
		}

		// Loop through and create printers list
		foreach ($lines AS $k => $v) { 
			// Break by separators
			$line = explode (':', $v);
			
			// Grab name
			list ($devices[], $__garbage) = explode('|', $line[0]);
		}
		
		return $devices;
	} // end method GetPrinters

	// Method: PrinterLPD->PrintFile
	//
	//	Print specified file to specified printer.
	//
	// Parameters:
	//
	//	$printer - Name of the selected printer.
	//
	//	$file - File to be printed.
	//
	function PrintFile ( $printer, $file ) {
		exec('lpr -P '.$printer.' '.$file);
	} // end method PrintFile

} // end class PrinterLPD

?>
