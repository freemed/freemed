<?php
  // $Id$
  // note: module information
  // lic : GPL
  
$page_name   = basename($GLOBALS["PHP_SELF"]);
include_once ("lib/freemed.php");

// top of page
freemed::connect (); // authenticate user

//---- HIPAA logging
// Dont seem needed here...

// check for access
$this_user = CreateObject('FreeMED.User');
if (!freemed::acl('admin', 'menu')) {
	trigger_error(__("Access Denied"), E_USER_ERROR);
}
// top
$page_title = __("Module Information");

$module_list = CreateObject(
	'PHP.module_list', 
	PACKAGENAME, 
	array(
		'display_hidden' => true,
		'cache_file' => 'data/cache/modules'
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
	".template::link_button(
		__("Return to Administration Menu"),
		"admin.php"
	)."
	</div>
	";
} else {
	$display_buffer .= "<div ALIGN=\"CENTER\">".__("No categories.")."</div>\n";
} // end checking for categories

// Display the template
template_display();

?>
