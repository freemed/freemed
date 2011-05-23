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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class DiagnosisFamily extends SupportModule {

	var $MODULE_NAME    = "Diagnosis Family";
	var $MODULE_VERSION = "0.2";
	var $MODULE_DESCRIPTION = "Diagnosis families are part of FreeMED's attempt to make practice management more powerful through outcomes management. Diagnosis families are used to group diagnoses more intelligently, allowing FreeMED to analyze treatment patterns.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "8af17933-d92b-43d6-a989-67a96c03f1cf";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name     = "diagfamily";
	var $record_name    = "Diagnosis Family";
	var $order_field    = "dfname, dfdescrip";

	var $variables      = array (
		"dfname",
		"dfdescrip"
	);

	var $rpc_field_map = array (
		'name' => 'dfname',
		'description' => 'dfdescrip'
	);

	var $widget_hash = '##dfname## (##dfdescrip##)';

	public function __construct () {
		// For i18n: __("Diagnosis Family")

		$this->list_view = array (
			__("Name")		=>	"dfname",
			__("Description")	=>	"dfdescrip"
		);

		// Run parent constructor
		parent::__construct();
	} // end constructor

} // end class DiagnosisFamily

register_module ("DiagnosisFamily");

?>
