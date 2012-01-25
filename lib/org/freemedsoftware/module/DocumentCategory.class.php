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

class DocumentCategory extends SupportModule {

	var $MODULE_NAME    = "Document Categorization";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;
	var $MODULE_UID     = "b9b6c952-c88f-473f-96bf-bacf1a23e439";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Document Category";
	var $table_name     = "documents_tc";

	var $widget_hash    = '##description##';

	var $variables = array (
		"type",
		"category",
		"description"
	);

	public function __construct () {
		// i18n: __("Document Category")

		$this->list_view = array (
			__("Type")		=>	"type",
			__("Category")		=>	"category",
			__("Description")	=>	"description"
		);

		parent::__construct( );
	} // end constructor

} // end class DocumentCategory

register_module ("DocumentCategory");

?>
