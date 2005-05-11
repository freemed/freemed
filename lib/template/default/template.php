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
if (!$GLOBALS['__freemed']['no_template_display']) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<html>
<head>
	<title><?php print prepare(PACKAGENAME) . " v". DISPLAY_VERSION . " - " .
		( !empty($GLOBALS['page_title']) ? $GLOBALS['page_title']." - " : "" ) .
		prepare(INSTALLATION); ?></title>
	<meta HTTP-EQUIV="Content-Type" 
		CONTENT="text/html; CHARSET=<?php print $__ISO_SET__; ?>">
<?php
//----- Handle refresh
if (isset($refresh)) {
?>
	<meta HTTP-EQUIV="REFRESH" CONTENT="0;URL=<?php print $refresh; ?>">
<?php
} else if (isset($GLOBALS['__freemed']['automatic_refresh'])) { // automatic refreshes
?>
	<meta HTTP-EQUIV="REFRESH" CONTENT="<?php
	print $GLOBALS['__freemed']['automatic_refresh'];
	?>;URL=<?php print basename($_SERVER['REQUEST_URI']); ?>">
<?php
} // end handle refresh
?>
	<meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT" />
	<meta http-equiv="Pragma" content="no-cache" />
	<link REL="StyleSheet" TYPE="text/css"
		HREF="lib/template/default/stylesheet.css" />
<?php if ($GLOBALS['__freemed']['header']) { print $GLOBALS['__freemed']['header']; } ?>
<?php include_once(freemed::template_file('key_bindings.php')); ?>
</head>

<body BGCOLOR="#ffffff" TEXT="#555555"
 ALINK="#000000" VLINK="#000000" LINK="#000000"
 MARGINWIDTH="0" MARGINHEIGHT="0" LEFTMARGIN="0" RIGHTMARGIN="0"
 <?php
	// Check for close_on_load
	if ($GLOBALS['__freemed']['close_on_load']) {
		print "onLoad=\"window.close(); return true;\"";
	} elseif (!empty($GLOBALS['__freemed']['on_load'])) {
		print " onLoad=\"".$GLOBALS['__freemed']['on_load']."(); return true;\"";
	}
 ?>>

<!-- tooltips -->
<div ID="dhtmltooltip"></div>
<script type="text/javascript" src="lib/template/default/tooltip.js"></script>

<!-- define main table -->

<table WIDTH="100%" CELLSPACING="0" CELLPADDING="0"
 style="margin-top: 0px; margin-left: 0px;"
 VALIGN="TOP" ALIGN="CENTER">

<?php

// Check for _collapse_menu
if ($_GET['_collapse_menubar'] == '1') {
	$_SESSION['collapsed_menu'] = true;
} elseif ($_GET['_collapse_menubar'] == '0') {
	$_SESSION['collapsed_menu'] = false;
}

if ($_SESSION['collapsed_menu']) {
	$GLOBALS['__freemed']['no_menu_bar'] = true;
}

// To conserve space, turn off the header bar if menu bar
if ($GLOBALS['__freemed']['no_menu_bar']) {
?>

<!-- top/header bar -->

<tr>
	<td COLSPAN="1" ALIGN="LEFT" VALIGN="TOP">
		<!-- <I>Banner goes here.</I> -->
		<img SRC="lib/template/default/banner.<?php
		print IMAGE_TYPE; ?>"
		 WIDTH="300" HEIGHT="40" ALT="freemed" />
	</td>
	<td COLSPAN="1" ALIGN="RIGHT" VALIGN="MIDDLE">
		<?php
		// Create URL
		$_expand_url = basename($_SERVER['REQUEST_URI']);
		if (strpos($_expand_url, '_collapse_menubar=1') === false) {
			$_expand_url = str_replace('_collapse_menubar=1',
				'', $_expand_url);
			$_expand_url = str_replace('?&', '?', $_expand_url);
			if (strpos($_expand_url, '?') === false) {
				$_expand_url .= '?_collapse_menubar=0';
			} else {
				$_expand_url .= '&_collapse_menubar=0';
			}
		} else {
			$_expand_url = str_replace('_collapse_menubar=1',
				'_collapse_menubar=0', $_expand_url);
		}

		// Check for _SESSION['collapsed_menu']
		if ($_SESSION['collapsed_menu']) {
		print "<a href=\"".$_expand_url."\" ".
			"onMouseOver=\"window.status='".__("Show Menubar")."'; ".
			"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\" ".
			"style=\"border: 1px solid; background: #ffffff; ".
			"color: #000000; text-decoration: none; padding: 0px; ".
			"font-size: 8pt;\" ".
			">".__("Show Menubar")."</a>\n";
		}
		?>
	</td>
</tr>

<?php } ?>

<tr>

<?php
//----- Check to see if we skip displaying this
if (!$GLOBALS['__freemed']['no_menu_bar']) {
	$_hide_url = basename($_SERVER['REQUEST_URI']);
	if (strpos($_hide_url, '_collapse_menubar=0') === false) {
		$_hide_url = str_replace('_collapse_menubar=0',
			'', $_hide_url);
		$_hide_url = str_replace('?&', '?', $_expand_url);
		if (strpos($_hide_url, '?') === false) {
			$_hide_url .= '?_collapse_menubar=1';
		} else {
			$_hide_url .= '&_collapse_menubar=1';
		}
	} else {
		$_hide_url = str_replace('_collapse_menubar=0',
			'_collapse_menubar=1', $_expand_url);
	}
?>

	<td COLSPAN="1" VALIGN="TOP" ALIGN="RIGHT" WIDTH="250">

	<!-- menu bar -->
	<table WIDTH="100%" CELLSPACING="0" CELLPADDING="0"
	 CLASS="menubar" VALIGN="TOP" ALIGN="CENTER">
	<tr><td VALIGN="TOP" ALIGN="CENTER" CLASS="menubar_title">
		<b><?php print INSTALLATION; ?></b>&nbsp;
		<a style="border: 1px solid; background: #ffffff; color: #000000; text-decoration: none; padding: 0px; font-size: 8pt;" href="<?php print $_hide_url; ?>"
		onMouseOver="window.status='<?php
		print __("Hide Menu"); ?>'; return true;"
		onMouseOut="window.status=''; return true;">X</a>
		<br/>
		<small><?php print PACKAGENAME." v".DISPLAY_VERSION; ?></small>
<?php
//----- Add page title text if it exists
if (isset($GLOBALS['page_title'])) {
	print "
		<br/>
		".prepare($GLOBALS['page_title'])."
	";
} // end isset page_title
?>
	</td></tr>
<?php
//----- Create user object if it doesn't exist and we're logged in
if (freemed::verify_auth() and !is_object($this_user)) {
	$this_user = CreateObject('FreeMED.User');
} // end check to see if we're logged in

//----- Generate session information portion of the bar
if (is_object($this_user)) {
	print "<tr><td VALIGN=\"TOP\" ALIGN=\"LEFT\" CLASS=\"menubar_info\">\n";
	print "<center>\n";
	print __("User")." : ".$this_user->getDescription()."\n";
	print "</center>\n";
	print "</td></tr>\n";
} // end checking if this_user exists
?>
	<tr><td VALIGN="TOP" ALIGN="LEFT" CLASS="menubar_items">

<?php
	// Include menubar items
	include_once("lib/template/default/menubar.php");

?>
	</td></tr>
	<tr><td VALIGN="BOTTOM" ALIGN="RIGHT" CLASS="menubar_items">
	<img src="lib/template/default/img/menubar_lower_right.gif" border="0"
		alt=""/></td></tr></table>
	
	</td>

<?php } else { /* if there is *no* menu bar */ ?>

<!-- nothing -->

<?php } /* end of checking for no menu bar */ ?>
	<td COLSPAN="<?php 
		print ( ($GLOBALS['__freemed']['no_menu_bar']) ? '2' : '1' );
		?>" VALIGN="TOP" ALIGN="CENTER">

	<!-- body -->

		<table WIDTH="100%" CELLSPACING="0" CELLPADDING="3"
		 CLASS="mainbox">
		<tr><td VALIGN="MIDDLE" ALIGN="CENTER">
<?php
	// Actual content display
	print $display_buffer;
?>
		&nbsp;
		</td></tr></table>
	</td>
</tr>

<!-- master table end -->
</table>

<?php
	// Handle HTMLarea objects, if they exist
if (is_array($GLOBALS['__freemed']['rich_text_areas'])) {
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/htmlarea.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/lang/en.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/dialog.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/popupwin.js\"></script>\n";
	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/template/default/htmlarea/htmlarea.css\" />\n";
	print "<script type=\"text/javascript\">\n".
		"HTMLArea.loadPlugin(\"TableOperations\");\n".
		"HTMLArea.loadPlugin(\"SpellChecker\");\n".
		"function initEditor () {\n";
	$count = 0;
	foreach ($GLOBALS['__freemed']['rich_text_areas'] as $k => $v) {
		print "editor".$count." = new HTMLArea(\"".$v."\");\n".
			"editor".$count.".registerPlugin(\"TableOperations\");\n".
			"editor".$count.".registerPlugin(\"SpellChecker\");\n".
			"editor".$count.".generate();\n";
		$count += 1;
	}
	print "return false;\n".
		"}\n".
		"</script>\n";
}
?>

<!-- copyright notice -->
<p/>
<div NAME="copyright" ALIGN="CENTER">
	&copy; 1999-<?php print date("Y"); ?> by the FreeMED Software Foundation
</div>

<?php if ($GLOBALS['__freemed']['footer']) { print $GLOBALS['__freemed']['footer']; } ?>

</body>
</html>
<?php
} else {
	// Show what we have, if that's what we're doing
	print "<html>\n".
		"<head>\n".
		"<title>".prepare(PACKAGENAME)." v".DISPLAY_VERSION." - ".
		( !empty($GLOBALS['page_title']) ? $GLOBALS['page_title']." - " : "" ) .
		prepare(INSTALLATION)."</title>\n".
		"<link REL=\"StyleSheet\" TYPE=\"text/css\" ".
		"HREF=\"lib/template/default/stylesheet.css\"/>\n".
		"<div ID=\"dhtmltooltip\"></div>\n".
		"<script type=\"text/javascript\" src=\"lib/template/default/tooltip.js\"></script>\n";
	if ($GLOBALS['__freemed']['header']) { print $GLOBALS['__freemed']['header']; }
	if ($refresh) { print "<meta HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=".$refresh."\">\n"; }
	if ($GLOBALS['__freemed']['automatic_refresh']) {
		print "<meta HTTP-EQUIV=\"REFRESH\" CONTENT=\"".
			$GLOBALS['__freemed']['automatic_refresh'].";URL=".
			basename($_SERVER['REQUEST_URI'])."\">\n";
	}
	// Include key bindings
	include_once(freemed::template_file('key_bindings.php'));
	print "</head>\n".
		"<body";
	// Check for close_on_load
	if ($GLOBALS['__freemed']['close_on_load']) {
		print " onLoad=\"window.close(); return true;\"";
	} elseif (!empty($GLOBALS['__freemed']['on_load'])) {
		print " onLoad=\"".$GLOBALS['__freemed']['on_load']."(); return true;\"";
	}
	print ">\n";
	// Handle HTMLarea objects, if they exist
	if (is_array($GLOBALS['__freemed']['rich_text_areas'])) {
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/htmlarea.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/lang/en.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/dialog.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/popupwin.js\"></script>\n";
	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/template/default/htmlarea/htmlarea.css\" />\n";
	print "<script type=\"text/javascript\">\n".
		"HTMLArea.loadPlugin(\"TableOperations\");\n".
		"HTMLArea.loadPlugin(\"SpellChecker\");\n".
		"function initEditor () {\n";
	$count = 0;
	foreach ($GLOBALS['__freemed']['rich_text_areas'] as $k => $v) {
		print "editor".$count." = new HTMLArea(\"".$v."\");\n".
			"editor".$count.".registerPlugin(\"TableOperations\");\n".
			"editor".$count.".registerPlugin(\"SpellChecker\");\n".
			"editor".$count.".generate();\n";
		$count += 1;
	}
	print "return false;\n".
		"}\n".
		"</script>\n";
	}
	print $display_buffer;
	if ($GLOBALS['__freemed']['footer']) { print $GLOBALS['__freemed']['footer']; }
	print "</body>\n".
		"</html>\n";
} // end checking for "no_template_display"
?>
