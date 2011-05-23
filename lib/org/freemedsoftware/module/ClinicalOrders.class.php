<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

class ClinicalOrders extends EMRModule {

	var $MODULE_NAME = "Clinical Orders";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "4d1ab69a-aaa9-4be0-b3a2-d060ac111936";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name   = "Clinical Orders";
	var $table_name    = "orders";
	var $patient_field = "patient";
	var $widget_hash   = "##note##";

	var $variables = array (
		  'dateof'
		, 'patient'
		, 'provider'
		, 'eoc'

		, 'ordertype'
		, 'orderstatus'
		, 'orderresponseflag'
		, 'orderingprovider'
		, 'delinquestdate'
		, 'orderpriority'
		, 'problems'
		, 'summary'
		, 'notes'
		, 'consultingprovider'

		, 'radiologycode'

		, 'labpanelcodeset'
		, 'labpanelcode'
		, 'labspecimenactioncode'

		, 'immunizationcode'
		, 'immunizationgivendate'
		, 'immunizationunits'

		, 'procedurecode'

		, 'user'
	);

	public function __construct ( ) {
		// Set associations
		$this->_SetAssociation('EmrModule');
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'eoc');

		// Call parent constructor
		parent::__construct( );
	} // end constructor

	protected function add_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		if ( $data['dateof'] ) {
			$data['dateof'] = $s->ImportDate( $data['dateof'] );
		}
		if ( $data['delinquentdate'] ) {
			$data['delinquentdate'] = $s->ImportDate( $data['delinquentdate'] );
		}
		if ( $data['immunizationgivendate'] ) {
			$data['immunizationgivendate'] = $s->ImportDate( $data['immunizationgivendate'] );
		}
	}

	protected function mod_pre ( &$data ) {
		$data['user'] = freemed::user_cache()->user_number;
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		if ( $data['dateof'] ) {
			$data['dateof'] = $s->ImportDate( $data['dateof'] );
		}
		if ( $data['delinquentdate'] ) {
			$data['delinquentdate'] = $s->ImportDate( $data['delinquentdate'] );
		}
		if ( $data['immunizationgivendate'] ) {
			$data['immunizationgivendate'] = $s->ImportDate( $data['immunizationgivendate'] );
		}
	}

} // end class ClinicalOrders

register_module ("ClinicalOrders");

?>
