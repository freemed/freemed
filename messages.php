<?php
 // $Id$
 // lic : GPL, v2

$page_name = "messages.php";          // page name
include ("lib/freemed.php");          // global variables
include ("lib/calendar-functions.php");
$record_name = _("Messages");         // name of record
$db_name = "messages";                // database name

//----- Open the database, etc
freemed_open_db ();
$this_user = new User ();

// Kludge to set action to add
if ($submit_action==" "._("Add")." ") { $action = "add"; }

switch ($action) {

	case "addform":
	// Set page title
	$page_title = _("Add")." "._($record_name);

	// Push onto stack
	page_push();

	// Check for default or passed physician
	if (!isset($msgfor)) { $msgfor = $this_user->user_phy; }

	// Set default urgency to 3
	if (!isset($msgurgency)) { $msgurgency = 3; }

	$display_buffer .= "
	<P>
	<FORM NAME=\"myform\" ACTION=\"$page_name\" METHOD=POST>
	<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
	<CENTER>
	".html_form::form_table(array(
		_("For") =>
		freemed_display_selectbox(
			$sql->query("SELECT * FROM physician WHERE ".
				"phyref != 'yes'"),
			"#phylname#, #phyfname#",
			"msgfor"
		),

		_("Patient")." ("._("if applicable").")" =>
		freemed::patient_widget("msgpatient"),

		_("From (if not a patient)") =>
		html_form::text_widget("msgperson", 20, 50),

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
	</CENTER>

	<P>
	<CENTER>
	<INPUT TYPE=SUBMIT NAME=\"submit_action\" VALUE=\" "._("Add")." \"  >
	<INPUT TYPE=RESET VALUE=\" "._("Clear")." \">
	</CENTER>
	</FORM>
	<P>
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
			"msgtext",
			"msgurgency",
			"msgread" => '0' // mark as not read
		)
	);
	$result = $sql->query ($query);

	if ($result) $display_buffer .= _("done");
	else $display_buffer .= _("ERROR");
	$display_buffer .= " 
	<P>
	<CENTER>
	<A HREF=\"messages.php\"
	>"._("Messages")."</A> |
	<A HREF=\"main.php\"
	>"._("Return to the Main Menu")."</A>
	</CENTER>
	<P>
	";
	break; // end action add

	case "del": case "delete":
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

	$display_buffer .= "<DIV ALIGN=\"CENTER\" CLASS=\"infobox\">\n".
		"<A HREF=\"messages.php?action=addform\">".
		_("Add Message")."</A> | \n".
		"<A HREF=\"main.php\">".
		_("Main Menu")."</A>\n".
		"</DIV>\n";

	// View list of messages for this doctor
	$query = "SELECT * FROM messages ".
		"WHERE msgfor='".$this_user->user_phy."' AND msgread='0' ".
		"ORDER BY msgtime DESC";
	$result = $sql->query($query);

	if (!$sql->results($result)) {
		$display_buffer .= "<P>
			"._("You have no waiting messages.").
			"<P>";
	} else {
		$display_buffer .= "
		<CENTER>
		<FORM ACTION=\"".$page_name."\" METHOD=\"POST\">
		<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" ".
		"CELLPADDING=\"3\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<TR CLASS=\"menubar\">
			<TD>&nbsp;</TD>
			<TD><B>"._("Date")."</B></TD>
			<TD><B>"._("Time")."</B></TD>
			<TD><B>"._("From")."</B></TD>
			<TD><B>"._("Urgency")."</B></TD>
		</TR>
		";
		while ($r = $sql->fetch_array($result)) {
			// Determine who we're looking at by number
			if ($r[msgpatient] > 0) {
				$this_patient = new Patient ($r[msgpatient]);
				$r[from] = "<A HREF=\"manage.php?id=".
					$r[msgpatient]."\">".
					$this_patient->fullName()."</A>";
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
			<TR>
				<TD><INPUT TYPE=\"CHECKBOX\" ".
					"NAME=\"mark[".$r[id]."]\" ".
					"VALUE=\"".prepare($r[id])."\"></TD>
				<TD>$y-$m-$d</TD>
				<TD>".fc_get_time_string($hour,$min)."</TD>
				<TD>".$r[from]."</TD>
				<TD>".$r[msgurgency]." out of 5</TD>
			</TR>
			<TR><TD>&nbsp;</TD><TD COLSPAN=\"4\">
				<I>".prepare($r[msgtext])."</I>
			</TD></TR>
			";
		}
		$display_buffer .= "
		</TABLE></CENTER>

		<SCRIPT LANGUAGE=\"JavaScript\"><!--
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
		</SCRIPT>

		<CENTER>
			<INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"mark\">
			<INPUT TYPE=\"BUTTON\" VALUE=\""._("Select All")."\" ".
			"onClick=\"selectAll(this.form); return true;\">
			<INPUT TYPE=\"SUBMIT\" VALUE=\""._("Mark as Read")."\">
			</FORM>
		</CENTER>
		";
	}

	$display_buffer .= "<DIV ALIGN=\"CENTER\" CLASS=\"infobox\">\n".
		"<A HREF=\"messages.php?action=addform\">".
		_("Add Message")."</A> | \n".
		"<A HREF=\"main.php\">".
		_("Main Menu")."</A>\n".
		"</DIV>\n";

	break;

} // end master switch

// Display template
template_display();

?>
