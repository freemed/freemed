<?php
 // file: db_maintenance.php3
 // note: where all of the database maintenance routines are
 //       called from, to save space on the main menu
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("global.var.inc");
include ("freemed-functions.inc");

SetCookie ("_ref", $page_name, time()+$_cookie_expire);

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_box_top (_("Database Maintenance"));

 // here is the actual guts of the menu
if (freemed_get_userlevel ($LoginCookie) > $database_level) { 
 echo "
    <$STDFONT_B>

     <A HREF=\"cpt.php3?$_auth&action=view\"
      >"._("CPT Codes")."</A>
     <BR>

     <A HREF=\"cptmod.php3?$_auth&action=view\"
      >"._("CPT Modifiers")."</A>
     <BR>

     <A HREF=\"patient_record_template.php3?$_auth&action=view\"
      >"._("Patient Record Templates")."</A>
     <BR>

     <A HREF=\"diagnosis_family.php3?$_auth&action=view\"
      >"._("Diagnosis Families")."</A>
     <BR>

     <A HREF=\"fixed_forms.php3?$_auth&action=view\"
      >"._("Fixed Forms")."</A>
     <BR>

     <A HREF=\"frmlry.php3?$_auth&action=view\"
      >"._("Formulary")."</A>
     <BR>

     <A HREF=\"icd9.php3?$_auth&action=view\"
      >"._("ICD Codes")."</A>
     <BR>

     <A HREF=\"insco.php3?$_auth&action=view\"
      >"._("Insurance Companies")."</A>
     <BR>

     <A HREF=\"inscogroup.php3?$_auth&action=view\"
      >"._("Insurance Company Groups")."</A>
     <BR>

     <A HREF=\"insurance_modifiers.php3?$_auth&action=view\"
      >"._("Insurance Modifiers")."</A>
     <BR>

     <A HREF=\"internal_service_type.php3?$_auth&action=view\"
      >"._("Internal Service Types")."</A>
     <BR>

     <A HREF=\"patient_status.php3?$_auth&action=view\"
      >"._("Patient Statuses")."</A>
     <BR>

     <A HREF=\"physician.php3?$_auth&action=view\"
      >"._("Physicians")."</A>
     <BR>

     <!-- not ready for prime time yet....
     <A HREF=\"phy_avail_map.php3?$_auth&action=view\"
      >"._("Physician Availability Map")."</A>
     <BR>
     -->

     <A HREF=\"degrees.php3?$_auth&action=view\"
      >"._("Physician Degrees")."</A>
     <BR>

     <A HREF=\"phygroup.php3?$_auth&action=view\"
      >"._("Physician Groups")."</A>
     <BR>

     <A HREF=\"specialties.php3?$_auth\"
      >"._("Physician")." "._("Specialties")."</A>
     <BR>

     <A HREF=\"phystatus.php3?$_auth&action=view\"
      >"._("Physician Statuses")."</A>
     <BR>

     <A HREF=\"facility.php3?$_auth&action=view\"
      >"._("Place of Service")."</A>
     <BR>

     <A HREF=\"questionnaire_template.php3?$_auth&action=view\"
      >"._("Questionnaire Templates")."</A>
     <BR>

     <A HREF=\"room.php3?$_auth&action=view\"
      >"._("Rooms")." (<I>"._("Scheduling Locations")."</I>)</A>
     <BR>

     <A HREF=\"roomequip.php3?$_auth&action=view\"
      >"._("Room Equipment")."</A>
     <BR>

     <A HREF=\"tos.php3?$_auth&action=view\"
      >"._("Type of Service")."</A>
     <BR>

     <A HREF=\"simplerep.php3?$_auth&action=view\"
      >"._("Simple Reports")."</A>
     <BR>

     <!-- doesn't work right now
     <A HREF=\"select_printers.php3?$_auth&action=view\"
      >"._("Printers")."</A>
     <BR>
     -->

    <$STDFONT_E>
  ";
  } else  { 
   echo "
      <P>
      <$HEADERFONT_B>
        "._("You don't have access for this menu.")."
      <$HEADERFONT_E>
      <P>
    ";
  } // end of checking for perms

freemed_display_box_bottom ();
freemed_display_html_bottom ();
freemed_close_db (); // close db

?>
