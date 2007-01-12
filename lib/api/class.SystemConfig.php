<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2007 FreeMED Software Foundation
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

} // end class Config

?>
