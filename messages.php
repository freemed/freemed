<?php
 // $Id$
 // lic : GPL, v2

$page_name = "messages.php";          // page name
include_once ("lib/freemed.php");          // global variables
include_once ("lib/calendar-functions.php");
$record_name = _("Messages");         // name of record
$db_name = "messages";                // database name

//----- Open the database, etc
freemed_open_db ();
$this_user = CreateObject('FreeMED.User');

if ($submit_action==" "._("Add")." ") { $action = "add"; }

switch ($action) {

	case "addform":
	// Set page title
	$page_title = _("Add")." "._($record_name);

	// Push onto stack
	page_push();

	// Check for default or passed physician
	if (!isset($msgfor)) { $msgfor = $this_user->user_number; }

	// Set default urgency to 3
	if (!isset($msgurgency)) { $msgurgency = 3; }

	// If !been_here and there's a current patient, use them
	if ((!$been_here) and $SESSION["current_patient"]>0) {
		$msgpatient = $SESSION["current_patient"];
	}

	$display_buffer .= "
	<p/>
	<form NAME=\"myform\" ACTION=\"$page_name\" METHOD=\"POST\">
	<input TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\"/>
	<input TYPE=HIDDEN NAME=\"been_here\" VALUE=\"1\"/>
	<div ALIGN=\"CENTER\">
	".html_form::form_table(array(
		_("For") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM user ".
				"WHERE username != 'root' ".
				"ORDER BY userdescrip"),
			"#username# (#userdescrip#)",
			"msgfor"
		),

		_("Patient")." ("._("if applicable").")" =>
		freemed::patient_widget("msgpatient"),

		_("From (if not a patient)") =>
		html_form::text_widget("msgperson", 20, 50),

		_("Subject") =>
		html_form::text_widget("msgsubject", 20, 75),

		_("Message") =>
		html_form::text_area("msgtext"),

		_("Urgency") =>
		html_form::select_widget(
			"msgurgency",
			array(
				_("not important") => 1,
				_("somewhat important") => 2,
				_("moderately important") => 3,
				_("very important") => 4,
				_("extremely urgent") => 5,
			)
		)
	))."
	</div>

	<p/>
	<div ALIGN=\"CENTER\">
	<INPUT TYPE=SUBMIT NAME=\"submit_action\" VALUE=\" "._("Add")." \" />
	<INPUT TYPE=RESET VALUE=\" "._("Clear")." \"/>
	</div>
	</form>
	<p/>
	";
	break; // end action addform

	case "add":
	$page_title = _("Adding")." "._("Message");
	$display_buffer .= "\n"._("Adding")." "._("Message")." ... \n";
	$query = $sql->insert_query(
		"messages",
		array(
			"msgfor",
			"msgtime" => SQL_NOW, // pass proper timestamp
			"msgpatient",
			"msgperson",
			"msgsubject",
			"msgtext",
			"msgurgency",
			"msgread" => '0' // mark as not read
		)
	);
	$result = $sql->query ($query);

	if ($result) $display_buffer .= _("done");
	else $display_buffer .= _("ERROR");
	$display_buffer .= " 
	<p/>
	<div ALIGN=\"CENTER\">
	<a HREF=\"messages.php\"
	>"._("Messages")."</a> |
	<a HREF=\"main.php\"
	>"._("Return to the Main Menu")."</A>
	</div>
	<p/>
	";
	break; // end action add

	case "remove":
	// Perform deletion
	$result = $sql->query("DELETE FROM messages WHERE id='".
		addslashes($id)."'");

	// Check if we return to management
	if ($return=="manage") {
		Header("Location: manage.php?id=".$SESSION["current_patient"]);
		die("");
	} else {
		// Otherwise refresh to messages screen
		Header("Location: messages.php");
		die("");
	}
	break; // end action remove

	case "del": case "delete":
	// Perform "deletion" (marking as read)
	$result = $sql->query($sql->update_query(
			'messages',
			array('msgread' => '1'),
			array('id' => $id)
		));

	// Check if we return to management
	if ($return=="manage") {
		Header("Location: manage.php?id=".$SESSION["current_patient"]);
		die("");
	} else {
		// Otherwise refresh to messages screen
		Header("Location: messages.php");
		die("");
	}
	break; // end action del

	case "mark";
	if (is_array($mark)) {
		$query = "UPDATE messages SET msgread = '1', ".
			"msgtime=msgtime ".
			"WHERE FIND_IN_SET(id, '".join(",", $mark)."')";
		$result = $sql->query($query);
	} else {
		// Do nothing.
	}
	// NOTE: There is no "break", as this is meant to mark them
	// then display again...

	default:
	// Set page title
	$page_title = _("Messages");
  
	// Push onto stack
	page_push();

	// Check for proper "old" value
	if (!isset($old) or ($old < 0) or ($old > 1)) $old = 0;

	$display_buffer .= "<div ALIGN=\"CENTER\" CLASS=\"infobox\">\n".
		"<a HREF=\"messages.php?action=addform\">".
		_("Add Message")."</a> | \n".
		( ($old != 1) ?
			"<a HREF=\"messages.php?old=1\">".
			_("Old Messages")."</a> | \n" :
			"<a HREF=\"messages.php?old=0\">".
			_("New Messages")."</a> | \n"
		).
		"<a HREF=\"main.php\">".
		_("Main Menu")."</a>\n".
		"</div>\n";

	// View list of messages for this doctor
	$query = "SELECT * FROM messages ".
		"WHERE msgfor='".$this_user->user_number."' AND ".
		"msgread='".addslashes($old)."' ".
		"ORDER BY msgtime DESC";
	$result = $sql->query($query);

	if (!$sql->results($result)) {
		$display_buffer .= "<p/>
			". ($old ?
				_("You have no old messages.") :
				_("You have no waiting messages.")
			)."<p/>";
	} else {
		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<form ACTION=\"".$page_name."\" METHOD=\"POST\">
		<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" ".
		"CELLPADDING=\"3\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<tr CLASS=\"menubar\">
			<td>&nbsp;</td>
			<td><b>"._("Date")."</b></td>
			<td><b>"._("Time")."</b></td>
			<td><b>"._("From")."</b></td>
			<td><b>"._("Urgency")."</b></td>
		</tr>
		";
		while ($r = $sql->fetch_array($result)) {
			// Determine who we're looking at by number
			if ($r[msgpatient] > 0) {
				$this_patient = CreateObject('FreeMED.Patient', $r[msgpatient]);
				$r[from] = "<a HREF=\"manage.php?id=".
					$r[msgpatient]."\">".
					$this_patient->fullName()."</a>";
			} else {
				$r[from] = stripslashes($r[msgperson]);
			}

			// Convert from timestamp to time/date
			$y = $m = $d = $hour = $min = '';
			$y = substr($r[msgtime], 0, 4);
			$m = substr($r[msgtime], 4, 2);
			$d = substr($r[msgtime], 6, 2);
			$hour = substr($r[msgtime], 8, 2);
			$min  = substr($r[msgtime], 10, 2);

			// Display message
			$display_buffer .= "
			<tr>
				<td><input TYPE=\"CHECKBOX\" ".
					"NAME=\"mark[".$r[id]."]\" ".
					"VALUE=\"".prepare($r[id])."\"/></td>
				<td>$y-$m-$d</td>
				<td>".fc_get_time_string($hour,$min)."</td>
				<td>".$r[from]."</td>
				<td>".$r[msgurgency]." out of 5</td>
			</tr>
			<tr><td>&nbsp;</td><td COLSPAN=\"4\">
				<i>".prepare($r[msgtext])."</i>
			</td></tr>
			";
		}
		$display_buffer .= "
		</table></div>

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
			<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"mark\"/>
			<input TYPE=\"BUTTON\" VALUE=\""._("Select All")."\" ".
			"onClick=\"selectAll(this.form); return true;\"/>
			".( ($old==0) ?
			"<input TYPE=\"SUBMIT\" VALUE=\""._("Mark as Read")."\"/>" :"")."
			</form>
		</div>
		";
	}

	$display_buffer .= "<div ALIGN=\"CENTER\" CLASS=\"infobox\">\n".
		"<a HREF=\"messages.php?action=addform\">".
		_("Add Message")."</a> | \n".
		( ($old != 1) ?
			"<a HREF=\"messages.php?old=1\">".
			_("Old Messages")."</a> | \n" :
			"<a HREF=\"messages.php?old=0\">".
			_("New Messages")."</a> | \n"
		).
		"<a HREF=\"main.php\">".
		_("Main Menu")."</a>\n".
		"</div>\n";

	break;

} // end master switch

// Display template
template_display();

?>
