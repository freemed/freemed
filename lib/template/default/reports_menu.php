<?php
	// $Id$
	// $Author$

// Information for module loader
$category = "Reports";
$module_template = "
	<tr>
	<td ALIGN=\"RIGHT\">#icon#</td>
	<td ALIGN=\"LEFT\"><a HREF=\"module_loader.php?module=#class#\"".
	">#name#</a></td>
	</tr>
";

// Module loader
$module_list = CreateObject(
	'PHP.module_list',
	PACKAGENAME,
	array(
		'cache_file' => 'data/cache/modules'
	)
);
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

?>
