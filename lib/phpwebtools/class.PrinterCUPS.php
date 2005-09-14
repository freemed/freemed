<?php
	// $Id$
	// $Author$

// Class: PHP.PrinterCUPS
class PrinterCUPS {

	function PrinterCUPS ( ) {
		// Currently no functionality in the constructor
	} // end constructor PrinterCUPS

	// Method: PrinterCUPS->GetPrinters
	//
	//	Get list of printers available to system.
	//
	// Returns:
	//
	//	Array of available printers.
	//
	function GetPrinters ( ) {
		$lpstat_raw = `lpstat -v`;
		$lpstat = explode ("\n", $lpstat_raw);
		foreach ($lpstat AS $__garbage => $line) {
			if (strpos($line, ':') > 10) {
				// Break at ":"
				list ($device_hash, $__garbage) = explode (":", $line);
				// Kill "device for " at beginning of line
				$devices[] = substr($device_hash, -(strlen($device_hash) - 11));
			}
		}
		return $devices;
	} // end method GetPrinters

	// Method: PrinterCUPS->PrintFile
	//
	//	Print specified file to specified printer.
	//
	// Parameters:
	//
	//	$printer - Name of the selected printer.
	//
	//	$file - File to be printed.
	function PrintFile ( $printer, $file ) {
		exec('lp -d '.$printer.' '.$file);
	} // end method PrintFile

} // end class PrinterCUPS

?>
