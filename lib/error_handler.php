<?php
 // $Id$
 // note: error_handler
 // lic : GPL, v2

if (!defined("__ERROR_HANDLER_PHP__")) {

define (__ERROR_HANDLER_PHP__, true);

function freemed_standard_error_handler ($no, $str, $file, $line, $context) {
	switch ($no) {
		case E_USER_ERROR:
		case E_USER_WARNING:
			$error =
				"Package : ".PACKAGENAME."\n".
				"Version : ".VERSION."\n".
				"Installation : ".INSTALLATION."\n".
				"IP : ".$GLOBALS["SERVER_NAME"]."\n".
				"Timestamp : ".date("D M d Y h:i a")."\n".
				"File : ".$file."\n".
				"Line : ".$line."\n".
				"Error : ".$str."\n";

			// currently, show error
			echo "<PRE>\n$error\n</PRE>\n";
	
			if (function_exists("freemed_display_box_bottom"))	
				freemed_display_box_bottom ();
			if (function_exists("freemed_display_html_bottom"))	
				freemed_display_html_bottom ();
			DIE("");
			break;
	} // end switch
} // end function freemed_standard_error_handler

// set as default error handler
//error_reporting ( E_USER_ERROR );
$original_error_handler = set_error_handler("freemed_standard_error_handler");

} // end checking for __ERROR_HANDLER_PHP__

?>
