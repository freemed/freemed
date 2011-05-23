<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.Printing
class Printing {

	private $printer;

	public function __construct ( $driver = '' ) {
		$this->printer = CreateObject( 'org.freemedsoftware.core.PrinterWrapper' );
	} // end constructor

	// Method: GetPrinters
	//
	//	Get list of printers for the system.
	//
	// Parameters:
	//
	//	$param - (optional) Qualifying string to search for in results.
	//
	// Returns:
	//
	//	Hash of printers where key = value
	//
	public function GetPrinters( $param = NULL ) {
		$p = $this->printer->driver->GetPrinters();
		foreach ( $p AS $v ) {
			if ($param != NULL) {
				if ( strpos( strtolower( $v ), strtolower( $param ) ) !== false ) {
					$r[ $v ] = $v;
				}
			} else {
				$r[ $v ] = $v;
			}
		}
		return $r;
	} // end method GetPrinters

	// Method: PrinterAvailable
	//
	//	Checks passed printer availability
	//
	// Parameters:
	//
	//	$param - Printer Name
	//
	// Returns:
	//
	//	boolean - return true if available otherwise return false
	//
	public function PrinterAvailable( $printer ) {
		$p = $this->printer->driver->GetPrinters();
		foreach ( $p AS $v ) {
			if ( $v  == $printer ) 
				return true;
		}
		return false;
	} // end method GetPrinters

} // end class PrinterWrapper

?>
