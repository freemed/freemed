<?php
 // $Id$
 // note: calendar modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed_open_db ();

//----- Set page title
$page_title = __("Calendar");

//----- Push page onto stack
page_push ();

 // Check for appropriate access level
if (!freemed::user_flag(USER_DATABASE)) {
	$display_buffer .= " <P>".__("You don't have access for this menu.")."</P>\n";
	template_display();
} // end if not appropriate userlevel

// information for module loader
$category = "Calendar";
$module_template = "
	<TR>
	<TD ALIGN=RIGHT>#icon#</TD>
	<TD ALIGN=LEFT><A HREF=\"module_loader.php?module=#class#\"".
	">#name#</A></TD>
	</TR>
";

// module loader
$module_list = CreateObject('PHP.module_list', PACKAGENAME);
if (!$module_list->empty_category($category)) {
	$display_buffer .= "
	<P>
	<CENTER>
	<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=0 VALIGN=MIDDLE
	 ALIGN=CENTER>
	".$module_list->generate_list($category, 0, $module_template)."
    </TABLE>
	</CENTER>
	<P>
	<CENTER>
		<A HREF=\"main.php\"
		>".__("Return to Main Menu")."</A>
	</CENTER>
	<P>
	";
} else {
	$display_buffer .= "
	<P>
	<CENTER>
		".__("There are no report modules present.")."
	</CENTER>
	<P>
	<CENTER>
		<A HREF=\"main.php\"
		>".__("Return to Main Menu")."</A>
	</CENTER>
	<P>
	";
}

template_display ();
?>
