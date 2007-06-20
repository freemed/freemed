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

// File: Core API
//
//	This is the main FreeMED API, which contains the bulk of FreeMED's
//	commonly used functions. The rest of the functions are located in
//	classes which are called dynamically using CreateObject(). It is
//	located in lib/API.php.
//

class freemed {

	// Function: freemed::acl
	//
	//	Query ACL for a particular resource.
	//
	// Parameters:
	//
	//	$category - Which category of ARO is being queried. Examples
	//	would be things like 'admin', 'bill', 'schedule', et cetera.
	//
	//	$permission - Resource being queried. This would be things
	//	like 'add', 'modify', 'view', 'delete', et cetera.
	//
	//	$axo_group - (optional) AXO group to be searched on. Note
	//	that valid groups are things like 'patient'.
	//
	//	$axo_item - (optional) AXO item. This would be the patient
	//	ID key for a patient, or something else for another AXO
	//	group.
	//
	// Returns:
	//
	//	Boolean, depending on whether the resource is allowed or denied.
	//
	public function acl ( $category, $permission, $axo_group=NULL, $axo_item=NULL ) {
		static $user;
		if ( !isset( $user ) ) {
			$user = freemed::user_cache()->user_number;
		}

		if ( $axo_group != NULL ) {
			return $GLOBALS['acl']->acl_check( $category, $permission, 'user', $user, $axo_group, $axo_item );
		} else {
			return $GLOBALS['acl']->acl_check( $category, $permission, 'user', $user );
		}
	} // end function freemed::acl

	// Function: freemed::acl_enforce
	//
	//	Wrapper for <freemed::acl> which errors out if a certain access
	//	level isn't matched.
	//
	// Parameters:
	//
	//	Same as <freemed::acl>
	//
	public function acl_enforce ( $category, $permission, $axo_group=NULL, $axo_item=NULL ) {
		$v = freemed::acl( $category, $permission, $axo_group, $axo_item );
		if ( ! $v ) {
			$user = freemed::user_cache()->user_number;
			syslog( LOG_INFO, "ACL| ${category}/${permission}/${axo_group}/${axo_item} failed for ${user}" );
			trigger_error( __("Access denied."), E_USER_ERROR );
			return false;
		}
		return true;
	} // end function freemed::acl_enforce

	// Function: freemed::acl_patient
	//
	//	Check ACLs, optionally with patient access. Note that this
	//	function behaves exactly like <freemed::acl> if the
	//	acl_patient configuration value is disabled.
	//
	// Parameters:
	//
	//	$category - Which category of ARO is being queried. Examples
	//	would be things like 'admin', 'bill', 'schedule', et cetera.
	//
	//	$permission - Resource being queried. This would be things
	//	like 'add', 'modify', 'view', 'delete', et cetera.
	//
	//	$pid - Patient id number (record id) for patient being
	//	checked.
	//
	// Returns:
	//
	//	Boolean, whether access is granted.
	//
	// See Also:
	//	<freemed::acl>
	//
	public function acl_patient ( $category, $permission, $pid ) {
		if (freemed::config_value('acl_patient')) {
			// Advanced check for patient ACL as well
			$r_acl = freemed::acl( $category, $permission );
			$p_acl = freemed::acl( $category, $permission, 'patient', $pid );
			// Decide on combination of regular and patient ACLs
			if ($p_acl == 1) {
				return true;
			}
			return false;
		} else {
			// Basic perms check
			return freemed::acl( $category, $permission );
		}
	} // end function freemed::acl_patient

	// Function: freemed::check_access_for_facility
	//
	//	Checks to see if the current user has access to the specified
	//	facility.
	//
	// Parameters:
	//
	//	$facility_number - the database id of the facility in question
	//
	// Returns:
	//
	//	$access - boolean value, whether access is granted
	//
	// See Also:
	//
	//	<freemed::check_access_for_patient>
	//
	public function check_access_for_facility ($facility_number) {
		global $_SESSION;

		// Separate out authdata
		$authdata = $_SESSION['authdata'];

		// Root has all access...
		if ($_SESSION['authdata']['user'] == 1) return true;

		// Grab the authorizations field
		$f_fac = freemed::get_link_field ($authdata['user'], "user", "userfac");

		// No facility, assume no access restrictions
		if ($facility_number == 0) return true;

		// If it's an "ALL" or it is found, return true
		if ((fm_value_in_string($f_fac, "-1")) OR
				(fm_value_in_string($f_fac, $facility_number)))
			return true;

	    	// Default to false
		return false;
	} // end function freemed::check_access_for_facility

	// Function: freemed::check_access_for_patient
	//
	//	Checks to see whether the current user has access to the
	//	specified patient.
	//
	// Parameters:
	//
	//	$patient_number - The database identifier for the patient
	//
	//	$user (optional) - The database identifier for the current
	//	user. (This should be used when dealing with XML-RPC or
	//	another protocol which does not use cookie-based authentication.
	//
	// Returns:
	//
	//	$access - boolean, whether access is granted
	//
	// See Also:
	//
	//	<freemed::check_access_for_facility>
	//
	public function check_access_for_patient ($patient_number, $_user=0) {
		if ($_user == 0) {
			// Grab authdata
			$_authdata = $_SESSION['authdata'];
			$user = $_authdata['user'];
		} else {
			$user = $_user;
		}
		
		//eventually logging should include different messages for all returns here...
		// Root has all access...
		if ($user == 1) return true;
	
		// Grab auth information from db
		$f_user   = $GLOBALS['sql']->get_link ( 'user', $user );
	
		// Get data records in question for the user
		$f_fac    = $f_user ["userfac"   ];
		$f_phy    = $f_user ["userphy"   ];
		$f_phygrp = $f_user ["userphygrp"];
	
		// Retrieve patient record
		$f_pat    = $GLOBALS['sql']->get_link ( 'patient', $patient_number );
		// check for universal access
		if ((fm_value_in_string ($f_fac,    "-1")) OR
			(fm_value_in_string ($f_phy,    "-1")) OR
			(fm_value_in_string ($f_phygrp, "-1")))
			return true;
	
		// Check for physician in any physician fields
		if (($f_pat["ptpcp"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptpcp"])))
			return true;
		if (($f_pat["ptphy1"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptphy1"])))
			return true;
		if (($f_pat["ptphy2"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptphy2"])))
			return true;
		if (($f_pat["ptphy3"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptphy3"])))
			return true;
		if (($f_pat["ptdoc"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptdoc"])))
			return true;

	    	// Default to false
		return false;
	} // end function freemed::check_access_for_patient

	// Function: freemed::config_value
	//
	//	Retrieves a configuration value from FreeMED's centralized
	//	configuration database table.
	//
	// Parameters:
	//
	//	$key - The name of the configuration value desired.
	//
	// Returns:
	//
	//	$value - The value of the configuration key, or NULL if the
	//	key is not found.
	//
	public function config_value ($config_var) {
		static $_config;
	 
 		// Set to cache values
 		if (!isset($_config)) {
			$query = $GLOBALS['sql']->queryAll("SELECT * FROM config");
	
			// If the table doesn't exist, skip out
			if (!count($query)) { return false; }
	
			// Loop through results
			foreach ($query AS $r) {
				$_config[stripslashes($r['c_option'])] = stripslashes($r['c_value']);
			} // end of looping through results
		} // end of caching
	
		// Return from cache
		return $_config["$config_var"];
	} // end function freemed::config_value

	// Function: freemed::config_user_value
	//
	//	Check configuration value against user database then
	//	system configuration database.
	//
	// Parameters:
	//
	//	$key - The name of the configuration value desired.
	//
	// Returns:
	//
	//	$value - The value of the configuration key, or NULL if the
	//	key is not found.
	//
	// See Also:
	//	<freemed::config_value>
	//
	public function config_user_value ( $key ) {
		static $_cache;

		if (!isset($_cache)) {
			$u = CreateObject('org.freemedsoftware.core.User');
			$_cache = $u->manage_config;
		}

		if (!empty($_cache[$key])) {
			// Use user value
			return $_cache[$key];
		} else {
			// Default to system-wide setting
			return freemed::config_value($key);
		}
	} // end function freemed::config_user_value

	// Function: freemed::connect
	//
	//	Master function to run authentication routines for the
	//	current used. This method should be called at the beginning
	//	of every standalone FreeMED script when dealing with standard
	//	session based authentication.
	//
	public function connect () {
		$a = CreateObject('org.freemedsoftware.core.Authentication', AUTHENTICATION_TYPE);

		$v = $a->VerifyAuthentication();

		// Verify
		if (!$v) {
			$a->RequestNewAuthentication();
		} // end if connected loop

		return true;
	} // end function freemed::connect

	// Function: freemed::dates_between
	//
	//	Determine dates between two YYYY-MM-DD dates.
	//
	// Parameters:
	//
	//	$start - Starting date in YYYY-MM-DD
	//
	//	$end - Ending date in YYYY-MM-DD
	//
	// Returns:
	//
	//	Array of dates in YYYY-MM-DD
	//
	function dates_between ( $start, $end ) {
		$_start = explode('-', $start);
		$_end   = explode('-', $end);
		$ts_start = mktime(0, 0, 0, $_start[1], $_start[2], $_start[0]);
		$ts_end   = mktime(0, 0, 0, $_end[1],   $_end[2],   $_end[0]);
		$ts = $ts_start;
		while ( $ts <= $ts_end ) {
			$out[] = date('Y-m-d', $ts);
			// Push this up by a day's worth of seconds
			$ts += 86400;
		} // end while
		return $out;
	} // end function freemed::dates_between
	
	// Function: freemed::get_link_field
	//
	//	Return a single field from a particular database table
	//	from its "id" field.
	//
	// Parameters:
	//
	//	$id - Value of the id field requested.
	//
	//	$table - Name of the FreeMED database table.
	//
	//	$field - Name of the field in the database table.
	//
	// Returns:
	//
	//	$val - Scalar value of the database table field.
	//
	function get_link_field($id, $table, $field="id") {
		// Die if no table was passed
		if (empty($table)) {
			trigger_error ("freemed::get_link_field: no table provided", E_USER_ERROR);
		}

		// Retrieve the entire record
		$this_array = $GLOBALS['sql']->get_link( $table, $id );

		// TODO: Get this to automatically deserialize serialized
		// data so that we can transparently get arrays. Probably
		// would break some phenomenal amount of code.

		// Return just the key asked for
		return $this_array["$field"];
	} // end function freemed::get_link_field

	// Function: freemed::handler_breakpoint
	//
	//	Set breakpoint for handlers for a certain function
	//
	// Parameters:
	//
	//	$name - Name of handler (example: MainMenuNotify)
	//
	//	$params - Array of parameters to be passed to the
	//	associated handlers. None are passed by default.
	//
	function handler_breakpoint ( $name, $params = NULL ) {
		$handlers = freemed::module_handler($name);
		if (is_array($handlers)) {
			foreach ($handlers AS $class => $handler) {
				T_textdomain(strtolower($class));
				if ($params != NULL) {
					$reply[] = module_function ($class, $handler, $params);
				} else {
					$reply[] = module_function ($class, $handler);
				}
			}
			return $reply;
		}
		return false;
	} // end function freemed::handler_breakpoint

	// Function: freemed::image_filename
	//
	//	Resolves a stored document's full path based on the qualifiers
	//	presented.
	//
	// Parameters:
	//
	//	$patient - Patient identifier
	//
	//	$record - Record identifier of the "images" table
	//
	//	$type - File type (usually "djvu")
	//
	//	$date_store - (optional) Boolean, whether or not
	//	the relative pathname will be prepended (usually "data/store/").
	//
	// Returns:
	//
	//	The relative path and file name of the image.
	//
	public function image_filename($patient, $record, $type, $data_store = true) {
		$m = md5($patient);
		return ($data_store ? 'data/store/' : '' ).
			$m[0].$m[1].'/'.
			$m[2].$m[3].'/'.
			$m[4].$m[5].'/'.
			$m[6].$m[7].'/'.
			substr($m, -(strlen($m)-8)).
			'/'.$record.'.'.$type;
	} // end method freemed::image_filename

	// Function: freemed::module_check
	//
	//	Determines whether a module is installed in the system,
	//	and optionally whether it is above a certain minimum
	//	versioning number.
	//
	// Parameters:
	//
	//	$uid - Unique ID of the module
	//
	//	$minimum_version (optional) - The minimum allowable version
	//	of the specified module. If this is not specified, any
	//	version will return true.
	//
	// Returns:
	//
	//	$installed - Boolean, whether the module is installed
	//
	function module_check ($uid, $minimum_version="0.01") {
		static $_config;

		// cache all modules  
		if (!is_array($_config)) {
			unset ($_config);
			$query = ModuleIndex::LoadIndex ( );
			foreach ( $query AS $r ) {
				$_config[$r['module_uid']] = $r['module_version'];
			} // end of while results
		} // end caching modules config
	
		// check in cache for version > minimum_version
		return ( version_compare( $_config["$uid"], $minimum_version ) >= 0 );
	} // end function freemed::module_check

	// Function: freemed::module_check_acl
	//
	//	Check to see if module is allowable via ACLs
	//
	// Parameters:
	//
	//	$module - Module class name
	//
	//	$permission - Permission type (view, add, mod, et cetera)
	//
	// Returns:
	//
	//	Boolean value, true or false
	//
	function module_check_acl ( $module, $permission='' ) {
		// Get meta value for acl
		$m_acl = freemed::module_get_meta ( $module, 'acl' );
		if (!is_array($m_acl)) {
			// By default if there are no restrictions, allow
			return true;
		} else {
			// Check each individual ACL specified, if any work, ok
			foreach ( $m_acl AS $__grbge => $v ) {
				if (!$permission) {
					switch ($v) {
						case 'bill': $p = 'menu'; break;
						default: $p = 'view'; break;
					}
				} else {
					$p = $permission;
				}
				if (freemed::acl($v, $p)) { return true; }
			} // end foreach m_acl

			// If nothing passes, we fail
			return false;
		} // end if not array
	} // end function module_check_acl

	// Function: freemed::module_get_value
	//
	//	Gets a cached module value (such as "MODULE_NAME", etc)
	//	from the module cache.
	//
	// Parameters:
	//
	//	$module - Name of the module
	//
	//	$key - Variable name in question
	//
	// Returns:
	//
	//	$val - Value of the variable name in question 
	//
	// See Also:
	//	<freemed::module_get_meta>
	//
	function module_get_value ($module, $key) {
		// Get module list object
		$module_list = freemed::module_cache();

		foreach ($module_list AS $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v[$key];
			}
		}

		// If all else fails, return false
		return false;
	} // end function freemed::module_get_value

	// Function: freemed::module_get_meta
	//
	//	Gets cached metainformation for the specified module in
	//	the module cache. Acts as a wrapper for
	//	<freemed::module_get_value>.
	//
	// Parameters:
	//
	//	$module - Name of the module
	//
	//	$key - Hash index of the metainformation in question
	//
	// Returns:
	//
	//	$val - Value of the metainformation in question 
	//
	// See Also:
	//	<freemed::module_get_value>
	//
	function module_get_meta ($module, $key) {
		$module_list = freemed::module_cache();

		foreach ($module_list AS $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['META_INFORMATION'][$key];
			}
		}

		// If all else fails, return false
		return false;
	} // end function freemed::module_get_meta

	// Function: freemed::module_cache
	//
	//	Provides global access to an array of hashes containing
	//	cached module information.
	//
	// Returns:
	//
	//	$cache - An object (org.freemedsoftware.core.ModuleIndex) containing the
	//	cached module information.
	//
	function module_cache () {
		static $cache;
		if (! isset($cache) ) {
			$cache = $GLOBALS['sql']->queryAll( "SELECT * FROM modules" );
		}
		return $cache;
	} // end function freemed::module_cache

	// Function: freemed::user_cache
	//
	//	Provides global access to a user object for the current user.
	//
	// Returns:
	//
	//	$cache - An object (org.freemedsoftware.core.User) containing the
	//	current user information.
	//
	function user_cache ( ) {
		static $cache;
		if (! isset($cache) ) {
			$cache = CreateObject( 'org.freemedsoftware.core.User' );
		}
		return $cache;
	} // end function freemed::user_cache

	// Function: freemed::module_handler
	//
	//	Returns the list of modules associated with a certain handler.
	//
	// Parameters:
	//
	//	$handler - Scalar name of the handler. This is case sensitive.
	//
	// Returns:
	//
	//	$modules - Array of modules which are associated with the
	//	specified handler. These all will be in lowercase, so
	//	remember to use strtolower().
	//
	public function module_handler ( $handler ) {
		static $_cache;

		if (! isset( $_cache[$handler] ) ) {
			$_cache[$handler] = $GLOBALS['sql']->queryCol( "SELECT LOWER( module_class ) FROM modules WHERE FIND_IN_SET( '".addslashes($handler)."', module_handlers )" );
		}

		// Return composite
		return $_cache[$handler];
	} // end function freemed::module_handler

	// Function: freemed::module_lookup
	//
	//	Lookup the module name as needed by FreeMED's module calls,
	//	but by the class name of the module.
	//
	// Parameters:
	//
	//	$class - Class name of the module
	//
	// Returns:
	//
	//	$module - MODULE_NAME of the specified module.
	//
	function module_lookup ($module) {
		// Get module list object
		$module_list = freemed::module_cache();

		// Use protected __freemed array to get module name
		foreach ($module_list AS $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['MODULE_NAME'];
			}
		}

		// If all else fails, return false
		return false;
	} // end function freemed::module_lookup

	// Function: freemed::module_register
	//
	//	Registers module or newer version of module in FreeMED's
	//	global module registry.
	//
	// Parameters:
	//
	//	$uid - Unique ID of module. ($this->MODULE_UID for any module)
	//
	//	$version - Version of module to register.
	//
	function module_register ( $uid, $version ) {
		// Perform update query
		$query = $GLOBALS['sql']->query(
			$GLOBALS['sql']->update_query(
				'module',
				array (
					'module_version' => $version
				),
				array (
					'module_uid' => $uid
				)
			)
		);
		return true;
	} // end function freemed::module_register

	// Function: freemed::module_tables
	//
	//	Get list of tables for modules.
	//
	// Returns:
	//
	//	i18n'd associative array
	//
	function module_tables ( ) {
		$cache = freemed::module_cache();
		foreach ($cache AS $v) {
			if ($t = $v['META_INFORMATION']['table_name']) {
				T_textdomain(strtolower($v['MODULE_CLASS']));
				$r[__($v['MODULE_NAME'])] = $t;
			}
		}
		ksort($r);
		return $r;
	} // end function freemed::module_tables

	// Function: freemed::module_to_table
	//
	//	Get table name from module name.
	//
	// Parameters:
	//
	//	$module - Module name (class name)
	//
	// Returns:
	//
	//	SQL table name
	//
	function module_to_table ( $module ) {
		static $lookup;
		$cache = freemed::module_cache();
		if (!$lookup) {
			foreach ($cache AS $v) {
				if ($t = $v['META_INFORMATION']['table_name']) {
					$lookup[strtolower($v['MODULE_CLASS'])] = $t;
				}
			}
		}
		return $lookup[strtolower($module)];
	} // end function freemed::module_to_table

	// Function: freemed::module_version
	//
	//	Get the current version number of a particular module in
	//	FreeMED from the module database table.
	//
	// Parameters:
	//
	//	$module - Name of module (must be resolved using
	//	freemed::module_lookup, or by using MODULE_NAME).
	//
	function module_version ( $module ) {
		static $cache;

		// cache all modules  
		if (!is_array($cache)) {
			$mcache = freemed::module_cache ( );
			foreach ( $mcache AS $r ) {
				$_config[$r['module_name']] = $r['module_version'];
			} // end of while results
		} // end caching modules config

		// check in cache for version
		return $cache["$module"];
	} // end function freemed::module_version

	// Function: freemed::query_to_array
	//
	//	Dumps the output of an SQL query to a multidimentional
	//	hashed array.
	//
	// Parameters:
	//
	//	$query - Text SQL query
	//
	//	$single_dimension - (optional) Reduce to single array, using
	//	k and v table columns as key and value. Defaults to false.
	//
	// Returns:
	//
	//	Multidimentional hashed array.
	//
	function query_to_array ( $query, $single_dimension=false ) {
		unset ($this_array);

		$result = $GLOBALS['sql']->queryAll($query);
		if (! $single_dimension ) { return $result; }

		$index = 0;
		foreach ( $result AS $r ) {
			foreach ( $r AS $k => $v ) {
				$this_index = $r['id'] ? $r['id'] : $index;
				$this_array[$this_index][(stripslashes($k))] = stripslashes($v);
			}
			$index++;
		}

		// Decide if we hash to associative array
		if ($single_dimension) {
			foreach ($this_array AS $k => $v) {
				$result_array[$v['k']] = $v['v'];
			}
			return $result_array;
		} else {
			if ($index == 1) {
				return $this_array[0];
			} else {
				return $this_array;
			}
		}
	} // end function freemed::query_to_array

	// Function: freemed::race_picklist
	//
	//	Create HL7 v2.3.1 compliant race widget (table 0005)
	//
	// Returns:
	//
	//	Hash of possible options for race widget
	//
	function race_picklist ( ) {
		// HL7 v2.3.1 compliant race widget (table 0005)
		return array (
			__("unknown race") => '7',
			__("Hispanic, white") => '1',
			__("Hispanic, black") => '2',
			__("American Indian or Alaska Native") => '3',
			__("Black, not of Hispanic origin") => '4',
			__("Asian or Pacific Islander") => '5',
			__("White, not of Hispanic origin") => '6'
		);
	} // end function freemed::race_picklist

	// Function freemed::religion_picklist
	//
	//	Create HL7 v2.3.1 compliant religion widget (table 0006)
	//
	// Returns:
	//
	//	Hash of possible options for religion widget.
	//
	function religion_picklist ( ) {
		// HL7 v2.3.1 compliant race widget (table 0006)
		return array (
			"---" => '',
			__("Catholic") => '0',
			__("Jewish") => '1',
			__("Eastern Orthodox") => '2',
			__("Baptist") => '3',
			__("Methodist") => '4',
			__("Lutheran") => '5',
			__("Presbyterian") => '6',
			__("United Church of God") => '7',
			__("Episcopalian") => '8',
			__("Adventist") => '9',
			__("Assembly of God") => '10',
			__("Brethren") => '11',
			__("Christian Scientist") => '12',
			__("Church of Christ") => '13',
			__("Church of God") => '14',
			__("Disciples of Christ") => '15',
			__("Evangelical Covenant") => '16',
			__("Friends") => '17',
			__("Jehovah's Witness") => '18',
			__("Latter-Day Saints") => '19',
			__("Islam") => '20',
			__("Nazarene") => '21',
			__("Other") => '22',
			__("Pentecostal") => '23',
			__("Protestant, Other") => '24',
			__("Protestant, No Denomenation") => '25',
			__("Reformed") => '26',
			__("Salvation Army") => '27',
			__("Unitarian; Universalist") => '28',
			__("Unknown/No preference") => '29',
			__("Native American") => '30',
			__("Buddhist") => '31'
		);
	} // end function freemed::religion_widget

	// Function: freemed::secure_filename
	//
	//	Remove potentially hazardous characters from filenames
	//
	// Parameters:
	//
	//	$original - Original filename
	//
	// Returns:
	//
	//	$sanitized - Sanitized filename
	//
	function secure_filename ( $filename ) {
		// Items to remove
		$secure_these = array (
			"\\",
			".",
			"/",
			"|"
		);

		// Pass to internal variable
		$this_filename = $filename;

		// Perform replacements
		foreach ( $secure_these AS $drek => $secure_var ) {
			$this_filename = str_replace (
				"\$".$secure_var,
				"",
				$this_filename
			);
		}

		// Return secured filename
		return $this_filename;
	} // end function freemed::secure_filename

	// Function: freemed::sql2date
	//
	//	Convert an SQL timestamp (such as MySQL timestamps) into
	//	a nice-looking date string.
	//
	// Parameters:
	//
	//	$date - SQL timestamp string
	//
	// Returns:
	//
	//	Formatted date/time string
	//
	function sql2date ( $date ) {
		if (substr($date, 4, 1) == '-') {
			// Handle MySQL 4.1+ timestamps
			$y = substr($date, 0, 4);
			$m = substr($date, 5, 2);
			$d = substr($date, 8, 2);
			$hour = substr($date, 11, 2);
			$min  = substr($date, 14, 2);
			$sec  = substr($date, 17, 2);
		} else {
			// Handle MySQL 4.0 and before timestamps
			$y = substr($date, 0, 4);
			$m = substr($date, 4, 2);
			$d = substr($date, 6, 2);
			$hour = substr($date, 8, 2);
			$min  = substr($date, 10, 2);
			$sec  = substr($date, 12, 2);
		}
		$ts = mktime ( $hour, $min, $sec, $m, $d, $y );
		return date('m/d/Y H:i', $ts);
	} // end function freemed::sql2date

	// Function: freemed::store_image
	//
	//	Stores posted file in scanned document image store.
	//
	// Parameters:
	//
	//	$patient_id - Patient identifier from the patient table.
	//	Do not pass a patient object.
	//
	//	$varname - The variable name describing the file that was
	//	posted using the HTTP POST method.
	//
	//	$type - (optional) Record number of the identifying record
	//	or array ( type, id ) if from another table.
	//
	//	$encoding - (optional) Type of DjVu encoding. Currently
	//	'cjb2' and 'c44' encodings are supported.
	//
	// Returns:
	//
	//	Name of file if successful.
	//
	function store_image ( $patient_id=0, $varname, $type=0, $encoding='cjb2' ) {
		global ${$varname};

		// Check for valid patient id
		if ($patient_id < 1) return false;

		// Determine extension
		$file_parts = explode (".", $_FILES[$varname]["name"]);
		$ext = $file_parts[count($file_parts)-1];

		// If there is no extension, die
		if (strlen($ext) < 3) { return false; }

		// Get temporary name
		$image = $_FILES[$varname]["tmp_name"];

		// If temp name doesn't exist, return false
		if (empty($image)) return false;

		if (is_array($type)) {
			$id = $type[1];
			$ext = 'id.djvu';
		} else {
			$id = $type;
			$ext = 'djvu';
		}

		// Create proper path
		$mkdir_command = 'mkdir -p '.PHYSICAL_LOCATION.'/'.
			dirname(
				freemed::image_filename(
					$patient_id,
					$id,
					$ext
				)
			);
		//print "mkdir_command = $mkdir_command<br/>\n";
		$mkdir_output = `$mkdir_command`;
		//print $mkdir_output."<br/>\n";

		// Process depending on 
		switch (strtolower($ext)) {
			/*
			case "jpg":
			case "jpeg":
				// Simple JPEG handler: copy
				$name = freemed::image_filename(
					$patient_id,
					$id,
					$ext
				);
				copy ($image, "./".$name);
				return $name;
				break; // end handle JPEGs
			*/

			default:
				// More complex: use imagemagick
				$name = freemed::image_filename(
					$patient_id,
					$id,
					$ext
				);
				// Convert to PBM
				$command = "/usr/bin/convert ".
					freemed::secure_filename($image).
					" ".PHYSICAL_LOCATION."/".
					freemed::image_filename(
						$patient_id,
						$id,
						$ext.'.'.
						( $encoding=='c44' ?
						'jpg' : 'pbm' )
					);
				//print "convert command = ".$command."<br/>\n";
				$output = `$command`;
				//print "convert output = ".$output."<br/>\n";

				// Convert to DJVU
				switch ($encoding) {
					case 'c44':
						$ee = '/usr/bin/c44';
						break;
					case 'cjb2':
					default:
						$ee = '/usr/bin/cjb2';
						break;
				}
				$command = $ee." ".
					PHYSICAL_LOCATION."/".
					freemed::image_filename(
						$patient_id,
						$id,
						$ext.'.'.
						( $encoding=='c44' ?
						'jpg' : 'pbm' )
					)." ".
					PHYSICAL_LOCATION."/".
					freemed::image_filename(
						$patient_id,
						$id,
						$ext
					);
				//print "command = $command<br/>\n";
				//print "<br/>".exec ($command)."<br/>\n";
				$output = `$command`;
				//print "<br/>".`$command`."<br/>\n";

				// Remove PBM
				unlink(PHYSICAL_LOCATION.'/'.
					freemed::image_filename(
						$patient_id,
						$id,
						$ext.'.'.
						( $encoding=='c44' ?
						'jpg' : 'pbm' )
					)
				);
				return $name;
				break; // end handle others
		} // end checking by extension
	} // end function freemed::store_image

	// Function: freemed::lock_override
	//
	//	Determine if the current user has the ability to "override"
	//	the locking of a record.
	//
	// Returns:
	//
	//	Boolean value, whether or not the user has override
	//	permissions.
	//
	function lock_override () {
		$this_user = freemed::user_cache();

		$a = explode(',', freemed::config_value('lock_override'));
		foreach ($a as $u) {
			if ($u == $this_user->user_number) { return true; }
		}
		
		return false;
	} // end function lock_override

	// Function: freemed::log_object
	//
	//	Get log object with caching.
	//
	// Returns:
	//
	//	org.freemedsoftware.core.Log object
	//
	function log_object ( ) {
		static $_cache;
		if (!isset($_cache)) { $_cache = CreateObject('org.freemedsoftware.core.Log'); }
		return $_cache;
	} // end function freemed::log_object

	// Function: freemed::phone_display
	//
	//	Displays phone number in system format.
	//
	// Parameters:
	//
	//	$phone - Phone number raw
	//
	// Returns:
	//
	//	Formatted phone number for display
	//
	function phone_display ( $phone ) {
		if (strlen($phone) < 7) { return __("NONE"); }
		switch (freemed::config_value('phofmt')) {
			case "usa":
				return '('.substr($phone, 0, 3).') '.
					substr($phone, 3, 3).'-'.
					substr($phone, 6, 4).
					( strlen($phone)>10 ? ' '.substr($phone, 10, 4) : '' );
				break;
			case "fr":
				return '+'.substr($w, 0, 2).
					substr($w, 2, 2). 
					substr($w, 4, 2). 
					substr($w, 6, 2). 
					substr($w, 8, 2); 
				break;
			case "unformatted":
			default:
				return $phone;
				break;
		} // end formatting case statement
	} // end function phone_display

} // end namespace/class freemed

//------------------ NON NAMESPACE FUNCTIONS ---------------------

//---------------------------------------------------------------------------
// Time and Date Functions
//---------------------------------------------------------------------------

// Function: freemed_get_date_next
//
//	Get next valid SQL format date (YYYY-MM-DD)
//
// Parameters:
//
//	$date - Starting date
//
// Returns:
//
//	Next date.
//
function freemed_get_date_next ($cur_dt) {
	global $cur_date;

	$y = substr ($cur_dt, 0, 4); // get year
	$m = substr ($cur_dt, 5, 2); // get month
	$d = substr ($cur_dt, 8, 2); // get date

	// check for validity of given date... if not, cur_date
	if (!checkdate($m, $d, $y)) { 
		$y = substr ($cur_date, 0, 4);
		$m = substr ($cur_date, 5, 2);
		$d = substr ($cur_date, 8, 2); 
	}

	if (!checkdate($m, $d + 1, $y)) { // roll day?
		if (!checkdate($m + 1, 1, $y)) { // roll month?
			// roll year
			return date ("Y-m-d", mktime (0,0,0,1,1,$y+1));
		} else {
			// roll month
			return date ("Y-m-d", mktime (0,0,0,$m+1,1,$y));
		} // end checking roll month?
	} else { // checking roll day
		// roll day
		return date ("Y-m-d", mktime (0,0,0,$m,$d+1,$y));
	} // end checking roll day
} // end function freemed_get_date_next

// Function: freemed_get_date_prev
//
//	Get previous date in SQL format (YYYY-MM-DD)
//
// Parameters:
//
//	$date - Starting date
//
// Returns:
//
//	Previous date.
//
function freemed_get_date_prev ($cur_dt) {
	$cur_date = date ("Y-m-d");

	$y = substr ($cur_dt, 0, 4); // year
	$m = substr ($cur_dt, 5, 2); // month
	$d = substr ($cur_dt, 8, 2); // day 

	if (!checkdate ($m, $d, $y)) {
		$y = substr ($cur_date, 0, 4);
		$m = substr ($cur_date, 5, 2);
		$d = substr ($cur_date, 8, 2);
	} // if not right, use current date

	if (($d==1) AND ($m>1)) { // if first day...
		$d = 31; $m--; // roll back
		  // while day too high, decrease
		while (!checkdate ($m, $d, $y)) $d--;
		return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
	} else if (($d==1) AND ($m==1)) { 
		// roll back year
		$m=12; $y--; $d=31;
		return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
	} else { // checking for day
		// roll back day
		$d--;
		return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
	} // end checking for first day
} // end function freemed_get_date_prev

// Function: fm_date_print
//
//	Create a nicely formatted date display
//
// Parameters:
//
//	$date - SQL formated date
//
//	$show_text_days - (optional) Whether or not to show the day names
//	as text names. Defaults to false.
//
// Returns:
//
//	Formatted date display.
//
function fm_date_print ($actualdate, $show_text_days=false) {
	$y  = substr ($actualdate, 0, 4);        // extract year
	$m  = substr ($actualdate, 5, 2);        // extract month
	$d  = substr ($actualdate, 8, 2);        // extract day
	$ts = mktime (0, 0, 0, $m, $d, $y<1970 ? 2000 : $y); // generate timestamp (fake year)

	$lang_months = array (
		'',
		__("Jan"),
		__("Feb"),
		__("Mar"),
		__("Apr"),
		__("May"),
		__("Jun"),
		__("Jul"),
		__("Aug"),
		__("Sep"),
		__("Oct"),
		__("Nov"),
		__("Dec")
	);

	// Return depending on configuration format
	switch (freemed::config_value("dtfmt")) {
		case "mdy":
			if ($show_text_days and ($y > 1969)) {
				return date("D F d, ", $ts).$y;
			} else {
				return date("F d, ", $ts).$y;
			}
			break;
		case "dmy":
			if ($show_text_days and ($y > 1969)) {
				return $d." ".$lang_months[0+$m].", ".$y;
			} else {
				return date("d M, ", $ts).$y;
			}
			break;
		case "ymd": default:
			if ($show_text_days and ($y > 1969)) {
				return date("D", $ts)." ".$y.date("-m-d", $ts);
			} else {
				return $y.date("-m-d", $ts);
			}
			break; 
	} // end switch
} // end function fm_date_print

function fm_phone_assemble ($phonevarname="", $array_index=-1) {
  $buffer = ""; // we use buffered output for notebook class!
  if ($phonevarname=="") return ""; // return nothing if no variable is given
  global ${$phonevarname}, ${$phonevarname."_1"},
    ${$phonevarname."_2"}, ${$phonevarname."_3"}, 
    ${$phonevarname."_4"}, ${$phonevarname."_5"};
  if ($array_index == -1) {
    $w  = ${$phonevarname};    // whole number
    $p1 = ${$phonevarname."_1"};    // part 1
    $p2 = ${$phonevarname."_2"};    // part 2
    $p3 = ${$phonevarname."_3"};    // part 3
    $p4 = ${$phonevarname."_4"};    // part 4
    $p5 = ${$phonevarname."_5"};    // part 5
  } else {
    $w  = ${$phonevarname}[$array_index];  // whole number
    $p1 = ${$phonevarname."_1"}[$array_index];  // part 1
    $p2 = ${$phonevarname."_2"}[$array_index];  // part 2
    $p3 = ${$phonevarname."_3"}[$array_index];  // part 3
    $p4 = ${$phonevarname."_4"}[$array_index];  // part 4
    $p5 = ${$phonevarname."_5"}[$array_index];  // part 5
  } // end checking for array index

  // Check for case where parts aren't set, but whole is
  $phofmt = freemed::config_value('phofmt');
  if (${$phonevarname} and !${$phonevarname.'_1'} and ($phofmt=='usa' or $phofmt=='fr')) {
    return $w;
  }
  
  switch (freemed::config_value("phofmt")) {
    case "usa":
     return $p1.$p2.$p3.$p4;        // assemble number and put it all together
    case "fr":
     return $p1.$p2.$p3.$p4.$p5;    // assemble number and put it all together
    case "unformatted":
    default:
     return $w;                     // return whole number...
  } // end switch for formatting
} // end function fm_phone_assemble

//---------------------------------------------------------------------------
// Variable Manipulation Functions
//---------------------------------------------------------------------------

function fm_split_into_array ($original_string) {
	// If there is nothing to split, return nothing
	if (empty($original_string)) return "";

	// Split and return
	return explode (",", $original_string);
} // end function fm_split_into_array

function fm_value_in_array ($cur_array, $value) {
	// If there is no array, it obviously does not have the value
	if (count ($cur_array) < 0) return false;

	// Not sure about this...
	//if (!is_array ($cur_array)) return ($cur_array == $value);

	// loop through array
	for ($c=0;$c<count($cur_array);$c++)
		if ($cur_array[$c]==$value) // if there is a match...
			return true; // return true.

	// Return false if we didn't find it
	return false;
} // end function fm_split_into_array

function fm_value_in_string ($cur_string, $value) {
	// Check for "," separator indicating hash'd array
	if ( ! (strpos ($cur_string, ",") === false) ) {
		// Split it out...
		$this_array = fm_split_into_array ($cur_string);
		// ... then use fm_value_in_array to return the value
		return fm_value_in_array ($this_array, $value);
	} // end checking for ","

	// Otherwise do a simple substring match check
	//if (strstr($cur_string,$value) != "") return true;
	if (trim($cur_string) == trim($value)) { return true; }

	// If it hasn't been found, return false
	return false;
} // end function fm_value_in_string

//---------------------------------------------------------------------------
// Patient Coverage Functions
//---------------------------------------------------------------------------

function fm_get_active_coverage ($ptid=0) {
	// Initialize results
	$result = 0;

	// If no patient ID was given, return 0
	if ($ptid == 0) return 0;

	// Form and perform query
	$query = "SELECT id FROM coverage WHERE ".
		"covpatient='".addslashes($ptid)."' ".
		"AND covstatus='".ACTIVE."'";
	$ins_id = $GLOBALS['sql']->queryAll( $query );

	// If nothing was returned, return 0
	if (!count($ins_id)) { return 0; }

	// Return the array of coverages
        return $ins_id;
} // end function fm_get_active_coverages

function fm_verify_patient_coverage($ptid=0, $coveragetype=PRIMARY) {
	// Initialize result
	$result = 0;

	// Check for ptid, otherwise return 0
	if ($ptid == 0) return 0;
	
	// default coveragetype is primary	
	$query = "SELECT id FROM coverage WHERE ".
		"covpatient='".addslashes($ptid)."' AND ".
		"covstatus='".ACTIVE."' AND ".
		"covtype='".addslashes($coveragetype)."'";
	$result = $GLOBALS['sql']->queryOne( $query );

	// Return the id
	return $result;
} // end function fm_verify_patient_coverage

//---------------------------------------------------------------------------
// Time-related Functions
//---------------------------------------------------------------------------

// Function: page_push
//
//	Push page onto global history stack.
//
function page_push () {
	global $page_title;
	$page_history = $_SESSION['page_history'];

	// Import it if it exists
	if (isset($_SESSION['page_history'])) {
		// Import
		$_page_history = $_SESSION['page_history'];

		// Check to see if this is the last item on the list...
		// ... kick out without adding.
		if (basename($_page_history[(count($_page_history))]) ==
			basename($_SERVER['PHP_SELF'])) return true;
	} // end checking for existing history

	// Add to the list of pages
	$_page_history["$page_title"] = basename($_SERVER['PHP_SELF'])."?".
		"module=".urlencode($_REQUEST['module'])."&".
		"action=".urlencode($_REQUEST['action'])."&".
		"type=".urlencode($_REQUEST['type']);

	// Reimport into SESSION
	$_SESSION['page_history'] = $_page_history;
} // end function page_push

// Function: page_pop
//
//	Pop off page from global history stack.
//
function page_pop () {
	// Return false if there is nothing in the list
	if (!isset($_SESSION['page_history'])) return false;

	// Import page_history
	$_page_history = $_SESSION['page_history'];

	// Otherwise get the last one and return it ...
	$to_return = $_page_history[(count($page_history)-1)];
	$to_return_name = $_page_history[(count($page_history_name)-1)];

	// .. then remove it from the stack
	unset($_page_history[(count($_page_history)-1)]);
	unset($_page_history_name[(count($_page_history)-1)]);

	// Reimport into SESSION
	$_SESSION['page_history'] = $_page_history;
	$_SESSION['page_history_name'] = $_page_history_name;

	// And return value (access as list(x,y) = page_pop())
	return array ($to_return, $to_return_name);
} // end function page_pop

// Function: patient_push
//
//	Push patient onto global history stack.
//
function patient_push ($patient) {
	// Import it if it exists
	if (isset($_SESSION['patient_history'])) {
		// Import
		$patient_history = $_SESSION['patient_history'];

		// Clean out null entries... and rogue arrays
		foreach ($patient_history AS $k => $v) {
			if (!$v) unset($patient_history[$k]);
			if (is_array($v)) unset($patient_history[$k]);
		} // end foreach

		// Check to see if this is the last item on the list...
		// ... kick out without adding.
		if ($patient_history[(count($patient_history))] == $patient) {
			// Reimport due to cleaning
			$_SESSION['patient_history'] = $patient_history;

			// And we don't have to add it, exit with true
			return true;
		} // end checking if we just saw them...
	} // end checking for existing history

	// Add to the list of pages
	$patient_history[] = $patient;

	// Reimport into SESSION
	$_SESSION['patient_history'] = $patient_history;
} // end function patient_push

// Function: patient_history_list
//
//	Get global history list for patients
//
// Returns:
//
//	Array of patients in global history list.
//
function patient_history_list () {
	// Return false if there is nothing in the list
	if (!is_array($_SESSION['patient_history'])) return false;

	// Import patient_history
	$patient_history = $_SESSION['patient_history'];

	// Check for no patient history
	if (count($patient_history)<1) return false;

	// Create new empty array
	unset($history);

	// Loop through array
	foreach ($patient_history AS $k => $v) {
		// Kludge to get around strange PHP crashing error on
		// $v processing by checking if it's an array.
		if (!is_array($v)) {
			// Get patient information
			$this_patient = CreateObject('org.freemedsoftware.core.Patient', $v);
	
			// Form Lastname, Firstname, ID list item
			$key = $this_patient->fullName(true) . " (".$v.")";

			// Add to new array
			$history["$key"] = $v;
		}
	} // end foreach

	// Sort by alpha
	ksort($history);

	// Return generated array
	return array_reverse($history);
} // end function patient_history_list

// Function: page_history_list
//
//	Get global history list for pages
//
// Returns:
//
//	Array of pages in global history list.
//
function page_history_list () {
	// Return false if there is nothing in the list
	if (!is_array($_SESSION['page_history'])) return false;

	// Import patient_history
	$page_history = $_SESSION['page_history'];

	// Check for no patient history
	if (count($page_history)<1) return false;

	// Create new empty array
	unset($history);

	// Loop through array
	foreach ($page_history AS $k => $v) {
		if (!empty($k) and !empty($v) and !is_array($v)) {
			// Add to new array
			$history["$k"] = $v;
		}
	} // end foreach

	// Return generated array
	return array_reverse($history);
} // end function page_history_list

?>
