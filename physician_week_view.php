<?php
 // $Id$
 // desc: physician's weekly calendar view
 // lic : GPL, v2

$page_name="physician_week_view.php";
include ("lib/freemed.php");
include ("lib/calendar-functions.php");

//----- Login/authenticate
freemed::connect ();

//----- Add to stack
$page_title = __("Physician Weekly View");
page_push();

//----- Check ACLs
if (!freemed::acl('schedule', 'view')) {
	trigger_error(__("You don't have permission to do that."));
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"physician_week_view.php|user $user_to_log ");}	

// check if there is a valid date... if not, assign current date
if (!checkdate(substr($for_date, 5, 2), substr($for_date, 8, 2),
	substr($for_date, 0, 4))) $for_date = $cur_date;

// calculate previous and next dates for menubar
$prev_date = freemed_get_date_prev ($for_date);
for ($i=1; $i<=6; $i++)
	$prev_date = freemed_get_date_prev ($prev_date);
$next_date = freemed_get_date_next ($for_date);
for ($i=1; $i<=6; $i++)
	$next_date = freemed_get_date_next ($next_date);

// Get week days
unset($week);
$week[] = $for_date;
$n = $for_date;
for ($i=1; $i<=6; $i++) {
	$n = freemed_get_date_next($n);
	$week[] = $n;
}

//----- Set page title
$page_title = __("Physician Weekly View");

//----- Display previous/next bar
$display_buffer .= "
  <TABLE WIDTH=\"100%\" BGCOLOR=\"#000000\" VALIGN=\"TOP\"
   ALIGN=\"CENTER\" BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR BGCOLOR=\"#000000\"><TD VALIGN=CENTER ALIGN=LEFT>
   <A HREF=\"$page_name?for_date=$prev_date&physician=$physician\"
    ><FONT COLOR=\"#ffffff\">&lt;</FONT></A>
   </TD><TD VALIGN=CENTER ALIGN=RIGHT>
   <A HREF=\"$page_name?for_date=$next_date&physician=$physician\"
    ><FONT COLOR=\"#ffffff\">&gt;</FONT></A>
   </TD></TR></TABLE>
   <BR>
";

// Check for us being a physician
$this_user = CreateObject('FreeMED.User');
if ($this_user->isPhysician() and ($physician < 1)) {
	$physician = $this_user->getPhysician();
}

// check if there is a physician specified, and if so, display their
// name, etc at the top...
if ($physician<=0) {
	$display_buffer .= "
     <CENTER>
      <B>".__("No Physician Selected")."</B>
     </CENTER>
     <BR>
	";
} else {
	$phyinfo  = freemed::get_link_rec ($physician, "physician");
	$phylname = $phyinfo["phylname"];
	$phyfname = $phyinfo["phyfname"];
	$phymname = $phyinfo["phymname"];
	$display_buffer .= "
     <CENTER>
      ".__("Physician").": $phylname, $phyfname $phymname
     </CENTER>
     <BR>
	";
}

//----- Create the maps
$scheduler = CreateObject('FreeMED.Scheduler');
foreach ($week AS $this_date) {
	$map[$this_date] = $scheduler->multimap("SELECT * FROM ".
		"scheduler WHERE calphysician='".addslashes($physician)."' ".
		"AND caldateof='".addslashes($this_date)."'");
	if ($map[$this_date][0]['count'] !== 0) {
		$temp = $map[$this_date];
		unset ($map[$this_date]);
		$map[$this_date][] = $temp;
	}
	if (count($map[$this_date]) == 0) {
		$map[$this_date][0] = $scheduler->map_init();
	}
} // and creating maps foreach

$display_buffer .= "<table border=\"0\"><tr>\n".
	"<td colspan=\"2\">&nbsp;</td>\n";
foreach ($week AS $this_date) {
	$display_buffer .= "<td ALIGN=\"LEFT\" ".
		"STYLE=\"border: 1px solid; \" ".
		"COLSPAN=\"".count($map[$this_date])."\"><b>".
		"<a href=\"physician_day_view.php?".
		"selected_date=".urlencode($this_date)."&".
		"physician=".urlencode($_REQUEST['physician'])."\"".
		">".$this_date."</a></b></td>\n";
} // end foreach week
$display_buffer .= "</tr>\n";

// Loop through the day
for ($c_hour=freemed::config_value('calshr');
		$c_hour<freemed::config_value('calehr');
		$c_hour++) {
	$display_buffer .= "<tr><td VALIGN=\"TOP\" ALIGN=\"RIGHT\" ".
		"ROWSPAN=\"4\" CLASS=\"calcell_hour\" WIDTH=\"7%\">".
		"<a NAME=\"hour".$c_hour."\" /><b>".
		$scheduler->display_hour($c_hour)."</b></td>\n";
	for ($c_min='00'; $c_min<60; $c_min+=15) {
		$idx = $c_hour.':'.$c_min;
		$display_buffer .= ( ($c_min>0) ? "<tr>" : "" ).
			"<td>".$c_min."</td>\n";
		foreach ($week AS $day) {
			foreach ($map[$day] AS $map_key => $cur_map) {
				$event = false;
				if (($cur_map[$idx]['span']+0)==0) {
					$event = true;
				} elseif (($cur_map[$idx]['link']+0)!=0) {
					$event = true;
					$display_buffer .= "<td COLSPAN=\"1\" ".
					"ROWSPAN=\"".$cur_map[$idx]['span']."\" ".
					"ALIGN=\"LEFT\" ".
					"CLASS=\"calmark".($cur_map[$idx]['mark']+0)."\">".
					$scheduler->event_calendar_print(
						$cur_map[$idx]['link'],
						true
					)."</td>\n";
				} else {
					$display_buffer .= "<td COLSPAN=\"1\" ".
					"CLASS=\"cell\" ALIGN=\"LEFT\" ".
					"VALIGN=\"MIDDLE\">&nbsp;</td>\n";
				}
			} // foreach map
		} // end foreach week
	} // end looping minutes for
	$display_buffer .= "</tr>\n";
} // end looping hours for
$display_buffer .= "</table>\n";

//----- End and display everything
template_display();
?>
