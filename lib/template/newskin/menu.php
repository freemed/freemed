<?php
 // $Id$
 // $Author$

function menu_item($text, $link) {
	return "\t<a class=\"menuItem\" href=\"".$link."\"".
		">".prepare($text)."</a>\n";
} // end function menu_item

function sub_menu($text, $menu) {
	return "\t<a class=\"menuItem\" ".
		"onMouseOver=\"menuItemMouseover(event, '".$menu."')\"".
		">".
		"<span class=\"menuItemText\">".prepare($text)."</span>".
		"<span class=\"menuItemArrow\">&gt;</span></a>\n";
} // end function sub_menu

function menu_sep() {
	return "\t<div class=\"menuItemSep\"></div>\n";
} // end function menu_sep

// Load handler for menu
LoadObjectDependency('PHP.module');
$handlers = freemed::module_handler('MenuNotifyItems');
if (is_array($handlers)) {
	foreach ($handlers AS $class => $handler) {
		$reply = module_function($class, $handler);
		if (is_array($reply)) {
			$__handle[$text] = $link;
		}
	}
}

/*

// Language bar
if (defined('ALWAYS_LANGUAGE_BAR') or ($_COOKIE['language_bar']==1)) {
	$langugage_bar = true;
	$registry = CreateObject('FreeMED.LanguageRegistry');
	print "
	<tr><td align=\"RIGHT\" valign=\"MIDDLE\">
	<form ACTION=\"".page_name()."\" METHOD=\"POST\">
	<input type=\"HIDDEN\" name=\"module\" value=\"".prepare($module)."\"/>
	<input type=\"HIDDEN\" name=\"id\" value=\"".prepare($id)."\"/>
	<input type=\"HIDDEN\" name=\"patient\" value=\"".prepare($patient)."\"/>
	<input type=\"HIDDEN\" name=\"action\" value=\"".prepare($action)."\"/>
	".$registry->widget('__language',
		array ('style' => 'width: 220px;', 'refresh' => true))."
	</td><td align=\"CENTER\" valign=\"MIDDLE\">
	<input TYPE=\"IMAGE\" SRC=\"lib/template/default/forward.".
	IMAGE_TYPE."\"
		WIDTH=\"16\" HEIGHT=\"16\" ALT=\"[".__("Change Language")."]\"/>
	</form>
	</td></tr>
	";
}
*/

	//----- Derive pieces of menu bar

$patient_history = patient_history_list();
ksort($patient_history);
$page_history = page_history_list();

?>

<div class="menuBar" style="position: absolute; top: 0px; width: 100%; ">
	<a class="titleButton"><b><?php print PACKAGENAME." v".DISPLAY_VERSION; ?></b></a>
	<a class="menuButton" onMouseOver="buttonMouseover(event, 'systemMenu')"><?php print __("System"); ?></a>
	<a class="menuButton" onMouseOver="buttonMouseover(event, 'mainMenu')"><?php print __("Main"); ?></a>
	<a class="menuButton" onMouseOver="buttonMouseover(event, 'userMenu')"><?php print __("User"); ?></a>
	<a class="menuButton" onMouseOver="buttonMouseover(event, 'patientMenu')"><?php print __("Patient"); ?></a>
	<?php if (is_array($__handle)) print "<a class=\"menuButton\" onMouseOver=\"buttonMouseover(event, 'notifyMenu')\">".__("Notifications")."</a>\n"; ?>
	<span class="pageTitle"><b><?php print $GLOBALS['page_title']; ?></b></span>
</div>

<div id="systemMenu" class="menu" onMouseOver="menuMouseover(event)">
	<?php
	print menu_item(__("Preferences"), "preferences.php");
	print menu_item(__("Return to Main Menu"), "main.php");
	print menu_item(__("Logout"), "logout.php");
	?>
</div>


<div id="mainMenu" class="menu" onMouseOver="menuMouseover(event)">
	<?php
	print menu_item(__("Administration Menu"), "admin.php");
	print menu_item(__("Billing Functions"), "billing_functions.php");
	print menu_item(__("Calendar"), "calendar.php");
	print menu_item(__("Call-In"), "call-in.php");
	print menu_item(__("Reports"), "reports.php");
	print menu_item(__("Support Data"), "db_maintenance.php");
	?>
</div>

<div id="userMenu" class="menu" onMouseOver="menuMouseover(event)">
	<?php
	print sub_menu(__("Messages"), 'messagesMenu');
	if ($patient_history) {
		print sub_menu(__("History"), "pageHistoryMenu");
	}
	if (!is_object($this_user)) {
		$this_user = CreateObject('FreeMED.User');
	}
	if ($this_user->isPhysician()) {
		print menu_sep();
		print menu_item(__("Day View"), "physician_day_view.php?physician=".$this_user->getPhysician());
		print menu_item(__("Week View"), "physician_week_view.php?physician=".$this_user->getPhysician());
	}
	?>
</div>

<div id="messagesMenu" class="menu" onMouseOver="menuMouseover(event)">
	<?php
	print menu_item(__("Check"), "messages.php");
	print menu_item(__("Send"), "messages.php?action=addform");
	?>
</div>

<div id="patientMenu" class="menu" onMouseOver="menuMouseover(event)">
	<?php
	print menu_item(__("New"), "patient.php?action=addform");
	print menu_item(__("Select"), "patient.php");
	if ($patient_history) {
		print sub_menu(__("Recent"), "patientHistoryMenu");
	}
	print menu_sep();
		print sub_menu(__("Call-In"), "callinMenu");
	?>
</div>

<?php if ($patient_history) {
?><div id="patientHistoryMenu" class="menu" onMouseOver="menuMouseover(event)"><?php
	foreach ($patient_history as $k => $v) {
		print menu_item($k, "manage.php?id=".urlencode($v));
	}
?></div><?php
} ?>

<?php if ($page_history) {
?><div id="pageHistoryMenu" class="menu" onMouseOver="menuMouseover(event)"><?php
	foreach ($page_history as $k => $v) {
		print menu_item($k, $v);
	}
?></div><?php
} ?>

<div id="callinMenu" class="menu" onMouseOver="menuMouseover(event)">
	<?php
	print menu_item(__("Entry"), "call-in.php?action=addform");
	print menu_item(__("Menu"), "call-in.php");
	?>
</div>

<?php

// Only display notifications "menu" if there are any
if (is_array($__handle)) {
	print "<div id=\"notifyMenu\" class=\"menu\" onMouseOver=\"".
		"menuMouseover(event)\">\n";
	foreach ($__handle AS $text => $link) {
		print menu_item($text, $link);
	}
	print "</div>\n";
}

if (0==1) {
?><table CLASS="menubar" WIDTH="100%" BORDER="0"><?php
//----- Check for help file link
if ( ($help_url = help_url()) != "help.php" ) {
	print "\t<tr>\n".
		"\t\t<td COLSPAN=\"2\" CLASS=\"menubar_items\" ".
		"onMouseOver=\"this.className='menubar_items_hilite'; return true;\" ".
		"onMouseOut=\"this.className='menubar_items'; return true;\" ".
		"onClick=\"window.open('".$help_url."', 'Help', 'width=600,height=400,".
			"resizable=yes'); return true;\" ".
		"><a href=\"#\">".
		prepare(__("Help"))."</a></td>\n".
		"\t</tr>\n";
}

	// Create the rest of the stock menubar entries

?>
</table>
<!-- new functions come *after* everything else -->
<?php 

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
			"onMouseOver=\"window.status='".prepare(_($k))."'; ".
			"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\">".
			prepare(__($k))."</A>\n";
		} // end checking for help.php
		} // end checking for null
	} // end foreach
	print "</table>\n";
} else { // if is array
	print "&nbsp;\n";
} // end if is array

} // end 0==1
?>

<!-- Need spacing to push content down a bit -->
<br/>

