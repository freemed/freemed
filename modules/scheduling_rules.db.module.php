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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class SchedulingRules extends SupportModule {

	var $MODULE_NAME    = "Scheduling Rules";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "7a84acc1-e460-4123-b4c6-92c6a8be4069";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name     = "schedulingrules";
	var $record_name    = "Scheduling Rule";
	var $order_field    = "id";
 
	var $variables      = array (
		"user",
		"provider",
		"reason",
		"dowbegin",
		"dowend",
		"datebegin",
		"dateend",
		"timebegin",
		"timeend",
		"newpatient"
	); 

	public function __construct ( ) {
		// For i18n: __("Scheduling Rules")

		$this->list_view = array (
			__("Comment") => 'reason'
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		if ( is_array( $data['provider'] ) or is_object( $data['provider'] ) ) {
			$data['provider'] = join( ',', $data['provider'] );
		}
		$data['user'] = freemed::user_cache()->user_number;
		$data['datebegin'] = $data['datebegin'] ? $s->ImportDate( $data['datebegin'] ) : '' ;
		if ( !$data['datebegin'] ) { unset( $data['datebegin'] ); }
		$data['dateend'] = $data['dateend'] ? $s->ImportDate( $data['dateend'] ) : '' ;
		if ( !$data['dateend'] ) { unset( $data['dateend'] ); }
	}

	protected function mod_pre ( &$data ) {
		$s = CreateObject( 'org.freemedsoftware.api.Scheduler' );
		if ( is_array( $data['provider'] ) or is_object( $data['provider'] ) ) {
			$data['provider'] = join( ',', $data['provider'] );
		}
		$data['user'] = freemed::user_cache()->user_number;
		$data['datebegin'] = $data['datebegin'] ? $s->ImportDate( $data['datebegin'] ) : '' ;
		if ( !$data['datebegin'] ) { unset( $data['datebegin'] ); }
		$data['dateend'] = $data['dateend'] ? $s->ImportDate( $data['dateend'] ) : '' ;
		if ( !$data['dateend'] ) { unset( $data['dateend'] ); }
	}

} // end class SchedulingRules

register_module ("SchedulingRules");

?>
