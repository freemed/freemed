<?php
	// $Id$
	// $Author$
	// Defines FreeMED.DynamicModule.* namespace

	// This XML-RPC module handles all dynamic/loadable modules in
	// FreeMED. Individual calls are made by:
	// 	FreeMED.DynamicModule.[call]([function], [params])
	// ex: FreeMED.DyanamicModule.picklist('FacilityModule')

class DynamicModule {

	function picklist ($module, $params=NULL) {
		// Load module list
		$module_list = CreateObject('PHP.module_list', PACKAGENAME);

		// Check for name in hash
		$resolved = check_module($module);
		if (!$resolved) {
			// TODO: Return error
			return false;	
		}

		// Run proper module
		return module_function($module, 'picklist', $params);
	} // end method picklist

} // end class DynamicModule

?>
