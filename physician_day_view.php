<?php
 // $Id$
 // desc: physician's daily calendar view
 // lic : GPL, v2

$page_name="physician_day_view.php";
include_once ("lib/freemed.php");
include_once ("lib/calendar-functions.php");

//----- Login/authenticate
freemed_open_db ();

//----- Check if there is a valid date... if not, assign current date
if (!empty($selected_date)) $for_date = $selected_date;
if (!checkdate(substr($for_date, 5, 2), substr($for_date, 8, 2),
	substr($for_date, 0, 4))) $for_date = $cur_date;

//----- Calculate previous and next dates for menubar
$prev_date = freemed_get_date_prev ($for_date);
$next_date = freemed_get_date_next ($for_date);

//----- Set page title
$page_title = __("Physician Daily View");

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
      <B>".__("Physician")." : </B>
       $phylname, $phyfname $phymname
     </CENTER>
     <BR>
	";
}

//----- Quick fix for first load
if (empty($selected_date)) $selected_date = date("Y-m-d");

//----- Call API function to generate miniature calendar
fc_generate_calendar_mini ($selected_date, "$page_name?physician=$physician");

//----- Actually display the day calendar
fc_display_day_calendar ($selected_date, "calphysician='$physician'");

//----- Display and end
template_display();
?>
