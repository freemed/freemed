<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class StatementBilling extends BillingModule {

	var $MODULE_NAME = "Statement Billing";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = true;
	var $ICON = "img/reports.gif";
	
	var $MODULE_FILE = __FILE__;

	function StatementBilling () {
		// Add appropriate handler information
		$this->_SetHandler('BillingFunctions', 'menu');
		$this->_SetMetaInformation('BillingFunctionName', __("Patient Statements"));

		// Call parent constructor
		$this->BillingModule();
	} // end constructor StatementBilling

	function menu () {
		// Get all associations
		$a = $this->_GetAssociations();

		if ($a == array()) {
			return __("No statement billing functions are available.");
		}

		$buffer = "
		<table border=\"0\" align=\"center\">
		";
		foreach ($a AS $__blah => $module) {
			// Get associated meta information
			$function = freemed::module_get_meta($module, 'StatementBillingFunction');
			$name = freemed::module_get_meta($module, 'StatementBillingName');
			$buffer .= "
			<tr><td>
			<a href=\"module_loader.php?module=".urlencode($module)."&action=".urlencode($function)."\"
			>".__($name)."</a>
			</td></tr>
			";
		}
		$buffer .= "
		</table>
		";
		return $buffer;
	}
} // end class StatementBilling

register_module('StatementBilling');

?>
