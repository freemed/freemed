<?php
 // $Id$
 // note: database maintenance modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");

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

     <A HREF=\"cpt.php3?$_auth\"
      >"._("CPT Codes")."</A>
     <BR>

     <A HREF=\"diagnosis_family.php3?$_auth\"
      >"._("Diagnosis Families")."</A>
     <BR>

     <A HREF=\"fixed_forms.php3?$_auth\"
      >"._("Fixed Forms")."</A>
     <BR>

     <A HREF=\"frmlry.php3?$_auth\"
      >"._("Formulary")."</A>
     <BR>

     <A HREF=\"icd9.php3?$_auth\"
      >"._("ICD Codes")."</A>
     <BR>

     <A HREF=\"insco.php3?$_auth\"
      >"._("Insurance Companies")."</A>
     <BR>

     <A HREF=\"inscogroup.php3?$_auth\"
      >"._("Insurance Company Groups")."</A>
     <BR>

     <!-- not ready for prime time yet....
     <A HREF=\"phy_avail_map.php3?$_auth\"
      >"._("Physician Availability Map")."</A>
     <BR>
     -->

     <A HREF=\"facility.php3?$_auth\"
      >"._("Place of Service")."</A>
     <BR>

     <A HREF=\"questionnaire_template.php3?$_auth\"
      >"._("Questionnaire Templates")."</A>
     <BR>

     <A HREF=\"room.php?$_auth\"
      >"._("Rooms")." (<I>"._("Scheduling Locations")."</I>)</A>
     <BR>

     <A HREF=\"simplerep.php3?$_auth\"
      >"._("Simple Reports")."</A>
     <BR>

     <!-- doesn't work right now
     <A HREF=\"select_printers.php3?$_auth\"
      >"._("Printers")."</A>
     <BR>
     -->

	<B>Dynamic Modules:</B><BR>

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
