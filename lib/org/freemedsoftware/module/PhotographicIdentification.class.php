<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class PhotographicIdentification extends EMRModule {

	var $MODULE_NAME = "Photographic Identification";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Photographic identification for patients.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "75257903-d839-43ef-adf5-fae3d2c2349e";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name   = "Photographic Identification";
	var $table_name    = "photoid";
	var $patient_field = "p_patient";
	var $order_by      = "p_stamp";
	var $widget_hash   = "##p_stamp##";

	var $variables = array (
		'p_patient',
		'p_description',
		'p_filename',
		'p_user'
	);

	public function __construct () {
		// __("Photographic Identification")

		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Timestamp")   =>	"my_date"
		);
		$this->summary_query = array (
			"DATE_FORMAT(p_stamp, '%m/%d/%Y') AS my_date"
		);
		$this->summary_order_by = "p_stamp DESC";

		// Set associations
		$this->_SetAssociation( 'EmrModule' );

		// Define images base path
		$this->base_path = dirname(dirname(__FILE__)).'/data/store/';

		// Call parent constructor
		parent::__construct();
	} // end constructor PhotographicIdentification

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: UploadPhotoID
	//
	//	Upload photographic ID using PHP's file upload capabilities.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function UploadPhotoID ( $patient ) {
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		if( $_FILES["file"]["name"] != "" ) {
			if ( $value == UPLOAD_ERR_OK ) {
				// Create the upload wrapper here
				$q = $GLOBALS['sql']->insert_query(
					$this->table_name,
					array (
						'p_user' => freemed::user_cache()->user_number,
						'p_patient' => $patient
					)
				);
				syslog( LOG_INFO, $q );
				$GLOBALS['sql']->query( $q );
				$id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );

				$origfilename = $_FILES["file"]["tmp_name"];
				syslog( LOG_INFO, "originalfilename = $origfilename");
				$success = $pds->StoreFile( $patient, get_class( $this ), $id, $origfilename );
				if ( $success ) {
					syslog( LOG_INFO, get_class($this)."| found file ".$_FILES['file']['name'] );
					$q = $GLOBALS['sql']->update_query(
						$this->table_name,
						array(
							'p_filename' => $pds->ResolveFilename( $patient, get_class($this), $id )
						), array( 'id' => $id )
					);
					syslog( LOG_INFO, $q );
					$GLOBALS['sql']->query( $q );
					return true;
				}
			} else {
				syslog( LOG_INFO, get_class($this)."| uploading file failed" );
				return false;
			}
		}
		return false;
	} // end method UploadPhotoID

	// Method: UploadPhotoIDInline
	//
	//	Upload photographic ID using only JSON.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$blob - BLOB string containing image.
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function UploadPhotoIDInline ( $patient, $blob ) {
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		// Create the upload wrapper here
		$q = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array (
				'p_user' => freemed::user_cache()->user_number,
				'p_patient' => $patient
			)
		);
		syslog( LOG_INFO, $q );
		$GLOBALS['sql']->query( $q );
		$id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );

		// Drop the blob to temp file
		$origfilename = tempnam( '/tmp', 'photoIdUpload' );
		file_put_contents( $origfilename, $blob );

		syslog( LOG_INFO, "originalfilename = $origfilename");
		$success = $pds->StoreFile( $patient, get_class( $this ), $id, $origfilename );
		if ( $success ) {
			syslog( LOG_INFO, get_class($this)."| found file ".$_FILES['file']['name'] );
			$q = $GLOBALS['sql']->update_query(
				$this->table_name,
				array(
					'p_filename' => $pds->ResolveFilename( $patient, get_class($this), $id )
				), array( 'id' => $id )
			);
			syslog( LOG_INFO, $q );
			$GLOBALS['sql']->query( $q );
			return true;
		}
		return false;
	} // end method UploadPhotoIDInline

	// Method: ImportMugshotPhoto
	//
	//	Upload photographic ID using mugshot inline upload
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function ImportMugshotPhoto ( ) {
		// Pull patient ID from URL passing in mugshot widget
		$patient = (int)($_POST['username']);

		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		// Create the upload wrapper here
		$q = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array (
				'p_user' => freemed::user_cache()->user_number,
				'p_patient' => $patient
			)
		);
		syslog( LOG_INFO, $q );
		$GLOBALS['sql']->query( $q );
		$id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );

		// Get data from mugshot widget
		$blob = base64_decode( $_POST['img'] );
		//$width = $_POST['width'];
		//$height = $_POST['height'];

		// Drop the blob to temp file
		$origfilename = tempnam( '/tmp', 'photoIdUpload' );
		file_put_contents( $origfilename, $blob );

		syslog( LOG_INFO, "originalfilename = $origfilename");
		$success = $pds->StoreFile( $patient, get_class( $this ), $id, $origfilename );
		if ( $success ) {
			syslog( LOG_INFO, get_class($this)."| found file ".$_FILES['file']['name'] );
			$q = $GLOBALS['sql']->update_query(
				$this->table_name,
				array(
					'p_filename' => $pds->ResolveFilename( $patient, get_class($this), $id )
				), array( 'id' => $id )
			);
			syslog( LOG_INFO, $q );
			$GLOBALS['sql']->query( $q );
			return true;
		}
		return false;
	} // end method ImportMugshotPhoto

	// Method: GetPhotoID
	//
	//	Get BLOB of latest photo id, or no photo image if there is none.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$force_id - (optional) Forced ID, for viewing past photos.
	//
	public function GetPhotoID ( $patient, $force_id = false ) {
		ob_start();
		if ( ! $force_id ) {
			$id = (int) $GLOBALS['sql']->queryOneStoredProc( "CALL photoId_GetLatest ( ".( (int) $patient )." ) " );
		} else {
			$id = (int) $force_id;
		}
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		$pic = $pds->GetFile( (int) $patient, get_class($this), $id );
		if ( ! $pic ) {
			$x = 'ui/dojo/htdocs/images/teak/noimage.250x250.png';
			ob_end_clean();
			ob_start();
			readfile( $x );
			$pic = ob_get_contents();
			ob_end_clean();
		}
		if ( strpos( $pic, 'JFIF' ) > 0 ) {
			Header( 'Content-type: image/jpeg' );
		} else if ( strpos( $pic, 'GIF89a' ) === 0 ) {
			Header( 'Content-type: image/gif' );
		} else if ( strpos( $pic, 'PNG' ) > 0 ) {
			Header( 'Content-type: image/png' );
		}
		Header( 'Pragma: no-cache' );
		Header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		Header( 'Cache-Control: post-check=0, pre-check=0', false );
		Header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		print $pic;
		die();
	} // end method GetPhotoID

	protected function add_post ( $id, &$data ) {
		// Using example from http://www.php.net/manual/en/features.file-upload.php
		// Assume 'file' is the name of the file, as per default

		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );

		foreach( $_FILES["file"]["error"] as $key => $value ) {
			if( $_FILES["file"]["name"][$key] != "" ) {
				if ( $value == UPLOAD_ERR_OK ) {
					$origfilename = $_FILES["file"]["name"][$key];
					$success = $pds->StoreFile( $data[$this->patient_field], get_class( $this ), $id, $origfilename );
					if ( $success ) {
						// Do not process further
						return true;
					}
				} else {
					syslog( LOG_INFO, get_class($this)."| uploading file failed" );
					return false;
				}
			}
		}

		//$query = $GLOBALS['sql']->update_query (
		//	$this->table_name,
		//	array ( "imagefile" => $imagefilename ),
		//	array ( "id" => $last_record )
		//);
		//$result = $GLOBALS['sql']->query ($query);
 	}

	protected function del_pre ( $id ) {
		$data = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		$filename = $pds->ResolveFilename( $data[$this->patient_field], get_class($this), $id );
		unlink( $filename );
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: GetDocumentPage
	//
	//	Get fax/document page image as JPEG.
	//
	// Parameters:
	//
	//	$id - Record id of document
	//
	//	$thumbnail - (optional) Boolean, if image is to be rendered
	//	as a thumbnail. Defaults to false.
	//
	// Returns:
	//
	//	BLOB data containing jpeg image.
	//
	public function GetDocumentPage( $id, $thumbnail = false ) {
		// Return image ...
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			PHYSICAL_LOCATION . '/' . freemed::image_filename( $r[$this->patient_field], $id, 'djvu' ));

		return readfile( $thumbnail ? $djvu->GetPageThumbnail( $page ) : $djvu->GetPage( $page, false, false, false ) );
	} // end method GetDocumentPage

	// Method: NumberOfPages
	//
	//	Expose the number of pages of a Djvu document
	//
	// Parameters:
	//
	//	$id - Table record id
	//
	// Returns:
	//
	//	Integer, number of pages in the specified document
	//
	public function NumberOfPages ( $id ) {
		$r = $GLOBALS['sql']->get_link ( $this->table_name, $id );
		$djvu = CreateObject('org.freemedsoftware.core.Djvu', 
			PHYSICAL_LOCATION . '/' . freemed::image_filename( $r[$this->patient_field], $id, 'djvu' ));
		return $djvu->NumberOfPages();
	} // end method NumberOfPages

	protected function print_override ( $id ) {
		// Create djvu object
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$filename = freemed::image_filename($rec[$this->patient_field], $id, 'djvu');
		$d = CreateObject('org.freemedsoftware.core.Djvu', $filename);
		return $d->ToPDF( true );
	} // end method print_override

} // end of class PhotographicIdentification

register_module ("PhotographicIdentification");

?>
