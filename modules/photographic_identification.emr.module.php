<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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
		$this->base_path = dirname(__FILE__).'/../data/store/';

		// Call parent constructor
		parent::__construct();
	} // end constructor PhotographicIdentification

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function add_post ( $id ) {
		// Handle upload
		if (!($imagefilename = freemed::store_image($patient, "imageupload", $id))) {
			syslog("Failed to upload");
			return false;
		}

		$query = $GLOBALS['sql']->update_query (
			$this->table_name,
			array ( "imagefile" => $imagefilename ),
			array ( "id" => $last_record )
		);
		$result = $GLOBALS['sql']->query ($query);
 	}

	protected function del_pre ( $id ) {
		unlink(freemed::image_filename(
			freemed::secure_filename($patient),
			freemed::secure_filename($id),
			'id.djvu'
		));
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
	}

	function additional_move ( $id, $from, $to ) {
		$orig = freemed::image_filename($from, $id, 'id.djvu');
		$new = freemed::image_filename($to, $id, 'id.djvu');
		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array ( 'p_filename' => $new ),
			array ( 'id' => $id )
		);

		syslog(LOG_INFO, "Photographic Identification| moved $orig to $new");

		$result = $GLOBALS['sql']->query($q);
		//if (!$result) { return false; }

		$result = rename ( $orig, $new );
		$dir = dirname($new);
		`mkdir -p "$dir"`;
		`mv "$orig" "$new"`;
		//print "mv \"$orig\" \"$new\"<br/>\n";
		//print "orig = $orig, new = $new<br/>\n";
		//if (!$result) { return false; }

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
		$filename = freemed::image_filename($rec[$this->patient_field], $id, 'djvu');
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

} // end of class ScannedDocuments

register_module ("ScannedDocuments");

?>
