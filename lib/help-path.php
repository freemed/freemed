<?php
// $Id$
// FreeMED — help page path resolver (security-critical; see tests/security/help_path.test.php)

// Function: help_resolve_path
//
//	Resolve a help request PATH_INFO to an absolute file under
//	<root>/ui/<ui>/help/<locale>/, or return false if it is invalid,
//	does not exist, or escapes the help directory.
//
// Parameters:
//
//	$path_info - raw $_SERVER['PATH_INFO'] (e.g. '/gwt/en_US/main')
//	$root      - application root (normally dirname(__FILE__) of help.php)
//
// Returns:
//
//	Absolute path string on success, boolean false otherwise.
//
function help_resolve_path ( $path_info, $root ) {
	$parts = explode ( '/', (string) $path_info );
	$ui     = isset($parts[1]) ? $parts[1] : '';
	$locale = (isset($parts[2]) and $parts[2] != '') ? $parts[2] : 'en_US';

	// Sanitized parameters
	if (!preg_match('/^[[:alpha:]]+$/', $ui) or !preg_match('/^[[:alpha:]_]+$/', $locale)) {
		return false;
	}

	// Everything after /<ui>/<locale>/ is the page name
	$page = isset($parts[3]) ? implode('/', array_slice($parts, 3)) : '';
	$page = urldecode($page);

	// Page names are simple tokens. No separators, no dot-segments, no NUL,
	// no hidden files. Optional trailing "<dot><locale>" is stripped so that
	// both '/main' and '/main.en_US' resolve to main.en_US.html.
	$page = preg_replace('/\.' . preg_quote($locale, '/') . '$/', '', $page);
	if ($page === '' or !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $page) or strpos($page, '..') !== false) {
		return false;
	}

	// Deterministic suffix: every help page is <page>.<locale>.html
	$helpdir = realpath ( $root . '/ui/' . $ui . '/help/' . $locale );
	if ($helpdir === false) { return false; }
	$candidate = realpath ( $helpdir . '/' . $page . '.' . $locale . '.html' );
	if ($candidate === false) { return false; }

	// Confinement: the resolved file must live directly in the help directory
	if (strpos($candidate, $helpdir . DIRECTORY_SEPARATOR) !== 0) { return false; }
	if (basename($candidate) !== $page . '.' . $locale . '.html') { return false; }
	if (!is_file($candidate) or !is_readable($candidate)) { return false; }

	return $candidate;
} // end function help_resolve_path

?>
