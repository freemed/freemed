<?php
 // $Id$
 // note: scheduling module for freemed-project
 // lic : GPL, v2

$page_name = "book_appointment.php";
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/calendar-functions.php");

//----- Login/authenticate
freemed_open_db ();

//----- Set current user
$this_user = new User ();

//----- Check for current patient
if ($travel) {
	// Kludge travel, patient = 0
	$patient = 0; $type = "pat"; $room = 0;
} elseif ($patient>0) {
	$this_patient = new Patient ($patient, ($type=="temp"));
} elseif ($SESSION["current_patient"]>0) {
	$this_patient = new Patient ($SESSION["current_patient"]);
	$type = "pat"; // kludge to keep real patient for this
}

// Check for current physician, if not, use default
if (!isset($physician) and $this_user->isPhysician()) {
	$physician = $this_user->getPhysician();
}

if (strlen($selected_date)!=10) {
	$selected_date = $cur_date;
} // fix date if not correct

// set previous and next date variables...
$next = freemed_get_date_next ($selected_date);
$next_wk = $selected_date;
for ($i=1;$i<=7;$i++) $next_wk = freemed_get_date_next ($next_wk);
$prev = freemed_get_date_prev ($selected_date);
$prev_wk = $selected_date;
for ($i=1;$i<=7;$i++) $prev_wk = freemed_get_date_prev ($prev_wk);

//----- Display patient management bar if not travel
if (!$travel) {
	$display_buffer .= freemed_patient_box($this_patient);
} else {
	// Display travel bar
	$display_buffer .= "
	<DIV ALIGN=\"CENTER\">
	<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=\"100%\">
	<TR BGCOLOR=\"#000000\"><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
	<FONT COLOR=\"#ffffff\"><B>"._("Travel")."</B></FONT>
	</TD></TR></TABLE>
	</DIV>
	";
}

//----- Create form
if (date_in_the_past($selected_date)) {
	$form .= "
		<DIV ALIGN=\"CENTER\"><I><FONT SIZE=-2
		>"._("this date occurs in the past")."</FONT></I>
		</DIV>
	";
}
$form .= "
	<FORM ACTION=\"".page_name()."\" METHOD=\"POST\">
	<INPUT TYPE=\"HIDDEN\" NAME=\"selected_date\" ".
	"VALUE=\"".prepare($selected_date)."\">
	<INPUT TYPE=\"HIDDEN\" NAME=\"type\" ".
	"VALUE=\"".prepare($type)."\">
	<INPUT TYPE=\"HIDDEN\" NAME=\"patient\" ".
	"VALUE=\"".prepare($patient)."\">
	<TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
	 VALIGN=\"TOP\" ALIGN=\"CENTER\">
";

//----- Add mini calendar to the top
$form .= "
	<TR><TD>".fc_generate_calendar_mini(
		$selected_date,
		"$page_name?".
			"patient=".urlencode($patient)."&".
			"room=".urlencode($room)."&".
			"type=".urlencode($type)."&".
			"travel=".urlencode($travel)."&".
			"physician=".urlencode($physician)."&".
			"duration=".urlencode($duration)."&".
			"note=".urlencode($note)
	)."</TD>
";

//----- Room/Physician/etc selection
$form .= "
	<TD ALIGN=\"LEFT\">
	".html_form::form_table(array(
	
	"<SMALL>Phy</SMALL>" =>
	freemedCalendar::refresh_select(
		"physician", 
		freemed::query_to_array(
			"SELECT phylname,phyfname, ".
			"CONCAT(phylname,', ',phyfname) AS k,".
			"id AS v FROM physician ".
			"ORDER BY phylname,phyfname"
		)
	),

	"<SMALL>Rm</SMALL>" =>
	freemedCalendar::refresh_select(
		"room",
		( $travel ? array ( _("Travel") => "0" ) :
		freemed::query_to_array(
			"SELECT CONCAT(room.roomname,' (',".
			"facility.psrcity,"."', ',facility.psrstate,')') AS k,".
			"room.id AS v ".
			"FROM room,facility ".
			"WHERE room.roompos=facility.id AND ".
			"room.roombooking='y' ".
			"ORDER BY k"
		)
		)
	),

	"<SMALL>Dur</SMALL>" =>
	freemedCalendar::refresh_select(
		"duration", 
		array (
			"0:15" => 15,	
			"0:30" => 30,	
			"0:45" => 45,	
			"1:00" => 60,	
			"1:15" => 75,	
			"1:30" => 90,	
			"1:45" => 105,
			"2:00" => 120,	
			"3:00" => 180,	
			"8:00" => 480	
		)
	),

	"<SMALL>Note</SMALL>" =>
	freemedCalendar::refresh_text_widget("note", 25)

	), "", "", "")."

	<DIV ALIGN=\"CENTER\"><INPUT TYPE=\"SUBMIT\" VALUE=\"Refresh\"></DIV>

	</TD>
	</TR>
";

//----- Generate calendar
if ($room>0 and $physician>0) { // check for this first

// Get room information
$rm_name = freemed::get_link_field ($room, "room", "roomname");
$rm_desc = freemed::get_link_field ($room, "room", "roomdescrip");

$form .= "
	<TR><TD COLSPAN=2>
	<!-- begin calendar display -->

	<TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
	 CLASS=\"calendar\">
	<TR><TD COLSPAN=4 VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
		<B>Calendar</B>
	</TD></TR>

	<TR>
		<TD COLSPAN=2 WIDTH=\"20%\">&nbsp;</TD>
		<TD COLSPAN=1 WIDTH=\"40%\">"._("Physician")."</TD>
		<TD COLSPAN=1 WIDTH=\"40%\">".prepare($rm_name)."</TD>
	</TR>
";

// Create maps for each
$p_map = freemedCalendar::map("SELECT * FROM scheduler ".
	"WHERE calphysician='".addslashes($physician)."' AND ".
	"caldateof='".addslashes($selected_date)."'");
$r_map = freemedCalendar::map("SELECT * FROM scheduler ".
	"WHERE calroom='".addslashes($room)."' AND ".
	"caldateof='".addslashes($selected_date)."'");

// Loop through the hours
for ($c_hour=freemed::config_value("calshr");
	 $c_hour<freemed::config_value("calehr");
	 $c_hour++) {
	// Display beginning of row/hour
	$form .= "<TR><TD VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"4\" ".
		"CLASS=\"calcell_hour\"><B>".
		freemedCalendar::display_hour($c_hour)."</B></TD>\n";

	// Loop through the minutes
	for ($c_min="00"; $c_min<60; $c_min+=15) {
		// Change cell_class
		$cell_class = alternate_colors (
			array("calcell", "calcell_alt")
		);

		// Get index
		$idx = $c_hour.":".$c_min;
	
		// If 15 minutes, row change (because of rowspan args)
		if ($c_min==15) {
		//	$form .= "</TR><TR>\n";
		}	

		// Generate minute cell
		$form .= "<TD ALIGN=\"LEFT\" VALIGN=\"TOP\" CLASS=\"".
			$cell_class."\"".">:".$c_min."</TD>\n";

		// First physician...
		$p_event = false;
		if ($p_map[$idx][span] == 0) {
			// Skip
		} elseif ($p_map[$idx][link] != 0) {
			// Display actual booking
			$booking = freemed::get_link_rec(
				$p_map[$idx][link], "scheduler"
			);
			
			// Show it
			$p_event = true;
			$form .= "
			<TD COLSPAN=1 CLASS=\"".$cell_class."\" ".
			"ROWSPAN=\"".$p_map[$idx][span]."\"".
			">".freemedCalendar::event_calendar_print(
			$p_map[$idx][link])."</TD>
			";
		} else {
			// Decide if it fits here
		   if (freemedCalendar::map_fit($p_map, $idx, $duration)
		   and (freemedCalendar::map_fit($r_map, $idx, $duration))) {
				$form .= "<TD COLSPAN=\"2\" ALIGN=\"CENTER\"".
				" CLASS=\"calendar_book_link\">".
				"<A HREF=\"".page_name()."?process=1&".
				"hour=".urlencode($c_hour)."&".
				"minute=".urlencode($c_min)."&".
				"room=".urlencode($room)."&".
				"physician=".urlencode($physician)."&".
				"duration=".urlencode($duration)."&".
				"type=".urlencode($type)."&".
				"selected_date=".urlencode($selected_date)."&".
				"patient=".urlencode($patient)."\"".
				">Book</A></TD>\n";
			} else {
				// If not, null block
				$form .= "<TD COLSPAN=\"1\" ".
					"CLASS=\"".$cell_class."\" ".
					">&nbsp;</TD>\n";
			}
		} // end of processing physician map

		// ... then room
		if ($r_map[$idx][span] == 0) {
			// Skip
		} elseif ($r_map[$idx][link] != 0) {
			// Display actual booking
			$booking = freemed::get_link_rec(
				$r_map[$idx][link], "scheduler"
			);
			
			// Show it
			$form .= "
			<TD COLSPAN=1 ROWSPAN=\".$r_map[$idx][span].\"".
			" CLASS=\"calcell\">".
			freemedCalendar::event_calendar_print(
			$r_map[$idx][link])."</TD>
			";
		} else {
			// Decide if it fits here
		   if (freemedCalendar::map_fit($p_map, $idx, $duration)
		   and (freemedCalendar::map_fit($r_map, $idx, $duration))) {
				// Already in physician map
				$form .= "";
			} else {
				// If not, null block
				$form .= "<TD COLSPAN=\"1\" ".
					"CLASS=\"".$cell_class."\"".
					">&nbsp;</TD>\n";
			}
		} // end of processing room map

		// End of row
		$form .= "</TR>\n"; $row = false;
		
	} // end minute looping

	// Display end of this row
	$form .= "</TR>\n";
} // end hour looping

//----- End of calendar
$form .= "
	</TABLE>
";

} // end of checking for room & physician

//----- End of page
$form .= "
	</TABLE>
";



//----- Check for process form
if ($process) {
	// Process form here
	$page_title = _("Add Appointment");
	$display_buffer .= "<CENTER>"._("Adding")." ... ";

	// Travel kludge modifications
	if ($travel) {
		$calfacility = 0;
		$calroom     = 0;
		$calpatient  = 0;
		$calprenote  = _("Travel");
	}

	$query = $sql->insert_query(
		"scheduler",
		array (
			"caldateof" => $selected_date,
			"caltype" => $type,
			"calhour" => $hour,
			"calminute" => $minute,
			"calduration" => $duration,
			"calfacility" => $facility,
			"calroom" => $room,
			"calphysician" => $physician,
			"calpatient" => $patient,	
			"calcptcode" => $cptcode,
			"calstatus" => $status,
			"calprenote" => $note
		)
	);
	$result = $sql->query ($query);

	if ($result) { $display_buffer .= _("done")."."; }
	 else        { $display_buffer .= _("ERROR");    }

	$display_buffer .= " </CENTER> <P> <CENTER>\n";
	if (!$travel) {
		if ($type=="pat") {
			//$refresh = "manage.php?id=".urlencode($patient);
			$display_buffer .= "
			<A HREF=\"manage.php?id=$patient\"
			>"._("Manage Patient")."</A>
			</CENTER>
			";
		} else {
			//$refresh = "call-in.php?action=display&id=".
				urlencode($patient);
			$display_buffer .= "
			<A HREF=\"call-in.php?action=display&id=$patient\"
			>"._("Manage Patient")."</A>
			</CENTER>
			";
		} // end checking type
	} else {
		// Travel "link"
		$refresh = "main.php";
	}
} else {
	$display_buffer .= $form;
} // done checking for processing

//----- Display the actual page
template_display();

?>
