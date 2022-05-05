<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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
		$this->base_path = PHYSICAL_LOCATION . '/data/store';
	}

	// Method: ResolveFilename
	//
	//	Resolve absolute path filename to resource
	//	(Deprecated)
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
		syslog(LOG_WARN, "PatientDataStore::ResolveFilename - deprecated function called");
		$path = $this->base_path . '/' . $this->_PatientToPath( $patient ) . '/' . strtolower( $module ) . '/' . $id;
		return $path;
	} // end method ResolveFilename

	// Method: GetFile
	//
	//	Get file content.
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
	public function GetFile ( $patient, $module, $id ) {
		$query = "SELECT contents FROM pds WHERE patient = ".$GLOBALS['sql']->quote( $patient )." AND module = ".$GLOBALS['sql']->quote( strtolower( $module ) )." AND id = ".$GLOBALS['sql']->quote( $id );
		$r = $GLOBALS['sql']->queryOne( $query );

		if ( ! $r ) {
			syslog( LOG_INFO, get_class($this)."| could not resolve file for ${patient}/${module}/${id}" );
			return false;
		}

		return $r;
	} // end method GetFile

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
		$query = "SELECT * FROM pds WHERE patient = ".$GLOBALS['sql']->quote( $patient )." AND module = ".$GLOBALS['sql']->quote( strtolower( $module ) )." AND id = ".$GLOBALS['sql']->quote( $id );
		$r = $GLOBALS['sql']->queryRow( $query );

		if ( ! array( $r ) ) {
			syslog( LOG_INFO, get_class($this)."| could not resolve file for ${patient}/${module}/${id}" );
			return false;
		}

		// If we have a PDF but need a JPEG ...
		if ( substr($r['contents'], 0, 4) == '%PDF' && $filetype == 'image/jpeg' ) {
			// Convert
		}

		Header( "Content-Type: ${filetype}" );
		header( "Content-Length: " .(string)( strlen( $r['contents'] ) ) );
		header( "Content-Transfer-Encoding: binary" );
		print( $r['contents'] );
		die();
	} // end method ServeFile

	// Method: ServeFileThumbnail
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
	//	Serves file's thumbnail back to browser with appropriate headers.
	//

	public function ServeFileThumbnail ( $patient, $module, $id, $filetype='application/x-binary' ) {
		$query = "SELECT * FROM pds WHERE patient = ".$GLOBALS['sql']->quote( $patient )." AND module = ".$GLOBALS['sql']->quote( strtolower( $module ) )." AND id = ".$GLOBALS['sql']->quote( $id );
		$r = $GLOBALS['sql']->queryRow( $query );

		if ( ! array( $r ) ) {
			syslog( LOG_INFO, get_class($this)."| could not resolve file for ${patient}/${module}/${id}" );
			return false;
		}

                 $im = imagecreatefromstring($r['contents']);
                 $width = imagesx($im);
                 $height = imagesy($im);            
                                         
                 // Set thumbnail-height to 282 pixels                                    
                 $imgh = 282;                                          
                 // calculate thumbnail-height from given width to maintain aspect ratio
                 $imgw = $width / $height * $imgh;                                          
                 // create new image using thumbnail-size
                 $thumb=imagecreatetruecolor($imgw,$imgh);                  
                 // copy original image to thumbnail
                 imagecopyresampled($thumb,$im,0,0,0,0,$imgw,$imgh,ImageSX($im),ImageSY($im)); //makes thumb

  		 Header( "Content-Type: ${filetype}" );
  		 imagejpeg($thumb, null, 85);
		 print( $thumb );
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
	//	PDS id.
	//
	public function StoreFile( $patient, $module, $id, $contents ) {
		syslog( LOG_DEBUG, get_class($this)."::StoreFile ( $patient, $module, $id, $contents )" );

		syslog( LOG_DEBUG, get_class($this)."::StoreFile| Importing uploaded file " );
		$res = $GLOBALS['sql']->query( "INSERT INTO pds ( id, patient, module, contents ) VALUES ( ".
			$GLOBALS['sql']->quote($id).", ".
			$GLOBALS['sql']->quote($patient).", ".
			$GLOBALS['sql']->quote(strtolower($module)).", ".
			$GLOBALS['sql']->quote(file_get_contents($contents)).
		" );" );

		return $GLOBALS['sql']->lastInsertID( 'pds', 'id' );
	} // end method StoreFile

	// Method: GetLocalCachedFile
	//
	// Returns:
	//
	//	Path to locally cached filesystem copy of database object.
	//
	public function GetLocalCachedFile( $patient, $module, $id ) {
		// Create hash for filename
		$hash = PHYSICAL_LOCATION . "/data/cache/" . $module. "-" . md5( $id );

		// If it exists, return file name
		if (file_exists( $hash )) {
			return $hash;
		} else {
			// ... otherwise cache it first ...
	                $r = $GLOBALS['sql']->get_link( $this->table_name, $id );
			file_put_contents( $hash, $this->GetFile( $patient, $module, $id ) );
			// ... then return the hash.
			return $hash;
		}
	} // end method GetLocalCachedFile

	public function UpdateFileFromCachedFile( $patient, $module, $id ) {
		$hash = PHYSICAL_LOCATION . "/data/cache/" . $module. "-" . md5( $id );

		if (!file_exists( $hash )) {
			return false;
		} else {
			$query = "UPDATE pds SET content = " . $GLOBALS['sql']->quote( file_get_contents( $hash ) ) . " WHERE patient = " . $GLOBALS['sql']->quote( $patient ) . " AND module = " . $GLOBALS['sql']->quote( $module ) . " AND id = " . $GLOBALS['sql']->quote( $id );
			$GLOBALS['sql']->query( $query );
			return true;
		}
	} // end method UpdateFileFromCachedFile

	// Method: _PatientToPath
	//
	//	Internal method to convert patient ID to path to files.
	//	(Deprecated)
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
		syslog(LOG_WARN, "PatientDataStore::_PatientToPath - deprecated function called");
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
