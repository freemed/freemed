<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //     Mark Lesswin <lesswin@ibm.net>
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

class IcdCodes extends SupportModule {

	var $MODULE_NAME = "ICD Codes";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "bfe6eb44-331b-44e9-9f66-8805e2d98f1d";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name 	 = "icd9";
	var $record_name	 = "ICD9 Code";
	var $order_field	 = "icd9code,icdnum";
	var $widget_hash	 = "##icd9code## ##icd9descrip##";
	var $archive_field = "icdarchive";
	var $variables = array (
		"icd9code",
		"icd10code",
		"icd9descrip",
		"icd10descrip",
		"icdmetadesc",
		"icddrg",
		"icdng",
		"icdamt",
		"icdcoll",
		"icdarchive"
	);

	public function __construct () {
		$this->list_view = array (
			__("Code")        => 	"icd9code",
			__("Description") =>	"icd9descrip"
		);

		parent::__construct( );
	} // end constructor IcdCodes

	public function display_short ( $code ) {
		switch (freemed::config_value('icd')) {
			case '10':
				$suffix = '10'; break;
			case '9':
			default: 
				$suffix = '9'; break;
		}

		$code_record = $GLOBALS['sql']->get_link( $this->table_name, $code );
		return $code_record['icd'.$suffix.'code'].' - '.
			$code_record['icd'.$suffix.'descrip'];
	} // end method display_short

} // end class IcdCodes

register_module ("IcdCodes");

?>
