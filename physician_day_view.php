<?php
 // $Id$
 // desc: physician's daily calendar view
 // lic : GPL, v2

$page_name="physician_day_view.php";
include_once ("lib/freemed.php");
include_once ("lib/calendar-functions.php");

//----- Login/authenticate
freemed::connect ();

//----- Add to page stack
$page_title = __("Physician Daily View");
page_push();

//----- Check ACLs
if (!freemed::acl('schedule', 'view')) {
	trigger_error(__("You do not have permission to do that."));
}

// If selected_date is set, store in session
if ($_REQUEST['selected_date']) {
	$_SESSION['remember']['physician_calendar_selected_date'] = $_REQUEST['selected_date'];
}

//------HIPAA Logging
$user_to_log=$_SESSION['authdata']['user'];
if((LOGLEVEL<1)||LOG_HIPAA){syslog(LOG_INFO,"physician_day_view.php|user $user_to_log ");}	

// If we are a provider, get that and throw it in
if (!$physician) {
	if (!is_object($this_user)) { $this_user = CreateObject('FreeMED.User'); }
	if ($this_user->getPhysician()) { $physician = $this_user->getPhysician(); }
}

//----- Check if there is a valid date... if not, assign current date
if ($_SESSION['remember']['physician_calendar_selected_date']) {
	$selected_date = $for_date = $_SESSION['remember']['physician_calendar_selected_date'];
} else {
	if (!empty($selected_date)) $for_date = $selected_date;
	if (!checkdate(substr($for_date, 5, 2), substr($for_date, 8, 2),
		substr($for_date, 0, 4))) $for_date = $cur_date;
}

//----- Calculate previous and next dates for menubar
$prev_week = $prev_date = freemed_get_date_prev ($for_date);
for ($i=1; $i<=6; $i++)
	$prev_week = freemed_get_date_prev ($prev_week);
$next_week = $next_date = freemed_get_date_next ($for_date);
for ($i=1; $i<=6; $i++)
	$next_week = freemed_get_date_next ($next_week);

//----- Set page title
$page_title = __("Physician Daily View");

//----- Set key bindings appropriately
freemed::key_binding(array(
	'37' => "$page_name?selected_date=$prev_date&physician=$physician",
	'39' => "$page_name?selected_date=$next_date&physician=$physician",
	'38' => "$page_name?selected_date=$prev_week&physician=$physician",
	'40' => "$page_name?selected_date=$next_week&physician=$physician"
));

//----- Display previous/next bar
$display_buffer .= "
  <TABLE WIDTH=\"100%\" BGCOLOR=\"#000000\" VALIGN=TOP ALIGN=CENTER BORDER=0
   CELLSPACING=0 CELLPADDING=2><TR BGCOLOR=\"#000000\">
   <TD VALIGN=CENTER ALIGN=LEFT>
   <A HREF=\"$page_name?selected_date=$prev_date&physician=$physician\"
    ><FONT COLOR=\"#ffffff\">&lt;</FONT></A>
   </TD><TD VALIGN=\"CENTER\" ALIGN=\"CENTER\">
   <A HREF=\"physician_week_view.php?physician=".urlencode($physician)."&".
	"for_date=".urlencode($for_date)."\"
	<FONT COLOR=\"#ffffff\">".__("Week View")."</FONT></A>
   </TD><TD VALIGN=CENTER ALIGN=RIGHT>
   <A HREF=\"$page_name?selected_date=$next_date&physician=$physician\"
    ><FONT COLOR=\"#ffffff\">&gt;</FONT></A>
   </TD></TR></TABLE>
   <P>
";

// check if there is a physician specified, and if so, display their
// name, etc at the top...
if ($physician<=0) {
	$display_buffer .= "
     <div ALIGN=\"CENTER\">
      <b>".__("No Physician Selected")."</b>
     </div>
     <br/>
	";
} else {
	$phyinfo  = freemed::get_link_rec ($physician, "physician");
	$phylname = $phyinfo["phylname"];
	$phyfname = $phyinfo["phyfname"];
	$phymname = $phyinfo["phymname"];
	$display_buffer .= "
     <div ALIGN=\"CENTER\">
      <B>".__("Physician")." : </B>
       $phylname, $phyfname $phymname
     </div>
     <br/>
	";
}

//----- Quick fix for first load
if (empty($selected_date)) $selected_date = date("Y-m-d");

//----- Call API function to generate miniature calendar
$display_buffer .= fc_generate_calendar_mini ($selected_date, "$page_name?physician=$physician");

//----- Create multimap
$scheduler = CreateObject('FreeMED.Scheduler');
unset($map);
$map = $scheduler->multimap(
	"SELECT scheduler.*,atcolor FROM scheduler ".
	"LEFT OUTER JOIN appttemplate t ON t.id=scheduler.calappttemplate ".
	"WHERE ".
		"calphysician='".addslashes($physician)."' AND ".
		"caldateof='".addslashes($selected_date)."'"
);

//----- Display table
$display_buffer .= "<table>\n";
for ($c_hour=freemed::config_value('calshr');
		$c_hour<freemed::config_value('calehr');
		$c_hour++) {
	$display_buffer .= "
	<tr><td VALIGN=\"TOP\" ALIGN=\"RIGHT\" ROWSPAN=\"12\" ".
	"CLASS=\"calcell_hour\" WIDTH=\"7%\"
	><a NAME=\"hour".$c_hour."\" /><b>".
	$scheduler->display_hour($c_hour)."</b></td>
	";
	
	for ($c_min="00"; $c_min<60; $c_min+=5) {
		$idx = sprintf('%02s:%02s', $c_hour, $c_min);
		$display_buffer .= ( ($c_min>0) ? '<tr>' : '' ).
			"<td>".( ($c_min % 15 == 0) ? ':'.$c_min : '' )."</td>\n";
		foreach ($map AS $map_key => $cur_map) {
			$event = false;
			if (($cur_map[$idx]['span']+0) == 0) {
				$event = true;
			} elseif (($cur_map[$idx]['link']+0) != 0) {
				$event = true;
				$display_buffer .= "<td COLSPAN=\"1\" ".
				"ROWSPAN=\"".$cur_map[$idx]['span']."\" ".
				( $cur_map[$idx]['color'] ? "STYLE=\"background: ".$cur_map[$idx]['color']."; \" " : "" ).
				"ALIGN=\"LEFT\" ".
				"CLASS=\"calmark".($cur_map[$idx]['mark']+0)."\">".
				$scheduler->event_calendar_print($cur_map[$idx]['link']).
				"</td>\n";
			} else {
				$buffer .= "<td COLSPAN=\"1\" CLASS=\"cell\" ".
				"ALIGN=\"LEFT\" VALIGN=\"MIDDLE\">&nbsp;</td>\n";
			}
		} // end foreach map
	} // end c_min for loop
	$display_buffer .= "</tr>\n";
} // end c_hour for loop		
$display_buffer .= "</table>\n";

template_display();
?>
