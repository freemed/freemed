<?php
    // $Id$
    // $Author$

LoadObjectDependency('PHP.GettextXML');

class LanguageRegistry {

	function LanguageRegistry () {

		// Get directory
		if (! ($d = dir("./locale/")) ) {
			die(get_class($this)." :: could not open directory ./locale");
		}

		// Check for serialized file
		$cache = CreateObject(
			'PHP.FileSerialize',
			'./locale/.registry'
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
			//$cache->write($this->registry);
		} else {
			// Read from cache
			$this->registry = $cache->read();
		}
	} // end constructor LanguageRegistry

	function cached () {
		// Check for non-existant cache file
		if (!file_exists('./locale/.registry')) {
		/*
			$touched = touch ('./locale/.registry');
			if (!$touched) {
				die(
				__("FreeMED was unable to create a file to record the language registry.")."<br/>\n".
				__("FreeMED's 'locale' directory should be owned by the user that the webserver is running as...")."<br/>\n".
				__("Usually this is 'apache'. You can also fix this by giving universal write access to the 'locale' directory of FreeMED.")."<br/>\n"
				);
			}
		*/
			return false;
		}

		// Get directory modification date
		clearstatcache();
		$dir_modified = array_element(lstat('./locale/'), 9);

		// Get cache modification date
		clearstatcache();
		$cache_modified = array_element(lstat('./locale/.registry'), 9);

		// If the cache is older than the directory
		if ($cache_modified < $dir_modified) {
			print "rebuild<br/>\n";
			// Rebuild cache
			return false;
		} else {
			// Otherwise cache is up to date
			print "fine<br/>\n";
			return true;
		}
	} // end method cached

	function register ($dir) {
		if (!file_exists('./locale/'.$dir.'/freemed.xml')) {
			print "COULD NOT INDEX $dir<br/>\n";
			return false;
		}

		// Read the using GettextXML::metainformation
		$meta = GettextXML::metainformation(
			'./locale/'.$dir.'/freemed.xml'
		);

		//print "meta[Locale] = ".$meta['Locale']."<br/>\n";
		$this->registry[$meta['LocaleName']] = $meta['Locale'];
	} // end method register

	function widget ($varname) {
		return html_form::select_widget(
			$varname,
			array_merge(
				$this->registry,
				array(__("Default Language") => $GLOBALS['language'])
			)
		);
	} // end method widget

} // end class LanguageRegistry

?>
