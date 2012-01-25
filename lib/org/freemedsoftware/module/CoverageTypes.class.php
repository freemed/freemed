<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //	"Fred Forester <fforest@netcarrier.com>
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

class CoverageTypes extends SupportModule {

	var $MODULE_NAME = "Insurance Coverage Types";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "017873cf-2bd6-4ef6-832a-f5a9b3ca4403";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "Coverage Types";
	var $table_name  = "covtypes";
	var $order_field = "covtpname,covtpdescrip";

	var $widget_hash = '##covtpname## ##covtpdescrip##';

	var $variables = array (
		"covtpname",
		"covtpdescrip",
		"covtpdtadd",
		"covtpdtmod"
	);

	public function __construct ( ) {
		// __("Coverage Types")

		$this->list_view = array (
			__("Code") => "covtpname",
			__("Description") => "covtpdescrip"
		);

		// Run parent constructor
		parent::__construct ( );
	} // end constructor CoverageTypes	

	protected function add_pre ( &$data ) {
		$data['covtpdtadd'] = date("Y-m-d");
		$data['covtpdtmod'] = date("Y-m-d");
	}

	protected function mod_pre ( &$data ) {
		$data['covtpdtmod'] = date("Y-m-d");
	}

} // end class CoverageTypes

register_module ("CoverageTypes");

?>
