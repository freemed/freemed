<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

class Immunizations extends EMRModule {

	var $MODULE_NAME = "Immunization";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "59f70f8a-4248-4310-b7a7-f4ace917e17b";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name   = "Immunizations";
	var $table_name    = "immunization";
	var $patient_field = "patient";
	var $widget_hash   = "##my_date##";

	var $variables = array (
		'dateof',
		'patient',
		'provider',
		'eoc',
		'immunization',
		'route',
		'body_site',
		'manufacturer',
		'lot_number',
		'previous_doses',
		'recovered',
		'notes',
		'user'
	);

	public function __construct ( ) {
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Provider")    =>	"provider:physician",
			__("Description") =>	"pnotesdescrip"
		);
		$this->summary_options |= SUMMARY_LOCK | SUMMARY_DELETE;
		$this->summary_query = array(
			"DATE_FORMAT(dateof, '%M %d, %Y') AS full_date",
			"DATE_FORMAT(dateof, '%m/%d/%Y') AS my_date"
		);
		$this->summary_query_link = array ( 'immunization' => 'bccdc' );
		$this->summary_order_by = 'dateof DESC,immunization.id';
		$this->loinc_mapping = '11369-6';
		$this->loinc_display = array (
			"Immunization" => 'description',
			"Date" => 'full_date'
		);

		// Set associations
		$this->_SetAssociation('EmrModule');
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'eoc');

		// Call parent constructor
		parent::__construct( );
	} // end constructor Immunizations

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
		if ( $data['dateof'] ) {
			$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
			$data['dateof'] = $s->ImportDate( $data['dateof'] );
		}
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
		if ( $data['dateof'] ) {
			$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
			$data['dateof'] = $s->ImportDate( $data['dateof'] );
		}
	}

} // end class Immunizations

register_module ("Immunizations");

?>
