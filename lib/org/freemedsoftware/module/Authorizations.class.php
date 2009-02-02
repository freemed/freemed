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

class Authorizations extends EMRModule {

	var $MODULE_NAME    = "Insurance Authorization";
	var $MODULE_VERSION = "0.2";
	var $MODULE_DESCRIPTION = "Insurance authorizations are used to track whether a patient is authorized by his or her insurance company for service during a particular period of time. If you do not use insurance support in FreeMED, this module is not needed.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "33447e8d-ba54-4255-af85-21876c020fa3";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Authorizations";
	var $table_name     = "authorizations";
	var $patient_field  = "authpatient";
	var $order_fields   = "authdtbegin,authdtend";
	var $widget_hash    = "##authdtbegin##-##authdtend## (##authvisitsremain##/##authvisits##)";

	public function __construct () {
		// __("Insurance Authorizations")
	
		// Set vars for patient management summary
		$this->list_view = array (
			__("From") => "authdtbegin",
			__("To")   => "authdtend",
			__("Remaining") => "_remaining"
		);
		$this->variables = array (
			"authdtmod",
			"authdtbegin",
			"authdtend",
			"authnum",
			"authtype",
			"authprov",
			"authprovid",
			"authinsco",
			"authvisits",
			"authvisitsused",
			"authvisitsremain",
			"authcomment",
			"authpatient",
			"authdtadd",
			"user"
		);

		$this->additional_query = array (
			"IF(authvisits>0,CONCAT(authvisitsremain,'/',authvisits),CONCAT(TO_DAYS(authdtend)-TO_DAYS(NOW()),' days')) AS _remaining"
		);

		$this->acl = array ( 'bill', 'emr' );
		$this->_SetAssociation( 'EmrModule' );

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function prepare ( $data ) {
		$d = $data;
		$d['authvisitsremain'] = $d['authvisits'] - $d['authvisitsused'];
		return $d;
	}

	protected function add_pre ( &$data ) {
		$data['authdtadd'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	}

	protected function mod_pre ( &$data ) {
		$data['authdtmod'] = date('Y-m-d');
		$data['user'] = freemed::user_cache()->user_number;
	}

} // end class Authorizations

register_module ("Authorizations");

?>
