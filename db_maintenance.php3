<?php
  # file: db_maintenance.php3
  # note: where all of the database maintenance routines are
  #       called from, to save space on the main menu
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

  $page_name = "db_maintenance.php3";
  include ("global.var.inc");
  include ("freemed-functions.inc");

  SetCookie ("_ref", $page_name, time()+$_cookie_expire);

  freemed_open_db ($LoginCookie);
  freemed_display_html_top ();
  freemed_display_banner ();
  freemed_display_box_top ("$Database_Maintenance", $_ref, $page_name);

   // here is the actual guts of the menu
  if (freemed_get_userlevel ($LoginCookie) > $database_level) { 
   echo "
    <$STDFONT_B>

     <A HREF=\"cpt.php3?$_auth&action=view\"
      >$CPT_Procedural_Codes</A>
     <BR>

     <A HREF=\"cptmod.php3?$_auth&action=view\"
      >$CPT_Procedural_Modifiers</A>
     <BR>

     <A HREF=\"patient_record_template.php3?$_auth&action=view\"
      >$Custom_Patient_Record_Templates</A>
     <BR>

     <A HREF=\"diagnosis_family.php3?$_auth&action=view\"
      >$Diagnosis_Families</A>
     <BR>

     <A HREF=\"fixed_forms.php3?$_auth&action=view\"
      >$Fixed_Forms</A>
     <BR>

     <A HREF=\"frmlry.php3?$_auth&action=view\"
      >$Formulary_Pharmacy_Module</A>
     <BR>

     <A HREF=\"icd9.php3?$_auth&action=view\"
      >$ICD_Codes</A>
     <BR>

     <A HREF=\"insco.php3?$_auth&action=view\"
      >$Insurance_Companies</A>
     <BR>

     <A HREF=\"inscogroup.php3?$_auth&action=view\"
      >$Insurance_Company_Groups</A>
     <BR>

     <A HREF=\"insurance_modifiers.php3?$_auth&action=view\"
      >Insurance Company Modifiers</A>
     <BR>

     <A HREF=\"internal_service_type.php3?$_auth&action=view\"
      >Internal Service Type</A>
     <BR>

     <A HREF=\"patient_status.php3?$_auth&action=view\"
      >Patient Status</A>
     <BR>

     <A HREF=\"physician.php3?$_auth&action=view\"
      >$Physicians</A>
     <BR>

     <!-- not ready for prime time yet....
     <A HREF=\"phy_avail_map.php3?$_auth&action=view\"
      >Physician Availability Map</A>
     <BR>
     -->

     <A HREF=\"degrees.php3?$_auth&action=view\"
      >$Physician_Degrees</A>
     <BR>

     <A HREF=\"phygroup.php3?$_auth&action=view\"
      >$Physician_Groups</A>
     <BR>

     <A HREF=\"specialties.php3?$_auth\"
      >$Physician_Specialties</A>
     <BR>

     <A HREF=\"phystatus.php3?$_auth&action=view\"
      >$Physician_Statuses</A>
     <BR>

     <A HREF=\"facility.php3?$_auth&action=view\"
      >$Place_of_Service_Facilities</A>
     <BR>

     <A HREF=\"questionnaire_template.php3?$_auth&action=view\"
      >Questionnaire Templates</A>
     <BR>

     <A HREF=\"room.php3?$_auth&action=view\"
      >$Rooms (<I>$Scheduling_Locations</I>)</A>
     <BR>

     <A HREF=\"roomequip.php3?$_auth&action=view\"
      >Room Equipment Prototypes</A>
     <BR>

     <A HREF=\"tos.php3?$_auth&action=view\"
      >$Type_of_Service</A>
     <BR>

     <A HREF=\"simplerep.php3?$_auth&action=view\"
      >$Templates_for_Simple_Reports</A>
     <BR>

     <!-- doesn't work right now
     <A HREF=\"select_printers.php3?$_auth&action=view\"
      >$Selectable_Printers</A>
     <BR>
     -->

    <$STDFONT_E>
  ";
  } else  { 
   echo "
      <P>
      <$HEADERFONT_B>
        $You_dont_have_access_for_this_menu
      <$HEADERFONT_E>
      <P>
    ";
  } // end of checking for perms

  freemed_display_box_bottom ();
  freemed_display_html_bottom ();
  freemed_close_db (); // close db

?>
