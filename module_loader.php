<?php
 // $Id$
 // desc: module loader
 // lic : GPL, v2

include_once "lib/freemed.php";
include_once "lib/API.php";
include_once "lib/module.php";

// module loaders
include_once "lib/module_admin.php";
include_once "lib/module_billing.php";
include_once "lib/module_cert.php";
include_once "lib/module_edi.php";
include_once "lib/module_emr.php";
include_once "lib/module_emr_report.php";
include_once "lib/module_maintenance.php";
include_once "lib/module_reports.php";
include_once "lib/module_calendar.php";

// get list of modules
$module_list = new module_list (PACKAGENAME);

// check for module
if (!$module_list->check_for($module)) {
	DIE("module \"$module\" not found");
} // end of checking for module

// load module
execute_module ($module);

?>
