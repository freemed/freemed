<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class InsuranceBilling extends BillingModule {

	var $MODULE_NAME = "Insurance Billing";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = true;
	var $ICON = "img/insurance.gif";
	
	var $MODULE_FILE = __FILE__;

	function InsuranceBilling () {
		// Add appropriate handler information
		$this->_SetHandler('BillingFunctions', 'menu');
		$this->_SetMetaInformation('BillingFunctionName', __("Insurance Billing"));

		// Call parent constructor
		$this->BillingModule();
	} // end constructor InsuranceBilling

	function menu () {
		// Get all associations
		$a = $this->_GetAssociations();

		if ($a == array()) {
			return __("No insurance billing functions are available.");
		}

		$buffer = "
		<div align=\"center\">
		".__("Please choose the type of insurance billing you wish to perform.")."
		</div>
		<br/>
		<table border=\"0\" align=\"center\">
		";
		foreach ($a AS $__blah => $module) {
			// Get associated meta information
			$function = freemed::module_get_meta($module, 'InsuranceBillingTransport');
			$name = freemed::module_get_meta($module, 'InsuranceBillingName');
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
} // end class InsuranceBilling

register_module('InsuranceBilling');

?>
