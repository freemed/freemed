<?php
 // $Id$
 // note: help module
 // lic : GPL

$_pg_desc = "[HELP]"; // show that we are a help page...
include_once ("lib/freemed.php");
freemed::connect ();

//----HIPAA Logging
// Doesnt seem needed here...

// Declare help_template_display()
function help_template_display () {
	global $display_buffer;
	global $template;
	foreach ($GLOBALS AS $k => $v) global ${$k};

	if (file_exists("lib/template/".$template."/template.help.php")) {
		include_once("lib/template/".$template."/template.help.php");
	} else {
		include_once("lib/template/default/template.help.php");
	} // end checking for template

	die();
} // end function help_template_display

 //
 //  eventually, we want to be able to call a
 //  statement like include "doc/$page_name.$section.php3"
 //  or something like that to read documentation in...
 //  because they are coded in plain HTML, they can be
 //  linked.

  //----- Build name without .php
$page = str_replace(".php", "", $page_name);

$language = $_SESSION['language'];

  // build helpfile name...
if (empty($page) AND empty($section)) {
	$_help_name = "locale/$language/doc/default.$language.html";
} elseif (!empty($page) AND empty($section)) {
	$_help_name = "locale/$language/doc/$page.$language.html";
} elseif (!empty($page) AND !empty($section)) {
	$_help_name = "locale/$language/doc/$page.$section.$language.html";
} else {
	$_help_name = "locale/$language/doc/default.$language.html";
}

 // if the helpfile doesn't exist, but is enabled, ERROR! out...
if (!file_exists($_help_name)) {
	unset($_help_name); // so that it doesn't come back...
	$page_title = PACKAGENAME." Help System Error";
	$display_buffer .= "
    <B>The requested help file was not found on this<BR>
       system. It is possible that it has not been<BR>
       implemented, or it is missing from your system.<BR>
    </B>
	";
	$display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"help.php\"
    >Go to the Help Page</A>
    </CENTER>
	"; // link back to the main help page
	help_template_display();
} // if the help file does not exist

$page_title =  PACKAGENAME." Help System";

if ($debug) {
	$display_buffer .= "
	page_name = $page<BR>
	section = $section<BR>
	";
} // debug stuff

/*
$display_buffer .= "
	<P>
	<CENTER>
	<B>If this is in a \"child window\",<BR>
	please close it or minimize it to<BR>
	return to </B>
	</CENTER>
";
*/

//----- Display the actual help template
help_template_display();

?>
