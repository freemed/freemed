<?php
	// $Id$
	// $Author$

$page_name = "utilities.php";
include ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//----- Create user object
$this_user = CreateObject('FreeMED.User');

//----- Set page title
$page_title = __("Utilities");

//----- Add page to stack
page_push();

//----- Check for "current_patient" in $_SESSION
if ($_SESSION['current_patient'] != 0) {
	$patient = $_SESSION['current_patient'];
}

$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"utilities.php|user $user_to_log accesses patient $patient");}	

// Deny if no access (check ACLs)
if (!freemed::acl('admin', 'menu')) {
	$display_buffer .= __("Access denied").".<br/>\n";
	template_display();
	die();
}
	
LoadObjectDependency('PHP.module');

switch ($_REQUEST['action']) {
	case 'type':
	// Execute handler
	$module_handlers = freemed::module_handler('Utilities');
	$display_buffer .= module_function($_REQUEST['type'], $module_handlers[strtolower($_REQUEST['type'])]);

	// Display closing information for return to menu
	$display_buffer .= "
	<p/>
	<div align=\"center\">
		<a href=\"utilities.php\" class=\"button\"
		>".__("Return to")." ".
		__("Utilities")."</a>
	</div>
	";
	break; // end case 'type'

	default:
	//----- Determine handlers for billing types
	$type_handlers = freemed::module_handler('Utilities');
	if (!is_array($type_handlers)) {
		$display_buffer .= __("Your FreeMED installation has no utilties handlers defined.")."<br/>\n";
		template_display();
		die();
	} else {
		$display_buffer .= 
		"<div class=\"section\">".__("Utilities")."</div><br/> ".
		"<p/>\n";
	}

	foreach ($type_handlers AS $class => $handler) {
		// Load proper GettextXML definitions for this class
		GettextXML::textdomain(strtolower($class));
		
		// Get title from meta information
		$title = freemed::module_get_meta($class, 'UtilityName');
		$desc = freemed::module_get_meta($class, 'UtilityDescription');
		// Add to the list
		$types[$title] = $class;
		$description[$title] = $desc;

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
	$display_buffer .= "<table align=\"center\" border=\"0\" ".
		"cellspacing=\"0\" cellpadding=\"3\">\n".
		"<th class=\"reverse\">\n".
		"<td class=\"reverse\">".__("Action")."</td>\n".
		"<td class=\"reverse\">".__("Description")."</td>\n".
		"</th>\n";
	foreach ($types AS $name => $link) {
		$display_buffer .= "<tr><td valign=\"top\">".
			( isset($icons[$name]) ?
			"<a href=\"utilities.php?".
			"action=type&type=".urlencode($link)."\"".
			"><img src=\"".$icons[$name]."\" border=\"0\" ".
			"alt=\"\"/></a>" :
			"&nbsp;" ).
			"</td><td valign=\"top\">".
			"<a href=\"utilities.php?".
			"action=type&type=".urlencode($link)."\"".
			">".$name."</a></td>\n".
			"<td valign=\"top\">".$description[$name].
			"</td></tr>\n";
	}
	$display_buffer .= "</table>\n";
	
	break; // end of default action
} // end of master action switch

//----- Finish template display
template_display ();

?>
