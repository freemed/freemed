<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2015 FreeMED Software Foundation
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

class DicomModule extends EMRModule {

	var $MODULE_NAME = "DICOM Images";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "DICOM image storage.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "acce5604-de27-41ae-92b3-5a160102a844";
	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name   = "DICOM";
	var $table_name    = "dicom";
	var $patient_field = "d_patient";
	var $order_by      = "d_stamp";
	var $widget_hash   = "##d_study_date## ##d_study_description##";

	var $variables = array (
		'd_md5',
		'd_patient',
		'd_study_description',
		'd_filename',
		'd_study_date',
		'd_institution_name',
		'd_institution_address',
		'd_study_uid',
		'd_series_uid',
		'd_referring_provider',
		'd_xml_data',
		'storage_status',
		'user'
	);

	public function __construct () {
		// __("DICOM")

		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Description")   =>	"d_study_description"
		);
		$this->summary_query = array (
			"DATE_FORMAT(d_stamp, '%m/%d/%Y') AS my_date"
		);
		$this->summary_order_by = "d_stamp DESC";

		// Set associations
		$this->_SetAssociation( 'EmrModule' );

		// Define images base path
		$this->base_path = dirname(dirname(__FILE__)).'/data/store/';

		// Call parent constructor
		parent::__construct();
	} // end constructor DicomModule

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	// Method: CheckForDuplicates
	//
	//	Check for any DICOM images in the data store with a duplicate MD5 sum.
	//
	// Parameters:
	//
	//	$md5 - MD5 sum of the DICOM image in question.
	//
	// Returns:
	//
	//	Boolean. True if there are duplicates, false if there are not.
	//
	public function CheckForDuplicates( $md5 ) {
		$q = "SELECT COUNT(*) AS c FROM dicom WHERE d_md5 = ".$GLOBALS['sql']->quote( $md5 );
		$r = $GLOBALS['sql']->queryOne( $q );
		return ( $r > 0 ) ? true : false;
	} // end method CheckForDuplicates

	// Method: LookupPatient
	//
	//	Lookup patient from DICOM parameters.
	//
	// Parameters:
	//
	//	$patient - Blob of patient data like 'LAST^FIRST^MIDDLE'
	//
	//	$dob - Date of birth in 'YYYY-MM-DD'
	//
	// Returns:
	//
	//	Patient ID or 0 if not able to resolve.
	//
	public function LookupPatient ( $patient, $dob ) {
		if ( strpos( $patient, '^' ) !== false ) {
			$name = explode ( '^', $patient );
		} else {
			$name = explode ( ' ', $patient );
		}

		syslog( LOG_DEBUG, get_class($this)."::LookupPatient( name = $patient, dob = $dob )" );

		// Check first for DOB + Last Name
		$r = $GLOBALS['sql']->queryCol(
			"SELECT id FROM patient WHERE ptlname = ".$GLOBALS['sql']->quote( $name[0] )." AND ptdob = ".$GLOBALS['sql']->quote( $dob )
		);
		if ( count($r) == 1 ) {
			syslog( LOG_DEBUG, get_class($this)."::LookupPatient( $patient, $dob ) resolved to patient " . $r[0] );
			return $r[0];
		}
	} // end method LookupPatient

	// Method: UploadDICOM
	//
	//	Upload DICOM using PHP's file upload capabilities.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$params - Hash of other parameters
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function UploadDICOM ( $patient, $params ) {
		syslog( LOG_DEBUG, get_class($this)."::UploadDICOM ( $patient, ... )" );
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		if( $_FILES["file"]["name"] != "" ) {
			if ( $value == UPLOAD_ERR_OK ) {
				// Create the upload wrapper here
				$q = $GLOBALS['sql']->insert_query(
					$this->table_name,
					array (
						'user' => freemed::user_cache()->user_number,
						'd_patient' => $patient
					)
				);
				syslog( LOG_DEBUG, get_class($this)."| ".$q );
				$GLOBALS['sql']->query( $q );
				$id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );

				$origfilename = $_FILES["file"]["tmp_name"];
				$md5 = md5_file( $origfilename );
				syslog( LOG_DEBUG, "DICOM originalfilename = $origfilename");
				$success = $pds->StoreFile( $patient, get_class( $this ), $id, $origfilename );
				if ( $success ) {
					syslog( LOG_INFO, get_class($this)."| found file ".$_FILES['file']['name'] );
					$x = $params;
					$x['user'] = freemed::user_cache()->user_number;
					$x['d_filename'] = $pds->ResolveFilename( $patient, get_class($this), $id );
					$x['d_md5'] = $md5;
					$x['d_patient'] = $patient;
					$GLOBALS['sql']->load_data( $x );
					$q = $GLOBALS['sql']->update_query(
						$this->table_name,
						$this->variables,
						array( 'id' => $id )
					);
					syslog( LOG_DEBUG, get_class($this)."| ".$q );
					$GLOBALS['sql']->query( $q );
					return $id;
				}
			} else {
				syslog( LOG_INFO, get_class($this)."| uploading file failed" );
				return false;
			}
		}
		syslog( LOG_ERR, get_class($this)."::UploadDICOM| did not find 'file' in POST" );
		return false;
	} // end method UploadDICOM

	// Method: UploadDICOMInline
	//
	//	Upload DICOM using only JSON.
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$blob - BLOB string containing image, base64 encoded.
	//
	//	$params - Hash of other parameters
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function UploadDICOMInline ( $patient, $blob, $params ) {
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		// Create the upload wrapper here
		$q = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array (
				'user' => freemed::user_cache()->user_number,
				'd_patient' => $patient
			)
		);
		syslog( LOG_DEBUG, get_class($this)."| ".$q );
		$GLOBALS['sql']->query( $q );
		$id = $GLOBALS['sql']->lastInsertId( $this->table_name, 'id' );

		// Drop the blob to temp file
		$decoded_blob = base64_decode( $blob );
		$origfilename = tempnam( '/tmp', 'dicomUpload' );
		file_put_contents( $origfilename, $decoded_blob, FILE_BINARY );
		$md5 = md5_file( $origfilename );
		syslog( LOG_DEBUG, get_class($this)."| Blob MD5 = $md5" );
		syslog( LOG_DEBUG, get_class($this)."| Blob Size = ".strlen($decoded_blob) );

		syslog( LOG_DEBUG, get_class($this)."| originalfilename = $origfilename");
		$success = $pds->StoreFile( $patient, get_class( $this ), $id, $origfilename );
		if ( $success ) {
			syslog( LOG_INFO, get_class($this)."| found file {$origfilename} ( output = $success )" );
			$x = $params;
			$x['d_filename'] = $pds->ResolveFilename( $patient, get_class($this), $id );
			$x['d_md5'] = $md5;
			$GLOBALS['sql']->load_data( $x );
			foreach ( $x AS $k => $v ) {
				syslog( LOG_DEBUG, " $k => $v " );
			}
			$q = $GLOBALS['sql']->update_query(
				$this->table_name,
				$this->variables,
				array( 'id' => $id )
			);
			syslog( LOG_DEBUG, get_class($this)."| ".$q );
			$GLOBALS['sql']->query( $q );
			return $id;
		}
		return false;
	} // end method UploadDICOMInline

	// Method: GetDICOM
	//
	//	Get BLOB of selected DICOM image
	//
	// Parameters:
	//
	//	$patient - Patient ID
	//
	//	$id - Requested table ID
	//
	//	$convert - (optional) Convert to JPEG, defaults to false.
	//
	public function GetDICOM ( $patient, $id, $convert = false ) {
		if ( ! $id ) {
			return false;
		} else {
			$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
			$pic = $pds->ResolveFilename( $patient+0, get_class($this), $id+0 );
		}

		// No caching headers
		Header( 'Pragma: no-cache' );
		Header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		Header( 'Cache-Control: post-check=0, pre-check=0', false );
		Header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );

		if ( $convert ) {
			Header( 'Content-type: image/jpeg' );
			$temp = tempnam( '/tmp', 'dicomView' );
			system( "dcmj2pnm +oj '{$pic}' '{$temp}'" );	
			Header( 'Content-length: '.(string)( filesize( $temp ) ) );
			Header( 'Content-disposition: inline; filename="'.mktime().'-dicom.jpg"' );
			readfile( $temp );
			unlink( $temp );
		} else {
			Header( 'Content-type: image/dicom' );
			Header( 'Content-length: '.(string)( filesize( $pic ) ) );
			Header( 'Content-disposition: inline; filename="'.mktime().'-dicom.dcm"' );
			readfile( $pic );
		}

		die();
	} // end method GetDICOM

	protected function add_post ( $id, $data ) {
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

} // end class DicomModule

register_module ("DicomModule");

?>
