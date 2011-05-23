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

class ProgressNotesTemplates extends SupportModule {

	var $MODULE_NAME = "Progress Notes Templates";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "860e70c2-9d7f-4c9d-9d63-86adc92f3643";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name = "Progress Notes Template";
	var $table_name = "pnotes_templates";
	var $order_field = "pntname";
	var $widget_hash = "##pntname##";

	public function __construct () {
		$this->variables = array (
			'pntname',
			'pntphy',
			'pnt_S',
			'pnt_O',
			'pnt_A',
			'pnt_P',
			'pnt_I',
			'pnt_E',
			'pnt_R',
		);

		$this->list_view = array (
			__("Name") => "pntname"
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
	//	Hash.
	//
	public function GetTemplate ( $id ) {
		$q = "SELECT * FROM pnotes_templates WHERE id=" . ( $id + 0 );
		return $GLOBALS['sql']->queryRow( $q );
	} // end method GetTemplate

} // end class ProgressNotesTemplates

register_module ("ProgressNotesTemplates");

?>
