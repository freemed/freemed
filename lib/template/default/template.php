<?php
 // $Id$
 // $Author$

// Check for refresh location
/*
if (isset($refresh)) {
	Header("Location: ".$refresh_location);
}
*/

// Check for avoiding template
if (!$no_template_display) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
	<TITLE><?php print prepare(PACKAGENAME) . " v". VERSION . " - " .
		( !empty($page_title) ? $page_title." - " : "" ) .
		prepare(INSTALLATION); ?></TITLE>
	<META HTTP-EQUIV="Content-Type" 
		CONTENT="text/html; CHARSET=<?php print $__ISO_SET__; ?>">
<?php
//----- Handle refresh
if (isset($refresh)) {
?>
	<META HTTP-EQUIV="REFRESH" CONTENT="0;URL=<?php print $refresh; ?>">
<?php
} else if (isset($automatic_refresh)) { // handle automatic refreshes
?>
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $automatic_refresh; ?>;URL=<?php print $PHP_SELF; ?>">
<?php
} // end handle refresh
?>
	<LINK REL="StyleSheet" TYPE="text/css"
		HREF="lib/template/default/stylesheet.css">
</HEAD>

<BODY BGCOLOR="#ffffff" TEXT="#555555"
 ALINK="#000000" VLINK="#000000" LINK="#000000"
 MARGINWIDTH="0" MARGINHEIGHT="0" LEFTMARGIN="0" RIGHTMARGIN="0">

<!-- define main table -->

<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="2"
 VALIGN="MIDDLE" ALIGN="CENTER">

<!-- top/header bar -->

<TR>
	<TD COLSPAN="2" ALIGN="LEFT" VALIGN="TOP">
		<!-- <I>Banner goes here.</I> -->
		<IMG SRC="lib/template/default/banner.<?php
		print IMAGE_TYPE; ?>"
		 WIDTH="300" HEIGHT="40" ALT="freemed">
	</TD>
</TR>

<TR>
	<TD COLSPAN="1" VALIGN="TOP" ALIGN="RIGHT" WIDTH="20%">

	<!-- menu bar -->
<?php
//----- Check to see if we skip displaying this
if (!$no_menu_bar) {
?>
	<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="2"
	 CLASS="menubar" VALIGN="TOP" ALIGN="CENTER">
	<TR><TD VALIGN="TOP" ALIGN="CENTER" CLASS="menubar_title">
		<B><?php print INSTALLATION; ?></B>
		<BR>
		<SMALL><?php print PACKAGENAME." v".VERSION; ?></SMALL>
<?php
//----- Add page title text if it exists
if (isset($page_title)) {
	print "
		<BR>
		".prepare($page_title)."
	";
} // end isset page_title
?>
	</TD></TR>
<?php
//----- Create user object if it doesn't exist and we're logged in
if (freemed_verify_auth() and !is_object($this_user)) {
	$this_user = new User;
} // end check to see if we're logged in

//----- Generate session information portion of the bar
if (is_object($this_user)) {
	print "<TR><TD VALIGN=\"TOP\" ALIGN=\"LEFT\" CLASS=\"menubar_info\">\n";
	print "<CENTER>\n";
	print _("User description")." : ".$this_user->getDescription()."\n";
	print "&nbsp;\n";
	print _("User level")." : ".$this_user->getLevel()."\n";
	print "</CENTER>\n";
	print "</TD></TR>\n";
} // end checking if this_user exists
?>
	<TR><TD VALIGN="TOP" ALIGN="LEFT" CLASS="menubar_items">

<?php
	// Include menubar items
	include_once("lib/template/default/menubar.php");

?>
	</TD></TR></TABLE>

<?php } else { /* if there is *no* menu bar */ ?>

&nbsp;

<?php } /* end of checking for no menu bar */ ?>
	</TD> <TD COLSPAN="1" VALIGN="TOP" ALIGN="CENTER">

	<!-- body -->

		<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="3"
		 CLASS="mainbox">
		<TR><TD VALIGN="MIDDLE" ALIGN="CENTER">
<?php
	// Actual content display
	print $display_buffer;
?>
		&nbsp;
		</TD></TR></TABLE>
	</TD>
</TR>

<!-- master table end -->
</TABLE>

<!-- copyright notice -->
<P>
<DIV NAME="copyright" ALIGN="CENTER">
	&copy; 1999-<?php print date("Y"); ?> by the FreeMED Software Foundation
</DIV>

</BODY>
</HTML>
<?php
} // end checking for "no_template_display"
?>
