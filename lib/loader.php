<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
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

// File: Object Loader API
//
//	These functions should only be used internally in FreeMED
//	module and class handling routines.
//

// Function: CallMethod
//
//	Call a method in a designated namespace, automatically instantiating the
//	appropriate class.
//
// Parameters:
//
//	$namespace - FreeMED object namespace
//
//	$parameters - (optional) As many optional parameters as are required. These
//	should be passed as separate parameters, as the function will recomposite
//	then into an array.
//
// Returns:
//
//	Mixed.
//
function CallMethod ( $namespace ) {
	$argc = func_num_args ( );
	$argv = func_get_args ( );
	unset ( $argv[0] ); // get rid of $namespace parameter

	// Resolve object path properly
	$path = ResolveObjectPath ( $namespace );
	$class_name = ResolveClassName ( $namespace );
	$method = ResolveMethodName ( $namespace );
	if ( ! ereg ( '^[A-Za-z0-9_]+$', $method ) or empty ( $method ) ) {
		trigger_error( "CallMethod: invalid method '${method}' given", E_USER_ERROR );
	}

	if ( $argc < 1 ) {
		trigger_error( "CallMethod: no parameters given", E_USER_ERROR );
	}

	$obj = InstantiateClass( $path, $class_name );
	if ( $argc == 1 ) {
		return call_user_func_array ( array ( &$obj, $method ), array ( ) );
	} else {
		return call_user_func_array ( array ( &$obj, $method ), $argv );
	}
} // end function CallMethod

// Function: InstantiateClass
//
//	Instantiate class instance from SHM or new instance depending on
//	contents of SHM. Handles this per session.
//
// Parameters:
//
//	$path - Fully qualified path name to PHP class source.
//
//	$class_name - Resolved name of the class
//
// Returns:
//
//	Object instance.
//
function InstantiateClass( $path, $class_name ) {
	static $shared;
	$session_id = session_id( );

	// Check for global SHM_CACHE setting
	if ( ! defined('SHM_CACHE') or ! SHM_CACHE ) {
		syslog( LOG_DEBUG, "InstantiateClass : not caching {$class_name} for session {$session_id}" );
		include_once( $path );
		$x = new ${class_name};
		return $x;
	}

	if ( !isset( $shared ) ) {
		LoadObjectDependency( 'net.php.pear.System_SharedMemory' );
		$shared = & System_SharedMemory::factory();
	}

	include_once( $path );
	$x = $shared->get( "object-{$class_name}-{$session_id}" );
	if ( isset( $x ) ) {
		syslog( LOG_DEBUG, 'InstantiateClass : loaded object-'.$class_name.'-'.$session_id.' from SHM' );
		return $x;
	} else {
		$x = new ${class_name};
		syslog( LOG_DEBUG, 'InstantiateClass : stored object-'.$class_name.'-'.$session_id.' in SHM' );
		$shared->set( "object-{$class_name}-{$session_id}", $x );
		return $x;
	}
} // end method InstantiateClass

// Function: CreateObject
//
//	Instantiate a new object based on a namespace.
//
// Parameters:
//
//	$namespace - Class namespace.
//
//	$parameters - (optional) Variable arguments list.
//
// Returns:
//
//	Object
//
function CreateObject ( $namespace ) {
	$argc = func_num_args ( );
	$argv = func_get_args ( );
	unset ( $argv[0] ); // get rid of $namespace parameter

	// Resolve object path properly
	$path = ResolveObjectPath ( $namespace );
	$class_name = ResolveClassName ( $namespace );
	//print "DEBUG: $path / $class_name<br/>";

	if ( $argc < 1 ) {
		trigger_error( "CreateObject: no parameters given", E_USER_ERROR );
	} elseif ( $argc == 1 ) {
		return InstantiateClass( $path, $class_name );
	} else {
		include_once ( $path );
		//return call_user_func_array ( array ( $class_name, '__construct' ), $argv );
		// Use magic from http://www.php.net/manual/en/function.call-user-func-array.php#59926
		$reflection = new ReflectionClass ( $class_name );
		//print "DEBUG : $namespace ( argc = $argc ) <br/>\n"; print_r($argv); print "<br/>\n";
		return call_user_func_array ( array ( &$reflection, 'newInstance' ), $argv );
	}
} // end function CreateObject

// Function: LoadObjectDependency
//
//	Load PHP classes required by a file. This is necessary when it is not
//	possible to use <CreateObject>.
//
// Parameters:
//
//	$dependency - Object path required.
//
function LoadObjectDependency ( $dependency ) {
	$path = ResolveObjectPath ( $dependency );
	if ( file_exists ( $path ) ) { include_once ( $path ); }
} // end function LoadObjectDependency

// Function: ResolveClassName
//
//	Determine the name of a PHP class from a namespace.
//
// Parameters:
//
//	$object - Object namespace
//
// Returns:
//
//	Name of the class that requires instantiation.
//
function ResolveClassName ( $object ) {
	$parts = explode( '.', $object );
	return $parts[3];
} // end function ResolveClassName

// Function: ResolveMethodName
//
//	Determine the name of a PHP method from a namespace.
//
// Parameters:
//
//	$object - Object namespace
//
// Returns:
//
//	Name of the method that requires instantiation.
//
function ResolveMethodName ( $object ) {
	$parts = explode( '.', $object );
	return $parts[4];
} // end function ResolveMethodName

// Function: ResolveObjectPath
//
//	Determine the file path of a PHP class based on its namespace.
//
// Parameters:
//
//	$object - Object namespace
//
// Returns:
//
//	Path to PHP class file.
//	
function ResolveObjectPath ( $object ) {
	$base_path = dirname( dirname( __FILE__ ) );
	switch (true) {
		case substr( $object, 0, 24 ) == 'org.freemedsoftware.api.':
			$cname = str_replace ( 'org.freemedsoftware.api.', '', $object );
			$cname = eregi_replace( '\..+', '', $cname );
			return "${base_path}/lib/api/class.${cname}.php";
			break;

		case substr( $object, 0, 25 ) == 'org.freemedsoftware.core.':
			$cname = str_replace ( 'org.freemedsoftware.core.', '', $object );
			$cname = eregi_replace( '\..+', '', $cname );
			return "${base_path}/lib/core/class.${cname}.php";
			break;

		case substr( $object, 0, 27 ) == 'org.freemedsoftware.module.':
			$cname = str_replace ( 'org.freemedsoftware.module.', '', $object );
			$cname = eregi_replace( '\..+', '', $cname );
			$module_path = resolve_module( $cname );
			if (! $module_path ) {	
				trigger_error( "Could not resolve object path ${object}", E_USER_ERROR );
			}
			return "${base_path}/${module_path}";
			break;

		case substr( $object, 0, 20 ) == 'org.freemedsoftware.':
			$name = str_replace ( 'org.freemedsoftware.', '', $object );
			list ( $pname, $cname ) = explode ( '.', $name );
			$cname = eregi_replace( '\..+', '', $cname );
			if (!file_exists("${base_path}/lib/${pname}/.namespace")) {
				trigger_error("Object ${object} not valid.", E_USER_ERROR);
			}
			if (file_exists("${base_path}/lib/${pname}/class.${cname}.php")) {
				return "${base_path}/lib/${pname}/class.${cname}.php";
			} else {
				return "${base_path}/lib/${pname}/${cname}.class.php";
			}
			break;

		case substr( $object, 0, 13 ) == 'net.php.pear.':
			$name = str_replace ( 'net.php.pear.', '', $object );
			$name = eregi_replace( '\..+', '', $name );
			ini_set('include_path', ini_get('include_path').':'.dirname(dirname(__FILE__)).'/pear');
			$my_class = str_replace( '_', '/', $name );
			$path = dirname(__FILE__).'/pear/'.$my_class.'.php';
			return $path;
			break;

		default:
			trigger_error( "Could not resolve object path ${object}", E_USER_ERROR );
			break;
	}
} // end function ResolveObjectPath

?>
