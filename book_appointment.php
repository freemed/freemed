<?php
	// $Id$
	// $Author$

$page_name = "book_appointment.php";
include ("lib/freemed.php");
$page_title = __("Book Appointment");

//----- Login/authenticate
freemed::connect ();

//----- Set current user
$this_user = CreateObject('FreeMED.User');

//----- Cache information for loadable modules
$_cache = freemed::module_cache();

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
	if (!isset($_REQUEST['type'])) {
		$_REQUEST['type'] = $type = 'pat';
	}
} elseif ($_COOKIE["current_patient"]>0) {
	if ($type != 'temp') {
		$this_patient = CreateObject('FreeMED.Patient', $_COOKIE["current_patient"]);
		$type = "pat"; // kludge to keep real patient for this
		$_REQUEST['patient'] = $patient = $_COOKIE['current_patient'];
	}
}

//----- Create scheduler object
$scheduler = CreateObject('FreeMED.Scheduler');

//----- Ledger and checking for collections, only if patient
if ($_REQUEST['type'] != 'temp') {
	$ledger = CreateObject('FreeMED.Ledger');
	$collections = $ledger->collection_warning($_REQUEST['patient']);
} else {
	$collections = false;
}

//----- Check ACLs
if (!freemed::acl('schedule', 'book')) {
	trigger_error(__("You do not have permission to book an appointment."), E_USER_ERROR);
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"book_appointment.php|user $user_to_log accesses patient $patient");}	


// Check for current physician, if not, use default
if (!isset($physician) and $this_user->isPhysician()) {
	$_REQUEST['physician'] = $physician = $this_user->getPhysician();
}

// If we have an ID present and we haven't been here, pull from database
if ($_REQUEST['id'] and !$been_here) {
	$appt = freemed::get_link_rec ($_REQUEST['id'], "scheduler");
	$selected_date = $appt['caldateof'];
	$type = $appt['caltype'];
	$duration = $appt['calduration'];
	$_REQUEST['facility'] = $facility = $appt['calfacility'];
	$room = $appt['calroom'];
	$_REQUEST['physician'] = $physician = $appt['calphysician'];
	$patient = $appt['calpatient'];
	$status = $appt['calstatus'];
	$note = stripslashes($appt['calprenote']);
} elseif (!$been_here and !isset($room)) {
	// Fudge room, if we have a current facility
	if ($_COOKIE['default_facility']) {
		$_REQUEST['facility'] = $facility = $_COOKIE['default_facility']; 
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
		$_REQUEST['facility'] = $facility = 
			freemed::get_link_rec($room, 'room', 'roompos');
	}
}

// Set duration to :15 by default
if (!isset($duration)) { $_REQUEST['duration'] = $duration = 15; }

if (strlen($selected_date) != 10) {
	$selected_date = date('Y-m-d');
} // fix date if not correct

//----- Set previous and next date variables...
$next = freemed_get_date_next ($selected_date);
$next_wk = $selected_date;
for ($i=1;$i<=7;$i++) $next_wk = freemed_get_date_next ($next_wk);
$prev = freemed_get_date_prev ($selected_date);
$prev_wk = $selected_date;
for ($i=1;$i<=7;$i++) $prev_wk = freemed_get_date_prev ($prev_wk);

//--------------- Start actual scheduler ----------------------

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

// Deal with "next available" stuff passed
if ($_REQUEST['na']) {
	$na_criteria = $_REQUEST['na'];
	unset($_REQUEST['na']); unset($na);
	$_REQUEST['stage'] = $stage = 'nextavailable';
}

switch ($_REQUEST['stage']) {

	default:
	// If there is a template, set duration and find which rooms
	// work under these circumstances
	if ($_REQUEST['appttemplate'] > 0) {
		$_REQUEST['duration'] = $duration = module_function (
			'AppointmentTemplates',
			'get_duration',
			array ( $_REQUEST['appttemplate'] )
		);
		$rooms = module_function (
			'AppointmentTemplates',
			'get_rooms',
			array ( $_REQUEST['appttemplate'] )
		);
	}
	
	$calendar_form .= "
	<form ACTION=\"".page_name()."\" METHOD=\"POST\" name=\"myform\">
	<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"1\"/>
	<input TYPE=\"HIDDEN\" NAME=\"selected_date\" VALUE=\"".addslashes($selected_date)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"hour\" VALUE=\"".$_REQUEST['hour']."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"minute\" VALUE=\"".$_REQUEST['minute']."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"stage\" VALUE=\"0\"/>
	<input TYPE=\"HIDDEN\" NAME=\"type\" VALUE=\"".prepare($type)."\"/>
	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\"
	 valign=\"TOP\" align=\"CENTER\">
	";

	//----- Add calendar to the top
	$calendar_form .= "<tr><td>".generate_calendar_mini($scheduler, $selected_date)."</td>";

	//----- Room/Physician/etc selection
	$calendar_form .= "
	<td align=\"LEFT\">
	".html_form::form_table(array(

	"<small>".__("Patient")."</small>" =>
	( $_REQUEST['type'] == 'temp' ?
	$this_patient->fullName().
	"<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\" />" :
	freemed::patient_widget("patient").
	scheduler_collection_warning($collections) ),

	"<small>".__("Template")."</small>" =>
	module_function(
		"AppointmentTemplates",
		"widget",
		array ( "appttemplate", '', 'id', array('refresh' => ( $refresh_disable ? false : true )))
	),
	
	"<small>".__("Provider")."</small>" =>
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

	"<small>".__("Duration")."</small>" =>
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
		)
		//,array('refresh' => ( $refresh_disable ? false : true ))
	),

	"<small>".__("Facility")."</small>" =>
	module_function(
		'FacilityModule',
		'widget',
		array ( 'facility' )
	),

	__("Next Available") => na_widget()

	), "", "", "")."

	<div ALIGN=\"CENTER\">
		<input class=\"button\" name=\"submit_action\" TYPE=\"SUBMIT\" VALUE=\"".__("Refresh")."\"/>
	</div>

	</td>
	</tr>
	</table>
	";

	$calendar_form .= display_booking_calendar($selected_date);

	//----- End of page
	$calendar_form .= "</form>\n";
	$display_buffer .= $calendar_form;
	template_display();
	break; // end default page

	case '2': // stage 2
	if ($a = unserialize(stripslashes($_REQUEST['na_choice']))) {
		$_REQUEST['selected_date'] = $a[0];
		$_REQUEST['hour'] = $a[1];
		$_REQUEST['minute'] = $a[2];
	}
	if ($_REQUEST['appttemplate']) {
		global $note;
		$_REQUEST['note'] = $note = module_function(
			'AppointmentTemplates',
			'get_description',
			array ( $_REQUEST['appttemplate'] )
		);
	}
	$display_buffer .= pre_screen();
	//ob_start();
	//print "<pre>";
	//print_r($_REQUEST);
	//print "</pre>";
	//$display_buffer .= ob_get_contents();
	//ob_end_clean();
	template_display();
	break; // end stage 2

	case __("Confirm Booking"):
	case '3':
	$display_buffer .= process();
	template_display();
	break; // end stage 3

	case 'nextavailable':
	// display criteria, allow choice
	$_nextday = $scheduler->date_add($_REQUEST['selected_date']);
	switch ($na_criteria) {
		case 'inaweek':
		$display_buffer .= display_available_appointments(array(
			'date' => $scheduler->date_add($_REQUEST['selected_date'], 7),
			'days' => 7,
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break;
		
		case 'in2weeks':
		$display_buffer .= display_available_appointments(array(
			'date' => $scheduler->date_add($_REQUEST['selected_date'], 14),
			'days' => 7,
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end in2weeks
		
		case 'inamonth':
		$display_buffer .= display_available_appointments(array(
			'date' => $scheduler->date_add($_REQUEST['selected_date'], 28),
			'days' => 7,
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end inamonth
		
		case 'weekday':
		$display_buffer .= display_available_appointments(array(
			'date' => $scheduler->date_add($_REQUEST['selected_date'], 7),
			'weekday' => true,
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end weekday
		
		case 'mon':
		$display_buffer .= display_available_appointments(array(
			'date' => $_next_day,
			'days' => 28,
			'forceday' => 1, // monday
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end mon
		
		case 'tue':
		$display_buffer .= display_available_appointments(array(
			'date' => $_next_day,
			'days' => 28,
			'forceday' => 2, // tuesday
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end tue
		
		case 'wed':
		$display_buffer .= display_available_appointments(array(
			'date' => $_next_day,
			'days' => 28,
			'forceday' => 3, // wednesday
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end wed
		
		case 'thu':
		$display_buffer .= display_available_appointments(array(
			'date' => $_next_day,
			'days' => 28,
			'forceday' => 4, // thursday
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end thu
		
		case 'fri':
		$display_buffer .= display_available_appointments(array(
			'date' => $_next_day,
			'days' => 28,
			'forceday' => 5, // friday
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end fri
		
		case 'sat':
		$display_buffer .= display_available_appointments(array(
			'date' => $_next_day,
			'days' => 28,
			'forceday' => 6, // saturday
			'provider' => $_REQUEST['physician'],
			'duration' => $_REQUEST['duration']
		)); break; // end sat
		break;
	
		default:
		trigger_error(__("That is not a valid selection!"), E_USER_ERROR);
		break; // end default
	}
	template_display();
	break; // end next available
}

trigger_error(__("You should never be here!"), E_USER_ERROR);

//------------------------------------------------- FUNCTIONS ----------------

function process () {
	global $id, $sql, $page_title, $scheduler;

	global $room, $facility, $patient, $appttemplate, $selected_date,
		$hour, $minute, $duration, $physician, $type;

	// Process form here
	if (!$id) {
		$page_title = __("Book Appointment");
	} else {
		$page_title = __("Move Appointment");
	}
	$buffer .= "<div ALIGN=\"CENTER\">".
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
				"calstatus" => 'scheduled',
				"calprenote" => stripslashes($note)
			)
		);
		$move = false;
	} else {
		// Perform move
		$result = $scheduler->move_appointment(
			$id,
			array (
				"caldateof" => $_REQUEST['selected_date'],
				"calhour" => $_REQUEST['hour'],
				"calminute" => $_REQUEST['minute'],
				"calduration" => $_REQUEST['duration'],
				"calfacility" => $facility,
				"calroom" => $room,
				"calprenote" => stripslashes($note),
				"calphysician" => $_REQUEST['physician']
			)
		);
		$move = true;
	}
	if ($result) { $buffer .= __("done")."."; }
	 else        { $buffer .= __("ERROR");    }

	$display_buffer .= " </div> <p/> <div ALIGN=\"CENTER\">\n";
	if (!$travel) {
		if ($type != "temp") {
			$refresh = "manage.php?id=".urlencode($patient);
			$buffer .= "
			<a href=\"manage.php?id=$patient\"
			class=\"button\"
			>".__("Manage Patient")."</a>
			</div>
			";
		} else {
			$refresh = "call-in.php?action=display&id=".
				urlencode($patient);
			$buffer .= "
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
	if ($move) {
		// Override refresh back to group calendar
		$refresh = "module_loader.php?module=groupcalendar&selected_date=".
			urlencode($_REQUEST['selected_date']);
	}
	return $buffer;
} // end function process

// We split this into a subroutine so we can call it more than once
function display_booking_calendar ($date) {
	foreach ($GLOBALS AS $k => $v) { global ${$k}; }

	//----- Generate a multiple mapping index (multimap)
	$maps = $scheduler->multimap(
		"SELECT * FROM scheduler WHERE ".
		"calphysician='".addslashes($_REQUEST['physician'])."' AND ".
		"caldateof='".addslashes($date)."'",
		( $_REQUEST['id']>0 ? $_REQUEST['id'] : -1 )
	);

	//----- Create blank map for time references
	$blank_map = $scheduler->map_init();

	//----- Set how many columns we need
	$columns = count($maps);
	if ($columns < 4) {
		$columns = 4;
		while (count($maps) < 4) {
			$maps[] = $blank_map;
		}
	}

	$form .= "
	<script language=\"javascript\"><!--
	function setApptTime(h,m) {
		document.myform.hour.value = h
		document.myform.minute.value = m
		document.myform.stage.value = '2'
	}
	function goToDate(d) {
		document.myform.selected_date.value = d
	}
	//-->
	</script>

	<center>
	<table CELLSPACING=\"0\" CELLPADDING=\"2\" BORDER=\"1\" CLASS=\"calendar\">

	<tr>
		<td COLSPAN=\"2\" WIDTH=\"80\">&nbsp;</td>
		<td COLSPAN=\"".($columns + 1)."\">&nbsp;</td>
	</tr>
";

	// Loop through the hours
	for ($c_hour=freemed::config_value("calshr"); $c_hour<freemed::config_value("calehr"); $c_hour++) {
		// Display beginning of row/hour
		$form .= "<tr><td VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"4\" ".
			"CLASS=\"calcell_hour\" width=\"50\"><b>".
			$scheduler->display_hour($c_hour)."</b></td>\n";
	
		// Loop through the minutes
		for ($c_min="00"; $c_min<60; $c_min+=15) {
			if ($c_min+0 != 0) { $form .= "<tr>"; }
			
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
			$cell_class."\""." width=\"30\">:".$c_min."</td>\n";

			// Set fit to true, by default
			$fit = true;

			// Loop through all maps
			for($cur_map=0; $cur_map<$columns; $cur_map++) {
				// Check for fitting
				if (!$scheduler->map_fit($maps[$cur_map], $idx, $duration, $id) and isset($maps[$cur_map])) {
					//$display_buffer .= "$idx not fit on $cur_map<br/>\n";
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
					<td colspan=\"1\" width=\"100\" ".
					"class=\"reverse\" ".
					"rowspan=\"".($maps[$cur_map][$idx]['span']).
					"\" onMouseOver=\"tooltip('".
					str_replace("\n", '\n', htmlentities($scheduler->event_calendar_print($maps[$cur_map][$idx]['link']))).
					"');\" onMouseOut=\"hidetooltip();\" >".
					"&nbsp;".
					"</td>\n";
				} else {
					// If not, null block
					if (!$scheduler->map_fit($blank_map, $idx, $duration)) {
						$fit = false;
					}

					if ($fit) {
						$form .= "<td colspan=\"1\" ".
						"width=\"100\" ".
						"class=\"".$cell_class."\" ".
						"onMouseOver=\"window.status='".__("Book an appointment at this time")."'; return true;\" ".
						"onMouseOut=\"window.status=''; return true;\"".
						"onClick=\"setApptTime('".$c_hour."', ".
						"'".$c_min."'); myform.submit();\" ".
						">&nbsp;</td>\n";
					} else {
						$form .= "<td class=\"calendar_book_link\"><small>&nbsp;</small></td>\n";
					}
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
				$form .= "<td colspan=\"1\" rowspan=\"1\" ".
					" width=\"100\" align=\"CENTER\"".
					" class=\"".$cell_class."\" ".
					"onMouseOver=\"window.status='".__("Book an appointment at this time")."'; return true;\" ".
					"onMouseOut=\"window.status=''; return true;\"".
					"onClick=\"setApptTime('".$c_hour."', ".
					"'".$c_min."'); myform.submit();\" ".
					">&nbsp;</td>\n";
			} else {
				$form .= "<td class=\"calendar_book_link\"><small>&nbsp;</small></td>\n";
			}
	
			// End of row
			$form .= "</tr>\n"; $row = false;
		} // end minute looping

		// Display end of this row
		if ($row) { $form .= "</tr>\n"; }
	} // end hour looping

	//----- End of calendar
	$form .= " </table></center>\n";
	return $form;
} // end function display_booking_calendar

// Function: generate_calendar_mini
//
//	Generate a miniature calendar, linking to the given page
//
// Parameters:
//
//	$given_date - Date to be selected
//
// Returns:
//
//	HTML code for miniature calendar
//
function generate_calendar_mini ($scheduler, $given_date) {
		// mostly hacked code from TWIG's calendar - ancient
		$cur_date = date('Y-m-d');

		$lang_days = array (
			"",
			__("Sun"),
			__("Mon"),
			__("Tue"),
			__("Wed"),
			__("Thu"),
			__("Fri"),
			__("Sat")
		);
		$lang_months = array (
			'',
			__("Jan"),
			__("Feb"),
			__("Mar"),
			__("Apr"),
			__("May"),
			__("Jun"),
			__("Jul"),
			__("Aug"),
			__("Sep"),
			__("Oct"),
			__("Nov"),
			__("Dec")
		);

    // break current day into pieces
    list ($cur_year, $cur_month, $cur_day) = explode ("-", $cur_date);
    if ($cur_month < 10) $cur_month = "0".$cur_month;
    if ($cur_day   < 10) $cur_day   = "0".$cur_day  ;

    // validate day
    if ((empty ($given_date)) or (!strpos($given_date, "-")))
          { $this_date = $cur_date;   }
     else { $this_date = $given_date; }

    // break day into pieces
    list ($this_year, $this_month, $this_day) = explode ("-", $this_date);

    // Figure out the last day of the month
    $lastday  [4] = $lastday [6] = $lastday [9] = $lastday [11] = 30;
    // check for leap years in february)
    if (checkdate( $this_month, 29, $this_year )) { $lastday [2] = 29; }
      else                                        { $lastday [2] = 28; }
    $lastday  [1] = $lastday  [3] = $lastday  [5] = $lastday [7] =
    $lastday  [8] = $lastday [10] = $lastday [12] = 31;

    // generate top of table
    $buffer .= "
     <center>
     <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"3\" VALIGN=\"MIDDLE\"
      ALIGN=\"CENTER\" class=\"calendar_mini\" bgcolor=\"#dfdfdf\">
      <tr BGCOLOR=\"#ffffff\">
       <td ALIGN=\"LEFT\" colspan=\"2\">
    ";

    // previous month link
    $buffer .= "     
     <a onClick=\"goToDate('".
       $scheduler->scroll_prev_month( $scheduler->scroll_prev_month( $scheduler->scroll_prev_month($this_date) ) ).
       "'); document.myform.submit();\" class=\"button_text\"
      >3</a>
     <a onClick=\"goToDate('".
       $scheduler->scroll_prev_month($this_date).
       "'); document.myform.submit();\" class=\"button_text\"
      ><small>".__("prev")."</small></a>
     </td>
     <td COLSPAN=\"5\" ALIGN=\"CENTER\">
       <b>".prepare($lang_months[0+$this_month])." ".$this_year."</b>
     </td>
     <td ALIGN=\"RIGHT\" colspan=\"2\">
     <a onClick=\"goToDate('".
       $scheduler->scroll_next_month($this_date).
       "'); document.myform.submit();\" class=\"button_text\"
      ><small>".__("next")."</small></a>
     <a onClick=\"goToDate('".
       $scheduler->scroll_next_month( $scheduler->scroll_next_month( $scheduler->scroll_next_month($this_date) ) ).
       "'); document.myform.submit();\" class=\"button_text\"
      >3</a>
     </td>
     </tr>
     <tr>
      <td colspan=\"1\">&nbsp;</td>
    ";
    // print days across top
    for( $i = 1; $i <= 7; $i++) {
     $buffer .= "
      <td ALIGN=\"CENTER\">
       <small>".htmlentities($lang_days[$i])."</small>
      </td>
     ";
    } // end of day display
    $buffer .= "
      <td colspan=\"1\">&nbsp;</td>
     </tr>
     <tr>
      <td colspan=\"1\">&nbsp;</td>
    ";

    // calculate first day
    $first_day = date( 'w', mktime( 0, 0, 0, $this_month, 1, $this_year ) );
    $day_row = 0;

    if( $first_day > 0 ) {
  	while( $day_row < $first_day ) {
   		$buffer .= "\t<td ALIGN=\"RIGHT\" BGCOLOR=\"#dfdfdf\">&nbsp;</td>\n";
   		$day_row += 1;  
  		}
 	} // end while day row < first day

 	while( $day < $lastday[($this_month + 0)] ) 
		{
  		if( ( $day_row % 7 ) == 0) 
			{
   			$buffer .= "\t<td colspan=\"1\">&nbsp;</td>\n".
				"</tr>\n<tr>\n".
				"<td colspan=\"1\">&nbsp;</td>\n";
  			}

  		$dayp = $day + 1;

        $thisclass = (
	  ( $dayp       == $cur_day AND
            $this_month == $cur_month AND
            $this_year  == $cur_year ) ?
            "calendar_mini_selected" : 
	  ( $dayp       == $this_day ?
	    "calendar_mini_current" : "calendar_mini_cell" ) );
       
	$buffer .= "<td align=\"RIGHT\" class=\"".$thisclass."\">\n";

        $buffer .= "<a onClick=\"goToDate('".
         date("Y-m-d",mktime(0,0,0,$this_month,$dayp,$this_year) ).
         "'); document.myform.submit();\">$dayp</a>\n";
      $buffer .= "
       </td>
      ";
      $day++;
      $day_row++;
    }

	while( $day_row % 7 ) {
		$buffer .= "<td ALIGN=\"RIGHT\" BGCOLOR=\"#dfdfdf\">&nbsp;</td>\n";
		$day_row += 1;  
	} // end of day row
	$buffer .= "<td colspan=\"1\">&nbsp;</td>\n".
	"</tr><tr>\n".
	"<td COLSPAN=\"9\" ALIGN=\"RIGHT\" class=\"button_style\">\n".
	"<a onClick=\"goToDate('".date("Y-m-d") .
         "'); document.myform.submit();\" class=\"button_text\" ".
	"><small>".__("go to today")."</small></a>\n".
	"</td></tr></table></center>\n";
	return $buffer;
} // end function generate_calendar_mini

function na_widget ( ) {
	$na_types = array (
		"--" => '',
		__("One Week From Now") => 'inaweek',
		__("Two Weeks From Now") => 'in2weeks',
		__("One Month From Now") => 'inamonth',
		__("Weekdays Only") => 'weekdays',
		__("Mondays Only") => 'mon',
		__("Tuesdays Only") => 'tue',
		__("Wednesdays Only") => 'wed',
		__("Thursdays Only") => 'thu',
		__("Fridays Only") => 'fri',
		__("Saturdays Only") => 'sat'
	);
	$buffer .= "<select name=\"na\" onChange=\"document.myform.submit();\">\n";
	foreach ($na_types AS $k => $v) {
		$buffer .= "<option value=\"".prepare($v)."\">".prepare($k)."</option>\n";
	}
	$buffer .= "</select>\n";
	return $buffer;
} // end function na_widget

function display_available_appointments ( $params ) {
	global $scheduler;

	// Set stage to be second stage once we choose something
	$_REQUEST['stage'] = '2';

	$vars = array (
		'patient',
		'physician',
		'duration',
		'appttemplate',
		'type',
		'id',
		'facility',
		'stage'
	);
	
	$na = $scheduler->next_available($params);
	if (!$na) {
		return "<div align=\"center\">".
			__("There are no available appointments which match your criteria.").
			"</div>\n";
	}
	$buffer .= "<br/>\n";
	$buffer .= "<form method=\"post\" name=\"myform\" id=\"myform\">\n";
	$buffer .= "<div align=\"center\">\n";
	$buffer .= __("Please select from the following available times and dates:")."<br/>\n";
	foreach ($vars AS $v) {
		$buffer .= "<input type=\"hidden\" name=\"".$v."\" ".
			"value=\"".prepare($_REQUEST[$v])."\" />\n";
	}
	$buffer .= "<select name=\"na_choice\" onChange=\"this.form.submit();\">\n";
	$buffer .= "<option value=\"\"> ---- </option>\n";
	foreach ($na as $a) {
		$buffer .= "<option value=\"".prepare(serialize($a))."\">".
			fm_date_print($a[0], true)." ".
			$scheduler->display_time($a[1],$a[2])."</option>\n";
	}
	$buffer .= "</select>\n";
	$buffer .= "</div>\n";
	$buffer .= "<div align=\"center\">\n";
	$buffer .= "<input type=\"submit\" name=\"stage\" ".
		"class=\"button\" value=\"".__("Cancel")."\" />\n";
	$buffer .= "</div>\n";
	$buffer .= "</form>\n";
	return $buffer;
} // end function display_available_appointments

function pre_screen ( ) {
	global $scheduler;

	if ($_REQUEST['appttemplate']) {
		// Get list of available rooms
		$rooms = module_function (
			'AppointmentTemplates',
			'get_rooms',
			array ( $_REQUEST['appttemplate'] )
		);

		// Make sure that we don't overbook anything ...
		if ($rooms) {
			foreach ($rooms AS $room_key => $this_room) {
				$map = $scheduler->map(
					"SELECT * FROM scheduler ".
					"WHERE calfacility='".addslashes($_REQUEST['facility'])."' ".
					"AND calphysician='".addslashes($_REQUEST['physician'])."' ".
					"AND caldateof='".addslashes($_REQUEST['selected_date'])."' "
				);
				$idx = $_REQUEST['hour'].":".$_REQUEST['minute'];
				if (!$scheduler->map_fit($map, $idx, $_REQUEST['duration'])) {
					// Remove from list
					unset($rooms[$room_key]);
				} // end if doesn't fit
			} // end foreach rooms
			if (count($rooms) < 1) {
				// TODO: Should we ever be here?
				// TODO: Should this end differently?
				trigger_error(__("Unable to find open slot!"), E_USER_ERROR);
			}
		} // end checking for rooms
	}
	
	$provider = CreateObject('FreeMED.Physician', $_REQUEST['physician']);
	$patient = CreateObject('FreeMED.Patient', $_REQUEST['patient']);
	$buffer .= "<form method=\"post\" name=\"myform\" id=\"myform\">\n";
	$vars = array (
		'patient',
		'selected_date',
		'hour',
		'minute',
		'physician',
		'duration',
		'appttemplate',
		'id',
		'type'
	);
	foreach ($vars AS $v) {
		$buffer .= "<input type=\"hidden\" name=\"".$v."\" ".
			"value=\"".prepare($_REQUEST[$v])."\" />\n";
	}
	$buffer .= html_form::form_table(array(
		__("Date") => fm_date_print($_REQUEST['selected_date']),
		__("Time") => $scheduler->display_time(
			$_REQUEST['hour'],
			$_REQUEST['minute']
			),
		__("Duration") => $_REQUEST['duration'].' m',
		__("Patient") => $patient->fullName(),
		__("Provider") => $provider->fullName(),
		__("Place of Service") => module_function (
			'FacilityModule',
			'to_text',
			array ( $_REQUEST['facility'] )
		),
		__("Booking Location") =>
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
				( $rooms ?
				" AND FIND_IN_SET(room.id, '".join(',', $rooms)."') " :
				"" ).
				"ORDER BY k"
			) )
		),
		__("Note") => html_form::text_widget('note')
	));
	$buffer .= "<div align=\"center\">\n";
	$buffer .= "<input type=\"submit\" name=\"stage\" ".
		"class=\"button\" ".
		"value=\"".__("Confirm Booking")."\" />\n";
	$buffer .= "<input type=\"submit\" name=\"stage\" ".
		"class=\"button\" ".
		"value=\"".__("Cancel")."\" />\n";
	$buffer .= "</div>\n";
	/*
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
		"calstatus" => 'scheduled'
		"calprenote" => stripslashes($note)
	*/
	return $buffer;
} // end function pre_screen

function scheduler_collection_warning ( $amt ) {
	if ($amt) {
		return "<br>\n".
		"<span style=\"color: #ff0000;\">".
		"<small><b>[ \$$amt ".__("in collection")."]</b></small>".
		"</span>";
	} else {
		return "";
	}
} // end method scheduler_collection_warning

?>
