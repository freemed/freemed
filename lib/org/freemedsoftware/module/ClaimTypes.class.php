<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //     Fred Forester <fforest@netcarrier.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

class ClaimTypes extends SupportModule {

	var $MODULE_NAME = "Insurance Claim Types";
	var $MODULE_AUTHOR = "Fred Forester <fforest@netcarrier.com>";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "07adcce6-d38b-425a-a136-7102f9a0cb13";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Claim Types";
	var $table_name  = "claimtypes";
	var $order_field = "clmtpname,clmtpdescrip";

	var $variables = array (
		"clmtpname",
		"clmtpdescrip",
		"clmtpdtadd",
		"clmtpdtmod"
	);

	public function __construct () {
		$this->list_view = array (
			__("Code") => "clmtpname",
			__("Description") => "clmtpdescrip"
		);

		// Run constructor
		parent::__construct( );
	} // end constructor ClaimTypes	

	protected function add_pre ( &$data ) {
		$data['clmtpdtadd'] = date('Y-m-d');
		$data['clmtpdtmod'] = date('Y-m-d');
	}

	protected function mod_pre ( &$data ) {
		$data['clmtpdtmod'] = date('Y-m-d');
	}
	
	public function getClaimTypes(){
		$q="SELECT id as Id,CONCAT(clmtpname,'(',clmtpdescrip,')') as claim_info FROM claimtypes";
		return $GLOBALS['sql']->queryAll( $q );
	}

} // end of class ClaimTypes

register_module ("ClaimTypes");

?>
