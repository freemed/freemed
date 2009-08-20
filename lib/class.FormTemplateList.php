<?php
	// $Id$
	// $Author$

// Class: FreeMED.FormTemplateList
//
//	Maintain cached list of XML templates for filling out PDF forms.
//
class FormTemplateList {

	var $cache_file = 'data/cache/form_templates';

	// Constructor: FormTemplateList
	//
	function FormTemplateList ( ) { }

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
				$template = CreateObject('_FreeMED.FormTemplate', $basename);
				unset($information);
				$information = $template->GetInformation();
				// Add to the stack
				$scan[$basename] = $information;
			}
		} // end while entry

		$cache = CreateObject('PHP.FileSerialize', $this->cache_file);
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
		$cache = CreateObject('PHP.FileSerialize', $this->cache_file);
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
