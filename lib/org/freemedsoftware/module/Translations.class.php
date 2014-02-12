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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class Translations extends EMRModule {

	var $MODULE_NAME = "Translation";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "a1273c34-ee52-41c1-bd08-ab4cbe8d538b";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Translations";
	var $table_name = 'translation';
	var $patient_field = 'tpatient';

	public function __construct () {
		// __("Translations")

		$this->variables = array (
			'ttimestamp'.
			'tpatient',
			'tmodule',
			'ttable',
			'tid',
			'tuser',
			'language',
			'comment'
		);

		$this->list_view = array (
			__("Date") => 'ts',
			__("Module") => 'tmodule',
			__("User") => 'tuser',
			__("Language") => 'language',
			__("Comment") => 'comment',
		);

		$this->summary_options = SUMMARY_VIEW | SUMMARY_DELETE | SUMMARY_NOANNOTATE;

		// call parent constructor
		parent::__construct( );
	} // end constructor

	protected function add_pre ( $data ) {
 		$data['tuser'] = $this_user->user_number;
	}

	public function mod ( $data ) { return false; }

	// Method: createTranslation
	//
	//	Create an translation.
	//
	// Parameters:
	//
	//	$module - Module to create translation in
	//
	//	$id - ID number
	//
	//	$language - Language to mark
	//
	//	$patient - (optional) Patient number
	//
	function createTranslation ($module, $id, $language, $patient = NULL) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('org.freemedsoftware.core.User'); }
		$q = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array(
				'tmodule' => strtolower($module),
				'tid' => $id,
				'ttimestamp' => SQL__NOW,
				'tpatient' => ( $patient ? $patient : $this->lookupPatient($module, $id) ),
				'ttable' => $this->resolve_module_to_table($module),
				'tuser' => $this_user->user_number,
				'language' => $language
			)
		);
		$res = $GLOBALS['sql']->query( $q );
	} // end method createTranslation

	private function resolve_module_to_table ( $module ) {
		$cache = freemed::module_cache();
		foreach ( $cache AS $v ) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['META_INFORMATION']['table_name'];
			}
		}
		trigger_error(__("Could not resolve table name!"), E_USER_ERROR);
	} // end method resolve_module_to_table

} // end class Translations

register_module ("Translations");

?>
