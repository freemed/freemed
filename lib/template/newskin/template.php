<?php
 // $Id$
 // $Author$

/*
// Check for fscked up gecko rendering engine prior to 20031030
// which causes the menus not to work. Default back to the default
// template quasi-seamlessly if this happens, based on user agent.
$_ua = getenv('HTTP_USER_AGENT').' ';
if (eregi('Gecko/', $_ua)) {
	$gecko_pos = strpos($_ua, 'Gecko/');
	$next_space = strpos($_ua, ' ', $gecko_pos);
	$version = substr($_ua, $gecko_pos+6,
		($next_space - $gecko_pos)-6);
	//die ( "version = -".$version."-");
	if ($version < '20031030') {
		include "lib/template/default/template.php";
		die();
	}
}
*/

//----- Create user object if it doesn't exist and we're logged in
if ($_SESSION['authdata'] and !is_object($this_user)) {
	$this_user = CreateObject('FreeMED.User');
} // end check to see if we're logged in

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

	<!-- compliance patch for microsoft browsers -->
	<!--[if lt IE 7]><script src="lib/template/default/ie7/ie7-standard-p.js" type="text/javascript"></script><![endif]-->

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
	<link REL="StyleSheet" TYPE="text/css"
		HREF="lib/template/newskin/stylesheet.css" />
	<link REL="StyleSheet" TYPE="text/css"
		HREF="lib/template/newskin/dynamic.css.php" />
	<link rel="stylesheet" href="lib/template/newskin/layersmenu-newskin.css" type="text/css"></link>
<?php if ($GLOBALS['__freemed']['header']) { print $GLOBALS['__freemed']['header']; } ?>
<?php include_once(freemed::template_file('key_bindings.php')); ?>
<?php if (is_object($this_user)) { print $this_user->faxNotify(); } ?>
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

<!-- menu -->

<div id="top" align="left" style="width: 100%;" class="menuBar">
<?php if (!$GLOBALS['__freemed']['no_menu_bar']) {
	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" ".
		"width=\"100%\">\n";
		print "<tr><td align=\"left\" valign=\"top\"><span>".
		"<img src=\"img/freemed_logo_16x16.png\" border=\"0\" ".
		"width=\"16\" height=\"16\" alt=\"\" /></span>\n".
		"<span><b>".PACKAGENAME." v".DISPLAY_VERSION."</b></span></td>\n";
	print "<td valign=\"top\">";
	include "lib/template/newskin/menu.php";
	if ( ($help_url = help_url()) != "help.php" ) {
		print "</td><td align=\"left\" valign=\"top\">\n";
		print "\t<a HREF=\"#\" ".
		"onClick=\"window.open('".$help_url."', 'Help', ".
		"'width=600,height=400,resizable=yes');\" ".
		"onMouseOver=\"window.status='".__("Help")."'; ".
		"return true;\" ".
		"onMouseOut=\"window.status=''; return true;\">".
		__("Help")."</a>\n";
	} // end checking for help.php

	print "</td><td align=\"right\" valign=\"top\">".prepare($GLOBALS['page_title'])."&nbsp;&nbsp;</td>";
	print "</tr>";
	print "</table>\n";
} else { ?>
	<b><?php print PACKAGENAME." v".DISPLAY_VERSION.
		( $GLOBALS['page_title'] ? ' - '.$GLOBALS['page_title'] : '' );
		?></b>
<!-- Move main body down a little bit... -->
<?php } ?>
</div>

<!-- body -->
<br/><br/>

<div class="main" id="main">
	<?php print $display_buffer; ?>
</div>

<br/><br/>

<?php
	// Handle HTMLarea objects, if they exist
if (is_array($GLOBALS['__freemed']['rich_text_areas'])) {
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/htmlarea.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/lang/en.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/dialog.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/popupwin.js\"></script>\n";
	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/template/default/htmlarea/htmlarea.css\" />\n";
	print "<script type=\"text/javascript\">\n".
		"function initEditor () {\n";
	$count = 0;
	print "//HTMLArea.loadPlugin(\"TableOperations\");\n".
		"HTMLArea.loadPlugin(\"SpellChecker\");\n";
	foreach ($GLOBALS['__freemed']['rich_text_areas'] as $k => $v) {
		print "editor".$count." = new HTMLArea(\"".$v."\");\n".
			"//editor".$count.".registerPlugin(\"TableOperations\");\n".
			"//editor".$count.".registerPlugin(\"SpellChecker\");\n".
			"editor".$count.".generate();\n";
		$count += 1;
	}
	//print "var x = alert('built htmlareas');\n".
	print "return true;\n".
		"}\n";
	print "\n\ninitEditor();\n";
	print "</script>\n";
}
?>

<!-- copyright notice -->
<br/><br/>
<div id="bottom" align="CENTER" style="width: 100%;">
<span align="left">
	<small>&copy; 1999-<?php print date("Y"); ?> by the FreeMED Software Foundation</small>
</span>
<span align="center">
<?php
//----- Generate session information portion of the bar
if (is_object($this_user)) {
	print "<big>|</big> ".__("User")." : ".$this_user->getDescription().
		"\n";
	if ($this_user->newMessages()) {
		print "<big>|</big> ".
			"<a href=\"messages.php\">".
			"<img src=\"img/messages_small.gif\" alt=\"\" ".
			"width=\"16\" height=\"16\" border=\"0\"/></a>\n";
	}
} else {
	print "&nbsp;\n";
} // end checking if this_user exists
?>
</span>
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
		"HREF=\"lib/template/newskin/stylesheet.css\"/>\n".
		"<link REL=\"StyleSheet\" TYPE=\"text/css\" ".
		"HREF=\"lib/template/newskin/dynamic.css.php\" />\n".
		"<!-- compliance patch for microsoft browsers -->\n".
		"<!--[if lt IE 7]><script src=\"lib/template/default/ie7/ie7-standard-p.js\" type=\"text/javascript\"></script><![endif]-->\n";

	if ($GLOBALS['__freemed']['header']) { print $GLOBALS['__freemed']['header']; }
	if (is_array($GLOBALS['__freemed']['rich_text_areas'])) {
		print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/htmlarea.js\"></script>\n";
		print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/lang/en.js\"></script>\n";
		print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/dialog.js\"></script>\n";
		print "<script type=\"text/javascript\" src=\"lib/template/default/htmlarea/popupwin.js\"></script>\n";
		print "<link rel=\"stylesheet\" type=\"text/css\" href=\"lib/template/default/htmlarea/htmlarea.css\" />\n";
		print "<script type=\"text/javascript\">\n".
			"function initEditor () {\n";
		$count = 0;
		print "//HTMLArea.loadPlugin(\"TableOperations\");\n".
			"//HTMLArea.loadPlugin(\"SpellChecker\");\n";
		foreach ($GLOBALS['__freemed']['rich_text_areas'] as $k => $v) {
			print "editor".$count." = new HTMLArea(\"".$v."\");\n".
				"//editor".$count.".registerPlugin(\"TableOperations\");\n".
			"//editor".$count.".registerPlugin(\"SpellChecker\");\n".
			"editor".$count.".generate();\n";
			$count += 1;
		}
		//print "var x = alert('built htmlareas');\n".
		print "return true;\n".
		"}\n";
		print "\n\ninitEditor();\n";
		print "</script>\n";
		}
	// Add key bindings
	include_once(freemed::template_file('key_bindings.php'));
	print "</head>\n".
		"<body";
	// Check for close_on_load
	if ($GLOBALS['__freemed']['close_on_load']) {
		print " onLoad=\"window.close(); return true;\"";
	} elseif (!empty($GLOBALS['__freemed']['on_load'])) {
		print " onLoad=\"".$GLOBALS['__freemed']['on_load']."(); return true;\"";
	}
	print "><div class=\"main\">\n";
	print $display_buffer;
	print "</div>\n";
	if ($GLOBALS['__freemed']['footer']) { print $GLOBALS['__freemed']['footer']; }
	print "</body></html>\n";
} // end checking for "no_template_display"
?>
