<?php
 // $Id$
 // note: scheduling module for freemed-project
 // lic : GPL, v2

$page_name = "book_appointment.php";
include ("lib/freemed.php");
include ("lib/calendar-functions.php");

//----- Login/authenticate
freemed::connect ();

//----- Set current user
$this_user = CreateObject('FreeMED.User');

//----- Check for current patient
if ($travel) {
	// Kludge travel, patient = 0
	$patient = 0; $type = "pat"; $room = 0;
} elseif ($patient>0) {
	$this_patient = CreateObject('FreeMED.Patient', $patient, ($type=="temp"));
} elseif ($_COOKIE["current_patient"]>0) {
	$this_patient = CreateObject('FreeMED.Patient', $_COOKIE["current_patient"]);
	$type = "pat"; // kludge to keep real patient for this
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"bookappointment.php|user $user_to_log accesses patient $patient");}	


// Check for current physician, if not, use default
if (!isset($physician) and $this_user->isPhysician()) {
	$physician = $this_user->getPhysician();
}

// If we have an ID present and we haven't been here, pull from database
if ($id and !$been_here) {
	$appt = freemed::get_link_rec ($id, "scheduler");
	$selected_date = $appt['caldateof'];
	$type = $appt['caltype'];
	$duration = $appt['calduration'];
	$facility = $appt['calfacility'];
	$room = $appt['calroom'];
	$physician = $appt['calphysician'];
	$patient = $appt['calpatient'];
	$status = $appt['calstatus'];
	$note = $appt['calprenote'];
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
	$display_buffer .= freemed::patient_box($this_patient);
} else {
	// Display travel bar
	$display_buffer .= "
	<div ALIGN=\"CENTER\">
	<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"5\" WIDTH=\"100%\">
	<tr BGCOLOR=\"#000000\"><td ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
	<font COLOR=\"#ffffff\"><b>".__("Travel")."</b></font>
	</td></tr></table>
	</div>
	";
}

//----- Create form
if (date_in_the_past($selected_date)) {
	$form .= "
		<div ALIGN=\"CENTER\"><i><font SIZE=\"-2\"
		>".__("this date occurs in the past")."</font></i>
		</div>
	";
}
$form .= "
	<form ACTION=\"".page_name()."\" METHOD=\"POST\">
	<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"1\"/>
	<input TYPE=\"HIDDEN\" NAME=\"selected_date\" ".
	"VALUE=\"".prepare($selected_date)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"type\" ".
	"VALUE=\"".prepare($type)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"patient\" ".
	"VALUE=\"".prepare($patient)."\"/>
	<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\" BORDER=\"0\"
	 VALIGN=\"TOP\" ALIGN=\"CENTER\">
";

//----- Add mini calendar to the top
$form .= "
	<tr><td>".fc_generate_calendar_mini(
		$selected_date,
		"$page_name?".
			"patient=".urlencode($patient)."&".
			"room=".urlencode($room)."&".
			"type=".urlencode($type)."&".
			"travel=".urlencode($travel)."&".
			"physician=".urlencode($physician)."&".
			"duration=".urlencode($duration)."&".
			"note=".urlencode($note)."&".
			"id=".urlencode($id)
	)."</td>
";

//----- Room/Physician/etc selection
$form .= "
	<td ALIGN=\"LEFT\">
	".html_form::form_table(array(
	
	"<small>Phy</small>" =>
	html_form::select_widget(
		"physician", 
		freemed::query_to_array(
			"SELECT phylname,phyfname, ".
			"CONCAT(phylname,', ',phyfname) AS k,".
			"id AS v FROM physician ".
			"WHERE phylname != '' ".
			"ORDER BY phylname,phyfname"
		),
		array('refresh' => true)
	),

	"<small>Rm</small>" =>
	html_form::select_widget(
		"room",
		( $travel ? array ( __("Travel") => "0" ) :
		freemed::query_to_array(
			"SELECT CONCAT(room.roomname,' (',".
			"facility.psrcity,', ',facility.psrstate,')') AS k,".
			"room.id AS v ".
			"FROM room,facility ".
			"WHERE room.roompos=facility.id AND ".
			"room.roombooking='y' ".
			"ORDER BY k"
		)
		),
		array('refresh' => true)
	),

	"<small>Dur</small>" =>
	html_form::select_widget(
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
			"4:00" => 240,	
			"5:00" => 300,	
			"6:00" => 360,	
			"7:00" => 420,	
			"8:00" => 480	
		),
		array('refresh' => true)
	),

	"<small>".__("Note")."</small>" =>
	freemedCalendar::refresh_text_widget("note", 25)

	), "", "", "")."

	<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Refresh")."\"/>
	</div>

	</td>
	</tr>
";

//----- Generate calendar
if ($room>0 and $physician>0) { // check for this first

// Get room information
$rm_name = freemed::get_link_field ($room, "room", "roomname");
$rm_desc = freemed::get_link_field ($room, "room", "roomdescrip");

$form .= "
	<tr><td COLSPAN=\"2\">
	<!-- begin calendar display -->

	<table WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0 CLASS=\"calendar\">
	<tr><td COLSPAN=4 VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
		<B>".__("Calendar")."</B>
	</td></tr>

	<tr>
		<td COLSPAN=\"2\" WIDTH=\"20%\">&nbsp;</td>
		<td COLSPAN=\"1\" WIDTH=\"40%\">".__("Physician")."</td>
		<td COLSPAN=\"1\" WIDTH=\"40%\">".prepare($rm_name)."</td>
	</tr>
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
	$form .= "<tr><td VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"4\" ".
		"CLASS=\"calcell_hour\"><B>".
		freemedCalendar::display_hour($c_hour)."</B></td>\n";

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
		//	$form .= "</tr><tr>\n";
		}	

		// Generate minute cell
		$form .= "<td ALIGN=\"LEFT\" VALIGN=\"TOP\" CLASS=\"".
			$cell_class."\"".">:".$c_min."</td>\n";

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
			<td COLSPAN=\"1\" CLASS=\"".$cell_class."\" ".
			"ROWSPAN=\"".$p_map[$idx][span]."\"".
			">".freemedCalendar::event_calendar_print(
			$p_map[$idx][link])."</td>
			";
		} else {
			// Decide if it fits here
		   if (freemedCalendar::map_fit($p_map, $idx, $duration)
		   and (freemedCalendar::map_fit($r_map, $idx, $duration))) {
				$form .= "<td COLSPAN=\"2\" ALIGN=\"CENTER\"".
				" CLASS=\"calendar_book_link\" ".
				"onClick=\"window.location='".
					page_name()."?process=1&".
					"hour=".urlencode($c_hour)."&".
					"minute=".urlencode($c_min)."&".
					"room=".urlencode($room)."&".
					"physician=".urlencode($physician)."&".
					"duration=".urlencode($duration)."&".
					"type=".urlencode($type)."&".
					"selected_date=".urlencode($selected_date)."&".
					"id=".urlencode($id)."&".
					"note=".urlencode($note)."&".
					"patient=".urlencode($patient)."'; ".
					"return true;\"".
				">".
				"<a HREF=\"".page_name()."?process=1&".
				"hour=".urlencode($c_hour)."&".
				"minute=".urlencode($c_min)."&".
				"room=".urlencode($room)."&".
				"physician=".urlencode($physician)."&".
				"duration=".urlencode($duration)."&".
				"type=".urlencode($type)."&".
				"selected_date=".urlencode($selected_date)."&".
				"id=".urlencode($id)."&".
				"note=".urlencode($note)."&".
				"patient=".urlencode($patient)."\"".
				">".__("Book")."</a></td>\n";
			} else {
				// If not, null block
				$form .= "<td COLSPAN=\"1\" ".
					"CLASS=\"".$cell_class."\" ".
					">&nbsp;</td>\n";
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
			<td COLSPAN=\"1\" ROWSPAN=\"".$r_map[$idx][span]."\"".
			" CLASS=\"calcell\">".
			freemedCalendar::event_calendar_print(
			$r_map[$idx][link])."</td>
			";
		} else {
			// Decide if it fits here
		   if (freemedCalendar::map_fit($p_map, $idx, $duration)
		   and (freemedCalendar::map_fit($r_map, $idx, $duration))) {
				// Already in physician map
				$form .= "";
			} else {
				// If not, null block
				$form .= "<td COLSPAN=\"1\" ".
					"CLASS=\"".$cell_class."\"".
					">&nbsp;</td>\n";
			}
		} // end of processing room map

		// End of row
		$form .= "</tr>\n"; $row = false;
		
	} // end minute looping

	// Display end of this row
	$form .= "</tr>\n";
} // end hour looping

//----- End of calendar
$form .= "
	</table>
";

} // end of checking for room & physician

//----- End of page
$form .= "
	</table>
";



//----- Check for process form
if ($process) {
	// Process form here
	if (!$id) {
		$page_title = __("Book Appointment");
	} else {
		$page_title = __("Move Appointment");
	}
	$display_buffer .= "<div ALIGN=\"CENTER\">".
		( $id ? __("Moving") : __("Booking") )." ... ";

	// Travel kludge modifications
	if ($travel) {
		$calfacility = 0;
		$calroom     = 0;
		$calpatient  = 0;
		$calprenote  = __("Travel");
	}

	// Get facility from room
	$facility = freemed::get_link_field($room, "room", "roompos");

	if (!$id) {
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
	} else {
		// Perform move
		$query = $sql->update_query(
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
			),
			array (
				"id" => $id
			)
		);
	}
	$result = $sql->query ($query);

	if ($result) { $display_buffer .= __("done")."."; }
	 else        { $display_buffer .= __("ERROR");    }

	$display_buffer .= " </div> <p/> <div ALIGN=\"CENTER\">\n";
	if (!$travel) {
		if ($type != "temp") {
			$refresh = "manage.php?id=".urlencode($patient);
			$display_buffer .= "
			<a HREF=\"manage.php?id=$patient\"
			>".__("Manage Patient")."</a>
			</div>
			";
		} else {
			$refresh = "call-in.php?action=display&id=".
				urlencode($patient);
			$display_buffer .= "
			<a HREF=\"call-in.php?action=display&id=$patient\"
			>".__("Manage Patient")."</a>
			</div>
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
