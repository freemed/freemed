<?php
	// $Id$
	// $Author$
	// GettextXML format
	// Must be loaded by LoadObjectDependency()

// Class: PHP.GettextXML
class GettextXML {

	// Method: GettextXML::bindtextdomain
	//
	//	Use a particular text domain.
	//
	// Parameters:
	//
	//	$package - Name of package to use
	//
	//	$dir - Name of directory to use
	//
	function bindtextdomain ($package, $dir) {
		$GLOBALS['__phpwebtools']['gettextXML']['package'] = $package;
		$GLOBALS['__phpwebtools']['gettextXML']['dir'] = $dir;
	} // end GettextXML::bindtextdomain

	// Method: GettextXML::gettext_xml
	//
	//	GettextXML string retrieval function. This function
	//	is wrapped by <__> in misc_tools.
	//
	// Parameters:
	//
	//	$string - Text string to retrieve internationalization for
	//
	// Returns:
	//
	//	Translated string.
	//
	// See Also:
	//	<__>
	//
	function gettext_xml($string) {
		static $_cache;
	
		// Check for language, otherwise get from environment
		if (!isset($GLOBALS['__phpwebtools']['gettextXML']['language'])) {
			$GLOBALS['__phpwebtools']['gettextXML']['language'] =
				getenv('LANGUAGE');
		}

		extract($GLOBALS['__phpwebtools']['gettextXML']);

		// If we're not cached, cache *everything*
		if (!is_array($_cache)) {
			$domains = $GLOBALS['__phpwebtools']['gettextXML']['domains'];
			if (is_array($domains)) {
				$domains = array_unique($domains);
				foreach ($domains AS $__garbage => $xmlfile) {
					if (!$GLOBALS['__phpwebtools']['gettexmXML']['domain_cached'][$xmlfile]) {
						$GLOBALS['__phpwebtools']['gettextXML']['domain_cached'][$xmlfile] = $xmlfile;
						// Add to the cache
						$_cache = array_merge($_cache,
							GettextXML::_parse_xml($xmlfile)
						);
					}
				} // end foreach
			} // end is array
		} // end checking for cached copy

		// Get translated string
		$translated = $_cache["$string"];

		// If there's no translation, return original, with possible
		// marker
		if (empty($translated)) {
			$translated = $string . $GLOBALS['__phpwebtools']['gettextXML']['marker'];
		}

		return $translated;
	} // end GettextXML::gettext_xml

	// Method: GettextXML::markuntranslated
	//
	//	Set debugging status by adding a character or set of
	//	characters to the end of every untranslated string.
	//
	// Parameters:
	//
	//	$marker - Characters to append to untranslated strings.
	//
	function markuntranslated($marker) {
		$GLOBALS['__phpwebtools']['gettextXML']['marker'] = $marker;
	} // end GettextXML::markuntranslated

	// Method: GettextXML::metainformation
	//
	//	Get associated meta information for GettextXML files
	//
	// Parameters:
	//
	//	$filename - GettextXML file name (string)
	//
	// Returns:
	//
	//	Associative array of meta information.
	//
	function metainformation($filename) {
		return GettextXML::_parse_xml_meta($filename);
	} // end GettextXML::metainformation

	// Method: GettextXML::setlanguage
	//
	//	Set language to use for internationalization.
	//
	// Parameters:
	//
	//	$language - Language to use for internationalization
	//
	function setlanguage ($language) {
		$GLOBALS['__phpwebtools']['gettextXML']['language'] = $language;
	} // end GettextXML::setlanguage

	// Method: GettextXML::textdomain
	//
	//	Add an additional text domain to the list of domains
	//	that GettextXML searches for translations in.
	//
	// Parameters:
	//
	//	$domain - Text domain
	//
	function textdomain ($domain) {
		$GLOBALS['__phpwebtools']['gettextXML']['domains'][] = $domain;
	} // end GettextXML::textdomain

	//----- internal functions -------------------------------------------

	function _parse_xml_meta($file) {
		// Create RAX objects
		$parser = CreateObject('PHP.RAX');
		$record = CreateObject('PHP.RAX');

		// Grab external variables to local scope, for readability
		extract($GLOBALS['__phpwebtools']['gettextXML']);

		if (!$parser->openfile($file)) {
			// If not openable, return null array
			return array();
		}

		// Record delimiter is 'information'
		$parser->record_delim = 'information';

		// Parse the actual document
		$parser->parse();

		// Only one record
		$record = $parser->readRecord();
		return $record->getRow();
	} // end GettextXML::_parse_xml_meta

	function _parse_xml($file) {
		// Create RAX objects
		$parser = CreateObject('PHP.RAX');
		$record = CreateObject('PHP.RAX');

		// Grab external variables to local scope, for readability
		extract($GLOBALS['__phpwebtools']['gettextXML']);

		$fq_path = $dir . '/' . $language . '/' . $file .  '.xml';

		if (!$parser->openfile($fq_path)) {
			// If not openable, return null array
			return array();
		}

		// Record delimiter is 'translation'
		$parser->record_delim = 'translation';

		// Parse the actual document
		$parser->parse();

		// For each record...
		while ($record = $parser->readRecord()) {
			$r = $record->getRow();
			// Map to the return array
			$returned[$r['original']] = $r['translated'];
			// DEBUG
			//print $r['original'].' = '.$r['translated']."<br/>\n";
		} // end looping

		return $returned;
	} // end GettextXML::_parse_xml

} // end class GettextXML

?>
