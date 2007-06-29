<?php
 // $Id
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

// Class: org.freemedsoftware.core.PatientDataStore
//
//	Core handling routines for files which are stored outside of the
//	SQL database.
//
class PatientDataStore {

	protected $base_path;

	// Constructor: PatientDataStore
	//
	//	Create PatientDataStore object
	//
	public function __construct ( ) {
		//$this->base_path = dirname(dirname(dirname(__FILE__))).'/data/store';
		$this->base_path = 'data/store';
	}

	// Method: ResolveFilename
	//
	//	Resolve absolute path filename to resource
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$module - Module class name
	//
	//	$id - Record ID
	//
	// Returns:
	//
	//	String containing filename.
	//
	public function ResolveFilename ( $patient, $module, $id ) {
		$path = $this->base_path . '/' . $this->_PatientToPath( $patient ) . '/' . strtolower( $module ) . '/' . $id;
		return $path;
	} // end method ResolveFilename

	// Method: ServeFile
	//
	//	Serve file specified by parameters.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$module - Module class name
	//
	//	$id - Record ID
	//
	// Returns:
	//
	//	Serves file back to browser with appropriate headers.
	//
	public function ServeFile ( $patient, $module, $id, $filetype='application/x-binary' ) {
		$file = $this->ResolveFilename( $patient, $module, $id );
		if ( ! file_exists( $file ) ) {
			syslog( LOG_INFO, get_class($this)."| could not resolve file for ${patient}/${module}/${id}" );
			return false;
		}

		Header( "Content-Type: ${filetype}" );
		header( "Content-Length: " .(string)( filesize( $file ) ) );
		header( "Content-Transfer-Encoding: binary" );
		readfile( $file );
		die();
	} // end method ServeFile

	// Method: StoreFile
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$module - Module class name
	//
	//	$id - Record ID
	//
	//	$contents - String containing original path
	//
	// Returns:
	//
	//	Path to file.
	//
	public function StoreFile( $patient, $module, $id, $contents ) {
		$path = $this->base_path . '/' . $this->_PatientToPath( $patient ) . '/' . strtolower( $module );
		// Recursively create directory in case it doesn't exist
		mkdir ( $path, 0777, true );

		$dest = $path . '/' . $id;

		if ( is_uploaded_file( $contents ) ) {
			move_uploaded_file( $contents, $dest );
		} else {
			copy( $contents, $dest );
			unlink( $contents );
		}

		if ( file_exists ( $dest ) ) {
			return str_replace( $this->base_path.'/', '', $dest );
		} else {
			return false;
		}
	} // end method StoreFile

	// Method: _PatientToPath
	//
	//	Internal method to convert patient ID to path to files.
	//
	// Parameters:
	//
	//	$patient - Patient ID number
	//
	// Returns:
	//
	//	Path string
	//
	protected function _PatientToPath ( $patient ) {
		$m = md5( $patient );
		return 
			$m[0].$m[1].'/'.
			$m[2].$m[3].'/'.
			$m[4].$m[5].'/'.
			$m[6].$m[7].'/'.
			substr($m, -(strlen($m)-8));
	} // end method PatientToPath

} // end class PatientDataStore

?>
