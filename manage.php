<?php
 // $Id$
 // note: patient management functions -- links to other modules
 // lic : GPL, v2

$page_name = "manage.php";
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module.php");
include ("lib/module_emr.php");
include ("lib/module_cert.php");
include ("lib/module_emr_report.php");

//----- Set current patient cookie if it's not set...
if ($id != $current_patient)
	$SESSION["current_patient"] = $current_patient = $id;

//----- Push patient onto list
patient_push($id);

//----- Login/authenticate
freemed_open_db ();

//----- Determine ID
if (($id<1) AND ($current_patient>0)) { $id = $current_patient; }
 elseif (($id<1) and ($patient>0))    { $id = $patient;         }

$page_title = _("Manage Patient");

//----- Import template piece
if (file_exists("lib/template/".$template."/manage.php")) {
	include_once ("lib/template/".$template."/manage.php");
} else {
	include_once ("lib/template/default/manage.php");
} // end of importing template piece

freemed_close_db ();
template_display();
?>
