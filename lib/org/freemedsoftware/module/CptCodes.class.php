<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

class CptCodes extends SupportModule {

	var $MODULE_NAME = "CPT Codes";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "884f604b-b5c0-475a-9cfa-5c912111f80e";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "CPT Codes";
	var $table_name = "cpt";
	var $order_fields = "cptcode";
	var $widget_hash = "##cptcode## ##cptnameint##";
	var $archive_field = "cptarchive";
	public function __construct () {
		$this->variables = array (
			"cptcode",
			"cptnameint",
			"cptnameext",
			"cptgender",
			"cpttaxed",
			"cpttype",
			"cptreqcpt",
			"cptexccpt",
			"cptreqicd",
			"cptexcicd",
			"cptrelval",
			"cptdeftos",
			"cptdefstdfee",
			"cptstdfee",
			"cpttos",
			"cpttosprfx",
			"cptarchive"
		);

		$this->list_view = array (
			__("Procedural Code") => "cptcode",
			__("Internal Description") => "cptnameint",
			__("External Description") => "cptnameext"
		);
	
		// Run parent constructor
		parent::__construct();
	} // end constructor CptCodes

	protected function add_pre ( &$data ) {
		$d['cptstdfee'] = serialize ( $d['cptstdfee'] );
		$d['cpttos'] = serialize ( $d['cpttos'] );
	}

	protected function mod_pre ( &$data ) {
		$d['cptstdfee'] = serialize ( $d['cptstdfee'] );
		$d['cpttos'] = serialize ( $d['cpttos'] );
	}

} // end class CptCodes

register_module ("CptCodes");

?>
