<?php
	// $Id$
	// $Author$

// Class: PHP.module_list
//
//	Class that does the work of indexing and caching information
//	regarding directories of multiple modules.
//
// See Also: <PHP.module>
//
class module_list {

	// Method: constructor
	//
	// Parameters:
	//
	//	$package - String defining the name of the package
	//
	//	$_options - (optional) Associative array of options
	//	to be passed to the constructor.
	//	* cache_file - File to hold the cache information
	//	* directory - Directory location
	//	* display_hidden - Boolean, force display of hidden modules
	//	* recursive - Boolean, whether or not to recursively scan
	//	* suffix - File suffix filter
	//
	function module_list ($package, $_options) {
		// Figure out options
		if (!is_array($_options)) {
			$options = array ('directory' => $_options);
		} else {
			$options = $_options;
		}
		if (!isset($options['directory'])) {
			$options['directory'] = 'modules/'; 
		}
		if (!isset($options['suffix'])) { $options['suffix'] = ''; }
	
		// Check for display_hidden
		if ($options['display_hidden']) {
			$this->display_hidden = true;
		} else {
			$this->display_hidden = false;
		}

		// Derive module directory from the mask
		if ($options['directory']) {
			$this->modules_directory = $options['directory'];
		} else {
			$this->modules_directory = './modules';
		}

		if ($options['cache_file']) {
			$this->cache_file = $options['cache_file'];
		} else {
			$this->cache_file = $this->modules_directory.'/.modules_cache';
		}

		// Recursive directory spanning
		if ($options['recursive']) {
			$this->recursive = $options['recursive'];
		} else {
			$this->recursive = false;
		}
	
		// Form the extension
		$this->extension = ".module.php";
		if ($options['suffix'] != "") {
			$this->extension = $options['suffix']; 
			// check for leading period...
			if (substr($this->extension, 0, 1) != ".")
				$this->extension = "." . $this->extension;
		} // end checking for no prefix

		// Create cache object
		$cache = CreateObject('PHP.FileSerialize',
				$this->cache_file
				);

		// Check to see if the directory is cached
		if (!$this->cached($this->modules_directory)) {
			//print "not cached. looping through modules directory<BR>\n";
			// Farm the call out to a function so we can handle
			// recursive directory entries ...
			$this->_scan_directory($this->modules_directory, $this->recursive);

			// Now, store them in the cache
			//print "calling cache->write<BR>\n";
			$cache->write($GLOBALS['__phpwebtools']['GLOBAL_MODULES']);
		} else {
			//print "CACHED!?!<BR>\n";
			// Read from cache	
			$GLOBALS['__phpwebtools']['GLOBAL_MODULES'] = $cache->read();

			// TODO: Rebuild other indices
		} // end checking for cache
	} // end constructor module_list

	// Method: _scan_directory
	//
	//	Scans directory for modules
	//
	// Parameters:
	//
	//	$dir - Directory name
	//
	//	$recurse - (optional) Whether or not to recurse. Default is
	//	false.
	//
	function _scan_directory ( $dir, $recurse = false ) {
		if (! ($d = dir($dir)) ) {
			die(get_class($this)." :: could not open directory '".$dir."'");
		}

		// loop through directory entries
		while ($entry = $d->read()) {
			// Determine if it is a module (.module.php)
			if (strtolower(substr($entry,-(strlen($this->extension)))) == $this->extension) {
				// include the module (which registers it)
				//print "include ".( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry )."<br/>\n";
				include_once ( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry );
				// [ Create cache entries from register_module() ]
			} else { // if it *isn't* a module
				// If we recurse and it's a directory
				if ( is_dir ( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry ) and $recurse ) {
					// Make sure we can go here
					if ( ($entry != 'CVS') and (substr($entry, 0, 1) != '.') ) {
						//print "entering recurse on $entry<br/>\n";
						// Recurse into this directory
						$this->_scan_directory( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry, true);
					}
				}
			} // end determining if it is a module
		} // end looping through directory entries
	} // end method _scan_directory

	// Method: cached
	//
	//	Determine if the current directory is cached properly
	//
	// Parameters:
	//
	//	$dir_name - Directory name to check
	//
	// Returns:
	//
	//	Boolean, cached state of directory.
	//
	function cached ( $dir_name ) {
		// Check for a cache file not existing, if so, it's not cached
		if (!file_exists($this->cache_file)) { return false; }

		// Get directory modification date
		clearstatcache();
		$dir_modified = array_element(lstat($dir_name), 9);
		//print "dir_modified = $dir_modified<BR>\n";

		// Get cache last modication date
		clearstatcache();
		$cache_modified = array_element(lstat($this->cache_file), 9);
		//print "cache_modified = $cache_modified<BR>\n";

		// If the cache is older than the directory...
		if ($cache_modified < $dir_modified) {
			// .. the cache needs to be rebuilt
			return false;
		} else {
			// Or else, we have a cached copy
			return true;
		} // end checking cache time
	} // end function module_list->cached

	// Method: categories
	//
	//	Get list of categories.
	//
	// Returns:
	//
	//	Array of categories or NULL if none
	//
	function categories () {
		// clear categories variable
		unset ($categories);

		if ( !isset($GLOBALS['__phpwebtools']['GLOBAL_MODULES']) or !is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES']) )
			return true;
		
		reset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']);
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $el_garbage => $this_module) {
			// check to see if it is in this category
			if (!in_this($categories, $this_module['CATEGORY_NAME'])) {
				$categories[] = $this_module['CATEGORY_NAME'];
			}
		} // finish looping through GLOBAL_MODULES

		// send back null if it isn't an array
		if (!is_array($categories)) return NULL;

		// return the array
		sort ($categories);
		return $categories;
	} // end method categories

	// Method: check_for
	//
	//	Checks to see if a module exists in the loaded list
	//
	// Parameters:
	//
	//	$module_name - Module to check for
	//
	// Returns:
	//
	//	Boolean, whether module exists in the loaded list.
	//
	function check_for ($module_name) {
		// check for empties
		if (empty($module_name)) return false;

		// run through list of modules
		reset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']);
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $el_garbage => $this_module) {
			// if it is here, return
			if (strtolower($this_module['MODULE_CLASS']) == strtolower($module_name)) return true;
		} // finish looping through GLOBAL_MODULES

		// if not found, return false
		return $false;
	} // end function check_for

	function empty_category ($category, $category_version = 0) {
		if ( !isset($GLOBALS['__phpwebtools']['GLOBAL_MODULES']) or !is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES']) ) {
			return true;
		}
		
		reset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']);
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $el_garbage => $this_module) {
			// check to see if it is in this category
			if (($this_module['CATEGORY_NAME'] == $category) and
				($category_version >= $this_module['CATEGORY_MINIMUM_VERSION'])) {
				return false;
			} // end of add to buffer if in proper category
		} // finish looping through GLOBAL_MODULES

		// if there was nothing, we return true
		return true;
	} // end function empty_category

	function execute ($name, $parameters = NULL) {
		if (!is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES'])) return NULL;
		reset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']); $buffer = "";
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $el_garbage => $this_module) {
			// execute constructor
			$this_class = new $this_module['MODULE_CLASS'] ( );

			// determine if it's a method or not...
			if (method_exists($this_class, $name)) {

				// execute actual function
				if (!is_array ($parameters))
					$ret = call_user_method (
						$name,
						$this_class
					);
				else {
					$ret = call_user_method_array (
						$name,
						$this_class,
						$parameters
					);
				} // end checking for parameters

				// add to buffer
				$buffer .= $ret;

			} // end checking to see if method exists
		} // end foreach module

		// return the buffer value
		return $buffer;
	} // end function execute

	function generate_array ($category, $category_version,
			$key_template = "#module#",
			$val_template = "#class# (#module#, #version#)",
			$icon_template = "<IMG SRC=\"#icon#\" BORDER=0 ALT=\"#name#\">") {

		if (!is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES'])) return NULL;

		// Initialize array
		unset($this_array);
		$have_output = false;
		unset ($output);
		reset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']);
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $_x => $this_module) {
			// Check for category versioning
			$CATEGORY_NAME = $this_module['CATEGORY_NAME'];
			if (!isset($GLOBALS['__phpwebtools']['GLOBAL_CATEGORIES_VERSION'][$CATEGORY_NAME])) {
				$GLOBALS['__phpwebtools']['GLOBAL_CATEGORIES_VERSION'][$CATEGORY_NAME] = $this_module['CATEGORY_VERSION'];
			}

			// Create ICON template
			if ( $this_module['ICON'] == NULL ) {
				$icon_text = "";
			} else {
				// Import template
				$icon_text = $icon_template;
				// Substitute
				$icon_text = str_replace (
					"#icon#",
					$this_module['ICON'],
					$icon_text
				);
				$icon_text = str_replace (
					"#name#",
					$this_module['MODULE_NAME'],
					$icon_text
				);
			} // end creating icon template

			// Fill in the key template
			$this_key = $key_template;
			$this_key = str_replace ("#name#", 
				$this_module['MODULE_NAME'], $this_key);
			$this_key = str_replace ("#class#", 
				$this_module['MODULE_CLASS'], $this_key);
			$this_key = str_replace ("#version#", 
				$this_module['MODULE_VERSION'], $this_key);
			$this_key = str_replace ("#author#", 
				( ($this_module['MODULE_AUTHOR'] != NULL) ?
				$this_module['MODULE_AUTHOR'] : "&nbsp;" ),
				$this_key);
			$this_key = str_replace ("#icon#", $icon_text, $this_key);
			$this_key = str_replace ("#description#", 
				( ($this_module['MODULE_DESCRIPTION'] != NULL) ?
				$this_module['MODULE_DESCRIPTION'] : "&nbsp;" ),
				$this_key);
			$this_key = str_replace ("#vendor#", 
				( ($this_module['MODULE_VENDOR'] != NULL) ?
				$this_module['MODULE_VENDOR'] : "&nbsp;" ),
				$this_key);

			// fill in the template
			$this_val = $val_template;
			$this_val = str_replace ("#name#", 
				$this_module['MODULE_NAME'], $this_val);
			$this_val = str_replace ("#class#", 
				$this_module['MODULE_CLASS'], $this_val);
			$this_val = str_replace ("#version#", 
				$this_module['MODULE_VERSION'], $this_val);
			$this_val = str_replace ("#author#", 
				( ($this_module['MODULE_AUTHOR'] != NULL) ?
				$this_module['MODULE_AUTHOR'] : "&nbsp;" ),
				$this_val);
			$this_val = str_replace ("#icon#", $icon_text, $this_val);
			$this_val = str_replace ("#description#", 
				( ($this_module['MODULE_DESCRIPTION'] != NULL) ?
				$this_module['MODULE_DESCRIPTION'] : "&nbsp;" ),
				$this_val);
			$this_val = str_replace ("#vendor#", 
				( ($this_module['MODULE_VENDOR'] != NULL) ?
				$this_module['MODULE_VENDOR'] : "&nbsp;" ),
				$this_val);

			// Add to array
			if (($this_module['CATEGORY_NAME'] == $category) and
				($category_version >= $this_module['CATEGORY_MINIMUM_VERSION'])) {
				// Check for this actually being added
				if (!$this_module['MODULE_HIDDEN']) {
					$have_output = true;
					$this_array["$this_key"] = $this_val;	
				} elseif ($this->display_hidden) {
					// If *forced* to display...
					$have_output = true;
					$this_array["$this_key"] = $this_val;	
				}
			} // end of add to buffer if in proper category
		} // finish looping through GLOBAL_MODULES

		// If there is no output ...
		if (!$have_output) return NULL;

		// Sort this by module names...
		ksort ($this_array);

		// return the buffer
		return $this_array;
	} // end function generate_array

	function generate_list ($category, $category_version,
			$template = "#class# (#module#, #version#)<BR>\n",
			$icon_template = "<IMG SRC=\"#icon#\" BORDER=0 ALT=\"#name#\">") {
		if (!is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES'])) return NULL;

		$buffer = "";
		$have_output = false;
		unset ($output);
		reset ($GLOBALS['__phpwebtools']['GLOBAL_MODULES']);
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $el_garbage => $this_module) {
			// Check for category versioning
			$CATEGORY_NAME = $this_module['CATEGORY_NAME'];
			if (!isset($GLOBALS['__phpwebtools']['GLOBAL_CATEGORIES_VERSION'][$CATEGORY_NAME])) {
				$GLOBALS['__phpwebtools']['GLOBAL_CATEGORIES_VERSION'][$CATEGORY_NAME] = $this_module['CATEGORY_VERSION'];
			}

			// Create ICON template
			if ( $this_module['ICON'] == NULL ) {
				$icon_text = "";
			} else {
				// Import template
				$icon_text = $icon_template;
				// Substitute
				$icon_text = str_replace (
					"#icon#",
					$this_module['ICON'],
					$icon_text
				);
				$icon_text = str_replace (
					"#name#",
					$this_module['MODULE_NAME'],
					$icon_text
				);
			} // end creating icon template

			// fill in the template
			$this_one = $template;
			$this_one = str_replace ("#name#", 
				$this_module['MODULE_NAME'], $this_one);
			$this_one = str_replace ("#class#", 
				$this_module['MODULE_CLASS'], $this_one);
			$this_one = str_replace ("#version#", 
				$this_module['MODULE_VERSION'], $this_one);
			$this_one = str_replace ("#author#", 
				( ($this_module['MODULE_AUTHOR'] != NULL) ?
				$this_module['MODULE_AUTHOR'] : "&nbsp;" ),
				$this_one);
			$this_one = str_replace ("#icon#", $icon_text, $this_one);
			$this_one = str_replace ("#description#", 
				( ($this_module['MODULE_DESCRIPTION'] != NULL) ?
				$this_module['MODULE_DESCRIPTION'] : "&nbsp;" ),
				$this_one);
			$this_one = str_replace ("#vendor#", 
				( ($this_module['MODULE_VENDOR'] != NULL) ? 
				$this_module['MODULE_VENDOR'] : "&nbsp;" ),
				$this_one);

			// add to buffer
			if (($this_module['CATEGORY_NAME'] == $category) and
				($category_version >= $this_module['CATEGORY_MINIMUM_VERSION'])) {
				
				// Check for this actually being added
				if (!$this_module['MODULE_HIDDEN']) {
					$have_output = true;
					$output[$this_module['MODULE_NAME']] = $this_one;	
				} elseif ($this->display_hidden) {
					// If *forced* to display...
					$have_output = true;
					$output[$this_module['MODULE_NAME']] = $this_one;	
				}
			} // end of add to buffer if in proper category
		} // finish looping through GLOBAL_MODULES

		// if there is no output ...
		if (!$have_output) return NULL;

		// now create buffer from output list, sorted
		reset($output); ksort ($output);
		foreach ($output as $el_garbage => $this_output) {
			$buffer .= $this_output;
		} // end foreach output

		// return the buffer
		return $buffer;
	} // end function generate_list

	// Method: get_module_name
	//
	//	Resolve module class name into textual module name
	//
	// Parameters:
	//
	//	$module - Name of module class
	//
	// Returns:
	//
	//	Textual name of the module
	//
	function get_module_name ($module) {
		static $names; // cache
	
		if (!is_array ($names)) {
			foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $el_garbage => $this_module) {
				// add to cache
				$names[($this_module['MODULE_CLASS'])] = 
					$this_module['MODULE_NAME'];
			} // end looping through items
		} // end caching
		
		// return entry, or null if not listed
		return $names["$module"];
	} // end method get_module_name

} // end class module_list

?>
