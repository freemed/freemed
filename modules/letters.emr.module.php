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

class Letters extends EMRModule {

	var $MODULE_NAME    = "Letters";
	var $MODULE_VERSION = "0.3.5";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID = "791918e6-092a-44ec-9477-f87b50345659";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Letters";
	var $table_name     = "letters";
	var $patient_field  = "letterpatient";
	var $widget_hash    = "##letterdt## ##letterfrom:physician:phylname## to ##letterto:physician:phylname##";

	var $print_template = 'letters';

	var $variables = array (
		"letterdt",
		"lettereoc",
		"letterfrom",
		"letterto",
		"lettercc",
		"letterenc",
		"lettertext",
		"letterpatient",
		"lettertypist",
		"locked" => '0'
	);

	public function __construct ( ) {
		// __("Letters")

		// Set vars for patient management summary
		$this->summary_vars = array (
			__("Date") => "my_date",
			__("From")   => "letterfrom:physician",
			__("To")   => "letterto:physician"
		);
		$this->summary_options = SUMMARY_VIEW | SUMMARY_VIEW_NEWWINDOW
			| SUMMARY_PRINT | SUMMARY_LOCK | SUMMARY_DELETE;
		$this->summary_query = array (
			"DATE_FORMAT(letterdt, '%m/%d/%Y') AS my_date"
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'lettereoc');

		$this->acl = array ( 'bill', 'emr' );

		// Run parent constructor
		parent::__construct ( );
	} // end constructor Letters

	function add () {
		// Check for submit as add, else drop
		switch ($_REQUEST['my_submit']) {
			case __("Send to Provider"):
			include_once(resolve_module('LettersRepository'));
			$l = new LettersRepository();
			return $l->_add();
			break;

			case __("File Directly"):
			case __("Add"):
			break;

			default:
			global $action; $action = "addform";
			return $this->form();
			break;
		}

		// Check for uploaded msworddoc
		if (!empty($_FILES["msworddoc"]["tmp_name"]) and file_exists($_FILES["msworddoc"]["tmp_name"])) {
			$doc = $_FILES["msworddoc"]["tmp_name"];

			// Convert to the temporary file
			$__command = "/usr/bin/wvWare -x /usr/share/wv/wvText.xml \"$doc\"";
			$output = `$__command`;

			// Read temporary file into lettertext
			global $lettertext;
			$lettertext = $output;

			// Remove uploaded document
			unlink($doc);
		} // end checking for uploaded msworddoc

		// Call wrapped function
		$this->_add();

		// If this is management, refresh properly
		if ($_REQUEST['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
			Header("Location: ".$refresh);
			die();
		}
	} // end method add

	function _update() {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		if (!version_check($version, '0.3.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				 'ADD COLUMN lettereoc TEXT AFTER letterdt');
		}

		// Version 0.3.2
		//
		//	Added locking ability to letters module
		//
		if (!version_check($version, '0.3.2')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN locked INT UNSIGNED AFTER letterpatient');
			// Make sure they are all starting with 0
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'locked = \'0\'');
		}

		// Version 0.3.3
		//
		//	Added CC
		//
		if (!version_check($version, '0.3.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN lettercc BLOB AFTER letterto');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'lettercc = \'\'');
		}

		// Version 0.3.4
		//
		//	Added enclosures
		//
		if (!version_check($version, '0.3.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN letterenc BLOB AFTER lettercc');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'letterenc = \'\'');
		}

		// Version 0.3.5
		//
		//	Added typist
		//
		if (!version_check($version, '0.3.5')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN lettertypist VARCHAR(50) AFTER letterpatient');
			// Make sure they are all not null
			$sql->query('UPDATE '.$this->table_name.' SET '.
				'lettertypist = \'\'');
		}

	} // end method _update

} // end class Letters

register_module ("Letters");

?>
