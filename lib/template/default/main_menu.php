<?php
 // $Id$
 // $Author$
 // note: main menu module
 // lic : GPL

$page_name = "main.php";
include_once ("lib/freemed.php");

//----- Add page to page history list
page_push ();

//---- DB and authenticate
freemed::connect ();

//----- Create user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

//---- Set page title
$page_title = PACKAGENAME." ".__("Main Menu");

// Check for new messages (depreciated, moved into modules)
/*
if ($new_messages = $this_user->newMessages()) {
	$display_buffer .= "
		<div align=\"center\" valign=\"MIDDLE\" class=\"infobox\">
		<img src=\"img/messages_small.gif\" alt=\"\" ".
		"width=\"16\" height=\"16\" border=\"0\"/>
		<a HREF=\"messages.php\"
		>".sprintf(__("You have %d new message(s)."), $new_messages).
		"</a>
		<img src=\"img/messages_small.gif\" ALT=\"\" ".
		"WIDTH=\"16\" HEIGHT=\"16\" BORDER=\"0\"/>
		</div>
	";
}
*/

//----- New static menu system goes down here ....

//----- Define handler for main menu
LoadObjectDependency('PHP.module');
$handlers = freemed::module_handler('MainMenu');
if (is_array($handlers)) {
	foreach ($handlers AS $class => $handler) {
		$reply = module_function ($class, $handler);
		if (is_array($reply)) {
			// Array format is (title, content)
			list ($_t, $_c, $_i) = $reply;
			$display_buffer .=
				"<center>\n".
				"<div class=\"thinbox_noscroll\" style=\"width: 80%; text-align: left; align: center;\">\n".
				"<div class=\"reverse\" style=\"width: 100%; text-weight: bold; text-align: left; vertical-align: top; \">".prepare($_t)."</div><br />\n".
				( $_i ? "<span style=\"margin: 5px;\"><img src=\"".$_i."\" border=\"0\" /></span>" : "" ).
				"<span style=\"vertical-align: top;\">".$_c."</span></div>\n&nbsp;".
				"</center>\n";
		} else {
			// Flat, in a box, already formatted
			if ($reply) {
				$display_buffer .= "<center><div class=\"thinbox_noscroll\" style=\"width: 80%; align: center;\">\n".
				$reply."</div>\n&nbsp;</center>\n";
			}
		}
	} // end foreach handlers
} // end is array handlers

?>
