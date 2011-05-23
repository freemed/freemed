<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.FormTemplateList
//
//	Maintain cached list of XML templates for filling out PDF forms.
//
class FormTemplateList {

	var $cache_file = 'data/cache/form_templates';

	// Constructor: FormTemplateList
	//
	public function __construct ( ) { }

	// Method: Cache
	//
	//	Cache metainformation from templates into cache file.
	//
	function Cache ( ) {
		if (! ($d = dir('data/form/templates')) ) { return false; }

		while ($entry = $d->read()) {
			if (is_file('data/form/templates/'.$entry) and substr($entry, -4) == '.xml') {
				// Same format as FormTemplate constructor
				$basename = str_replace('.xml', '', basename($entry));

				// Get information by instantiating template
				$template = CreateObject('org.freemedsoftware.api.FormTemplate', $basename);
				$information = $template->GetInformation();
				// Add to the stack
				$scan[$basename] = $information;
			}
		} // end while entry

		$cache = CreateObject('org.freemedsoftware.core.FileSerialize', $this->cache_file);
		$cache->write($scan);
	} // end method Cache

	// Method: GetList
	//
	//	Retrieve list of form templates in the system.
	//
	// Returns:
	//
	//	Associative array of form template names associated with
	//	arrays containing representations of their information
	//	fields.
	//
	function GetList ( ) {
		if (!$this->IsCached()) { $this->Cache(); }
		$cache = CreateObject('org.freemedsoftware.core.FileSerialize', $this->cache_file);
		return $cache->read();
	} // end method GetList

	// Method: IsCached
	//
	//	Determine if the form template cache is up to date.
	//
	// Returns:
	//
	//	Boolean, whether or not the cache is up to date
	//
	function IsCached ( ) {
		if (!file_exists($this->cache_file)) { return false; }

		clearstatcache();
		$dir_modified = array_element(stat('data/form/templates'), 9);
		clearstatcache();
		$cache_modified = array_element(stat($this->cache_file), 9);

		if ($cache_modified < $dir_modified) { return false; }
		return true;
	} // end method IsCached

} // end class FormTemplateList

?>
