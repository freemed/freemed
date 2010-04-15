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

class Tools extends SupportModule {

	var $MODULE_NAME = "Tools";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "bfa9b67b-7305-40d1-9c2b-d9969a3da825";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name = "tools";

	public function __construct () {
		// Call parent constructor
		parent::__construct();
	} // end constructor

	// Method: GetTools
	//
	//	Get list of Tools.
	//
	// Parameters:
	//
	//	$locale - (optional) Locale of Tools to look up. Defaults
	//	to DEFAULT_LANGUAGE as defined in lib/settings.php
	//
	// Returns:
	//
	//	Array of hashes containing:
	//	* tool_name
	//	* tool_desc
	//	* tool_uuid
	//
	public function GetTools ( $locale = NULL ) {
		freemed::acl_enforce( 'admin', 'read' );
		$query = "SELECT tool_name, tool_desc, tool_uuid FROM tools WHERE tool_locale=". $GLOBALS['sql']->quote( $locale == NULL ? DEFAULT_LANGUAGE : $locale ). " ORDER BY tool_name";
		return $GLOBALS['sql']->queryAll( $query );
	} // end method GetTools

	// Method: GetToolParameters
	//
	//	Get information on this tool, including parameters.
	//
	// Parameters:
	//
	//	$uuid - UUID of designated tool
	//
	//	$flattened - Flatten results (default true)
	//
	// Returns:
	//
	//	Array of hashes
	//
	public function GetToolParameters ( $uuid, $flatten = true ) {
		freemed::acl_enforce( 'admin', 'write' );
		$query = "SELECT * FROM tools WHERE tool_uuid=".$GLOBALS['sql']->quote( $uuid );
		$r = $GLOBALS['sql']->queryRow( $query );
		$return = array ();
		$return['tool_name'] = $r['tool_name'];
		$return['tool_desc'] = $r['tool_desc'];
		$return['tool_type'] = $r['tool_type'];
		$return['tool_sp'] = $r['tool_sp'];
		$return['tool_param_count'] = $r['tool_param_count'];
		if ($r['tool_param_count'] == 0) {
			if ( ! ( defined('FORCE_CAST_TO_PHP_PRIMITIVE_TYPES') || $flatten ) ) {
				$return['params'] = array();
			}
		} else {
			$names = explode( ',', $r['tool_param_names'] );
			$types = explode( ',', $r['tool_param_types'] );
			$optional = explode( ',', $r['tool_param_optional'] );
			for ( $p = 0; $p < $r['tool_param_count'] ; $p++ ) {
				if ( defined('FORCE_CAST_TO_PHP_PRIMITIVE_TYPES') || $flatten ) {
					// Force flattening of output for GWT
					$return['tool_param_name_'.$p] = $names[$p];
					$return['tool_param_type_'.$p] = $types[$p];
					$return['tool_param_optional_'.$p] = ( $optional[$p] ? true : false );
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
	} // end method GetToolParameters

	// Method: ExecuteTool
	//
	//	Actual Tools generation routine.
	//
	// Parameters:
	//
	//	$uuid - Tool UUID
	//
	//	$param - Array of parameters
	//
	// Returns:
	//
	//	Tool
	//
	public function ExecuteTool ( $uuid, $param ) {
		freemed::acl_enforce( 'admin', 'write' );
		$tool = $this->GetToolParameters( $uuid );

		// Sanity checking
		if (!$tool['tool_name']) { return false; }

		$s = CreateObject('org.freemedsoftware.api.Scheduler');
		foreach ($tool['params'] AS $k => $v) {
			if ( !$v['optional'] and !$param[$k] ) {
				syslog(LOG_INFO, get_class($this)."| parameter $k failed for tool $uuid");
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
		$query = "CALL ".$tool['tool_sp']." ( ". @join( ', ', $pass )." ); ";
		//print_r($result); die();

		$result = $GLOBALS['sql']->queryAllStoredProc( $query );

		return $result;
	} // end method ExecuteTool

} // end class Tools

register_module("Tools");

?>
