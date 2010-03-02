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

class LettersTemplates extends SupportModule {

	var $MODULE_NAME = "Letters Templates";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "afbb6579-d81a-4020-8050-791daf464a00";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Letter Template";
	var $table_name = "letters_templates";
	var $order_field = "ltname";
	var $widget_hash = "##ltname##";

	public function __construct () {
		$this->variables = array (
			'ltname',
			'ltphy',
			'lttext'
		);

		$this->list_view = array (
			__("Name") => "ltname"
		);
	
		// Run parent constructor
		parent::__construct();
	} // end constructor

	// Method: GetTemplate
	//
	//	Get template text for an template by id.
	//
	// Parameters:
	//
	//	$id - ID of the template in question.
	//
	// Returns:
	//
	//	HTML-formatted text.
	//
	public function GetTemplate ( $id ) {
		$q = "SELECT lttext FROM letters_templates WHERE id=" . ( $id + 0 );
		return $GLOBALS['sql']->queryOne( $q );
	} // end method GetTemplate

} // end class LettersTemplates

register_module ("LettersTemplates");

?>
