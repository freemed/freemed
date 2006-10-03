<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
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

// Class: org.freemedsoftware.core.ModuleIndex
//
//	Class that does the work of indexing and caching information
//	regarding directories of multiple modules.
//
// See Also: <org.freemedsoftware.core.module>

class ModuleIndex {

	protected $index;
	private $ttl = '3600'; // 1h TTL

	// Method: constructor
	//
	// Parameters:
	//
	//	$force_index - (optional) Force indexing
	//
	function __construct ( $force_index = false ) {
		$this->display_hidden = true;

		// Derive module directory from the mask
		$this->modules_directory = dirname(dirname(dirname(__FILE__))).'/modules';

		// Recursive directory spanning
		$this->recursive = true;
	
		// Form the extension
		$this->extension = ".module.php";

		$this->index = $this->LoadIndex ( );

		if ($force_index) {
			// Check to see if the directory is cached
			if ( !$this->IsCached( ) ) {
				// Farm the call out to a function so we can handle
				// recursive directory entries ...
				$this->_ScanDirectory($this->modules_directory, $this->recursive);
			} // end checking for cache
		} // if forced index
	} // end constructor module_list

	// Method: LoadIndex
	//
	//	Load module index from the database table 'modules'.
	//
	// Returns:
	//
	//	Array of hashes containing module information.
	//
	protected function LoadIndex ( ) {
		$query = "SELECT * FROM modules";
		$results = $GLOBALS['sql']->queryAll ( $query );
		return $results;
	} // end protected function LoadIndex

	// Method: _ScanDirectory
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
	function _ScanDirectory ( $dir, $recurse = false ) {
		if (! ($d = dir($dir)) ) {
			die(get_class($this)." :: could not open directory '".$dir."'");
		}

		// loop through directory entries
		while ($entry = $d->read()) {
			// Determine if it is a module (.module.php)
			if (strtolower(substr($entry,-(strlen($this->extension)))) == $this->extension) {
				// include the module (which registers it)
				//print "include ".( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry )."<br/>\n";
				$this->_ScanFile ( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry );
				// [ Create cache entries from register_module() ]
			} else { // if it *isn't* a module
				// If we recurse and it's a directory
				if ( is_dir ( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry ) and $recurse ) {
					// Make sure we can go here
					if ( ($entry != 'CVS') and (substr($entry, 0, 1) != '.') ) {
						//print "entering recurse on $entry<br/>\n";
						// Recurse into this directory
						$this->_ScanDirectory( $dir . ( (substr($dir,-1)!="/") ? "/" : "" ).$entry, true);
					}
				}
			} // end determining if it is a module
		} // end looping through directory entries
	} // end method _ScanDirectory

	// Method: _ScanFile
	//
	//	Scan file against the 'modules' table.
	//
	// Parameters:
	//
	//	$file - Fully qualified file path/name to module file
	//
	protected function _ScanFile ( $file ) {
		// Determine if this file is indexed or not
		if ($this->IsIndexed( $file )) {

		} else {
			// If not, index this module
			@include_once ( $file );
		}
	} // end protected function _ScanFile

	// Method: IsIndexed
	//
	//	Determine if a module has been indexed already
	//
	// Parameters:
	//
	//	$file - Fully qualified path and file name
	//
	// Returns:
	//
	//	Boolean, whether or not this module has been indexed already.
	//
	protected function IsIndexed ( $file ) {
		foreach ($this->index AS $m) {
			if ($m['MODULE_FILE'] == $m) { return true; }
		}
		return false;
	} // end protected function IsIndexed

	// Method: IsCached
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
	private function IsCached ( ) {
		// Check for a cache file not existing, if so, it's not cached
		if (!file_exists($this->cache_file)) { return false; }

		// Get cached modification time
		$q = $GLOBALS['sql']->queryOne('SELECT MAX(stamp) AS cache_modified FROM modules');

		// Get directory modification date
		clearstatcache();
		$dir_modified = array_element(lstat($dir_name), 9);
		//print "DEBUG: dir_modified = $dir_modified<br/>\n";

		// If the cache is older than the directory...
		if ($q['cache_modified'] < $dir_modified) {
			// .. the cache needs to be rebuilt
			return false;
		} else {
			// Or else, we have a cached copy
			return true;
		} // end checking cache time
	} // end private function IsCached

	// Method: CheckForModule
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
	function CheckForModule ($module_name) {
		if (empty($module_name)) { return false; }
		if ($this->GetModuleProperty( $module_name, 'MODULE_CLASS' )) { return true; }
		return false;
	} // end function CheckForModule

	function execute ($name, $parameters = NULL) {
		if (!is_array($this->index)) return NULL;
		$buffer = "";
		foreach ($this->index AS $this_module) {
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

	// Method: GetModuleProperty
	//
	//	Resolve module class name into module property value
	//
	// Parameters:
	//
	//	$module - Name of module class
	//
	//	$property - Name of the property
	//
	// Returns:
	//
	//	Property value
	//
	public function GetModuleProperty ( $module, $property ) {
		static $idx;
		if (!is_array ($idx)) {
			foreach ($this->index AS $this_module) {
				$idx[($this_module['MODULE_CLASS'])] = $this_module;
			}
		}
		return $idx[$module][$property];
	} // end method GetModuleProperty

	// Method: GetModuleName
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
	public function GetModuleName ( $module ) {
		return $this->GetModuleProperty ( $module, 'MODULE_NAME' );
	} // end method GetModuleName

	// Method: GetModuleFile
	//
	//	Resolve module class name into module file name
	//
	// Parameters:
	//
	//	$module - Name of module class
	//
	// Returns:
	//
	//	Textual name of the module
	//
	public function GetModuleFile ( $module ) {
		return $this->GetModuleProperty ( $module, 'MODULE_FILE' );
	} // end method GetModuleFile

} // end class module_list

?>
