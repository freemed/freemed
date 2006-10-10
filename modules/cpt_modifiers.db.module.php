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

LoadObjectDependency('org.freemedsoftware.core.SupportModule');

class CptModifiers extends SupportModule {

	var $MODULE_NAME    = "CPT Modifiers";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "031fa33c-8824-4c6f-ab8a-d1bab8594a73";

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "CPT Modifiers";
	var $table_name     = "cptmod";

	var $widget_hash    = '##cptmod## - ##cptmoddescrip##';

	var $variables = array (
		"cptmod",
		"cptmoddescrip"
	);

	public function __construct () {
		// i18n: __("CPT Modifiers Maintenance")

		$this->list_view = array (
			__("Modifier")		=>	"cptmod",
			__("Description")	=>	"cptmoddescrip"
		);

		parent::__construct( );
	} // end constructor CptModifiers

} // end class CptModifiers

register_module ("CptModifiers");

?>
