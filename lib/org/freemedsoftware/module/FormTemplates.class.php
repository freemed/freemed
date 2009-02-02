<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
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

class FormTemplates extends SupportModule {

	var $MODULE_NAME    = "Form Templates";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "Form templates.";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "66f321db-0d59-4924-851a-5c8c919378ac";

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Form Template";
	var $table_name     = "form";
	var $order_by       = "f_name";

	var $widget_hash = "##f_name## (##f_lang##)";

	var $variables = array (
		'f_uuid',
		'f_lang',
		'f_name',
		'f_template',
		'f_electronic_template'
	);

	public function __construct () {
		// For i18n: __("Form Templates")

		$this->list_view = array (
			__("Name")	=> "f_name",
			__("Language")	=> "f_lang"
		);

		// Run constructor
		parent::__construct();
	} // end constructor

	protected function add_pre( &$data ) {
		unset( $data['f_created'] );
		$data['f_uuid'] = $this->GenerateUUID( );
	}

	protected function mod_pre( &$data ) {
		unset( $data['f_created'] );
		unset( $data['f_uuid'] );
	}

	// Method: GenerateUUID
	//
	//	Generate RFC-4122 compliant UUID, found at
	//	http://us2.php.net/manual/en/function.uniqid.php#69164
	//
	// Returns:
	//
	//	UUID string
	//
	public function GenerateUUID ( ) {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	} // end method GenerateUUID

} // end class FormTemplates

register_module ("FormTemplates");

?>
