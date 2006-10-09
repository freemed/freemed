<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

class AuthorizationsModule extends EMRModule {

	var $MODULE_NAME    = "Insurance Authorizations";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.2";
	var $MODULE_DESCRIPTION = "
		Insurance authorizations are used to track whether
		a patient is authorized by his or her insurance
		company for service during a particular period of
		time. If you do not use insurance support in
		FreeMED, this module is not needed.
	";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "33447e8d-ba54-4255-af85-21876c020fa3";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Authorizations";
	var $table_name     = "authorizations";
	var $patient_field  = "authpatient";
	var $order_fields   = "authdtbegin,authdtend";
	var $widget_hash    = "##authdtbegin##-##authdtend## (##authvisitsremain##/##authvisits##)";

	var $variables = array (
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
		"authdtadd"
	);

	public function __construct () {
		// __("Insurance Authorizations")
		// Table definition
		$this->table_definition = array (
			'authdtadd' => SQL__DATE,
			'authdtmod' => SQL__DATE,
			'authpatient' => SQL__INT_UNSIGNED(0),
			'authdtbegin' => SQL__DATE,
			'authdtend' => SQL__DATE,
			'authnum' => SQL__VARCHAR(25),
			'authtype' => SQL__INT_UNSIGNED(0),
			'authprov' => SQL__INT_UNSIGNED(0),
			'authprovid' => SQL__VARCHAR(20),
			'authinsco' => SQL__INT_UNSIGNED(0),
			'authvisits' => SQL__INT_UNSIGNED(0),
			'authvisitsused' => SQL__INT_UNSIGNED(0),
			'authvisitsremain' => SQL__INT_UNSIGNED(0),
			'authcomment' => SQL__VARCHAR(100),
			'id' => SQL__SERIAL
		);
	
		// Set vars for patient management summary
		$this->summary_vars = array (
			__("From") => "authdtbegin",
			__("To")   => "authdtend",
			__("Remaining") => "_remaining"
		);
		$this->summary_query = array (
			"IF(authvisits>0,CONCAT(authvisitsremain,'/',authvisits),CONCAT(TO_DAYS(authdtend)-TO_DAYS(NOW()),' days')) AS _remaining"
		);

		$this->form_vars = array (
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
			"authdtadd"
		);

		$this->acl = array ( 'bill', 'emr' );

		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function prepare ( $data ) {
		$d = $data;
		$d['authvisitsremain'] = $d['authvisits'] - $d['authvisitsused'];
		return $d;
	}

	protected function preadd ( $data ) {
		$d = $data;
		$d['authdtadd'] = date('Y-m-d');
		return $d;
	}

	protected function premod ( $data ) {
		$d = $data;
		$d['authdtmod'] = date('Y-m-d');
		return $d;
	}

} // end class AuthorizationsModule

register_module ("AuthorizationsModule");

?>
