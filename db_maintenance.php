<?php
 // $Id$
 // note: database maintenance modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module.php");
include ("lib/module_maintenance.php");

//----- Login and authenticate
freemed_open_db ();

//----- Set page title
$page_title = _("Database Maintenance");

//----- Add page to stack
page_push();

// information for module loader
$category = "Database Maintenance";
$module_template = "<A HREF=\"module_loader.php?module=#class#\"".
	">#name#</A><BR>\n";

 // Check for appropriate access level
if (freemed_get_userlevel ($LoginCookie) < $database_level) { 
	$display_buffer .= "
      <P>
        "._("You don't have access for this menu.")."
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
$display_buffer .= $module_list->generate_list($category, 0, $module_template);

// create menu bar
if (!is_array($menu_bar)) $menu_bar[] = NULL;
$menu_bar = array_merge (
	$menu_bar,
	$module_list->generate_array(
		$category,
		0,
		"#name#", // key template
		"module_loader.php?module=#class#" // value template
	)
);

// display end of listing
$display_buffer .= "
	</CENTER>
";

freemed_close_db (); // close db
template_display();

?>
