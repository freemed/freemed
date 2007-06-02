<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.core.PrinterWrapper
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
	public function __construct ( $driver = '' ) {
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
		$this->driver = CreateObject( 'org.freemedsoftware.core.Printer'.$this->type );

		// Check for driver loaded properly
		if (!is_object($this->driver)) {
			die('org.freemedsoftware.core.PrinterWrapper: invalid driver "Printer'.$this->type.'"');
		}
	} // end constructor

	// Method: IsCUPS
	//
	//	Determines if the system uses CUPS (Common Unix Printing
	//	System).
	//
	// Returns:
	//
	//	Boolean, determined if the system uses CUPS
	//
	public function IsCUPS ( ) {
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

	// Method: SetDebug
	//
	//	Sets the debug status of the wrapper.
	//
	// Parameters:
	//
	//	$debug - Boolean, whether debug is on. If not given,
	//	defaults to true.
	//
	public function SetDebug ( $debug = true ) {
		$this->debug = $debug;
	} // end method SetDebug

} // end class PrinterWrapper

?>
