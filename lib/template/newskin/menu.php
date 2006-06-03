<?php
	// $Id$
	// $Author$

// Hack to make sure PEAR is in the path
ini_set('include_path', ini_get('include_path').':'.dirname(dirname(dirname(__FILE__))).'/pear');

$GLOBALS['__freemed']['layermenu'] = '';

function menu_item($text, $link, $depth) {
	for ($i = 1; $i <= $depth; $i++) {
		$GLOBALS['__freemed']['layermenu'] .= '.';
	}
	$GLOBALS['__freemed']['layermenu'] .= '|'.$text.'|'.
		prepare($link)."\n";
} // end function menu_item

function sub_menu($text, $depth) {
	for ($i = 1; $i <= $depth; $i++) {
		$GLOBALS['__freemed']['layermenu'] .= '.';
	}
	$GLOBALS['__freemed']['layermenu'] .= '|'.$text."\n";
} // end function sub_menu

// Load handler for menu
LoadObjectDependency('PHP.module');
$handlers = freemed::module_handler('MenuNotifyItems');
if (is_array($handlers)) {
	foreach ($handlers AS $class => $handler) {
		$reply = module_function($class, $handler);
		if (is_array($reply)) {
			list ($text, $link) = $reply;
			$__handle[$text] = $link;
		}
	}
}

// Show headers
print "
<script language=\"JavaScript\" type=\"text/javascript\">
<!--
";
include dirname(__FILE__).'/libjs/layersmenu-browser_detection.js';
print "
// -->
</script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"".
	"lib/template/newskin/libjs/layersmenu-library.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"".
	"lib/template/newskin/libjs/layersmenu.js\"></script>
";
include (dirname(__FILE__)."/lib/PHPLIB.php");
include (dirname(__FILE__)."/lib/layersmenu-common.inc.php");
include (dirname(__FILE__)."/lib/layersmenu.inc.php");

	//----- Derive pieces of menu bar

$patient_history = patient_history_list();
if (is_array($patient_history)) ksort($patient_history);
$page_history = page_history_list();

	// System menu
	
	sub_menu (__("System"), 1);
	menu_item(__("Preferences"), "preferences.php", 2);
	menu_item(__("Return to Main Menu"), "main.php", 2);
	menu_item(__("Logout"), "logout.php", 2);

	// Main menu

	sub_menu (__("Main"), 1);
	if (freemed::acl('admin', 'menu')) {
		menu_item(__("Administration Menu"), "admin.php", 2);
	}
	if (freemed::acl('bill', 'menu')) {
		menu_item(__("Billing Functions"), "billing_functions.php", 2);
	}
	if (freemed::acl('schedule', 'view')) {
		menu_item(__("Calendar"), "calendar.php", 2);
	}
	menu_item(__("Call-In"), "call-in.php", 2);
	if (freemed::acl('report', 'menu')) {
		menu_item(__("Reports"), "reports.php", 2);
	}
	if (freemed::acl('support', 'menu')) {
		menu_item(__("Support Data"), "db_maintenance.php", 2);
	}
	menu_item(__("Utilities"), "utilities.php", 2);

	// User menu
	sub_menu(__("User"), 1);
	if (!is_object($this_user)) {
		$this_user = CreateObject('FreeMED.User');
	}
	if ($this_user->isPhysician()) {
		//print menu_sep();
		menu_item(__("Day View"), "physician_day_view.php?physician=".$this_user->getPhysician(), 2);
		menu_item(__("Week View"), "physician_week_view.php?physician=".$this_user->getPhysician(), 2);
	}

	// User->Messages menu
	
	sub_menu(__("Messages"), 2);
	menu_item(__("Check"), "messages.php", 3);
	menu_item(__("Send"), "messages.php?action=addform", 3);
	if ($page_history) {
		// User->History menu
		sub_menu(__("History"), 2);
		foreach ($page_history as $k => $v) {
			menu_item($k, $v, 3);
		}
	}

	// Patient menu

	sub_menu(__("Patients"), 1);
	if (freemed::acl('emr', 'add')) {
		menu_item(__("New"), "patient.php?action=addform", 2);
	}
	menu_item(__("Select"), "patient.php", 2);
	menu_item(__("Schedule"), "book_appointment.php", 2);
	if ($patient_history) {
		menu_item(__("Configure"),
			"manage.php?id=".(
				page_name() == 'manage.php' ?
				$_REQUEST['id'] :
				$patient_history[count($patient_history)-1]
			)."&action=config", 2);
		// Patient->Recent
		sub_menu(__("Recent"), 2);
		foreach ($patient_history as $k => $v) {
			menu_item($k, "manage.php?id=".urlencode($v), 3);
		}
	}

	// Patient->Callin
	
	sub_menu(__("Call-In"), 2);
	menu_item(__("Entry"), "call-in.php?action=addform", 3);
	menu_item(__("Menu"), "call-in.php", 3);

	// Only display notifications "menu" if there are any
	if (is_array($__handle)) {
		sub_menu(__("Notify"), 1);
		foreach ($__handle AS $text => $link) {
			menu_item($text, $link, 2);
		}
	}

/*
//----- Check to see if a menubar array exists
if (is_array($menu_bar)) {
	print "<div ALIGN=\"CENTER\">".
		"<img src=\"lib/template/default/img/black_pixel.png\" height=\"1\" ".
		"width=\"250\" alt=\"\"/></div>\n";
	print "<table WIDTH=\"100%\" BORDER=\"0\" CLASS=\"menubar\">\n";
	foreach ($menu_bar AS $k => $v) {
		if ($v != NULL) {
		if (strpos($v, "help.php")===false) {
			print menu_bar_cell(__($k), $v);
		} else { // if there *is* a help string in there
		// Make sure that bad help links aren't displayed
		if ($v != "help.php") print "\t<LI><A HREF=\"#\" ".
			"onClick=\"window.open('".$v."', 'Help', ".
			"'width=600,height=400,resizable=yes');\" ".
			"onMouseOver=\"window.status='".__($k)."'; ".
			"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\">".
			__($k)."</A>\n";
		} // end checking for help.php
		} // end checking for null
	} // end foreach
	print "</table>\n";
} else { // if is array
	print "&nbsp;\n";
} // end if is array
*/


//----- Generate actual menu
$menu = new LayersMenu(6, 7, 2, 1);
$menu->setDirroot(dirname(__FILE__));
$menu->setTpldir(dirname(__FILE__)."/templates/");
$menu->setImgdir(dirname(__FILE__)."/img/");
$menu->setImgwww("lib/template/newskin/img/");
$menu->setDownArrowImg("down-arrow.png");
$menu->setForwardArrowImg("forward-arrow.png");
$menu->setMenuStructureString($GLOBALS['__freemed']['layermenu']);
$menu->parseStructureForMenu("topmenu");
$menu->newHorizontalMenu("topmenu");
$menu->printHeader();
$menu->printMenu("topmenu");
$menu->printFooter();

//print "<pre>".$GLOBALS['__freemed']['layermenu']."</pre>\n";
?>
