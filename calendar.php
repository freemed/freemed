<?php
 // $Id$
 // note: calendar modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/module.php");
include ("lib/module_calendar.php");

freemed_open_db ();
$page_title = _("Calendar");
page_push ();

 // Check for appropriate access level
if (freemed_get_userlevel () < $database_level) { 
   $display_buffer .= "
      <P>
        "._("You don't have access for this menu.")."
      <P>
    ";
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
$module_list = new module_list (PACKAGENAME,".calendar.module.php");
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
		>"._("Return to Main Menu")."</A>
	</CENTER>
	<P>
	";
} else {
	$display_buffer .= "
	<P>
	<CENTER>
		"._("There are no report modules present.")."
	</CENTER>
	<P>
	<CENTER>
		<A HREF=\"main.php\"
		>"._("Return to Main Menu")."</A>
	</CENTER>
	<P>
	";
}

freemed_close_db ();
template_display ();

?>
