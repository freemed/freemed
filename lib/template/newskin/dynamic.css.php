<?php Header('Content-type: text/css'); ?>
/*
 *	$Id$
 *	$Author$
 *
 *	Menu and skin specific layout... everything else is inheirited from
 *	stylesheet.css
 */
<?php

	// This should *never* be necessary to do, but for some reason
	// browsers never interpret the standards the same way. So for
	// them, we have browser detection information.

	$user_agent = getenv('HTTP_USER_AGENT'); 
	if(ereg('MSIE ([0-9].[0-9]{1,2})',$user_agent)) {
		$IE = true;
	} else {
		$IE = false;
	}

	if(ereg(' Gecko\/([0-9])', $user_agent)) {
		$Gecko = true;
	} else {
		$Gecko = false;
	}

?>

body {
	background: #e5e5f5;
	color: #000000;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	margin-top: 0px;
	margin-left: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	text-decoration: none;
	<?php if ($Gecko) {
		// Weird gecko size adjustments
		print "\theight: 85%;\n";
		print "\twidth: auto;\n";
	} elseif ($IE) {
		// Even weirder IE adjustments
		print "\theight: 80%;\n";
		print "\toverflow: none;\n";
	
	} ?>
}

/* #top: Menu bar container */
#top {
	position: <?php if ($IE) print "absolute"; else print "fixed"; ?>;
	top: 0px;
	border: 0px;
}

/* #bottom: copyright notice container */
#bottom {
	background-color: #ffffff;
	left: 0px;
	bottom: 0px;
	border: 1px solid;
	border-color: #000000 #ffffff #ffffff #ffffff;
	/* This needs to be 'absolute' for IE, but 'fixed' for mozilla. */
	position: <?php if ($IE) print "absolute"; else print "fixed"; ?>;
}

/* div:patient_search - patient search dialog box */
div.patient_search {
	background-image: url('<?php print BASE_URL; ?>/lib/template/newskin/img/marble.jpg');
	background-repeat: repeat-x repeat-y;
	background-attachment: fixed;
	height: 100%;
}

/* span:pageTitle - page title at upper right on menubar */
td.pageTitle {
	right: 10px;
	position: absolute;
	align: left;
}

/* -----------------------------------------------------------------------
   Menu bar system CSS code 
   ----------------------------------------------------------------------- */
div.menuBar {
	font-size: 9pt;
	font-style: normal;
	font-weight: 500;
	color: #ffffff;
	background-color: #aaaaff;
	padding: 2px 2px 2px 2px;
	text-align: left;
	text-decoration: none;
	/* position: relative; */
}

div.menuBar a.menuButton, div.menuBar a.titleButton, div.menuBar a.titleButton:hover {
	font-size: 9pt;
	font-style: normal;
	font-weight: 500;
	color: #000000;
	background-color: transparent;
	border: 1px solid #aaaaff;
	cursor: default;
	margin: 1px;
	padding: 1px;
	position: relative;
	text-decoration: none;
	z-index: 100;
}

div.menuBar a.menuButton:hover {
	background-color: transparent;
	border: 1px outset #000088;
	color: #000000;
	text-decoration: none;
}

div.menuBar a.menuButtonActive {
	background-color: #ccccff;
	border: 1px inset #000088;
	color: #000000;
}

div.menuBar a.menuButtonActive:hover {
	background-color: #ccccff;
	border: 1px inset #000088;
	color: #000000;
}

div.menu {
	font-size: 9pt;
	font-style: normal;
	font-weight: 500;
	color: #000000;
	background-color: #ccccff;
	border: 1px outset #000088;
	padding: 0px 1px 1px 0px;
	position: absolute;
	visibility: hidden;
	z-index: 101;
}

div.menu a.menuItem {
	font-family: helvetica, arial, sans-serif;
	font-size: 9pt;
	font-style: normal;
	font-weight: 500;
	background-color: #ccccff;
	color: #000000;
	cursor: default;
	display: block;
	padding: 1px 1em;
	text-decoration: none;
	white-space: nowrap;
}

div.menu a.menuItem:hover {
	background-color: #9999ff;
	color: #000000;
}

div.menu a.menuItemHighlight {
	background-color: #ccccff;
	color: #000000;
}

div.menu a.menuItem span.menuItemText {}

div.menu a.menuItem span.menuItemArrow {
	margin-right: -.75em;
}

div.menu div.menuItemSep {
	border: 1px inset #000088;
	margin: 4px 2px;
}

/*
 *	Change the main box that everything is in to fill the entire
 *	screen and be more pleasing to the eye.
 */

<?php if ($IE) { ?>
#main {
	margin: 20px 0px 0px 20px;
}
<?php } ?>
#main {
	display: inline;
	top: 20px;
	padding: 5px;
	/* try not to overflow past the edges */
	<?php if ($Gecko) {
		print "\twidth: auto;\n";	
		print "\theight: auto;\n";	
	} elseif ($IE) {
		print "\theight: 90%;\n";
	} else {
		print "\twidth: auto;\n";	
	} ?>
	
	/* Note that there is no background, so that the image comes
	   through from the body tag, since IE and Mozilla do not
	   always render this the same way otherwise. */

	/* Handle scrolling the main body without moving the menubar ... */
	overflow: auto; 
}

/* Since the background is so close to white, need contrasting
   link cover color for the main div */
.main a:hover { color: #5959cc; background-color: #eeeeff; }

