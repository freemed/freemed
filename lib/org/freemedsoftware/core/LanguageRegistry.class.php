<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
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

// Class: org.freemedsoftware.core.LanguageRegistry
//
//	Class to handle FreeMED's language registry, built from entries
//	in FreeMED's "locale" folder. These are in the GettextXML format.
//
class LanguageRegistry {

	private $registry;

	public function __construct () {
		// Get directory
		if (! ($d = dir("./locale/")) ) {
			die(get_class($this)." :: could not open directory ./locale");
		}

		// Check for serialized file
		$cache = CreateObject(
			'org.freemedsoftware.core.FileSerialize',
			'data/cache/language_registry'
		);

		// Is it cached?
		if (!$this->cached()) {
			while ($entry = $d->read()) {
				//print "entry = $entry<br/>\n";
				if (is_dir('./locale/'.$entry) and
						($entry != "template") and
						(substr($entry,0,1) != '.') and
						($entry != 'CVS') ) {
					$this->register($entry);
				}
			}

			// Write to cache
			$cache->write($this->registry);
		} else {
			// Read from cache
			$this->registry = $cache->read();
		}
	} // end constructor

	// Method: cached
	//
	//	Determine if the language registry cache is up to date.
	//
	// Returns:
	//
	//	Boolean, true if data is up to date, false if recaching
	//	is necessary.
	//
	public function cached () {
		// Check for non-existant cache file
		if (!file_exists('data/cache/language_registry')) {
			return false;
		}

		// Get directory modification date
		clearstatcache();
		$dir_modified = array_element(lstat('./locale/'), 9);

		// Get cache modification date
		clearstatcache();
		$cache_modified = array_element(lstat('data/cache/language_registry'), 9);

		// If the cache is older than the directory
		if ($cache_modified < $dir_modified) {
			//print "rebuild<br/>\n";
			// Rebuild cache
			return false;
		} else {
			// Otherwise cache is up to date
			//print "fine<br/>\n";
			return true;
		}
	} // end method cached

	// Method: register
	//
	//	Register a language directory with the FreeMED language
	//	registry.
	//
	// Parameters:
	//
	//	$dir - Directory. This should be a subdirectory of
	//	FreeMED's "locale" folder.
	//
	function register ( $dir ) {
		if (!file_exists('./locale/'.$dir.'/registry')) {
			//print "COULD NOT INDEX $dir<br/>\n";
			return false;
		}
		$meta = file_get_contents ( './locale/'.$dir.'/registry' );
		$a = explode ( ':', $meta );
		$this->registry[$a[1]] = $a[0];
	} // end method register

	// Method: picklist
	//
	//	Create a language selection widget based on the current
	//	language registry.
	//
	// Returns:
	//
	//	Language selection picklist
	//
	public function picklist ( ) {
		return $this->registry;
	} // end method picklist 

} // end class LanguageRegistry

?>
