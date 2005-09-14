<?php
	// $Id$
	// $Author$

// Class: PHP.PrinterWrapper
class PrinterWrapper {

	// Variable: PrinterWrapper->driver
	//
	//	Object containing the methods used to print.
	//
	var $driver;

	// Variable: PrinterWrapper->type
	//
	//	Text name of the current printer driver.
	//
	var $type;

	// Variable: PrinterWrapper->debug
	//
	//	Boolean, determines if debug information is displayed
	//
	var $debug;

	// Method: PrinterWrapper constructor
	//
	// Parameters:
	//
	//	$driver - (optional) Force driver to specified. The
	//	driver is loaded as PHP.Printer + $driver. Defaults
	//	to autodetect.
	//
	function PrinterWrapper ( $driver = '' ) {
		// If not, use autodetection routine. This should probably
		// be abstracted to the individual drivers, but we'll just
		// assume CUPS and LPD (and eventually a WinXX driver as
		// well) are the only ones we autodetect.
		if ($driver == '') {
			if ($this->IsCUPS()) {
				$this->type = 'CUPS';
			} else {
				$this->type = 'LPD';
			}
		} else {
			// Use passed driver instead
			$this->type = $driver;
		}

		// Load driver
		$this->driver = CreateObject('PHP.Printer'.$this->type);

		// Check for driver loaded properly
		if (!is_object($this->driver)) {
			die('PHP.PrinterWrapper: invalid driver "'.
					'Printer'.$this->type.'"');
		}
	} // end constructor PrinterWrapper

	// Method: PrinterWrapper->IsCUPS
	//
	//	Determines if the system uses CUPS (Common Unix Printing
	//	System).
	//
	function IsCUPS ( ) {
		// Determine if the cups tools are present
		if (file_exists('/usr/bin/lpstat')) { return true; }
		if (file_exists('/usr/sbin/lpstat')) { return true; }
		if (file_exists('/usr/local/bin/lpstat')) { return true; }
		if (file_exists('/usr/local/sbin/lpstat')) { return true; }
		if (file_exists('/bin/lpstat')) { return true; }
		if (file_exists('/sbin/lpstat')) { return true; }

		// If all tests fail, return false
		return false;
	} // end method IsCUPS

	// Method: PrinterWrapper->SetDebug
	//
	//	Sets the debug status of the wrapper.
	//
	// Parameters:
	//
	//	$debug - Boolean, whether debug is on. If not given,
	//	defaults to true.
	//
	function SetDebug ( $debug = true ) {
		$this->debug = $debug;
	} // end method SetDebug

} // end class PrinterWrapper

?>
