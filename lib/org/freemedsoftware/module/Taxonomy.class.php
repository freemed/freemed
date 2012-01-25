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

class Taxonomy extends SupportModule {
	var $MODULE_NAME    = "Taxonomy";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "XMR terms taxonomy.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "eafc99c9-2148-4cb5-9cbc-fe92191a61a9";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Taxonomy";
	var $table_name     = "taxonomy";
	var $order_by       = "text_name";

	var $widget_hash = "##text_name## (##taxonomy_type##)";

	var $variables = array (
		  'text_name'
		, 'taxonomy_type'
		, 'code_set'
		, 'code_value'
		, 'external_population'
		, 'widget_type'
		, 'widget_options'
	);

	public function __construct () {
		// For i18n: __("Taxonomy")

		$this->list_view = array (
			  __("Name")		=> "text_name"
			, __("Type")		=> "taxonomy_type"
			, __("Code Set") 	=> "code_set"
			, __("Code Value") 	=> "code_value"
		);

		// Run constructor
		parent::__construct();
	} // end constructor

} // end class Taxonomy

register_module ("Taxonomy");

?>
