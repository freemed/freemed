<?php
 // $Id$
 // desc: module loader
 // lic : GPL, v2

include "global.var.inc";
include "freemed-functions.inc";
include "module.php";

// get list of modules
$module_list = new module_list (PACKAGENAME);

// check for module
if (!$module_list->check_for($module)) {
	DIE("module \"$module\" not found");
} // end of checking for module

// load module
execute_module ($module);

?>
