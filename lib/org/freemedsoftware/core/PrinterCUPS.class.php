<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.PrinterCUPS
class PrinterCUPS {

	public function __construct ( ) {
		// Currently no functionality in the constructor
	} // end constructor

	// Method: GetPrinters
	//
	//	Get list of printers available to system.
	//
	// Returns:
	//
	//	Array of available printers.
	//
	public function GetPrinters ( ) {
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

	// Method: PrintFile
	//
	//	Print specified file to specified printer.
	//
	// Parameters:
	//
	//	$printer - Name of the selected printer.
	//
	//	$file - File to be printed.
	public function PrintFile ( $printer, $file ) {
		exec('lp -d '.$printer.' '.$file);
	} // end method PrintFile

} // end class PrinterCUPS

?>
