<?php
 // $Id$
 // $Author$

include_once("lib/template/default/help.macros.php");
 
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

<TR>
	<TD COLSPAN="1" VALIGN="TOP" ALIGN="RIGHT" WIDTH="20%">

	<!-- menu bar -->
<?php
//----- Check to see if we skip displaying this
if (!$GLOBALS['__freemed']['no_menu_bar']) {
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
			onClick="window.close();"
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
			"section=".urlencode($section)."&framed=yes";
		?>" WIDTH="450" HEIGHT="350" SCROLL="AUTO"></IFRAME>
<?php
} else {
	// Actual content display
	print "<DIV CLASS=\"interior\">\n";
	if (isset($_help_name)) {
		// Read in the entire file instead of:
		//require($_help_name);
		$help_buffer = "";

		// Read entire file into buffer
		$fp = fopen($_help_name, "r");
		while (!feof($fp)) $help_buffer .= fgets($fp, 4096);
		fclose($fp);

		//----- Perform substitutions...
	
		// Split into an array
		$help_array = explode("%%", $help_buffer);
	
		// Loop through array
		for ($i=0; $i<count($help_array); $i++) {
			// If it's odd, you have to process it
			if ( ($i % 2) == 1 ) {
				// Odds need substitution, so...

				// Break by commas
				$this_element = explode(",", $help_array[$i]);

				// Check count
				switch(count($this_element)) {
					case 1: // page_name
					print "<A HREF=\"help.php?page_name=".
					urlencode($this_element[0]).
					"&framed=yes\" TARGET=\"help_frame\"".
					"><I>?</I></A>";
					break; // end 1 param
				
					case 2: // title,page_name
					print "<A HREF=\"help.php?page_name=".
					urlencode($this_element[1]).
					"&framed=yes\" TARGET=\"help_frame\"".
					"><I>".prepare($this_element[0]).
					"</I></A>";
					break; // end 2 param
				
					case 3: // title,page_name,section
					print "<A HREF=\"help.php?page_name=".
					urlencode($this_element[1]).
					"&section=".urlencode($this_element[2]).
					"&framed=yes\" TARGET=\"help_frame\"".
					"><I>".prepare($this_element[0]).
					"</I></A>";
					break; // end 3 param
				
					default:
					print("ERROR! Contact your FreeMED ".
					"maintainer!<BR>\n");
					break;
				} // end switch
			} else { // checking for odds
				// If it's even, display it

				// Pull into local variable
				$this_part = $help_array[$i];

				// Perform replacements
				foreach ($help_replacements AS $k => $v) {
					$this_part = str_replace (
						$k,
						$v,
						$this_part
					);
				} // end replacements (foreach)

				// Actual display
				print $this_part;
			} // end checking for odds
		} // end looping through array
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
