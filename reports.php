<?php
 // $Id$
 // note: reports modules
 // lic : GPL

$page_name = basename($GLOBALS["REQUEST_URI"]);
include ("lib/freemed.php");
include ("lib/API.php");

SetCookie ("_ref", $page_name, time()+$_cookie_expire);

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_box_top (_("Reports"));

 // Check for appropriate access level
if (freemed_get_userlevel ($LoginCookie) < $database_level) { 
   echo "
      <P>
      <$HEADERFONT_B>
        "._("You don't have access for this menu.")."
      <$HEADERFONT_E>
      <P>
    ";
	freemed_display_box_bottom();
	freemed_display_html_bottom();
	die("");
} // end if not appropriate userlevel

// information for module loader
$category = "Reports";
$template = "
	<TR>
	<TD ALIGN=RIGHT>#icon#</TD>
	<TD ALIGN=LEFT><A HREF=\"module_loader.php?$_auth&module=#class#\"".
	">#name#</A></TD>
	</TR>
";

// module loader
$module_list = new module_list (PACKAGENAME);
if (!$module_list->empty_category($category)) {
	echo "
	<P>
	<CENTER>
	<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=0 VALIGN=MIDDLE
	 ALIGN=CENTER>
	".$module_list->generate_list($category, 0, $template)."
    </TABLE>
	</CENTER>
	<P>
	<CENTER>
		<$STDFONT_B><A HREF=\"main.php?$_auth\"
		>"._("Return to Main Menu")."</A><$STDFONT_E>
	</CENTER>
	<P>
	";
} else {
	echo "
	<P>
	<CENTER>
		<$STDFONT_B>There are no report modules present.<$STDFONT_E>
	</CENTER>
	<P>
	<CENTER>
		<$STDFONT_B><A HREF=\"main.php?$_auth\"
		>"._("Return to Main Menu")."</A><$STDFONT_E>
	</CENTER>
	<P>
	";
}

freemed_display_box_bottom ();
freemed_display_html_bottom ();
freemed_close_db (); // close db

?>
