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

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class Annotations extends EMRModule {

	var $MODULE_NAME = "Annotations";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "58b7eb4e-a4dc-46db-841f-4e2bf3b64ddd";

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name = "Annotations";
	var $table_name = 'annotations';
	var $patient_field = 'apatient';

	public function __construct () {
		// __("Annotations")

		$this->variables = array (
			'atimestamp'.
			'apatient',
			'amodule',
			'atable',
			'aid',
			'auser',
			'annotation'
		);

		$this->list_view = array (
			__("Date") => 'ts',
			__("Module") => 'amodule',
			__("User") => 'auser',
			__("Annotation") => 'annotation'
		);

		$this->summary_options = SUMMARY_VIEW | SUMMARY_DELETE | SUMMARY_NOANNOTATE;

		// call parent constructor
		parent::__construct( );
	} // end constructor

	protected function add_pre ( &$data ) {
 		$data['auser'] = $this_user->user_number;
	}

	public function mod ( $data ) { return false; }

	// Method: createAnnotation
	//
	//	Create an annotation.
	//
	// Parameters:
	//
	//	$module - Module to create annotation in
	//
	//	$id - ID number
	//
	//	$text - Text to annotate
	//
	//	$patient - (optional) Patient number
	//
	function createAnnotation ($module, $id, $text, $patient = NULL) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('org.freemedsoftware.core.User'); }
		$q = $GLOBALS['sql']->insert_query(
			$this->table_name,
			array(
				'amodule' => strtolower($module),
				'aid' => $id,
				'atimestamp' => SQL__NOW,
				'apatient' => ( $patient ? $patient : $this->lookupPatient($module, $id) ),
				'atable' => $this->resolve_module_to_table($module),
				'auser' => $this_user->user_number,
				'annotation' => $text
			)
		);
		$res = $GLOBALS['sql']->query($q);
	} // end method createAnnotation

	private function resolve_module_to_table ( $module ) {
		$cache = freemed::module_cache();
		foreach ( $cache AS $v ) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['META_INFORMATION']['table_name'];
			}
		}
		trigger_error(__("Could not resolve table name!"), E_USER_ERROR);
	} // end method resolve_module_to_table

	// Method: getAnnotations
	//
	//	Get annotations, if present.
	//
	// Parameters:
	//
	//	$module - Module to examine for annotations
	//
	//	$id - ID number
	//
	// Returns:
	//
	//	Array of annotations, otherwise false.
	//
	public function getAnnotations ($module, $id) {
		$q = "SELECT * FROM ".$this->table_name." ".
			"WHERE amodule = '".addslashes($module)."' ".
			"AND aid = '".addslashes($id)."'";
		$res = $GLOBALS['sql']->queryAll( $q );
		return $res;
	} // end method getAnnotations

	// Method: outputAnnotations
	//
	//	Produce tooltip-friendly annotations from the output
	//	of <getAnnotations>.
	//
	// Parameters:
	//
	//	$annotations - Array of annotations
	//
	// Returns:
	//
	//	XHTML-formatted annotation string
	//
	public function outputAnnotations ( $annotations ) {
		foreach ($annotations AS $a) {
			$user = $GLOBALS['sql']->get_link( 'user', $a['auser'] );
			$p = str_replace("\r", '', stripslashes($a['annotation']));
			$b[] .= "<b>".stripslashes($user['userdescrip'])."</b>\n".
				"<i>".freemed::sql2date($a['atimestamp'])."</i>\n".
				$p;
		}
		return join("\n\n", $b);
	} // end method outputAnnotations

	// Method: prepareAnnotation
	//
	//	Prepare an annotation for being embedded in a Javascript
	//	string.
	//
	// Parameters:
	//
	//	$a - Annotation text.
	//
	// Returns:
	//
	//	Javascript string formatted text.
	//
	public function prepareAnnotation ( $a ) {
		$b = $a;
		$b = str_replace("'", '\\\'', $b);
		$b = str_replace("\"", '\\"', $b);
		$b = str_replace("\n", '<br/>\n', $b);
		$b = htmlentities($b);
		return $b;
	} // end method prepareAnnotation

	// Method: lookupPatient
	//
	//	Get patient from other information given.
	//
	// Parameters:
	//
	//	$module - Module name
	//
	//	$id - Record id
	//
	// Returns:
	//
	//	Patient id number
	//
	public function lookupPatient ( $module, $id ) {
		$cache = freemed::module_cache();
		foreach ( $cache AS $v ) {
			if ($t = $v['META_INFORMATION']['table_name']) {
				$tables[strtolower($v['MODULE_CLASS'])] = $t;
				$pfield[strtolower($v['MODULE_CLASS'])] = $v['META_INFORMATION']['patient_field'];
			}
		}
		if ($pfield[strtolower($module)] and $tables[strtolower($module)]) {
			$r = $GLOBALS['sql']->get_link( $tables[strtolower($module)], $id );
			return $r[$pfield[strtolower($module)]];
		} else {
			return 0;
		}
	} // end method lookupPatient

	// Update
	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);
		//if (!version_check($version, '0.2')) {
		//}	
	} // end method _update

} // end class Annotations

register_module ("Annotations");

?>
