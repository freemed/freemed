<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.BillingModule');

class MCSIBillingTransport extends BillingModule {

	var $MODULE_NAME = "MSCI Billing Transport";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";

	var $MODULE_FILE = __FILE__;

	function MCSIBillingTransport ( ) {
		// GettextXML:
		//	__("MCSI Electronic Clearinghouse")

		// Set configuration variables (username/password)
		$this->_SetMetaInformation('global_config_vars', array (
			'MCSI_u', 'MCSI_p', 'MCSI_f'
		));
		$this->_SetMetaInformation('global_config', array (
			__("MCSI Login") =>
			'html_form::text_widget("MCSI_u", 20, 50)',
			__("MCSI Password") =>
			'html_form::password_widget("MCSI_p", 20, 50)',
			__("Format") =>
			'html_form::select_widget("MCSI_f", array ( '.
				'"NSF" => "NSF", '.
				'"PRN" => "PRN" ))'
		));

		// Set appropriate associations
		$this->_SetAssociation('InsuranceBilling');
		$this->_SetMetaInformation('InsuranceBillingName', 'MCSI Electronic Clearinghouse');
		$this->_SetMetaInformation('InsuranceBillingTransport', 'transport');

		// Call parent constructor
		$this->BillingModule();
	} // end constructor MCSIBillingTransport

	function transport () {
		global $display_buffer;

		$display_buffer .= __("Creating electronic bill")." ... \n";

		// Generate the URL
		$url = "https://".
			urlencode(freemed::config_value('MCSI_u')).":".
			urlencode(freemed::config_value('MCSI_p'))."@".
			"trymcs.com/cgi-win/cgi.plc";
			
		// Decide what format we're sending this in
		switch (freemed::config_value('MCSI_f')) {
			case 'NSF':
			// Use NSF class to create the actual bill
			// $nsf = CreateObject('FreeMED.NSF');
			// $bill = $nsf->generate();
			$bill = 'NSF STUB';
			break; // end NSF renderer

			case 'PRN':
			default:
			// Use HCFA1500 rendering class to create bills
			$hcfa_renderer = CreateObject (
				'FreeMED.HCFA1500Renderer'
			);
			$bill = $hcfa_renderer->GenerateForms( );
			break; // end default PRN renderer	
		} // end switch

		$display_buffer .= "<b>".__("done")."</b><br/>\n";
		
		$display_buffer .= "<i>DEBUG: [[ ".prepare($bill)." ]]</i><br/>\n";
		// Move rendered bill to temporary file
		$tmpfile = tempnam('/tmp', 'mcsi');
		$fp = fopen ($tmpfile, 'w') or die('Could not open temporary file!');
		fwrite($fp, $bill);
		fclose($fp);

		// Use cURL to HTTPS POST the file, depending on the proper
		// transport

		// First, run through basic auth
		$login = $this->_curl_open(
			"https://trymcs.com/cgi-win/cgi.plc",
			"function=03"
		);
	
		// Decide if we logged on correctly
		if (strpos($login, 'Authentication Received') !== false) {
			$display_buffer .= __("Logged onto MCSI server as user:")." <b>".freemed::config_value('MCSI_u')."</b><br/>\n";
		} else {
			$display_buffer .= __("Unable to logon to the MCSI server with the username and password provided.");
			return false;
		}

		// Now, try to submit the actual form
		$submittal = $this->_curl_open(
			"https://trymcs.com/cgi-win/cgi.plc",
			"function=01&intype=1&@$tmpfile"
		);
		if (strpos($submittal, 'Error in Uploading a file!') !== false) {
			$display_buffer .= __("Unable to upload the claims.");
			return false;
		}

		// Display success message if we've gotten this far
		$display_buffer .= __("Successfully sent claims.")."<br/>\n";
		// DEBUG:
		//$display_buffer .= "<br/> ( SUBMITTAL: ".htmlentities($submittal)."<br/>\n )<br/>\n";

		// TODO: Store ID #'s etc about this form
		// TODO: Get result information about this form.

		// Destroy temporary file
		$display_buffer .= __("Removing temporary holding files")." ... ";
		unlink($tmpfile);
		$display_buffer .= "<b>".__("done")."</b><br/>\n";
	} // end function MCSIBillingTransport->transport

	function _curl_open ($url, $post) {
		//print "( using $url )";
		$ch = curl_init();    
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
		//curl_setopt($ch, CURLOPT_HEADER, 1); 
		//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_REFERER,
			"https://trymcs.com/mainssl.html");
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 100); 

		// Set authentication
		curl_setopt($ch, CURLOPT_USERPWD, 
			freemed::config_value('MCSI_u').":".
			freemed::config_value('MCSI_p')
		);

		// Set cookie jar properly
		curl_setopt($ch, CURLOPT_COOKIEJAR, "data/cache/MCSI_COOKIE");
		curl_setopt($ch, CURLOPT_COOKIEFILE, "data/cache/MCSI_COOKIE");
		
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		//	'function' => '01',
		//	'intype' => '1',
		//	'filenami' => "@$tmpfile"
		//ob_start();
		$result = curl_exec($ch); 
		//ob_end_clean();
		curl_close($ch);
		return $result;
	} // end method _curl_open

} // end class MCSIBillingTransport

register_module('MCSIBillingTransport');

?>
