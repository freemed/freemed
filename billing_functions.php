<?php
	// $Id$
	// $Author$
	// lic : GPL, v2

$page_name = "billing_functions.php";
include ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//----- Create user object
$this_user = CreateObject('FreeMED.User');

//----- Set page title
$page_title = __("Billing Functions");

//----- Add page to stack
page_push();

//----- Check for "current_patient" in $_SESSION
if ($_SESSION['current_patient'] != 0) {
	$patient = $_SESSION['current_patient'];
}

$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"billingfunctions.php|user $user_to_log accesses patient $patient");}	

$patient_information = "<b>".__("NO PATIENT SPECIFIED")."</b>\n";
if ($patient>0) {
	$this_patient = CreateObject('FreeMED.Patient', $patient);
	$patient_information = freemed::patient_box ($this_patient);
} // if there is a patient

// Deny if no access
if (!freemed::user_flag(USER_DATABASE)) {
	$display_buffer .= __("Access denied").".<br/>\n";
	template_display();
	die();
}
	
// This section is the start of "Billing v1.0". We are using "handlers"
// to assign different types of billing, and from there, we will 

LoadObjectDependency('PHP.module');

switch ($_REQUEST['action']) {
	case 'type':
	// Execute handler
	$module_handlers = freemed::module_handler('BillingFunctions');
	$display_buffer .= module_function($_REQUEST['type'], $module_handlers[strtolower($_REQUEST['type'])]);

	// Display closing information for return to menu
	$display_buffer .= "
	<p/>
	<div align=\"center\">
		<a href=\"billing_functions.php\" class=\"button\"
		>".__("Return to")." ".
		__("Billing Functions")."</a>
	</div>
	";
	break; // end case 'type'

	default:
	//----- Determine handlers for billing types
	$type_handlers = freemed::module_handler('BillingFunctions');
	if (!is_array($type_handlers)) {
		$display_buffer .= __("Your FreeMED installation has no billing handlers defined. This should not happen.")."<br/>\n";
		template_display();
		die();
	} else {
		$display_buffer .= 
		"<b>".__("Welcome to the FreeMED Billing System!")."</b><br/> ".
		__("Please select the type of billing you wish to perform.").
		"<p/>\n";
	}

	foreach ($type_handlers AS $class => $handler) {
		// Load proper GettextXML definitions for this class
		GettextXML::textdomain(strtolower($class));
		
		// Get title from meta information
		$title = freemed::module_get_meta($class, 'BillingFunctionName');
		// Add to the list
		$types[__($title)] = $class;

		if ($icon = freemed::module_get_value($class, 'ICON')) {
			$icons[__($title)] = $icon;
		} else {
			unset($icons[__($title)]);
		}
	}

	// Sort & unique values
	$types = array_unique($types);
	ksort($types);

	// Display
	$display_buffer .= "<table align=\"center\" border=\"0\">\n";
	foreach ($types AS $name => $link) {
		$display_buffer .= "<tr><td>".
			( isset($icons[$name]) ?
			"<a href=\"billing_functions.php?".
			"action=type&type=".urlencode($link)."\"".
			"><img src=\"".$icons[$name]."\" border=\"0\" ".
			"alt=\"\"/></a>" :
			"&nbsp;" ).
			"</td><td><a href=\"billing_functions.php?".
			"action=type&type=".urlencode($link)."\"".
			">".$name."</a></td></tr>\n";
	}
	$display_buffer .= "</table>\n";
	
	break; // end of default action
} // end of master action switch

//----- Finish template display
template_display ();

?>
