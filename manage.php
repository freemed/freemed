<?php
	// $Id$
	// note: patient management functions -- links to other modules
	// lic : GPL, v2

$page_name = "manage.php";
include_once ("lib/freemed.php");

//----- Set current patient cookie if it's not set...
if ($id != $_SESSION['current_patient']) {
	$_SESSION['current_patient'] = $id;
	SetCookie('current_patient', $id);
}

//----- Push patient onto list
$id = $_REQUEST['id'];
patient_push($id);

//----- Login/authenticate
freemed::connect ();

//----- Determine ID
if (($id<1) AND ($_COOKIE['current_patient']>0)) { $id = $_COOKIE['current_patient']; }
 elseif (($id<1) and ($patient>0))    { $id = $patient;         }

//----- Check ACLs 
//FIXME: remove (this is handled by module now)
//if (!freemed::acl_patient('emr', 'view', $id)) {
//	trigger_error(__("You are not authorized to view patient records."), E_USER_ERROR);
//}

// Check for access to current medical record
if (!freemed::check_access_for_patient($id)) {
	//------HIPAA Logging
	$user_to_log=$_SESSION['authdata']['user'];
	if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"manage.php|user $user_to_log accesses patient $patient failed! user does not have access");}	
	trigger_error(__("You are not authorized to view patient records."), E_USER_ERROR);
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"manage.php|user $user_to_log accesses patient $id");}	

$page_title = __("Manage Patient");

//----- Import template piece
include_once(freemed::template_file('manage.php'));

//----- Display template
template_display();

?>
