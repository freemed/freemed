<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //      Alexandru Zbarcea <zbarcea.a@gmail.com>
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

		ob_start();
		//syslog(LOG_INFO, "DEBUG: CheckDbCredentials: mysql_connect ( $host, $user, $pass ) with name = $name");
		$link = mysql_connect( $host, $user, $pass );
		//syslog(LOG_INFO, "DEBUG: CheckDbCredentials: mysql_connect printed: ".ob_get_contents());
		ob_end_clean();
		
		if ( !$link ) {
			syslog(LOG_INFO, "DEBUG: CheckDbCredentials: failed to get link");
			return false;
		}

		// Hack to create the database if it has not been created yet
		mysql_query( "CREATE DATABASE '".addslashes( $name )."'" );
		
		$select = mysql_select_db( $name, $link );
		syslog(LOG_INFO, "DEBUG: CheckDbCredentials: select = $select");
		return $select;
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

	// Method: CreateSettings
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
	public function CreateSettings ( $params ) {
		// Make sure we don't help out hack attempts
		if ( file_exists ( PHYSICAL_LOCATION . '/data/cache/healthy' ) ) {
			return false;
		}

		$smarty = CreateObject( 'net.php.smarty.Smarty' );

		// Override Smarty defaults
		$smarty->template_dir = PHYSICAL_LOCATION."/lib/";
		$smarty->compile_dir = PHYSICAL_LOCATION."/data/cache/smarty/templates_c/";
		$smarty->cache_dir = PHYSICAL_LOCATION."/data/cache/smarty/cache/";

		// Change delimiters to be something a bit more sane
		$smarty->left_delimiter = '<{';
		$smarty->right_delimiter = '}>';

		//syslog(LOG_INFO, "CreateSettings: params = ".serialize($params));
		foreach ( $params AS $k => $v ) { $smarty->assign( $k, $v ); }

		$return = @file_put_contents( PHYSICAL_LOCATION.'/lib/settings.php', $smarty->fetch( 'settings.php.tpl' ) );
		return $return ? true : false;
	} // end method CreateSettings

	// Method: CreateDatabase
	//
	//	Run actual database creation routines.
	//
	// Returns:
	//
	//	Boolean.
	//
	public function CreateDatabase ( $admin_username, $admin_password ) {
		syslog(LOG_INFO, "CreateDatabase() invoked");
		if ( ! ( defined('DB_USER') && defined('DB_PASSWORD') && defined('DB_NAME') ) ) {
			syslog(LOG_INFO, "CreateDatabase() failing due to inadequate DB setup");
			return false;
		}
		// Make sure we don't help out hack attempts
		if ( file_exists ( PHYSICAL_LOCATION . '/data/cache/healthy' ) ) {
			syslog(LOG_INFO, "CreateDatabase() failing due to healthy system in place");
			return false;
		}
	
		// Create initial modules table	
		syslog(LOG_INFO, "CreateDatabase(): modules table creation");
		$command = dirname(__FILE__).'/../../../../scripts/load_schema.sh '.escapeshellarg('mysql').' '.escapeshellarg('modules').' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME);
	        system ( $command );
		syslog(LOG_INFO, "CreateDatabase(): user table creation");
		$command = dirname(__FILE__).'/../../../../scripts/load_schema.sh '.escapeshellarg('mysql').' '.escapeshellarg('user').' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME);
	        system ( $command );

		// Check for SQL object
		if (!is_object($GLOBALS['sql'])) {
			syslog(LOG_INFO, "CreateDatabase(): creating sql object");
			$GLOBALS['sql'] = CreateObject ( 'org.freemedsoftware.core.FreemedDb' );
		}

		syslog(LOG_INFO, "CreateDatabase(): session table creation");
		$command = dirname(__FILE__).'/../../../../scripts/load_schema.sh '.escapeshellarg('mysql').' '.escapeshellarg('session').' '.escapeshellarg(DB_USER).' '.( DB_PASSWORD ? escapeshellarg(DB_PASSWORD) : '""' ).' '.escapeshellarg(DB_NAME);
	        system ( $command );

		// Load module index, which should cause complete initialization
		$modules = CreateObject( 'org.freemedsoftware.core.ModuleIndex', true, true );

		// Set admin username / password
		$this->CreateAdministrationAccount( $admin_username, $admin_password );

		// Return success status
		syslog(LOG_INFO, "CreateDatabase(): completed");
		return true;
	} // end method CreateDatabase

	// Method: SetHealthyStatus
	//
	//	Sets the system to be useable by "touching" the data/cache/healthy indicator.
	//
	// Returns:
	//
	//	Boolean, indicator of success.
	//
	public function SetHealthyStatus ( ) {
		$touched = touch ( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/cache/healthy' );
		return $touched ? true : false;
	} // end method SetHealthyStatus

} // end class Installation

?>
