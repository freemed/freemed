<?php
 // $Id$
 // $Author$
 // note: template for patient management functions
 // lic : GPL, v2

//----- Use "current_patient" SESSION variable if it's there
if (!$id and ($SESSION["current_patient"]>0))
	$id = $SESSION["current_patient"];

//----- Load the Patient object
$this_patient = CreateObject('FreeMED.Patient', $id);

//----- Make sure that $patient is also set to this
$patient = $id;

if ($id<1) {
  // if someone needs to 1st go to the patient menu
      $display_buffer .= "
        <BR><BR>
        <CENTER><B>"._("You must select a patient.")."</B></CENTER>
        <BR><BR>
        <CENTER>
        <A HREF=\"patient.php\">"._("Select a Patient")."</A>
        </CENTER>
        <BR><BR>
      ";
      template_display();
} // if there is an ID specified

//----- Switch management functions depending on arguments given
switch ($action) {
	// Configuration
	case "config":
	if (file_exists("lib/template/".$template."/manage_config.php"))
		include("lib/template/".$template."/manage_config.php");
	else include("lib/template/default/manage_config.php");
	break; // end config action

	// Remove
	case "remove":
	if (file_exists("lib/template/".$template."/manage_remove.php"))
		include("lib/template/".$template."/manage_remove.php");
	else include("lib/template/default/manage_remove.php");
	break; // end remove action

	// Default action is display/list
	default:
	if (file_exists("lib/template/".$template."/manage_main.php"))
		include("lib/template/".$template."/manage_main.php");
	else include("lib/template/default/manage_main.php");
	break; // end default action
} // end of switch
    
?>
