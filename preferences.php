<?php
 // $Id$
 // $Author$

$page_name = basename($GLOBALS["PHP_SELF"]);
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

	default: // display menu
	$display_buffer .= "
	<div align=\"center\">
	<a href=\"preferences.php?action=passwordform\"
	>"._("Change Password")."</a>
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
