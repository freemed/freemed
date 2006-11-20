<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

	var $MODULE_NAME = "Scanned Documents";
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
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'imageeoc');

		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
		parent::__construct();
	} // end constructor ScannedDocuments

	protected function add_pre ( &$data ) {
		list ( $data['imagetype'], $data['imagecat'] ) = explode('/', $data['imagetypecat']);
		$data['imagereviewed'] = 0;
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
			'djvu'
		));
	}

	protected function mod_pre ( &$data ) {
		list ( $data['imagetype'], $data['imagecat'] ) = explode('/', $data['imagetypecat']);
	}

	function additional_move ( $id, $from, $to ) {
		$orig = freemed::image_filename($from, $id, 'djvu');
		$new = freemed::image_filename($to, $id, 'djvu');
		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array ( 'imagefilename' => $new ),
			array ( 'id' => $id )
		);

		syslog(LOG_INFO, "Scanned Documents| moved $orig to $new");

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

	function print_override ( $id ) {
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

	function _update () {
		$version = freemed::module_version($this->MODULE_NAME);
		// Version 0.3
		//
		//	Add "category" sub-column
		//
		if (!version_check($version, '0.3')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imagecat VARCHAR(50) AFTER imagetype');
		}

		// Version 0.4
		//
		//	Add physician field
		//
		if (!version_check($version, '0.4')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imagephy INT UNSIGNED AFTER imagefile');
		}

		// Version 0.4.1
		//
		//	Add locking
		//
		if (!version_check($version, '0.4.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN locked INT UNSIGNED AFTER imagephy');
		}

		// Version 0.4.2
		//
		//	Add reviewed flag
		//
		if (!version_check($version, '0.4.2')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imagereviewed INT UNSIGNED AFTER imagephy');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET imagereviewed=0');
		}

		// Version 0.4.3
		//
		//	Add format
		//
		if (!version_check($version, '0.4.3')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imageformat CHAR (4) AFTER imagefile');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				"SET imageformat='djvu'");
		}
	} // end method _update

} // end of class ScannedDocuments

register_module ("ScannedDocuments");

?>
