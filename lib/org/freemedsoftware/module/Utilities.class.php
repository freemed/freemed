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

class Utilities extends SupportModule {

	var $MODULE_NAME = "Utilities";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "f2df6dc1-aef3-479a-bc9b-4737704ed164";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "utilities";

	public function __construct () {
		// Call parent constructor
		parent::__construct();
	} // end constructor

	// Method: GetUtilities
	//
	//	Get list of utilities.
	//
	// Parameters:
	//
	//	$locale - (optional) Locale of utilities to look up. Defaults
	//	to DEFAULT_LANGUAGE as defined in lib/settings.php
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* utility_name
	//	* utility_desc
	//	* utility_uuid
	//
	public function GetUtilities ( $locale = NULL ) {
		$query = "SELECT utility_name, utility_desc, utility_uuid FROM utilities WHERE utility_locale=". $GLOBALS['sql']->quote( $locale == NULL ? DEFAULT_LANGUAGE : $locale ). " ORDER BY utility_name";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetUtilities

	// Method: GetUtilityParameters
	//
	//	Get information on this utility, including parameters.
	//
	// Parameters:
	//
	//	$uuid - UUID of designated utility
	//
	//	$flattened - Flatten results (default true)
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetUtilityParameters ( $uuid, $flatten = true ) {
		$query = "SELECT * FROM utilities WHERE utility_uuid=".$GLOBALS['sql']->quote( $uuid );
		$r = $GLOBALS['sql']->queryRow( $query );
		$return = array ();
		$return['utility_name'] = $r['utility_name'];
		$return['utility_desc'] = $r['utility_desc'];
		$return['utility_type'] = $r['utility_type'];
		$return['utility_sp'] = $r['utility_sp'];
		$return['utility_param_count'] = $r['utility_param_count'];
		if ($r['utility_param_count'] == 0) {
			if ( ! ( defined('FORCE_CAST_TO_PHP_PRIMITIVE_TYPES') || $flatten ) ) {
				$return['params'] = array();
			}
		} else {
			$names = explode( ',', $r['utility_param_names'] );
			$types = explode( ',', $r['utility_param_types'] );
			$optional = explode( ',', $r['utility_param_optional'] );
			for ( $p = 0; $p < $r['utility_param_count'] ; $p++ ) {
				if ( defined('FORCE_CAST_TO_PHP_PRIMITIVE_TYPES') || $flatten ) {
					// Force flattening of output for GWT
					$return['utility_param_name_'.$p] = $names[$p];
					$return['utility_param_type_'.$p] = $types[$p];
					$return['utility_param_optional_'.$p] = ( $optional[$p] ? true : false );
				} else {
					$return['params'][$p] = array (
						'name' => $names[$p],
						'type' => $types[$p],
						'optional' => ( $optional[$p] ? true : false )
					);
				}
			}
		}
		return $return;
	} // end method GetUtilityParameters

	// Method: ExecuteUtility
	//
	//	Actual utilities generation routine.
	//
	// Parameters:
	//
	//	$uuid - Utility UUID
	//
	//	$param - Array of parameters
	//
	// Returns:
	//
	//	Utility
	//
	public function ExecuteUtility ( $uuid, $param ) {
		$utility = $this->GetUtilityParameters( $uuid );

		// Sanity checking
		if (!$utility['utility_name']) { return false; }

		$s = CreateObject('org.freemedsoftware.api.Scheduler');
		foreach ($utility['params'] AS $k => $v) {
			if ( !$v['optional'] and !$param[$k] ) {
				syslog(LOG_INFO, get_class($this)."| parameter $k failed for utility $uuid");
				return false;
			}

			switch ($v['type']) {
				case 'Date':
				$pass[] = $GLOBALS['sql']->quote( $s->ImportDate( $param[$k] ) );
				break;

				default:
				$pass[] = $GLOBALS['sql']->quote( $param[$k] );
				break;
			}
		}

		// Form query
		$query = "CALL ".$utility['utility_sp']." ( ". @join( ', ', $pass )." ); ";
		//print_r($result); die();

		$result = $GLOBALS['sql']->queryAllStoredProc( $query );

		return $result;
	} // end method ExecuteUtility

} // end class Utilities

register_module("Utilities");

?>
