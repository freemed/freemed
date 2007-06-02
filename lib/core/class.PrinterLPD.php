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

// Class: org.freemedsoftware.core.PrinterLPD
class PrinterLPD {

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

	// Method: PrintFile
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
