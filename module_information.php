<?php
  // $Id$
  // note: module information
  // lic : GPL
  
$page_name   = basename($GLOBALS["PHP_SELF"]);
include_once ("lib/freemed.php");

// top of page
freemed_open_db (); // authenticate user

// check for access
$this_user = CreateObject('FreeMED.User');
if (!freemed::user_flag(USER_ADMIN)) {
	$display_buffer .= __("Access Denied");
	template_display();
}
// top
$page_title = __("Module Information");

$module_list = CreateObject(
	'PHP.module_list', 
	PACKAGENAME, 
	array(
		'display_hidden' => true
	)
);
$categories = $module_list->categories();
if ($categories != NULL) {
	// show all modules
	$module_template = "
	<tr>
		<td BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#ffffff\">#name#</FONT></TD>
		<td BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">#author#</FONT></TD>
		<td BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">v#version#</FONT></TD>
		<td BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">#vendor#</FONT></TD>
	</tr><tr>
		<td BGCOLOR=\"#aaaacc\" COLSPAN=4>#description#</TD>
	</tr>
	";
	$display_buffer .= "
	<div ALIGN=\"CENTER\">
	<table BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\" VALIGN=\"MIDDLE\"
	 ALIGN=\"CENTER\" WIDTH=\"80%\">
	";
	foreach ($categories AS $this_category) {
		$module_generated = $module_list->generate_list ($this_category, 0, $module_template);
		$display_buffer .= "
		<tr>
			<td ALIGN=\"CENTER\" VALIGN=\"MIDDLE\" BGCOLOR=\"#bbbbee\"
					COLSPAN=\"4\">
				<b>".prepare($this_category)." (version ".
				$GLOBALS['__phpwebtools']['GLOBAL_CATEGORIES_VERSION'][$this_category].")</b>
			</td>
		</tr>
		";
		$display_buffer .= $module_generated;
	} // end of foreach
	$display_buffer .= "
	</table>
	</div>
	<p/>
	<div ALIGN=\"CENTER\">
	<a HREF=\"admin.php\"
	>".__("Return to Admin Menu")."</a>
	</div>
	";
} else {
	$display_buffer .= "<div ALIGN=\"CENTER\">No categories.</div>\n";
} // end checking for categories

// Display the template
template_display();

?>
