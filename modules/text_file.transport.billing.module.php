<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class BillingTextFileTransport extends BillingModule {

	var $MODULE_NAME = "Text File Billing Transport";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_DESCRIPTION = "
		Basic module to provide HCFA 1500 text rendering
		capabilities for offices which possess no billing
	";

	var $MODULE_FILE = __FILE__;

	function BillingTextFileTransport ( ) {
		// GettextXML:
		//	__("Text File Insurance Billing")
		//	__("Text File Billing Transport")

		// Set appropriate associations
		$this->_SetAssociation('InsuranceBilling');
		$this->_SetMetaInformation('InsuranceBillingName', 'Text File Insurance Billing');
		$this->_SetMetaInformation('InsuranceBillingTransport', 'transport');

		// Call parent constructor
		$this->BillingModule();
	} // end constructor BillingTextFileTransport

	function transport () {
		global $display_buffer;
		Header('Content-type: text/plain');

		// Use HCFA1500 rendering class to create bills
		$hcfa_renderer = CreateObject (
			'FreeMED.HCFA1500Renderer'
		);
		$bill = $hcfa_renderer->GenerateForms( );

		// Output to browser and croak.
		print $bill;
		die();
	} // end function BillingTextFileTransport->transport

} // end class BillingTextFileTransport

register_module('BillingTextFileTransport');

?>
