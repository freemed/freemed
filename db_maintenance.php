<?php
 // $Id$
 // $Author$
 // note: database maintenance modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");

//----- Login and authenticate
freemed::connect ();

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"db_maintenance.php|user $user_to_log views database manager");}	


//----- Set page title
$page_title = __("Support Data");

//----- Add page to stack
page_push();

// information for module loader
$category = "Support Data";
$module_template = "<a HREF=\"module_loader.php?module=#class#\"".
	">#name#</a><br/>\n";

 // Check for appropriate access level
if (!freemed::user_flag(USER_DATABASE)) {
	$display_buffer .= "
	<p/>
        ".__("You don't have access for this menu.")."
	<p/>
	";
	template_display();
} // end if not appropriate userlevel

// actual display routine

$display_buffer .= "<div ALIGN=\"CENTER\">\n\n";

// module loader
$module_list = CreateObject(
	'PHP.module_list',
	PACKAGENAME,
	array(
		'cache_file' => 'data/cache/modules'
	)
);
$all_modules = $module_list->generate_array(
	$category,
	0,
	"#name#",
	$module_template
);

// Check for number of modules
if (is_array($all_modules)) {
	$size = count($all_modules);
	if ($size > 10) {
		$display_buffer .= "<table BORDER=\"0\" CELLSPACING=\"0\" ".
			"CELLPADDING=\"2\">\n";
		$display_buffer .= "<tr><td VALIGN=\"TOP\">\n";
		$count = 0;
		foreach ($all_modules AS $k => $v) {
			if ($count==ceil($size/2)) {
				$display_buffer .= "</td><td VALIGN=\"TOP\">\n";
			}
			$display_buffer .= $v;
			$count++;
		}
		$display_buffer .= "</td></tr>\n";
		$display_buffer .= "</table>\n";
	} else {
		// Default, plain listing behavior
		$display_buffer .= $module_list->generate_list(
			$category,
			0,
			$module_template
		);
	}
}

// create menu bar
if (!is_array($menu_bar)) $menu_bar[] = NULL;
$menu_bar = array_merge (
	$menu_bar,
	$module_list->generate_array(
		$category,
		0,
		"#name#", // key template
		"module_loader.php?module=#class#" // value template
	)
);

// display end of listing
$display_buffer .= "
	</div>
";

template_display();

?>
