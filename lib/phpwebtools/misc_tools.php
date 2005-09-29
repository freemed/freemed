<?php
	// $Id$
	// code: jeff b <jeff@ourexchange.net>
	// lic : LGPL

// File: General API

// Loader for GettextXML
// Function: __
//
//	Wrapper for <GettextXML::gettext_xml> string translation function.
//
// Parameters:
//
//	$string - String to internationalize.
//
// Returns:
//
//	Translated string.
//
function __($string) {
	static $loaded;

	// Only call LoadObjectDependency once, for optimization in speed
	if (!isset($loaded)) {
		LoadObjectDependency('PHP.GettextXML');
		$loaded = true;
	} // end checking for loading

	return GettextXML::gettext_xml($string);
} // end function __()

function web_link ($text, $link = "") {
	if ($link == "") $link = $text;
	return "<a HREF=\"$link\" ".
	"onMouseOver=\"window.status='$text'; return true;\" ".
	"onMouseOut=\"window.status=''; return true;\" ".
	">$text</a>";
} // end function web_link

// Function: in_this
//
//	Determines if a value is in or equal to a variable
//
// Parameters:
//
//	$haystack - Variable to search in
//
//	$needle - Variable to search for
//
// Returns:
//
//	Boolean, whether variable is found.
//
function in_this ($var, $val) {
	if (!is_array ($var)) {
		// handle as scalar
		if ($var == $val) return true;
	} else {
		// handle as array
		reset ($var);
		while (list ($k, $v) = each ($var)) {
			if ($v == $val) return true;
		}
	} // end checking how to handle

	// if all else fails... false
	return false;
} // end function in_this

function array_element ($array, $element) {
	return ( (is_array($array)) ? $array[$element] : $array );
} // end function array_element

// implement PHP3 version of in_array
if (floor(phpversion()) < 4) {
	function in_array ($needle, $haystack) {
		for ($i=0; $i<count($haystack) && $haystack[$i] != $needle; $i++);
		return ($i != count($haystack));
	} // end function in_array
}

// Function: prepare
//
//	Prepares a string (possibly with slashes) to be displayed in
//	an HTML context.
//
// Parameters:
//
//	$string - String to be displayed
//
//	$force_conversion - (optional) Do we force conversion of
//	embedded HTML tags? Defaults to false.
//
// Returns:
//
//	HTML safe string
//
function prepare ($string, $force_conversion=false) {
	return ( (!$force_conversion and (
			eregi("<[A-Z/]*>", $string) or
			eregi("&quot;", $string)
		) ) ?
		stripslashes($string) :
		htmlentities(stripslashes($string), ENT_COMPAT, 'UTF-8')
	);
} // end function prepare

// Function: flatten_array
//
//	Recursively flatten multidimensional arrays into a single
//	dimension array.
//
// Parameters:
//
//	$inital_array - Array to be flattened.
//
// Returns:
//
//	Single dimensional array.
//
function flatten_array ($initial_array) {
	// handle non-array parameters
	if (!is_array($initial_array)) {
		$result_array[] = $intial_array;
		return $result_array;
	} // end if not array

	// loop through entire array
	reset ($initial_array);
	$result_array = array();
	while (list($key, $val) = each ($initial_array)) {
		if (is_array($val)) {
			$sub_array = flatten_array ($val);
			$result_array = ( (is_array($result_array)) ?
				array_merge ($result_array, $sub_array) :
				$sub_array
			);
		} else { // if scalar
			// Use strlen instead of empty to handle "0"s
			if (!strlen($val)<1) {
				// if it is a non-numeric key...
				if (($key+0)==0) {
					$result_array["$key"] = $val;
				} else {
					$result_array[] = $val;
				}
			} // end checking for empty key
		} // end checking array or scalar
	} // end of while loop

	// return result array
	asort ($result_array);
	return $result_array;
} // end function flatten_array

// function unique_array replaced with array_unique (PHP4 >= 4.0.1)

function alternate_colors ($colors = "") {
	static $cur_pos;

	// default values for switching
	if (!is_array($colors)) {
		$colors = array ( "#dddddd", "#ffffff" );
	}

	// if never run before, reset current position
	if (!isset($cur_pos)) {
		$cur_pos = 0;
	} else {
		$cur_pos++;
		if ($cur_pos >= count($colors)) $cur_pos = 0;
	}

	return $colors[$cur_pos]; 
} // end function alternate_colors

// Function: page_name
//
//	Derive name of current page from environmental variables.
//
// Parameters:
//
//	$page_name - (optional) Full request URI of page. If none is
//	provided, the current page name will be used.
//
// Returns:
//
//	Name of the PHP script.
//
function page_name ($page_name="") {
	// Fix from Fernando Telesca to deal with IIS
	if(!isset($_SERVER['REQUEST_URI'])) {
		$_SERVER['REQUEST_URI'] = substr($_SERVER['argv'][0],
			strpos($_SERVER['argv'][0], ';') + 1);
	}

	// Deal with "root" document
	if (substr($_SERVER['REQUEST_URI'], -1) == '/') {
		return 'index.php';
	}

	$this_page = ( (empty($page_name)) ? 
		$_SERVER['REQUEST_URI'] :
		$page_name
	);
	list ($page, $garbage) = explode ("?", basename ($this_page));
	return $page;
} // end function page_name

// Function: version_check
//
//	Compare a version number with single or multiple dots against
//	an arbitrary versioning number.
//
// Parameters:
//
//	$version - Version to test
//
//	$minimum - Arbitrary version to check against.
//
// Returns:
//
//	Boolean, depending on whether the version was greater than the
//	provided value.
//
function version_check ( $version, $minimum ) {
	// first, see if any .'s at all
	if ( !strpos($version, ".") ) {
		//echo "no dot";
		return ( $version >= $minimum );
	} else { // if there are dots
		$version_array = explode (".", $version);
		$minimum_array = explode (".", $minimum);
		for ($i=0; $i<count($minimum_array); $i++) {
			if (!isset($version_array[$i])) $version_array[$i] = 0;
			if ($version_array[$i] < $minimum_array[$i]) return false; 
			if ($version_array[$i] > $minimum_array[$i]) return true;
		} // end for
		return true; // true if they are *exactly* the same
	} // end if there are/n't dots
} // end function version_check

// Function: linkify
//
//	Converts email addresses and URLs into links.
//
// Parameters:
//
//	$original - Original string to be translated.
//
// Returns:
//
//	Linked text.
//
function linkify ( $original ) {
	$s = $original;

	// Linkify mail addresses
	$s = preg_replace("/\ ([-^!#$%&'*+\/=?`{|}~.\w]+@([-a-zA-Z0-9.]+\.)[-a-z
A-Z0-9]{2,6})/", ' <a href="mailto:\\1">\\1</a>', $s);

	// Linkify web addresses
	$s = preg_replace("/\s([a-z]*\:\/\/[A-Za-z0-9\.\/\?\&\~\%]*)\s/i", ' <a
href="\\1">\\1</a> ', $s);

	return $s;
} // end function linkify

?>
