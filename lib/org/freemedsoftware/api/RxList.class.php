<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.RxList
//
//	Wrapper for rxlist.com formulary access.
//
class RxList {

	// Method: RxList::getPage
	//
	//	Gets the web page from rxlist.com pertaining to the
	//	specified query.
	//
	// Parameters:
	//
	//	$query - String to search for
	//
	// Returns:
	//
	//	HTML text of web page.
	//
	function getPage ( $query ) {
		//$url = "http://www.rxlist.com/cgi/rxlist.cgi?drug=".
		$url = "http://129.250.146.18/cgi/rxlist.cgi?drug=".
			urlencode($query);
			
		if (!($fp = fopen($url, "r"))) {
			// If we fail to get the page, return false
			return false;
		} else {
			// Get the page
			$buffer = 0;
			while (!feof($fp)) {
				$buffer .= fgets($fp, 4096);
			}
			fclose ($fp);
			return $buffer;
		} // end checking for successful open
	} // end function RxList::getPage

	// Method: RxList::get_list
	//
	//	Retrieves the appropriate rxlist.com formulary list, based
	//	on the drug criteria provided.
	//
	// Parameters:
	//
	//	$query - Text to search in the rxlist.com formulary for.
	//
	// Returns:
	//
	//	Associative array (meant to be passed to phpwebtools'
	//	html_form::select_widget) containing the results.
	//
	function get_list ( $query ) {
		$page = RxList::getPage($query);
		$tokens = RxList::parse2tokens($page);
		$hash = RxList::tokens2hash($tokens);
		foreach ($hash AS $index => $data) {
			unset($my_data);
			foreach ($data AS $k => $v) {
				$my_data[$k] = $v;
			}
			$list[trim($my_data[brand_name])." (".trim($my_data[generic_name]).")"] =
				trim($my_data[brand_name]);
		}
		return $list;
	} // end function RxList::get_list

	function parse2tokens( $page ) {
		$pos = 0; $token = 0; unset ($tokens);
		$in_token = false;
		while ($pos < strlen($page)) {
			switch(substr($page, $pos, 1)) {
				case "<":
				if ($in_token) {
					$token++;
					$in_token = false;
				}
				$tokens[$token] .= substr($page,$pos,1);
				$in_token = true;
				break;

				case ">":
				$tokens[$token] .= substr($page,$pos,1);
				$in_token = false;
				$token++;
				break;

				default:
				$tokens[$token] .= substr($page,$pos,1);
				$in_token = false;
				break;

			} // end decide what to do
			$pos++;
		} // end looping through string
		return $tokens;
	} // end function RxList::parse2tokens

	function tokens2hash ( $tokens ) {
		$record = false; $current = 0; unset($hash); unset($all);
		for ($pos=0; $pos<count($tokens); $pos++) {
			if (!(strpos($tokens[$pos], "Brand Name") === false)){
				$type = "brand_name";
				$record = $pos;
				$ending = "</a>";
			}
			if (!(strpos($tokens[$pos], "Generic Name") === false)){
				$type = "generic_name";
				//print "generic_name record start at $pos<BR>\n";
				$ending = "</a>";
				$record = $pos;
			}
			if (!(strpos($tokens[$pos], "Drug Class") === false)){
				$type = "drug_class";
				//print "drug_class record start at $pos<BR>\n";
				$ending = "</font>";
				$record = $pos;
			}
			// Handle the ending (assume when all fields are set
			// that we're done)
			if (isset($hash["drug_class"]) and isset($hash["brand_name"]) and isset($hash["generic_name"])) {
				// Reset record
				$record = false;
				// Add hash to all
				$all[] = $hash;
				// Unset hash and type
				unset($hash); $type = ""; $ending = "";
			}
			if ((($pos == ($record + 1)) or
					($pos == ($record + 2)) or
					($pos == ($record + 3)))
					and ($ending != "")) {
				//print "tokens[$pos] = ".htmlentities($tokens[$pos])."<BR>\n";
				if ((!(strpos(strtoupper($tokens[$pos]), "</A>") === false)) or
					(!(strpos(strtoupper($tokens[$pos]), "</FONT>") === false))) {
					// Find where anchor is
					$my_pos = strpos(strtoupper($tokens[$pos]), "<");
					$hash[$type] = substr($tokens[$pos], 0, $my_pos);
					$hash[$type] = str_replace("&amp;", "&", $hash[$type]);
					//print "hash[$type] = ".htmlentities($hash[$type])."<BR>\n";
					$type = ""; $ending = "";
				}
			}
		} // end looping
		return $all;
	} // end function RxList::tokens2hash

} // end class RxList

/*
// TEST CRAP HERE
$page = RxList::getPage("ace");
$tokens = RxList::parse2tokens($page);
$hash = RxList::tokens2hash($tokens);
foreach ($hash AS $k => $v) {
	print "<UL>k = ".htmlentities($k)." \n";
	foreach ($v AS $key => $value) {
		print "<LI>$key = $value\n";
	}
	print "</UL>\n";
}

print "<FORM>\n";
print html_form::select_widget("test", RxList::get_list($drug));
print "</FORM>\n";
*/
?>
