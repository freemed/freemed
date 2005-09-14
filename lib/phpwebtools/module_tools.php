<?php
	// $Id$
	// $Author$

// File: Module API

// ***************** FUNCTIONS FOR MANIPULATING MODULES ****************

unset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']); // make sure nothing is using this

// Function: check_module
//
//	Looks up a module in the global module cache
//
// Parameters:
//
//	$module_name - Name of the module in question
//
//	$version - (optional) Only return true if the module has a
//	higher version number than this value. Defaults to disabled.
//
// Returns:
//
//	Boolean, whether module is found or not.
//
function check_module ($module_name, $version = NULL) {
	if ($version == NULL) {
		// Check the cache
		return (resolve_module($module_name) != false);
	} else {
		// Look up the name of the class
		$actual_version = '0';
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $garbage => $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module_name)) {
				$actual_version = $v['MODULE_VERSION'];
			}
		}
		// Check for version as well
		return ( (resolve_module($module_name) != false) and
			version_check($actual_version, $version) );
	}
} // end function check_module

function execute_module ($module_name) {
	// check for module existing
	if (!($module_path = resolve_module($module_name))) {
		DIE("execute_module :: class \"$module_name\" doesn't exist");
	}

	// Load module (resolving path first, to fix lowercase issue with get_class())
	include_once(resolve_module($module_name));

	// Load module through object
	$this_module = new $module_name ();
	return $this_module->execute();
} // end function execute_module

// Function: register_module
//
//	Register a dynamic module with phpwebtools' module caching
//	functions.
//
// Parameters:
//
//	$module_name - Name of the class that is the module. All
//	module variables, including MODULE_FILE, must be set in
//	order for this to properly register a module.
//
function register_module ($module_name) {
	global $GLOBAL_CATEGORIES_VERSION;

	// check for module existing
	if (!class_exists($module_name))
		DIE("register_module :: class \"$module_name\" doesn't exist");

	// load module through object
	$this_module = new $module_name ();

	// move information into array
	$GLOBALS['__phpwebtools']['GLOBAL_MODULES'][] = array (
			// package information
		'PACKAGE_NAME' => $this_module->PACKAGE_NAME,
		'PACKAGE_VERSION' => $this_module->PACKAGE_VERSION,
			// subpackage/category information
		'CATEGORY_NAME' => $this_module->CATEGORY_NAME,
		'CATEGORY_VERSION' => $this_module->CATEGORY_VERSION,
			// module information
		'MODULE_NAME' => $this_module->MODULE_NAME,
		'MODULE_CLASS' => $module_name, // (like $this_module->MODULE_CLASS)
		'MODULE_VERSION' => $this_module->MODULE_VERSION,
		'MODULE_AUTHOR' => $this_module->MODULE_AUTHOR,
		'MODULE_DESCRIPTION' => $this_module->MODULE_DESCRIPTION,
		'MODULE_VENDOR' => $this_module->MODULE_VENDOR,
		'MODULE_HIDDEN' => $this_module->MODULE_HIDDEN,
			// file name information for non-standard class loading
		'MODULE_FILE' => $this_module->MODULE_FILE,
			// minimum version requirement information
		'PACKAGE_MINIMUM_VERSION' => $this_module->PACKAGE_MINIMUM_VERSION,
		'CATEGORY_MINIMUM_VERSION' => $this_module->CATEGORY_MINIMUM_VERSION,
			// icon, if there is one
		'ICON' => $this_module->ICON,

			// misc information to pass around
		'META_INFORMATION' => $this_module->META_INFORMATION
	);
	$GLOBAL_CATEGORIES_VERSION["$this_module->CATEGORY_NAME"] =
		$this_module->CATEGORY_VERSION;

	// be nice and return true
	return true;
} // end function register_module

// Function: resolve_module
//
//	Look up the name of a file associated with a module class
//
// Parameters:
//
//	$module_name - Name of the module class to be resolved. This
//	is case insensitive.
//
// Returns:
//
//	File name of the module.
//
function resolve_module ($module_name) {
	// Look up the name of the class
	foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $garbage => $v) {
		if (strtolower($v['MODULE_CLASS']) == strtolower($module_name)) {
			return $v['MODULE_FILE'];
		}
	}

	// If we can't find it, return false
	return false;
} // end function resolve_module

function setup_module ($module_name) {
	// check for module existing
	if (!class_exists($module_name))
		DIE("register_module :: class \"$module_name\" doesn't exist");

	// load module through object
	$this_module = new $module_name ();

	$this_module->setup();
} // end function setup_module

// Function: module_function
//
//	Execute an arbitrary method in a module
//
// Parameters:
//
//	$module_name - Name of the module class in question
//
//	$function - Name of the method to be executed
//
//	$params - (optional) Array of parameters to be passed to
//	the specified method. Defaults to none.
//
// Returns:
//
//	Mixed output, returning the results of the method call.
//
function module_function ($module_name, $function, $params = "") {
	// Include proper file
	include_once(resolve_module($module_name));

	// check for module existing
	if (!class_exists($module_name)) {
		DIE("module_function :: class \"$module_name\" doesn't exist");
	}

	// Load module through object
	$this_module = new $module_name ();

	// execute the function
	if (is_array($params)) {
		return call_user_method_array ( $function, $this_module,
			$params );
	} elseif ($params=="") {
		return call_user_method ( $function, $this_module );
	} else {
		return call_user_method_array ( $function, $this_module,
			array($params) );
	}
} // end function module_function

?>
