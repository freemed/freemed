<?php
  // $Id$
  // note: module information
  // lic : GPL
  
$page_name   = basename($GLOBALS["REQUEST_URI"]);

include_once ("lib/freemed.php");
include_once ("lib/API.php");
include_once ("lib/module.php");

// module types to include ...
include_once ("lib/module_billing.php");
include_once ("lib/module_calendar.php");
include_once ("lib/module_cert.php");
include_once ("lib/module_edi.php");
include_once ("lib/module_emr.php");
include_once ("lib/module_emr_report.php");
include_once ("lib/module_maintenance.php");
include_once ("lib/module_reports.php");

// top of page
freemed_open_db (); // authenticate user

// check for access
$this_user = new User ();
if ($this_user->getLevel() < $admin_level) {
	$display_buffer .= _("Access Denied");
	template_display();
}
// top
$page_title = _("Module Information");

$module_list = new module_list (PACKAGENAME);
$categories = $module_list->categories();
if ($categories != NULL) {
	$display_buffer .= "
	<CENTER>
	<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE ALIGN=CENTER
	 WIDTH=\"80%\">
	";
	foreach ($categories AS $this_category) {
		$display_buffer .= "
		<TR>
			<TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#bbbbee\" COLSPAN=4>
				<B>".prepare($this_category)." (version ".
				$GLOBAL_CATEGORIES_VERSION["$this_category"].")</B>
			</TD>
		</TR>
		";
		// show all modules
		$module_template = "
		<TR>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#ffffff\">#name#</FONT></TD>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">#author#</FONT></TD>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">v#version#</FONT></TD>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">#vendor#</FONT></TD>
		</TR><TR>
			<TD BGCOLOR=\"#aaaacc\" COLSPAN=4>#description#</TD>
		</TR>
		";
		$display_buffer .= $module_list->generate_list ($this_category, 0, $module_template);
	} // end of foreach
	$display_buffer .= "
	</TABLE>
	</CENTER>
	<P>
	<CENTER>
	<A HREF=\"admin.php\"
	>"._("Return to Admin Menu")."</A>
	</CENTER>
	";
} else {
	$display_buffer .= "<P>No categories.<P>\n";
} // end checking for categories


// bottom
template_display();

?>
