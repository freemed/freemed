<?php
  // $Id$
  // note: module information
  // lic : GPL
  
$page_name   = basename($GLOBALS["REQUEST_URI"]);

include ("lib/freemed.php");
include ("lib/API.php");

freemed_open_db ($LoginCookie); // authenticate user
freemed_display_html_top ();  // generate top of page

// check for access
$this_user = new User ($LoginCookie);
if ($this_user->getLevel() < $admin_level) die ("Access Denied");

// top
freemed_display_box_top (_("Module Information"));

$module_list = new module_list (PACKAGENAME);
$categories = $module_list->categories();
if ($categories != NULL) {
	echo "
	<CENTER>
	<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE ALIGN=CENTER>
	";
	foreach ($categories AS $this_category) {
		echo "
		<TR>
			<TD ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#bbbbee\" COLSPAN=4>
				<B>".prepare($this_category)." (version ".
				$GLOBAL_CATEGORIES_VERSION["$this_category"].")</B>
			</TD>
		</TR>
		";
		// show all modules
		$template = "
		<TR>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#ffffff\">#name#</FONT></TD>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">#author#</FONT></TD>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">v#version#</FONT></TD>
			<TD BGCOLOR=\"#aaaacc\"><FONT COLOR=\"#eeeeee\">#vendor#</FONT></TD>
		</TR><TR>
			<TD BGCOLOR=\"#aaaacc\" COLSPAN=4>#description#</TD>
		</TR>
		";
		echo $module_list->generate_list ($this_category, 0, $template);
	} // end of foreach
	echo "
	</TABLE>
	</CENTER>
	<P>
	<CENTER>
	<A HREF=\"admin.php?$_auth\"
	>"._("Return to Admin Menu")."</A>
	</CENTER>
	";
} else {
	echo "<P>No categories.<P>\n";
} // end checking for categories


// bottom
freemed_display_box_bottom (); // display bottom of the box
freemed_close_db ();
freemed_display_html_bottom ();

?>
