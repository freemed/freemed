<?php
 // $Id$
 // lic : GPL, v2

$page_name = "messages.php";          // page name
include_once ("lib/freemed.php");          // global variables
$record_name = __("Messages");         // name of record
$db_name = "messages";                // database name

define ('PAGE_ROLL', 5);

//----- Open the database, etc
freemed::connect ();

//----- Get module cache for any loaded modules
$cache = freemed::module_cache();

//----- Create scheduler
if (!is_object($scheduler)) $scheduler = CreateObject('FreeMED.Scheduler');

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"messages.php|user $user_to_log message access");}	

$this_user = CreateObject('FreeMED.User');

if ($_REQUEST['submit_action']==" ".__("Add")." ") { $action = "add"; }
if ($_REQUEST['submit_action']==__("Cancel")) {
	$action = "";
	// Handle return to patient during cancel
	if ($return=="manage") {
		Header("Location: manage.php?id=".urlencode($_REQUEST['msgpatient']));
		die("");
	}
}
if ($_REQUEST['submit_action']==__("Mark as Read")) {
	$action = "del";
}
if ($_REQUEST['submit_action']==__("Delete Marked Messages")) { 
	$action = "remove";
}

switch ($action) {

	case "addform":
	// Set page title
	$page_title = __("Add")." ".__($record_name);

	// Push onto stack
	page_push();

	// Check for default or passed physician
	if (!isset($msgfor)) { $msgfor = $this_user->user_number; }

	// Set default urgency to 3
	if (!isset($msgurgency)) { $msgurgency = 3; }

	// If !been_here and there's a current patient, use them
	if ((!$been_here) and $_COOKIE['current_patient']>0) {
		$msgpatient = $_COOKIE['current_patient'];

		// Persistent EMR patient bar/box if we came from there
		$display_buffer .= freemed::patient_box(
			CreateObject('FreeMED.Patient', $msgpatient)
		);
	}

	$display_buffer .= "
	<p/>
	<form NAME=\"myform\" ACTION=\"$page_name\" METHOD=\"POST\">
	<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"addform\"/>
	<input TYPE=\"HIDDEN\" NAME=\"return\" VALUE=\"".prepare($_REQUEST['return'])."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"1\"/>
	<div ALIGN=\"CENTER\">
	".html_form::form_table(array(
		__("For") =>
		//freemed_display_selectbox(
		//	$sql->query("SELECT * FROM user ".
		//		"WHERE username != 'admin' ".
		//		"ORDER BY userdescrip"),
		//	"#username# (#userdescrip#)",
		//	"msgfor"
		//),
		freemed::multiple_choice(
			"SELECT CONCAT(username, ' (', userdescrip, ')')".
				"AS descrip, id FROM user ".
				"WHERE id > 1 ".
				"ORDER BY descrip",
			"descrip",
			"msgfor",
			fm_join_from_array($_REQUEST['msgfor']),
			false
		).
		module_function(
			'UserGroupsMaintenance',
			'widget',
			array ( 'group' )
		),

		__("Patient")." (".__("if applicable").")" =>
		freemed::patient_widget("msgpatient", "myform", "submit_action"),

		__("Regarding (if not a patient)") =>
		html_form::text_widget("msgperson", 20, 50),

		__("Subject") =>
		html_form::text_widget("msgsubject", 20, 75),

		__("Message") =>
		html_form::text_area("msgtext"),

		__("Urgency") =>
		html_form::select_widget(
			"msgurgency",
			array(
				__("not important") => 1,
				__("somewhat important") => 2,
				__("moderately important") => 3,
				__("very important") => 4,
				__("extremely urgent") => 5,
			)
		)
	))."
	</div>

	<p/>
	<div ALIGN=\"CENTER\">
	<input class=\"button\" TYPE=\"SUBMIT\" ".
	"NAME=\"submit_action\" VALUE=\" ".__("Add")." \" />
	<input class=\"button\" TYPE=\"RESET\" VALUE=\" ".__("Clear")." \"/>
	<input class=\"button\" TYPE=\"SUBMIT\" ".
	"NAME=\"submit_action\" VALUE=\"".__("Cancel")."\" />
	</div>
	</form>
	<p/>
	";
	break; // end action addform

	case "add":
	$page_title = __("Adding")." ".__("Message");
	$display_buffer .= "\n<div align=\"center\">".
		__("Adding")." ".__("Message")." ... \n";
	$result = true;
	if ($_REQUEST['group']) {
		$_g = freemed::get_link_rec($_REQUEST['group'], 'usergroup');
		$my_for = array_merge($_REQUEST['msgfor'], explode(',', $_g['usergroup']));
	} else {
		$my_for = $_REQUEST['msgfor'];
	}
	foreach ($my_for AS $this_is_for) {
		$query = $sql->insert_query(
			"messages",
			array(
				"msgby" => $this_user->user_number, // mark from user
				"msgfor" => $this_is_for,
				"msgrecip" => join(',', $my_for),
				"msgtime" => SQL__NOW, // pass proper timestamp
				"msgpatient",
				"msgperson",
				"msgsubject",
				"msgtext",
				"msgurgency",
				"msgread" => '0' // mark as not read
			)
		);
		$result &= $sql->query ($query);
	}

	if ($result) $display_buffer .= __("done");
	else $display_buffer .= __("ERROR");
	$display_buffer .= " 
	</div>
	<p/>
	".template::link_bar(array(
	__("Messages") => "messages.php",
	__("Return to the Main Menu") => "main.php"
	))."
	<p/>
	";
	if ($return=="manage") {
		Header("Location: manage.php?id=".urlencode($_REQUEST['msgpatient']));
		die("");
	}
	break; // end action add

	case "remove":
	// Perform deletion
	if ($_REQUEST['id'] > 0) {
		$result = $sql->query("DELETE FROM messages WHERE id='".
			addslashes($id)."'");
	} elseif (is_array($_REQUEST['mark'])) {
		$query = "DELETE FROM messages WHERE FIND_IN_SET(id, '".
				join(",", $_REQUEST['mark'])."')";
		$result = $sql->query($query);
	} else {
		$display_buffer .= __("There is nothing to delete.");
	}

	// Check if we return to management
	if ($return=="manage") {
		Header("Location: manage.php?id=".urlencode($_REQUEST['msgpatient']));
		die("");
	} else {
		// Otherwise refresh to messages screen
		Header("Location: messages.php?".
				"old=".urlencode($_REQUEST['old'])."&".
				"start=".urlencode($_REQUEST['start']));
		die("");
	}
	break; // end action remove

	case "del": case "delete":
	// Perform "deletion" (marking as read)
	if ($_REQUEST['id'] > 0) {
		$result = $sql->query($sql->update_query(
			'messages',
			array('msgread' => '1'),
			array('id' => $_REQUEST['id'])
		));
	} elseif (is_array($_REQUEST['mark'])) {
		foreach ($_REQUEST['mark'] AS $v) {
			$result = $sql->query($sql->update_query(
				'messages',
				array('msgread' => '1'),
				array('id' => $v)
			));
		}
	} else {
		$display_buffer .= __("There is nothing to mark as read.");
	}

	// Check if we return to management
	if ($return=="manage") {
		Header("Location: manage.php?id=".urlencode($_REQUEST['msgpatient']));
		die("");
	} else {
		// Otherwise refresh to messages screen
		Header("Location: messages.php?".
				"old=".urlencode($_REQUEST['old'])."&".
				"start=".urlencode($_REQUEST['start']));
		die("");
	}
	break; // end action del

	case "mark":
	if (is_array($mark)) {
		$query = "UPDATE messages SET msgread = '1', ".
			"msgtime=msgtime ".
			"WHERE FIND_IN_SET(id, '".join(",", $mark)."')";
		$result = $sql->query($query);
	} else {
		// Do nothing.
	}
	// NOTE - There is no "break", as this is meant to mark them
	// then display again...

	default:
	// Set page title
	$page_title = __("Messages");

	$messages = CreateObject('FreeMED.Messages');
  
	// Push onto stack
	page_push();

	// Check for proper "old" value
	if (!isset($old) or ($old < 0) or ($old > 1)) $old = 0;

	// Determine how many messages there are
	$paging = false;
	$p_result = $sql->query(
		"SELECT * FROM messages ".
		"WHERE msgfor='".$this_user->user_number."' AND ".
		"msgread='".addslashes($old)."' ".
		"ORDER BY msgtime DESC"
	);
	if ($sql->results($p_result)) {
		$total_results = $sql->num_rows($p_result);
		$paging = ($total_results > PAGE_ROLL);
	}

	$display_buffer .= 
		template::link_bar(array(
		__("Add Message") =>
		"messages.php?action=addform",
		( ($old != 1) ? __("Old Messages") : __("New Messages") ) =>
		( ($old != 1) ? "messages.php?old=1" : "messages.php?old=0" ),
		__("Main Menu") =>
		"main.php" ));

	// View list of messages for this doctor
	$query = "SELECT * FROM messages ".
		"WHERE msgfor='".$this_user->user_number."' AND ".
		"msgread='".addslashes($old)."' ".
		"ORDER BY msgtime DESC ".
		"LIMIT ".addslashes($start + 0).",".addslashes(PAGE_ROLL + 0);
		// this should be LIMIT (page roll) OFFSET (start)
	$result = $sql->query($query);

	if (!$sql->results($result)) {
		$display_buffer .= "<p align=\"center\">
			". ($old ?
				__("You have no old messages.") :
				__("You have no waiting messages.")
			)."</p>";
	} else {
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<form ACTION=\"".$page_name."\" METHOD=\"POST\">
		<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" ".
		"CELLPADDING=\"3\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<tr CLASS=\"menubar\">
			<td>&nbsp;</td>
			<td><b>".__("Date")."</b></td>
			<td><b>".__("Time")."</b></td>
			<td><b>".__("Sent By")."</b></td>
			<td><b>".__("Regarding")."</b></td>
			<td><b>".__("Urgency")."</b></td>
		</tr>
		";

		while ($r = $sql->fetch_array($result)) {
			// Determine who we're looking at by number
			if ($r['msgpatient'] > 0) {
				$this_patient = CreateObject('FreeMED.Patient',
						$r['msgpatient']);
				$r['from'] = "<a HREF=\"manage.php?id=".
					$r['msgpatient']."\">".
					$this_patient->fullName()."</a>";
			} else {
				$r['from'] = stripslashes($r['msgperson']);
			}

			// Figure out who sent it
			$sent_by = '';
			$sentuser = CreateObject('FreeMED.User', $r['msgby']);
			$sent_by = $sentuser->getName();

			// Convert from timestamp to time/date
			$y = $m = $d = $hour = $min = '';
			$y = substr($r['msgtime'], 0, 4);
			$m = substr($r['msgtime'], 4, 2);
			$d = substr($r['msgtime'], 6, 2);
			$hour = substr($r['msgtime'], 8, 2);
			$min  = substr($r['msgtime'], 10, 2);

			// Display message
			$display_buffer .= "
			<tr>
				<td><input TYPE=\"CHECKBOX\" ".
					"NAME=\"mark[".$r['id']."]\" ".
					"VALUE=\"".prepare($r['id'])."\"/></td>
				<td>$y-$m-$d</td>
				<td>".$scheduler->get_time_string($hour,$min)."</td>
				<td>".$sent_by."</td>
				<td>".$r['from']."</td>
				<td>".$r['msgurgency']."/5 ".
				"<a class=\"button\" href=\"".page_name()."?action=addform&been_here=1&msgperson=".urlencode($r['msgperson'])."&msgtext=".urlencode(":: ".stripslashes($r['msgtext'])." ::\n\n")."&msgfor=".urlencode($r['msgby'])."&msgpatient=".urlencode($r['msgpatient'])."&msgsubject=".urlencode('Re: '.$r['msgsubject'])."\">".__("Reply")."</a> ".
				"<a class=\"button\" href=\"".page_name()."?action=addform&been_here=1&msgperson=".urlencode($r['msgperson'])."&msgtext=".urlencode(":: ".stripslashes($r['msgtext'])." ::\n\n")."&msgpatient=".urlencode($r['msgpatient'])."&msgsubject=".urlencode('Fwd: '.$r['msgsubject'])."\">".__("Fwd")."</a></td>
			</tr>
			".( $r['msgrecip'] != $r['msgfor'] ? "
			<tr><td>&nbsp;</td><td COLSPAN=\"4\">
				<small><em>".__("Sent to:")."
				".$messages->recipients_to_text($r['msgrecip'])."
				</em></small>
			</td></tr>
			" : "" )."
			<tr><td>&nbsp;</td><td COLSPAN=\"4\">
				".( $r['msgsubject'] ? 
				"<span style=\"border: 1px dotted;\">
				<b>".prepare($r['msgsubject'])."</b></span> ".
				"&nbsp; <span>" : "" )."
				<i>".prepare($r['msgtext'])."</i>
				".( $r['msgsubject'] ? "</span>" : "" )."
			</td></tr>
			";
		}
		$display_buffer .= "
		</table></div>
		";

		// Create paging links
		if ($paging) {
			$display_buffer .= "<p/><div ALIGN=\"CENTER\" ".
				"CLASS=\"infobox\">\n";
			$pages = ceil($total_results / PAGE_ROLL);
			$display_buffer .= "&nbsp; ";
			for ($i = 1; $i <= $pages; $i++) {
				$display_buffer .= (
					(($i-1)*PAGE_ROLL) != ($start+0) ?
					"<a href=\"".page_name().
					"?start=".urlencode(($i-1) * PAGE_ROLL)."&".
					"old=".urlencode($old+0)."\">"
					: "" ).
					"<b>".$i."</b>".(
					(($i-1)*PAGE_ROLL) != ($start+0) ?
					"</a>" : "" ).
					" &nbsp; ";
					
			}
			$display_buffer .= "</div><p/>\n";
		}

		// Create buttons
		$display_buffer .= "
		<script LANGUAGE=\"JavaScript\"><!--
		// Quick script to mark all as read if the button is pressed
		function selectAll(myform) {
			for (var l=0; l<myform.length; l++) {
				myobject = myform.elements[l];
				if (myobject.type == 'checkbox') {
					myobject.checked = true;
				}
			}
		}
		//-->
		</script>

		<div ALIGN=\"CENTER\">
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
				( ($old != 1) ? 'mark' : 'remove' )."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"old\" VALUE=\"".
				prepare($old)."\"/>
			<input TYPE=\"HIDDEN\" NAME=\"start\" VALUE=\"".
				prepare($start)."\"/>
			<input TYPE=\"BUTTON\" VALUE=\"".__("Select All")."\" ".
			"onClick=\"selectAll(this.form); return true;\" ".
			"class=\"button\"/>
			".( ($old==0) ?
			"<input class=\"button\" name=\"submit_action\" TYPE=\"SUBMIT\" ".
				"VALUE=\"".__("Mark as Read")."\"/> " :
			"<input class=\"button\" name=\"submit_action\" TYPE=\"SUBMIT\" ".
				"VALUE=\"".__("Delete Marked Messages")."\"/> "
			).( ($old==0 and freemed::config_value('message_delete')) ?
			"<input class=\"button\" name=\"submit_action\" TYPE=\"SUBMIT\" ".
				"VALUE=\"".__("Delete Marked Messages")."\" ".
				"onClick=\"if (confirm('".addslashes(__("Are you sure that you want to permanently delete this message?"))."')) { return true; } else { return false; }\" />" : "" ).

			"</form>
		</div>
		";
	}

	$display_buffer .= 
		template::link_bar(array(
		__("Add Message") =>
		"messages.php?action=addform",
		( ($old != 1) ? __("Old Messages") : __("New Messages") ) =>
		( ($old != 1) ? "messages.php?old=1" : "messages.php?old=0" ),
		__("Main Menu") =>
		"main.php" ));
	break;

} // end master switch

//----- Display template
template_display();

?>
