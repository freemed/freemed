<?php
 // $Id$
 // note: database maintenance modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module_emr.php");
include ("lib/module_maintenance.php");

SetCookie ("_ref", $page_name, time()+$_cookie_expire);

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_box_top (_("Database Maintenance"));

// information for module loader
$category = "Database Maintenance";
$template = "<A HREF=\"module_loader.php?$_auth&module=#class#\"".
	">#name#</A><BR>\n";

 // Check for appropriate access level
if (freemed_get_userlevel ($LoginCookie) < $database_level) { 
   echo "
      <P>
      <$HEADERFONT_B>
        "._("You don't have access for this menu.")."
      <$HEADERFONT_E>
      <P>
    ";
	freemed_display_box_bottom();
	freemed_display_html_bottom();
	die("");
} // end if not appropriate userlevel

// actual display routine

echo "
	<CENTER>
    <$STDFONT_B>

	<!-- modules that still need to be converted ...

     <A HREF=\"frmlry.php3?$_auth\"
      >"._("Formulary")."</A>
     <BR>

     <A HREF=\"phy_avail_map.php3?$_auth\"
      >"._("Physician Availability Map")."</A>
     <BR>

     <A HREF=\"simplerep.php3?$_auth\"
      >"._("Simple Reports")."</A>
     <BR>

     <A HREF=\"select_printers.php3?$_auth\"
      >"._("Printers")."</A>
     <BR>

     -->

"; // end of static listing

// module loader
$module_list = new module_list (PACKAGENAME);
echo $module_list->generate_list($category, 0, $template);

// display end of listing
echo "
    <$STDFONT_E>
	</CENTER>
";

freemed_display_box_bottom ();
freemed_display_html_bottom ();
freemed_close_db (); // close db

?>
