<?php
 // $Id$
 // note: test module loader
 // lic : GPL, v2

$page_name = basename($GLOBALS["REQUEST_URI"]);

include ("global.var.inc");
include ("freemed-functions.inc");
include ("module.php");

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_box_top ("Test Module Menu");

$template = "<A HREF=\"module_loader.php?$_auth&module=#class#\"".
      ">#name#</A><BR>\n";

$module_list = new module_list (PACKAGENAME);

echo $module_list->generate_list("Test Category", 0, $template);

freemed_display_box_bottom ();
freemed_display_html_bottom ();
freemed_close_db (); // close db

?>
