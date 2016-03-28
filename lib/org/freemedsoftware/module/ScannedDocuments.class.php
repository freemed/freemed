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

class ScannedDocuments extends EMRModule {

	var $MODULE_NAME = "Scanned Document";
	var $MODULE_VERSION = "0.4.3";
	var $MODULE_DESCRIPTION = "Allows images to be stored, as if they were in a paper chart.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "5291e9dc-f660-4776-a52e-099ba7de9790";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name   = "Scanned Documents";
	var $table_name    = "images";
	var $patient_field = "imagepat";
	var $order_by      = "imagedt";
	var $widget_hash   = "##imagecat## [##imagedt##] ##imagedesc## (##imagetype##)";

	var $variables = array (
		'imagedt',
		'imagepat',
		'imagetype',
		'imagecat',
		'imagedesc',
		'imageeoc',
		'imagefile',
		'imageformat',
		'imagephy',
		'imagereviewed',
		'imagetext',
		'user'
	);

	public function __construct () {
		// __("Scanned Documents")
		// __("Allows images to be stored, as if they were in a paper chart.")

		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Type")        =>	"imagetype",
			__("Category")	  =>	"imagecat",
			__("Description") =>	"imagedesc",
			__("Reviewed")    =>	"reviewed"
		);
		$this->summary_options |= SUMMARY_VIEW | SUMMARY_LOCK | SUMMARY_DELETE | SUMMARY_PRINT;
		$this->summary_query = array (
			"DATE_FORMAT(imagedt, '%m/%d/%Y') AS my_date",
			"CASE imagereviewed WHEN 0 THEN 'no' ELSE 'yes' END AS reviewed"
		);
		$this->summary_order_by = "imagedt";

		// Set associations
		$this->_SetAssociation('EmrModule');
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'imageeoc');

		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
		parent::__construct();
	} // end constructor ScannedDocuments

	protected function add_pre ( &$data ) {
		list ( $data['imagetype'], $data['imagecat'] ) = explode('/', $data['imagetypecat']);
		$data['imagereviewed'] = 0;
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function add_post ( $id, $data ) {
		$this->uploadFile($id, $data);
 	}

	protected function uploadFile($id,$data){
		if($_FILES["imageupload"]["tmp_name"]){
			$origfilename = $_FILES["imageupload"]["tmp_name"];
			syslog( LOG_INFO, "originalfilename = $origfilename");
			$patient = $data[$this->patient_field];
			syslog( LOG_INFO, "patient = $patient");
			$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
			$success = $pds->StoreFile( $patient, get_class( $this ), $id, $origfilename );
			if ( $success ) {
				syslog( LOG_INFO, get_class($this)."| found file ".$_FILES['imageupload']['name'] );
				$q = $GLOBALS['sql']->update_query(
					$this->table_name,
					array(
						'imagefile' => $pds->ResolveFilename( $patient, get_class($this), $id )
					), array( 'id' => $id )
				);
				syslog( LOG_INFO, $q );
				$GLOBALS['sql']->query( $q );
				return true;
			}else
				syslog( LOG_INFO, "failed to store file!!!!" );			
		}else 
			syslog( LOG_INFO, "file not found!!!!" );			
	}

	protected function del_pre ( $id ) {
		unlink(freemed::image_filename(
			freemed::secure_filename($patient),
			freemed::secure_filename($id),
			'djvu'
		));
	}

	protected function mod_pre ( &$data ) {
		list ( $data['imagetype'], $data['imagecat'] ) = explode('/', $data['imagetypecat']);
		$data['user'] = freemed::user_cache()->user_number;
	}
	
	protected function mod_post($data){
		$this->uploadFile($data['id'], $data);
	}
	
	function additional_move ( $id, $from, $to ) {
		return true;
	} // end method additional_move

	// Method: GetDocumentPage
	//
	//	Get fax/document page image as JPEG.
	//
	// Parameters:
	//
	//	$id - Record id of document
	//
	//	$page - Page number
	//
	//	$thumbnail - (optional) Boolean, if image is to be rendered
	//	as a thumbnail. Defaults to false.
	//
	// Returns:
	//
	//	BLOB data containing jpeg image.
	//
	public function GetDocumentPage( $id, $page, $thumbnail = false ) {
		// Return image ...
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );

		// GetFile
		if($thumbnail) {
			$pds->ServeFileThumbnail($r[$this->patient_field],get_class($this),$id,"image/jpeg");
		} else {
			$pds->ServeFile($r[$this->patient_field],get_class($this),$id,"image/jpeg");
		}

	} // end method GetDocumentPage

	// Method: GetDocumentPdf
	//
	//	Get fax/document as PDF
	//
	// Parameters:
	//
	//	$id - Record id of document
	//
	public function GetDocumentPdf( $id ) {
		$r = $GLOBALS['sql']->get_link ( $this->table_name, $id );

		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		$content = $pds->GetFile( $r[$this->patient_field], get_class($this), $id );
		if (substr($content, 0, 4) == '%PDF') {
			// passthrough
			Header( 'Content-type: application/pdf' );
			print $content;
			die();
		}

		if (substr($content, 1, 3) == 'PNG') {
			syslog( LOG_INFO, "PNG" );
			
			// passthrough
			Header( 'Content-type: application/pdf' );

			// Temporary file
			$tempin  = tempnam( "/tmp", "convert-" );

			// Input
			file_put_contents( $tempin, $content );
	
			// Convert / Output
			passthru( "/usr/bin/convert png:\"$tempin\" pdf:-" );

			// Cleanup
			unlink( $tempin );

			die();
		}
		
		print file_get_contents( $content );
	} // end method GetDocumentPdf

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
		/*$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		
		$djvu = CreateObject('org.freemedsoftware.core.Djvu',
			$pds->GetLocalCachedFile( $r[$this->patient_field], get_class($this), $id ) );
		return $djvu->NumberOfPages();
		*/
		return $r['imagefile']?1:0;
	} // end method NumberOfPages

	function tc_picklist ( ) {
		return array (
			__("Operative Report") => "op_report/misc",
				"- ".__("Colonoscopy") => "op_report/colonoscopy",
				"- ".__("Endoscopy") => "op_report/endoscopy",
			__("Miscellaneous") => "misc/misc",
				"- ".__("Consult") => "misc/consult",
				"- ".__("Discharge Summary") => "misc/discharge_summary",
				"- ".__("History and Physical") => "misc/history_and_physical",
			__("Lab Report") => "lab_report/misc",
				"- ".__("CBC") => "lab_report/cbc",
				"- ".__("C8") => "lab_report/c8",
				"- ".__("LFT") => "lab_report/lft",
				"- ".__("Lipid Profile") => "lab_report/lipid_profile",
				"- ".__("UA") => "lab_report/ua",
				"- ".__("Thyroid Profile") => "lab_report/thyroid_profile",
			__("Letters") => "letters/misc",
			__("Oncology") => "oncology/misc",
			__("Hospital Records") => "hospital/misc",
				"- ".__("Discharge Summary") => "hospital/discharge",
			__("Pathology") => "pathology/misc",
			__("Patient History") => "patient_history/misc",
			__("Questionnaire") => "questionnaire/misc",
			__("Radiology") => "radiology/misc",
				"- ".__("Abdominal Radiograph") => "radiology/abdominal_radiograph",
				"- ".__("Chest Radiograph") => "radiology/chest_radiograph",
				"- ".__("Abdominal CT Reports") => "radiology/abdominal_ct_reports",
				"- ".__("Chest CT Reports") => "radiology/chest_ct_reports",
				"- ".__("Mammogram Reports") => "radiology/mammogram_reports",
			__("Insurance Card") => "insurance_card",
			__("Referral") => "referral/misc",
				"- ".__("Notes") => "referral/notes",
				"- ".__("Radiographs") => "referral/radiographs",
				"- ".__("Lab Reports") => "referral/lab_reports",
				"- ".__("Consult") => "referral/consult",
			__("Financial Information") => "financial/misc"
		);
	} // end method tc_picklist

	protected function print_override ( $id ) {
		// Create djvu object
		$rec = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$pds = CreateObject( 'org.freemedsoftware.core.PatientDataStore' );
		$filename = $pds->GetLocalCachedFile( $r[$this->patient_field], get_class($this), $id );
		$d = CreateObject('org.freemedsoftware.core.Djvu', $filename);
		return $d->ToPDF( true );
	} // end method print_override

	function fax_widget ( $varname, $id ) {
		global ${$varname};
		$r = $GLOBALS['sql']->get_link( $this->table_name, $id );
		$phy = $GLOBALS['sql']->get_link( 'physician', $r['imagephy'] );
		${$varname} = $phy['phyfaxa'];
		return module_function('faxcontacts',
			'widget',
			array ( $varname, false, 'phyfaxa' )
		);
	} // end method fax_widget

	public function GetPatientAllRecords($patient){
		$patient = $GLOBALS['sql']->quote($patient);
		$q = "select im.id,im.imagedt,im.imagefile,im.imagetype,im.imagecat,CONCAT(ph.phylname, ', ', ph.phyfname, ' ', ph.phymname) AS physician from images im left join physician ph on ph.id = im.imagephy where im.imagepat=".$patient;
		return $GLOBALS['sql']->queryAll( $q );
	}

} // end of class ScannedDocuments

register_module ("ScannedDocuments");

?>
