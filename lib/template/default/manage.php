<?php
 // $Id$
 // $Author$
 // note: template for patient management functions
 // lic : GPL, v2

//----- Use "current_patient" SESSION variable if it's there
if (!$id and ($_SESSION['current_patient']>0)) {
	$id = $_SESSION['current_patient'];
}

//----- Load the Patient object
if (!is_object($this_patient)) {
	$this_patient = CreateObject('FreeMED.Patient', $id);
}

//----- Make sure that $patient is also set to this
$patient = $id;

if ($id<1) {
  // if someone needs to 1st go to the patient menu
	$display_buffer .= "
        <p/>
        <div ALIGN=\"CENTER\"><b>".__("You must select a patient.")."</b></div>
        <p/>
        <div ALIGN=\"CENTER\">
        <a HREF=\"patient.php\">".__("Select a Patient")."</a>
        </div>
        <p/>
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
	case "moveup": case "movedown":
	if (file_exists("lib/template/".$template."/manage_move.php"))
		include("lib/template/".$template."/manage_move.php");
	else include("lib/template/default/manage_move.php");
	break; // end moveup/movedown action

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
