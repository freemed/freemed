<?php
 // $Id$
 // note: database maintenance modules
 // lic : GPL, v2

$page_name = basename($GLOBALS["REQUEST_URI"]);

include ("global.var.inc");
include ("freemed-functions.inc");
include ("module.php");

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_box_top ("Database Maintenance");

$category = "Database Maintenance";

$template = "<A HREF=\"module_loader.php?$_auth&module=#class#\"".
      ">#name#</A><BR>\n";

$module_list = new module_list (PACKAGENAME);

echo "<CENTER>\n";
echo $module_list->generate_list($category, 0, $template);
echo "</CENTER>\n";

freemed_display_box_bottom ();
freemed_display_html_bottom ();
freemed_close_db (); // close db

?>
