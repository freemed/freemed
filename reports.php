<?php
 // $Id$
 // note: reports modules
 // lic : GPL

$page_name = basename($GLOBALS["PHP_SELF"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed_open_db ();

//----- Set page title
$page_title = __("Reports");

//----- Add page to history
page_push();

//----- Create user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

 // Check for appropriate access level
if (!freemed::user_flag(USER_DATABASE)) {
   $display_buffer .= "
	<p/>
        ".__("You don't have access for this menu.")."
	<p/>
    ";
	template_display();
} // end if not appropriate userlevel

// information for module loader
$category = "Reports";
$module_template = "
	<tr>
	<td ALIGN=\"RIGHT\">#icon#</td>
	<td ALIGN=\"LEFT\"><a HREF=\"module_loader.php?module=#class#\"".
	">#name#</a></td>
	</tr>
";

// module loader
$module_list = CreateObject('PHP.module_list', PACKAGENAME);
if (!$module_list->empty_category($category)) {
	$display_buffer .= "
	<p/>
	<div ALIGN=\"CENTER\">
	<table BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"0\" VALIGN=\"MIDDLE\"
	 ALIGN=\"CENTER\">
	".$module_list->generate_list($category, 0, $module_template)."
	</table>
	</div>
	<p/>
	<div ALIGN=\"CENTER\">
		<a HREF=\"main.php\"
		>".__("Return to Main Menu")."</a>
	</div>
	<p/>
	";
} else {
	$display_buffer .= "
	<p/>
	<div ALIGN=\"CENTER\">
		".__("There are no report modules present.")."
	</div>
	<p/>
	<div ALIGN=\"CENTER\">
		<a HREF=\"main.php\">".__("Return to Main Menu")."</a>
	</div>
	<p/>
	";
}

//----- Show template
template_display();

?>
