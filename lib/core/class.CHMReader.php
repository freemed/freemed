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

// Class: org.freemedsoftware.core.CHMReader
//
//	Class for reading from a CHM compressed help file.
//
class CHMReader {

	protected $file = '';

	// Constructor: CHMReader
	//
	// Parameters:
	//
	//	$file - Name of CHM file to open.
	//
	public function __construct ( $file ) {
		if ( file_exists ( $file ) ) {
			$this->file = $file;
		} else {
			trigger_error("CHMReader::constructor - could not read ${file}", E_USER_ERROR);
		}
	} // end constructor

	// Method: GetResource
	//
	//	Return contents of a CHM resource
	//
	// Parameters:
	//
	//	$resource - Resource path
	//
	// Returns:
	//
	//	Binary contents of resource
	//
	public function GetResource ( $resource ) {
		if ( !$this->ValidResource ( $resource ) ) {
			trigger_error("Could not locate ${resource}", E_USER_ERROR);	
		}
		$temp = tempnam ( '/tmp', 'chm' );
		exec( "test_chmLib ".escapeshellarg( $this->file )." ".escapeshellarg( $resource )." ".escapeshellarg ( $temp ) );
		ob_start( );
		readfile ( $temp );
		unlink ( $temp );
		$buffer = ob_get_contents( );
		ob_end_clean( );
		return $buffer;
	} // end public function GetResource

	// Method: ValidResource
	//
	//	Determine if a resource is valid
	//
	// Parameters:
	//
	//	$resource - Resource path
	//
	// Returns:
	//
	//	Boolean.
	//
	public function ValidResource ( $resource ) {
		$result = exec( "enum_chmLib ".escapeshellarg( $this->file )." | grep 'normal file' | grep ".escapeshellarg( $resource . '$' ) );
		return !empty ( $result );
	} // end public function ValidResource

} // end class CHM

?>
