<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2010 FreeMED Software Foundation
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

class DrugSampleInventory extends SupportModule {

	var $MODULE_NAME    = "Drug Sample Inventory";
	var $MODULE_VERSION = "0.2";
	var $MODULE_DESCRIPTION = "";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "31244998-b40f-4d68-a703-87f4f7b02867";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name     = "drugsampleinv";
	var $record_name    = "Drug Sample Inventory";
	var $order_field    = "logdate DESC";
	var $widget_hash    = "##logdate## - ##drugformal## ##samplecountremain##/##samplecount## (##lot##)";

	var $variables      = array (
		  "drugndc"
		, "packagecount"
		, "location"
		, "drugco"
		, "drugrep"
		, "invoice"
		, "samplecount"
		, "samplecountremain"
		, "lot"
		, "expiration"
		, "received"
		, "assignedto"
		, "loguser"
		, "logdate"
		, "disposeby"
		, "disposedate"
		, "disposemethod"
		, "disposereason"
		, "witness"
	);

	public function __construct () {
		// Run parent constructor
		parent::__construct();
	} // end constructor

	protected function add_pre ( &$data ) {
		$user = freemed::user_cache();
		$data['loguser'] = $user->user_number;
	} // end method add_pre

	// Method: Deduct
	//
	//	Deduct sample count.
	//
	// Parameters:
	//
	//	$id - Record id
	//
	//	$count - Number of samples to deduct
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function Deduct ( $id, $count ) {
		syslog(LOG_INFO, "deduct $amount from record $id");
		syslog(LOG_INFO, "UPDATE ".$this->table_name." SET ".
			"amount = amount - ".($count + 0)." ".
			"WHERE id = '".addslashes($id)."'");
		$result = $GLOBALS['sql']->query(
			"UPDATE ".$this->table_name." SET ".
			"samplecountremain = samplecountremain - ".($count + 0)." ".
			"WHERE id = '".addslashes($id)."'"
		);
		return $result;
	} // end method Deduct

} // end class DrugSampleInventory

register_module ("DrugSampleInventory");

?>
