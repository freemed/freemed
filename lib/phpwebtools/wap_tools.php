<?php
 // $Id$
 // desc: WAP tools
 // code: jeff b <jeff@ourexchange.net>
 // lic : LGPL

if (!defined("__WAP_TOOLS_PHP__")) {

define ('__WAP_TOOLS_PHP__', true);

function wap_handler ($location="") {
  // first, find out if we have a WAP/WML capable browser
  $browser = CreateObject('PHP.browser_detect');

  // if this not a WAP/WML capable browser, exit cleanly (false)
  if (!$browser->support_wap()) return false;

  // send headers depending on the type of browser
  if ($browser->support_wml()) {
    // else (if WAP/WML capable browser), send headers
    Header("Content-type: text/vnd.wap.wml");
    echo "<?xml version=\"1.0\" ?>\n";
    echo "<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.1//EN\" ".
         "\"http://www.wapforum.org/DTD/wml_1.1.xml\">\n";
  } else if ($browser->support_hdml()) {
    Header("Content-type: text/hdml");
    echo "<hdml version=\"3.0\">\n\n";
  } // end checking for different browser types

  // check if location is empty/not specified. if so, check for
  // proper browser, and return true if it is a WAP/WML capable
  // browser.
  if (empty($location)) {
    if ($browser->support_hdml()) return WAP_HDML;
    if ($browser->support_wml() ) return WAP_WML ;
  } else {
    // otherwise include given page
    if (file_exists($location)) { include ("$location");       }
     else { DIE("wap_handler :: \"$location\" doesn't exist"); }
  } // end checking for empty location
} // end function wap_handler

} // end checking if defined

?>
