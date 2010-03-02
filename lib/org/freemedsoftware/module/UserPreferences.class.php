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

class UserPreferences extends SupportModule {

	var $MODULE_NAME = "User Preferences";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "4ffb9e1c-816d-48ea-8aa5-508d727b923d";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "userpreferences";

	public function __construct () {
		// Call parent constructor
		parent::__construct();
	} // end constructor

	// Method: GetAll
	//
	//	Get entire list of configuration slots for building a configuration
	//	interface.
	//
	// Returns:
	//
	//	Array of hashes.
	//
	public function GetAll( ) {
		$u = freemed::user_cache();
		$q = "SELECT * FROM ".$this->table_name." WHERE NOT ISNULL(u_title) ORDER BY u_title";
		$res = $GLOBALS['sql']->queryAll( $q );
		$result = array ( );
		foreach ( $res AS $r ) {
			$result[] = array_merge(
				$r,
				array(
					'u_value' => $u->manage_config[ $r['u_option'] ],
					'options' => explode( ',', $r['u_options'] )
				)
			);
		}
		return $result;
	} // end method GetAll

	// Method: GetConfigSections
	//
	//	Get list of configuration sections
	//
	// Returns:
	//
	//	Array of configuration sections.
	//
	public function GetConfigSections ( ) {
		$q = "SELECT DISTINCT( u_section ) AS s FROM ".$this->table_name." WHERE NOT ISNULL( u_section ) ORDER BY s";
		return $GLOBALS['sql']->queryCol( $q );
	} // end method GetConfigSections

	// Method: SetValues
	//
	//	Batch set configuration values.
	//
	// Parameters:
	//
	//	$hash - Hash of configuration values.
	//
	// Returns:
	//
	//	Boolean, success.
	//
	public function SetValues( $hash ) {
		if ( ! is_object( $hash ) && ! is_array( $hash ) ) {
			return false;
		}

		$h = (array) $hash;

		$u = freemed::user_cache();
		foreach ( $h AS $k => $v ) {
			$u->setManageConfig( $k, $v );
		}

		// Repopulate session
		$l = CreateObject( 'org.freemedsoftware.public.Login' );
		$l->SessionPopulate();

		return true;
	} // end method SetValues

} // end class UserPreferences

register_module("UserPreferences");

?>
