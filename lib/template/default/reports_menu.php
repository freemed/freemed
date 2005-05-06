<?php
	// $Id$
	// $Author$

// Information for module loader
$category = "Reports";
$module_template = "
	<tr>
	<td valign=\"top\"><a HREF=\"module_loader.php?module=#class#\"".
	">#name#</a></td>
	<td>#description#</td>
	</tr>
";

// Module loader
$module_list = freemed::module_cache();
if (!$module_list->empty_category($category)) {
	$display_buffer .= "
	<div class=\"section\">".__("Reports")."</div><br/>
	<p/>
	<table align=\"center\" border=\"0\" cellspacing=\"0\" ".
	"cellpadding=\"3\" width=\"80%\">\n".
	"<tr class=\"reverse\">\n".
	"<td class=\"reverse\">".__("Report")."</td>\n".
	"<td class=\"reverse\">".__("Description")."</td>\n".
	"</tr>\n".
	$module_list->generate_list($category, 0, $module_template).
	"</table>
	</div>
	<p/>
	<div ALIGN=\"CENTER\">
		<a HREF=\"main.php\" class=\"button\"
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
		<a HREF=\"main.php\" class=\"button\">".__("Return to Main Menu")."</a>
	</div>
	<p/>
	";
}

?>
