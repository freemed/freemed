<?php
 // $Id$
 // $Author$

$page_name = basename($_SERVER["PHP_SELF"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed_open_db ();

//----- Set page title
$page_title = _("Preferences");

//----- Add page to history
page_push();

//----- Create user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

// Check for cancel
if ($submit==_("Cancel")) {
	unset($action);
}

switch ($action) {
	case "passwordform":
	$display_buffer .= "
	<form action=\"".$page_name."\" method=\"POST\">
	<input type=\"HIDDEN\" name=\"action\" value=\"password\">
	<div align=\"center\">
	".html_form::form_table(array(
		_("Current Password") =>
		html_form::password_widget("current_password"),

		_("New Password") =>
		html_form::password_widget("new_password")
	))."
	</div>
	<div align=\"center\">
	<input type=\"submit\" name=\"submit\" value=\""._("Update")."\"/>
	<input type=\"submit\" name=\"submit\" value=\""._("Cancel")."\"/>
	</div>
	</form>
	";
	break; // end passwordform

	case "password":
	// Check for password being correct
	if (!$this_user->checkPassword($current_password) or
			($this_user->user_number == 1)) {
		// Do nothing
	} else {
		// Change password
		$result = $sql->query($sql->update_query(
			"user",
			array("userpassword" => $new_password),
			array("id" => $this_user->user_number)
		));
	}
	$refresh = $page_name;
	template_display();
	break;

	case "templateform":
	// Include proper template file
	if (file_exists("./lib/template/".$template."/id.php")) {
		include_once ("./lib/template/".$template."/id.php");
	} else {
		include_once ("./lib/template/default/id.php");
	}
	
	// Form header
	$display_buffer .= "
		<form action=\"preferences.php\" method=\"post\">
		<input type=\"hidden\" name=\"action\" value=\"template\"/>
	";
	// Loop through template options
	foreach ($TEMPLATE_OPTIONS AS $k => $v) {
		// Handle selects
		if (is_array($v['options'])) {
			$form_parts[$v['name']] = html_form::select_widget(
				$v['var'],
				$v['options']
			);
		}
	}
	// Display form_parts
	$display_buffer .= html_form::form_table($form_parts)."\n";
	$display_buffer .= "
		<div align=\"center\">
		<input type=\"submit\" name=\"submit\" class=\"button\" value=\"".
			_("Set Options")."\"/>
		<input type=\"submit\" name=\"submit\" class=\"button\" value=\"".
			_("Cancel")."\"/>
		</div>
		</form>
	";
	break; // end templateform


	case "template":
	// Include proper template file
	if (file_exists("./lib/template/".$template."/id.php")) {
		include_once ("./lib/template/".$template."/id.php");
	} else {
		include_once ("./lib/template/default/id.php");
	}
	
	// Loop through variables, and set them properly
	foreach ($TEMPLATE_OPTIONS AS $k => $v) {
		if (isset(${$v['var']})) {
			// Keep cookie for a year (find better way to do this)
			SetCookie($v['var'], ${$v['var']}, time()+(60*60*24*365));
		}
	}

	// And use header to move back to action=(nothing)
	$refresh = "preferences.php";
	break; // end template


	default: // display menu
	$display_buffer .= "
	<div align=\"center\">
	<a href=\"preferences.php?action=passwordform\"
	>"._("Change Password")."</a>
	</div>

	<div align=\"center\">
	<a href=\"preferences.php?action=templateform\"
	>"._("Change Template Options")."</a>
	</div>

	<p/>

	<div align=\"center\">
	<a href=\"main.php\">"._("Return to Main Menu")."</a>
	</div>
	";
	break;
} // end action

//----- Display template
template_display();

?>
