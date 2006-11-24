<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
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

// Class: org.freemedsoftware.public.Installation
//
//	Installation wizard public methods. Please note that these methods will
//	not work if the installation has been successfully completed.
//
class Installation {

	public function __constructor () { }

	// Method: CheckDbCredentials
	//
	// Parameters:
	//
	//	$data - Hash of data to check
	//
	// Returns:
	//
	//	Boolean
	//
	public function CheckDbCredentials ( $host, $name, $user, $pass ) {
		// Make sure we don't help out hack attempts
		if ( file_exists ( PHYSICAL_LOCATION . '/data/cache/healthy' ) ) {
			return false;
		}

		$link = @mysql_connect( $host, $user, $pass );
		if ( !$link ) { return false; }
		
		return mysql_select_db( $name, $link );
	} // end method CheckDbCredentials

	// Method: CheckPhpMysqlEnabled
	//
	//	Determine whether PHP's MySQL extension is properly installed
	//
	// Returns:
	//
	//	Boolean.
	//
	public function CheckPhpMysqlEnabled ( ) {
		return function_exists ( 'mysql_pconnect' );
	} // end method CheckPhpMysqlEnabled

	// Method: CreateAdministrationAccount
	//
	//	Create system administration account.
	//
	// Parameters:
	//
	//	$username - Target username for administration account
	//
	//	$password - Target password for administration account
	//
	// Returns:
	//
	//	Boolean, on success or failure
	//
	public function CreateAdministrationAccount ( $username, $password ) {
		// Check for an admin account (id = 1) already
		$q = $GLOBALS['sql']->queryOne ( "SELECT id FROM user WHERE id=1" );
		if ($q == 1) { return false; }

		// Otherwise, add an admin account
		$query = $GLOBALS['sql']->insert_query (
			'user',
			array (
				'username' => $username,
				'userpassword' => md5( $password ),
				'userlevel' => 'admin',
				'userdescrip' => 'Administrator',
				'usertype' => 'misc',
				'userfac' => '-1',
				'userphy' => '-1',
				'userphygrp' => '-1',
				'userrealphy' => '0',
				'id' => '1'
			)
		);
		$res = $GLOBALS['sql']->query( $query );
		return $res ? true : false;
	} // end method CreateAdministrationAccount

	// Method: CreateSettingsFile
	//
	//	Generate lib/settings.php file.
	//
	// Parameters:
	//
	//	$params - Hash containing:
	//	* host - Database host name
	//	* name - Database name
	//	* username - Database user
	//	* password - Database password
	//	* installation - Installation name
	//	* starttime - Start time for scheduler
	//	* endtime - End time for scheduler
	//
	// Returns:
	//
	//	Boolean.
	//
	public function CreateSettingsFile ( $params ) {
		// Make sure we don't help out hack attempts
		if ( file_exists ( PHYSICAL_LOCATION . '/data/cache/healthy' ) ) {
			return false;
		}

		include_once( PHYSICAL_LOCATION.'/lib/smarty/Smarty.class.php' );
		$smarty = new Smarty;

		// Override Smarty defaults
		$smarty->template_dir = PHYSICAL_LOCATION."/lib/";
		$smarty->compile_dir = PHYSICAL_LOCATION."/data/cache/smarty/templates_c/";
		$smarty->cache_dir = PHYSICAL_LOCATION."/data/cache/smarty/cache/";

		// Change delimiters to be something a bit more sane
		$smarty->left_delimiter = '<{';
		$smarty->right_delimiter = '}>';

		foreach ( $params AS $k => $v ) { $smarty->assign( $k, $v ); }

		$return = @file_put_contents( $smarty->fetch( 'settings.php.tpl' ), PHYSICAL_LOCATION.'/lib/settings.php' );
		return $return ? true : false;
	} // end method CreateSettingsFile

	// Method: CreateDatabase
	//
	//	Run actual database creation routines.
	//
	// Returns:
	//
	//	Boolean.
	//
	public function CreateDatabase ( ) {
		if ( ! ( is_defined('DB_USER') && is_defined('DB_PASSWORD') && is_defined('DB_NAME') ) ) {
			return false;
		}
		// Make sure we don't help out hack attempts
		if ( file_exists ( PHYSICAL_LOCATION . '/data/cache/healthy' ) ) {
			return false;
		}
	
		// Create initial modules table	
		$command = dirname(__FILE__).'/../../scripts/load_schema.sh '.escapeshellarg('mysql').' '.escapeshellarg('modules').' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME);
	        system ( $command );

		// Load module index, which should cause complete initialization
		$modules = CreateObject( 'org.freemedsoftware.core.ModuleIndex', true );

		// Return success status
		return true;
	} // end method CreateDatabase

} // end class Installation

?>
