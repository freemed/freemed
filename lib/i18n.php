<?php
	// $Id$
	// $Author$
	// GettextXML support

LoadObjectDependency('PHP.GettextXML');

GettextXML::bindtextdomain('FreeMED', './locale');
GettextXML::setlanguage($_COOKIE['language']);

// Set basic text domain
GettextXML::textdomain('freemed');

// Set text domain for page
GettextXML::textdomain(str_replace('.php', '', page_name()));

// If there is a module loaded, get its text
if (isset($_REQUEST['module'])) {
	GettextXML::textdomain(strtolower($_REQUEST['module']));
}

// For debugging, uncomment the next line; it allows all untranslated strings
// to come up with a '*' appended to them.
//GettextXML::markuntranslated('*');

?>
