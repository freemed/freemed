<?php
 // $Id$
 // note: database maintenance modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module.php");
include ("lib/module_maintenance.php");

SetCookie ("_ref", $page_name, time()+$_cookie_expire);

freemed_open_db ($LoginCookie);
$page_title = _("Database Maintenance");

// information for module loader
$category = "Database Maintenance";
$template = "<A HREF=\"module_loader.php?module=#class#\"".
	">#name#</A><BR>\n";
$template_menubar = "<LI><A HREF=\"module_loader.php?module=#class#\"".
	"><FONT SIZE=\"-3\">#name#</FONT></A>\n";

 // Check for appropriate access level
if (freemed_get_userlevel ($LoginCookie) < $database_level) { 
	$display_buffer .= "
      <P>
      <$HEADERFONT_B>
        "._("You don't have access for this menu.")."
      <$HEADERFONT_E>
      <P>
	";
	template_display();
} // end if not appropriate userlevel

// actual display routine

$display_buffer .= "
	<CENTER>
	<!-- modules that still need to be converted ...

     <A HREF=\"frmlry.php\"
      >"._("Formulary")."</A>
     <BR>

     <A HREF=\"phy_avail_map.php\"
      >"._("Physician Availability Map")."</A>
     <BR>

     <A HREF=\"simplerep.php\"
      >"._("Simple Reports")."</A>
     <BR>

     <A HREF=\"select_printers.php\"
      >"._("Printers")."</A>
     <BR>

     -->

"; // end of static listing

// module loader
$module_list = new module_list (PACKAGENAME, ".db.module.php");
$display_buffer .= $module_list->generate_list($category, 0, $template);

// create menu bar
$menu_bar = "<UL>\n";
$menu_bar .= $module_list->generate_list($category, 0, $template_menubar);
$menu_bar .= "</UL>\n";

// display end of listing
$display_buffer .= "
	</CENTER>
";

freemed_close_db (); // close db
template_display();

?>
