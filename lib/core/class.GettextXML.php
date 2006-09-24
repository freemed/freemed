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

// Class: org.freemedsoftware.core.GettextXML
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
	public function bindtextdomain ($package, $dir) {
		$GLOBALS['__freemed']['gettextXML']['package'] = $package;
		$GLOBALS['__freemed']['gettextXML']['dir'] = $dir;
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
	public function gettext_xml ( $string ) {
		static $_cache;

		// Check for language, otherwise get from environment
		if (!isset($GLOBALS['__freemed']['gettextXML']['language'])) {
			$GLOBALS['__freemed']['gettextXML']['language'] =
				getenv('LANGUAGE');
		}

		extract($GLOBALS['__freemed']['gettextXML']);

		// If we're not cached, cache *everything*
		$domains = $GLOBALS['__freemed']['gettextXML']['domains'];
		if (is_array($domains)) {
			$domains = array_unique($domains);
			foreach ($domains AS $__garbage => $xmlfile) {
				if (!$GLOBALS['__freemed']['gettextXML']['domain_cached'][$xmlfile]) {
					$GLOBALS['__freemed']['gettextXML']['domain_cached'][$xmlfile] = $xmlfile;
					// Add to the cache
					$_cache = @array_merge($_cache, GettextXML::_parse_xml($xmlfile));
				}
			} // end foreach
		} // end is array

		// Get translated string
		$translated = $_cache["$string"];

		// If there's no translation, return original, with possible
		// marker
		if (empty($translated)) {
			$translated = $string . $GLOBALS['__freemed']['gettextXML']['marker'];
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
	public function markuntranslated ( $marker ) {
		$GLOBALS['__freemed']['gettextXML']['marker'] = $marker;
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
	public function metainformation ( $filename ) {
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
	public function setlanguage ( $language ) {
		$GLOBALS['__freemed']['gettextXML']['language'] = $language;
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
	public function textdomain ( $domain ) {
		$GLOBALS['__freemed']['gettextXML']['domains'][] = $domain;
		$GLOBALS['__freemed']['forget_i18n_cache'] = 1;
	} // end GettextXML::textdomain

	//----- internal functions -------------------------------------------

	protected function _parse_xml_meta ( $file ) {
		// Create RAX objects
		$parser = CreateObject('org.freemedsoftware.core.RAX');
		$record = CreateObject('org.freemedsoftware.core.RAX');

		// Grab external variables to local scope, for readability
		extract($GLOBALS['__freemed']['gettextXML']);

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

	protected function _parse_xml ( $file ) {
		// Create RAX objects
		$parser = CreateObject('org.freemedsoftware.core.RAX');
		$record = CreateObject('org.freemedsoftware.core.RAX');

		// Grab external variables to local scope, for readability
		extract($GLOBALS['__freemed']['gettextXML']);

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
