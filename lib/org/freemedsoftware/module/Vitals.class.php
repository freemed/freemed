<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

class Vitals extends EMRModule {

	var $MODULE_NAME = "Vitals";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "b1180011-fda5-42bb-b69e-6cbe8441cc68";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name   = "Vitals";
	var $table_name    = "vitals";
	var $patient_field = "patient";
	var $widget_hash   = "##note##";

	var $variables = array (
		  'dateof'
		, 'patient'
		, 'provider'
		, 'eoc'

		, 'v_temp_status'
		, 'v_temp_value'
		, 'v_temp_units'
		, 'v_temp_qualifier'

		, 'v_pulse_status'
		, 'v_pulse_value'
		, 'v_pulse_location'
		, 'v_pulse_method'
		, 'v_pulse_site'

		, 'v_pulseox_status'
		, 'v_pulseox_flowrate'
		, 'v_pulseox_o2conc'
		, 'v_pulseox_method'

		, 'v_glucose_status'
		, 'v_glucose_value'
		, 'v_glucose_units'
		, 'v_glucose_qualifier'

		, 'v_resp_status'
		, 'v_resp_value'
		, 'v_resp_method'
		, 'v_resp_position'

		, 'v_bp_status'
		, 'v_bp_s_value'
		, 'v_bp_d_value'
		, 'v_bp_location'
		, 'v_bp_method'
		, 'v_bp_position'

		, 'v_cvp_status'
		, 'v_cvp_value'
		, 'v_cvp_por'

		, 'v_cg_status'
		, 'v_cg_value'
		, 'v_cg_units'
		, 'v_cg_location'
		, 'v_cg_site'

		, 'v_h_status'
		, 'v_h_value'
		, 'v_h_units'
		, 'v_h_quality'

		, 'v_w_status'
		, 'v_w_value'
		, 'v_w_method'
		, 'v_w_quality'

		, 'v_pain_status'
		, 'v_pain_value'
		, 'v_pain_scale'

		, 'notes'
		, 'user'
	);

	public function __construct ( ) {
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Provider")    =>	"provider:physician",
			__("Description") =>	"notes"
		);
		$this->summary_options |= SUMMARY_LOCK | SUMMARY_DELETE;
		$this->summary_query = array(
			"DATE_FORMAT(dateof, '%M %d, %Y') AS full_date",
			"DATE_FORMAT(dateof, '%m/%d/%Y') AS my_date"
		);
		$this->summary_order_by = 'dateof DESC';
		//$this->loinc_mapping = '11369-6';
		//$this->loinc_display = array (
		//	"Immunization" => 'description',
		//	"Date" => 'full_date'
		//);

		// Set associations
		$this->_SetAssociation('EmrModule');
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'eoc');

		// Call parent constructor
		parent::__construct( );
	} // end constructor Vitals

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

} // end class Vitals

register_module ("Vitals");

?>
