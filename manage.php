<?php
 // $Id$
 // note: patient management functions -- links to other modules
 // lic : GPL, v2

$page_name = "manage.php";
include ("lib/freemed.php");

//----- Set current patient cookie if it's not set...
if ($id != $_SESSION['current_patient']) {
	$_SESSION['current_patient'] = $id;
	SetCookie('current_patient', $id);
}

//----- Push patient onto list
patient_push($id);

//----- Login/authenticate
freemed_open_db ();

//----- Determine ID
if (($id<1) AND ($current_patient>0)) { $id = $current_patient; }
 elseif (($id<1) and ($patient>0))    { $id = $patient;         }

// Check for access to current medical record
if (!freemed::check_access_for_patient($id)) {
	trigger_error("User not authorized for this function", E_USER_ERROR);
}

$page_title = _("Manage Patient");

//----- Import template piece
if (file_exists("lib/template/".$template."/manage.php")) {
	include_once ("lib/template/".$template."/manage.php");
} else {
	include_once ("lib/template/default/manage.php");
} // end of importing template piece

//----- Display template
template_display();

?>
