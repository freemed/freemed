<?php
 // $Id$
 // note: scheduling module for freemed-project
 // lic : GPL, v2

$page_name = "book_appointment.php";
include ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//----- Set current user
$this_user = CreateObject('FreeMED.User');

//----- Check for booking refresh
if ($this_user->getManageConfig('booking_refresh') == '0') {
	$refresh_disable = true;
} else {
	$refresh_disable = false;
}

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

//----- Create scheduler object
$scheduler = CreateObject('FreeMED.Scheduler');

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"book_appointment.php|user $user_to_log accesses patient $patient");}	


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
	$note = stripslashes($appt['calprenote']);
} elseif (!$been_here and !isset($room)) {
	// Fudge room, if we have a current facility
	if ($_COOKIE['default_facility']) {
		// Get first room from there
		$result = $sql->query("SELECT * FROM room ".
			"WHERE roompos='".addslashes(
			$_COOKIE['default_facility'])."'");
		if ($sql->results($result)) {
			$r = $sql->fetch_array($result);
			$room = $r['id'];
		}
	}
	if ($this_user->getManageConfig('default_room')) {
		$room = $this_user->getManageConfig('default_room');
	}
}

// Set duration to :15 by default
if (!isset($duration)) { $duration = 15; }

if (strlen($selected_date) != 10) {
	$selected_date = $cur_date;
} // fix date if not correct

//----- Set previous and next date variables...
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
	<div align=\"CENTER\">
	<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">
	<tr bgcolor=\"#000000\"><td align=\"CENTER\" valign=\"MIDDLE\">
	<font color=\"#ffffff\"><b>".__("Travel")."</b></font>
	</td></tr></table>
	</div>
	";
}

//----- Create form
if ($scheduler->date_in_the_past($selected_date)) {
	$calendar_form .= "
		<div ALIGN=\"CENTER\"><i><font SIZE=\"-2\"
		>".__("this date occurs in the past")."</font></i>
		</div>
	";
}
$calendar_form .= "
	<form ACTION=\"".page_name()."\" METHOD=\"POST\">
	<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"1\"/>
	<input TYPE=\"HIDDEN\" NAME=\"selected_date\" ".
	"VALUE=\"".prepare($selected_date)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"type\" ".
	"VALUE=\"".prepare($type)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"patient\" ".
	"VALUE=\"".prepare($patient)."\"/>
	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\"
	 valign=\"TOP\" align=\"CENTER\">
";

//----- Add mini calendar to the top
$calendar_form .= "
	<tr><td>".$scheduler->generate_calendar_mini(
		$selected_date,
		"$page_name?".
			"patient=".urlencode($patient)."&".
			"room=".urlencode($room)."&".
			"type=".urlencode($type)."&".
			"travel=".urlencode($travel)."&".
			"physician=".urlencode($physician)."&".
			"duration=".urlencode($duration)."&".
			"note=".urlencode(stripslashes($note))."&".
			"been_here=1&".
			"id=".urlencode($id)
	)."</td>
";

//----- Room/Physician/etc selection
$calendar_form .= "
	<td align=\"LEFT\">
	".html_form::form_table(array(
	
	"<small>".__("Phy")."</small>" =>
	html_form::select_widget(
		"physician", 
		array_merge(
			freemed::query_to_array(
				"SELECT phylname,phyfname, ".
				"CONCAT(phylname,', ',phyfname) AS k,".
				"id AS v FROM physician ".
				"WHERE phylname != '' AND phyref != 'yes' ".
				"ORDER BY phylname,phyfname"
			),
			array(__("Other") => "0")
		),
		array('refresh' => ( $refresh_disable ? false : true ))
	),

	"<small>".__("Rm")."</small>" =>
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
		array('refresh' => ( $refresh_disable ? false : true ))
	),

	"<small>".__("Dur")."</small>" =>
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
			"2:15" => 135,
			"2:30" => 150,	
			"2:45" => 165,
			"3:00" => 180,	
			"3:15" => 195,
			"3:30" => 210,	
			"3:45" => 225,
			"4:00" => 240,	
			"4:15" => 255,
			"4:30" => 270,
			"4:45" => 285,
			"5:00" => 300,	
			"5:15" => 315,
			"5:30" => 330,
			"5:45" => 345,
			"6:00" => 360,	
			"6:15" => 375,
			"6:30" => 390,
			"6:45" => 405,
			"7:00" => 420,	
			"7:30" => 450,
			"8:00" => 480	
		),
		array('refresh' => ( $refresh_disable ? false : true ))
	),

	"<small>".__("Note")."</small>" =>
	html_form::text_widget('note', array ('length' => 250,
		'refresh' => !$refresh_disable ) )

	), "", "", "")."

	<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Refresh")."\"/>
	</div>

	</td>
	</tr>
";

//----- Generate calendar
//if ($room>0 and isset($physician)) { // check for this first

// Get room information
if ($room > 0) {
	$_room = freemed::get_link_rec($room, 'room');
	$rm_name = $_room['roomname'];
	$rm_desc = $_room['roomdescrip'];
}

// We split this into a subroutine so we can call it more than once
function display_booking_calendar ($date) {
	foreach ($GLOBALS AS $k => $v) { global ${$k}; }

//----- Generate a multiple mapping index (multimap)
$maps = $scheduler->multimap(
	"SELECT * FROM scheduler WHERE ( ".
		"calphysician='".addslashes($physician)."' OR ".
		"calroom='".addslashes($room)."' ) AND ".
		"caldateof='".addslashes($date)."'",
		( isset($id) ? $id : -1 )
);

//----- Create blank map for time references
$blank_map = $scheduler->map_init();

//----- Set how many columns we need
$columns = count($maps);

$form .= "
	<tr><td COLSPAN=\"2\">
	<!-- begin calendar display -->

	<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\" BORDER=\"0\" CLASS=\"calendar\">

	<tr>
		<td COLSPAN=\"2\" WIDTH=\"50\">&nbsp;</td>
		<td COLSPAN=\"".($columns + 1)."\">&nbsp;</td>
	</tr>
";

// Loop through the hours
for ($c_hour=freemed::config_value("calshr"); $c_hour<freemed::config_value("calehr"); $c_hour++) {
	// Display beginning of row/hour
	$form .= "<tr><td VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"4\" ".
		"CLASS=\"calcell_hour\"><b>".
		$scheduler->display_hour($c_hour)."</b></td>\n";

	// Loop through the minutes
	for ($c_min="00"; $c_min<60; $c_min+=15) {
		// Make sure to zero the current event counter
		$event = false;
	
		// Change cell_class
		$cell_class = alternate_colors (
			array("calcell", "calcell_alt")
		);

		// Get index
		$idx = $c_hour.":".$c_min;
	
		// Generate minute cell
		$form .= "<td align=\"LEFT\" valign=\"TOP\" class=\"".
			$cell_class."\"".">:".$c_min."</td>\n";

		// Set fit to true, by default
		$fit = true;

		// Loop through all maps
		for($cur_map=0; $cur_map<$columns; $cur_map++) {
			// Check for fitting
			if (!$scheduler->map_fit($maps[$cur_map], $idx, $duration, $id)) {
				$fit = false;
			}
			
			if ($maps[$cur_map][$idx]['span'] == 0) {
				// Skip
				$event = true;
			} elseif ($maps[$cur_map][$idx]['link'] != 0) {
				// Display booking
				$booking = freemed::get_link_rec(
					$maps[$cur_map][$idx]['link'],
					'scheduler'
				);

				// Show the event
				$event = true;
				$form .= "
				<td colspan=\"1\" class=\"".(
				$maps[$cur_map][$idx]['selected'] ?
				'calcell_selected' : $cell_class )."\" ".
				"rowspan=\"".($maps[$cur_map][$idx]['span']).
				"\">".$scheduler->event_calendar_print(
					$maps[$cur_map][$idx]['link']
				)."</td>\n";
			} else {
				// If not, null block
				$form .= "<td colspan=\"1\" ".
				"class=\"".$cell_class."\" ".
				">&nbsp;</td>\n";
			}
		} // end looping

		// If overbooking, true fit by default
		if (freemed::config_value('cal_ob') == 'enable') {
			$fit = true;
		} elseif ($event) {
			$fit = false;
		}

		// Quick check to see if this will fit on a blank map
		if (!$scheduler->map_fit($blank_map, $idx, $duration)) {
			$fit = false;
		}

		// Add booking row
		if ($fit) {
			$form .= "<td colspan=\"1\" align=\"CENTER\"".
				" class=\"calendar_book_link\" ".
				//----------------------------------------
				// If the following portion of code is
				// uncommented, the system will double
				// book from most browsers. Might have
				// to either-or the link and the
				// onClick to solve the problem. -Jeff
				//----------------------------------------
				//"onClick=\"window.location='".
				//page_name()."?process=1&".
				//"hour=".urlencode($c_hour)."&".
				//"minute=".urlencode($c_min)."&".
				//"room=".urlencode($room)."&".
				//"physician=".urlencode($physician)."&".
				//"duration=".urlencode($duration)."&".
				//"type=".urlencode($type)."&".
				//"selected_date=".urlencode($date)."&".
				//"id=".urlencode($id)."&".
				//"note=".urlencode($note)."&".
				//"patient=".urlencode($patient)."'; ".
				//"return true;\" ".
				"onMouseOver=\"window.status='".__("Book an appointment at this time")."'; return true;\" ".
				"onMouseOut=\"window.status=''; return true;\"".
				">".
				"<a href=\"".page_name()."?process=1&".
				"hour=".urlencode($c_hour)."&".
				"minute=".urlencode($c_min)."&".
				"room=".urlencode($room)."&".
				"physician=".urlencode($physician)."&".
				"duration=".urlencode($duration)."&".
				"type=".urlencode($type)."&".
				"selected_date=".urlencode($date)."&".
				"id=".urlencode($id)."&".
				"note=".urlencode(stripslashes($note))."&".
				"patient=".urlencode($patient)."\" ".
				">".__("Book")."</a></td>\n";
		} else {
			$form .= "<td class=\"".$cell_class."\">&nbsp;</td>\n";
		}

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
	return $form;
} // end function display_booking_calendar

//} // end of checking for room & physician

$day = $selected_date;
for ($i = 1; $i <= 5; $i++) {
	$calendar_form .= "<div align=\"center\">".fm_date_print($day, true)."</div>\n";
	$calendar_form .= display_booking_calendar($day);
	$day = freemed_get_date_next($day);
}

//----- End of page
$calendar_form .= "
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
		$facility = 0;
		$room     = 0;
		$patient  = 0;
		$note     = __("Travel");
	} else {
		// Get facility for current room
		$facility = freemed::get_link_field($room, "room", "roompos");
		$note = stripslashes($_REQUEST['note']);
	}

	if (!$id) {
		$result = $scheduler->set_appointment(
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
				"calprenote" => stripslashes($note)
			)
		);
	} else {
		// Perform move
		$result = $scheduler->move_appointment(
			$id,
			array (
				"caldateof" => $_REQUEST['selected_date'],
				"calhour" => $_REQUEST['hour'],
				"calminute" => $_REQUEST['minute'],
				"calduration" => $duration,
				"calfacility" => $facility,
				"calroom" => $room,
				"calprenote" => stripslashes($note)
			)
		);
	}
	if ($result) { $display_buffer .= __("done")."."; }
	 else        { $display_buffer .= __("ERROR");    }

	$display_buffer .= " </div> <p/> <div ALIGN=\"CENTER\">\n";
	if (!$travel) {
		if ($type != "temp") {
			$refresh = "manage.php?id=".urlencode($patient);
			$display_buffer .= "
			<a href=\"manage.php?id=$patient\"
			class=\"button\"
			>".__("Manage Patient")."</a>
			</div>
			";
		} else {
			$refresh = "call-in.php?action=display&id=".
				urlencode($patient);
			$display_buffer .= "
			<a href=\"call-in.php?action=display&id=$patient\"
			class=\"button\"
			>".__("Manage Patient")."</a>
			</div>
			";
		} // end checking type
	} else {
		// Travel "link"
		$refresh = "main.php";
	}
} else {
	$display_buffer .= $calendar_form;
} // done checking for processing

//----- Display the actual page
template_display();

?>
