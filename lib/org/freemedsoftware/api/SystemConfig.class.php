<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.SystemConfig
//
//	System configuration management functions.
//
class SystemConfig {

	public function __construct ( ) { }

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
		$q = "SELECT * FROM config WHERE NOT ISNULL(c_title) ORDER BY c_title";
		$res = $GLOBALS['sql']->queryAll( $q );
		$result = array ( );
		foreach ( $res AS $r ) {
			$result[] = array_merge(
				$r,
				array( 'options' => defined('GWTPHP_FORCE_SHOEHORN') ? $r['c_options'] : explode( ',', $r['c_options'] ) )
			);
		}
		return $result;
	} // end method GetAll

	// Method: GetValue
	//
	// Parameters:
	//
	//	$config - Configuration key
	//
	// Returns:
	//
	//	Configuration value.
	//
	public function GetValue ($config) {
		// Perform search
		$query = "SELECT c_value FROM config WHERE c_option=".$GLOBALS['sql']->quote( $config );
		$result = $GLOBALS['sql']->queryOne( $query );
		return $result;
	} // end public function GetValue

	// Method: GetConfigSections
	//
	//	Get list of configuration sections
	//
	// Returns:
	//
	//	Array of configuration sections.
	//
	public function GetConfigSections ( ) {
		$q = "SELECT DISTINCT( c_section ) AS s FROM config WHERE NOT ISNULL( c_section ) ORDER BY s";
		return $GLOBALS['sql']->queryCol( $q );
	} // end method GetConfigSections

	// Method: SetValue
	//
	//	Set global configuration value
	//
	// Parameters:
	//
	//	$var - Configuration key
	//
	//	$val - Configuration value
	//
	function SetValue ( $var, $val ) {
		if (! freemed::acl ( 'admin', 'config' ) ) { 
			syslog(LOG_INFO, "Attempted SystemConfig.SetValue without authorization");
			return false;
		}

		// Perform search (to decide if it's insert or update)
		$query = "SELECT * FROM config WHERE c_option=".$GLOBALS['sql']->quote( $var );
		$result = $GLOBALS['sql']->queryRow( $query );

		if ($result['c_option']) {
			$res = $GLOBALS['sql']->query($GLOBALS['sql']->update_query(
				"config",
				array("c_value" => $val),
				array("c_option" => $var)
			));
			return ($res == true);
		} else {
			$res = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
				"config",
				array("c_value" => $val)
			));
			return ($res == true);
		}
	} // end public function SetValue

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
		freemed::acl_enforce( 'admin', 'config' );

		if ( ! is_object( $hash ) && ! is_array( $hash ) ) {
			return false;
		}

		$h = (array) $hash;

		foreach ( $h AS $k => $v ) {
			$q = "UPDATE config SET c_value=".$GLOBALS['sql']->quote( $v )." WHERE c_option=".$GLOBALS['sql']->quote( $k );
			$GLOBALS['sql']->query( $q );
		}

		return true;
	} // end method SetValues

} // end class Config

?>
