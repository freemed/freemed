<?php
 // $Id$
 // desc: module loader
 // lic : GPL, v2

include "lib/freemed.php";
include "lib/API.php";
include "lib/module.php";

// module loaders
include "lib/module_billing.php";
include "lib/module_edi.php";
include "lib/module_emr.php";
include "lib/module_emr_report.php";
include "lib/module_maintenance.php";
include "lib/module_reports.php";

// get list of modules
$module_list = new module_list (PACKAGENAME);

// check for module
if (!$module_list->check_for($module)) {
	DIE("module \"$module\" not found");
} // end of checking for module

// load module
execute_module ($module);

?>
