<?php
 // $Id$
 // $Author$
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
	<TITLE><?php print prepare(PACKAGENAME) . " v". VERSION . " - " .
		( !empty($page_title) ? $page_title." - " : "" ) .
		prepare(INSTALLATION); ?></TITLE>
	<META HTTP-EQUIV="Content-Type" 
		CONTENT="text/html; CHARSET=<?php print $__ISO_SET__; ?>">
	<LINK REL="StyleSheet" TYPE="text/css"
		HREF="lib/template/default/stylesheet.help.css">
</HEAD>

<BODY>

<?php
//----- Check for skipping everything (for IFRAMES) -----
if ($framed!="yes") {
?>

<!-- define main table -->

<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="2"
 VALIGN="MIDDLE" ALIGN="CENTER">

<!-- top/header bar -->

<!--
<TR>
	<TD COLSPAN="2" ALIGN="LEFT" VALIGN="TOP">
		<B>Help</B>
	</TD>
</TR>
-->

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
	<TR><TD VALIGN="TOP" ALIGN="LEFT" CLASS="menubar_items">
		<UL>
		<LI><A HREF="help.php?page_name=main.php&framed=yes"
			TARGET="help_frame"
			><?php print _("Main Menu"); ?></A>
		<LI><A HREF="#"
			onClick="this.close();"
			><?php print _("Close this Window"); ?></A>
		</UL>
	</TD></TR></TABLE>

<?php } else { /* if there is *no* menu bar */ ?>

&nbsp;

<?php } /* end of checking for no menu bar */ ?>
	</TD> <TD COLSPAN="1" VALIGN="TOP" ALIGN="CENTER">

	<!-- body -->

		<TABLE WIDTH="100%" CELLSPACING="0" CELLPADDING="3">
		<TR><TD VALIGN="TOP" ALIGN="LEFT">
		
		<!-- actual framed content -->
		<IFRAME NAME="help_frame" SRC="help.php?<?php
			print "page_name=".urlencode($page_name)."&".
			"framed=yes";
		?>" WIDTH="450" HEIGHT="350" SCROLL="AUTO"></IFRAME>
<?php
} else {
	// Actual content display
	print "<DIV CLASS=\"interior\">\n";
	if (isset($_help_name)) {
		require($_help_name);
	} else {
		print $display_buffer;
	}
	print "</DIV>\n";
} // end checking for "framed"

if ($framed!="yes") {
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

<?php
//----- End of checking if framed
}
?>

</BODY>
</HTML>
