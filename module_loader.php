<?php
 // $Id$
 // desc: module loader
 // lic : GPL, v2

include_once ("lib/freemed.php");

//----- Load object dependencies for module methods
LoadObjectDependency('PHP.module');

//----- Get list of modules
$module_list = CreateObject('PHP.module_list', PACKAGENAME);

//----- Check for module
if (!$module_list->check_for($module)) {
	$display_buffer .= "module \"$module\" not found";
	template_display();
} // end of checking for module

//----- Load specified module
execute_module ($module);

//----- Display template
template_display();

?>
