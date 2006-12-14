<?php
	// $Id$
	// calendar functions for the freemed project
	// lic : GPL, v2

// File: Calendar API
//
//	Calendar and date related functions. These are not included
//	unless needed.

if (!defined ("__CALENDAR_FUNCTIONS_PHP__")) {

define ('__CALENDAR_FUNCTIONS_PHP__', true);

    // function to see if a date is in a particular range

// Function: date_in_range
//
//	Determine if a date falls between a beginning and end date.
//
// Parameters:
//
//	$checkdate - Date to check. Should be in ANSI SQL date format
//	(YYYY-MM-DD).
//
//	$dtbegin - Beginning of time span to compare against.
//
//	$dtend - Ending of time span to compare against.
//
// Returns:
//
//	Boolean value, whether date falls between specified dates.
//
function date_in_range ($checkdate, $dtbegin, $dtend) {
    // split all dates into component parts
    $begin_y = substr ($dtbegin,   0, 4);
    $begin_m = substr ($dtbegin,   5, 2);
    $begin_d = substr ($dtbegin,   8, 2);
    $end_y   = substr ($dtend,     0, 4);
    $end_m   = substr ($dtend,     5, 2);
    $end_d   = substr ($dtend,     8, 2);
    $cur_y   = substr ($checkdate, 0, 4);
    $cur_m   = substr ($checkdate, 5, 2);
    $cur_d   = substr ($checkdate, 8, 2);

    $end = $end_y;
    $end .= $end_m;
    $end .= $end_d;
    $start = $begin_y;
    $start .= $begin_m;
    $start .= $begin_d;
    $current = $cur_y;
    $current .= $cur_m;
    $current .= $cur_d;

    if ( ($current >= $begin) AND ($current <= $end) )
	return true;
    return false;

    // check to see if it is before the beginning
    if     ($cur_y<$begin_y) return false;
    elseif ($cur_m<$begin_m) return false;
    elseif ($cur_d<$begin_d) return false;

    // check to see if it is after the ending
    if     ($cur_y<$end_y)   return false;
    elseif ($cur_m<$end_m)   return false;
    elseif ($cur_d<$end_d)   return false;

    // if it isn't before or after, return true
    return true;
  } // end function date_in_range

// Function: date_in_the_past
//
//	Check to see if date is in the past
//
// Parameters:
//
//	$date - SQL formatted date string (YYYY-MM-DD)
//
// Returns:
//
//	Boolean, true if date is past, false if date is present or future.
//
function date_in_the_past ($datestamp) {
    $cur_date = date("Y-m-d");
 
    $y_c = substr ($cur_date, 0, 4);
    $m_c = substr ($cur_date, 5, 2);
    $d_c = substr ($cur_date, 8, 2);
    $y   = substr ($datestamp, 0, 4);
    $m   = substr ($datestamp, 5, 2);
    $d   = substr ($datestamp, 8, 2);
    if ($y < $y_c) {
	return true;
    } elseif ($y > $y_c) {
	return false;
    }
    if ($m < $m_c) {
	return true;
    } elseif ($m > $m_c) {
	return false;
    }
    if ($d < $d_c) {
	return true;
    } elseif ($d > $d_c) {
	return false;
    }
    else return false;
}

// Function: day_of_the_week
//
//	Get the text name of a day of the week
//
// Parameters:
//
//	$this_date - (optional) Date to examine. Defaults to the current
//	date.
//
//	$short - (optional) Return short date format. Defaults to false.
//
// Returns:
//
//	Text string describing the day of the week.
//
function day_of_the_week ($this_date="", $short=false) {
    global $cur_date;

    if ($this_date == "") $this_date = $cur_date;
    $this_timestamp = mktime (0, 0, 0,
                       substr($this_date, 5, 2),
                       substr($this_date, 8, 2),
                       substr($this_date, 0, 4));
    if ($short) {  return strftime ("%a", $this_timestamp);  }
     else       {  return strftime ("%A", $this_timestamp);  }
} // end function day_of_the_week

// Function: fc_get_time_string
//
//	Form a human readable time string from an hour and a minute.
//
// Parameters:
//
//	$hour - Hour in 24 hour format (0 to 24).
//
//	$minute - Minutes (0 to 60).
//
// Returns:
//
//	Formatted time string.
//
function fc_get_time_string ( $hour, $minute ) {
	if ($minute==0) $minute="00";

	// time checking/creation if/else clause
	switch (freemed::config_value('hourformat')) {
		case '24':
		return $hour.":".$minute;
		break;

		case '12': default:
		if ($hour<12) {
			$_time = $hour.":".$minute." AM";
		} elseif ($hour == 12) {
			$_time = $hour.":".$minute." PM";
		} else {
			$_time = ($hour-12).":".$minute." PM";
		}
		return $_time;
		break;
	}
}

// Function: fc_scroll_prev_month
//
//	Scroll a given date back by a month
//
// Parameters:
//
//	$given_date - (optional) Date to scroll back from in SQL date
//	format (YYYY-MM-DD). Defaults to current date.
//
// Returns:
//
//	SQL formatted date string for a date approximately one month
//	previous to the given date.
//
function fc_scroll_prev_month ($given_date="") {
	$cur_date = date("Y-m-d");
	$this_date = (
		(empty($given_date) or !strpos($given_date, "-")) ?
		$cur_date :
		$given_date );
	list ($y, $m, $d) = explode ("-", $this_date);
	$m--;
	if ($m < 1) { $m = 12; $y--; }
	if (!checkdate ($m, $d, $y)) {;
		if ($d > 28) $d = 28; // be safe for February...
	}
	return date( "Y-m-d",mktime(0,0,0,$m,$d,$y));
} // end function fc_scroll_prev_month

// Function: fc_scroll_next_month
//
//	Scroll a given date forward by a month
//
// Parameters:
//
//	$given_date - (optional) Date to scroll forward from in SQL date
//	format (YYYY-MM-DD). Defaults to current date.
//
// Returns:
//
//	SQL formatted date string for a date approximately one month
//	after the given date.
//
function fc_scroll_next_month ($given_date="") {
	$cur_date = date("Y-m-d");
	$this_date = (
		(empty($given_date) or !strpos($given_date, "-")) ?
		$cur_date :
		$given_date );
	list ($y, $m, $d) = explode ("-", $this_date);
	$m++;
	if ($m > 12) { $m -= 12; $y++; }
	if (!checkdate ($m, $d, $y)) {
		$d = 28; // be safe for February...
	}
	return date( "Y-m-d",mktime(0,0,0,$m,$d,$y));
} // end function fc_scroll_next_month

// Function: fc_starting_hour
//
//	Retrieve starting hour for booking in the scheduler.
//
// Returns:
//
//	Starting hour of booking for the scheduler.
//
function fc_starting_hour () {
	global $cal_starting_hour;

	if (freemed::config_value("calshr")=="")
		return $cal_starting_hour;
	else return freemed::config_value ("calshr");
} // end function fc_starting_hour

// Function: fc_ending_hour
//
//	Retrieve ending hour for booking in the scheduler.
//
// Returns:
//
//	Ending hour of booking for the scheduler.
//
function fc_ending_hour () {
	global $cal_ending_hour;

	if (freemed::config_value("calehr")=="")
		return $cal_ending_hour;
	else return freemed::config_value ("calehr");
} // end function fc_ending_hour

// function fc_display_day_calendar
// -- displays calendar for current day where $querystring
// -- is the criteria (like calphysician='1') or something...
function fc_display_day_calendar ($datestring, $querystring = "1 = 1",
    $privacy = false) {
    global $current_imap;  // global interference map
    global $display_buffer;

    // first, build the global interference map
    fc_generate_interference_map ($querystring, $datestring, $privacy);

    // construct the top of the calendar
    $display_buffer .= "
     <TABLE WIDTH=100% CLASS=\"reverse\" CELLSPACING=2 CELLPADDING=2
      BORDER=0 VALIGN=\"CENTER\" ALIGN=\"CENTER\">
      <TR CLASS=\"reverse\"><TD CLASS=\"calcell\" COLSPAN=2 ALIGN=\"CENTER\"
       VALIGN=\"CENTER\">
       <B>$datestring</B> - <I>".$current_imap["count"]." ".
         __("appointment(s)")."</I>
      </TD></TR>
    ";

    // loop through the hours and display them
    $alt = true;
    for ($h=fc_starting_hour();$h<=fc_ending_hour();$h++) {
      // calculate proper way to display hour
      if      ($h== 0) $hour=__("midnight");
       elseif ($h< 12) $hour="$h am";
       elseif ($h==12) $hour=__("noon");
       else            $hour=($h-12)." pm";

	// Alternate cells
	if ($alt == true) {
		$alt = false;
	} else {
		$alt = true;
	}

      // display heading for hour
      $display_buffer .= "
       <tr CLASS=\"reverse\"><TD BGCOLOR=\"#cccccc\" COLSPAN=\"1\" ".
       "WIDTH=\"20%\">$hour</td>
       <td CLASS=\"calcell".($alt ? "_alt" : "" )."\" COLSPAN=\"1\">
      "; 

      // display data in fifteen minute increments, by dumping the
      // text of the interference map for the specified time.
      for ($i=0; $i<60; $i+=15) {
        if ($i==0) $itxt="00:"; // format time correctly
         else $itxt="$i:";
        $display_buffer .= "<B>$itxt</B> ".$current_imap["$h:$i"]."<BR>\n";
      } // end of for..(next) minutes loop

      // construct the bottom of the hour
      $display_buffer .= "
       </TD></TR>
      ";

    } // end hours "for" loop

    // construct the bottom of the calendar
    $display_buffer .= "
     </TABLE>
    ";

} // end function fc_display_day_calendar

// function fc_display_week_calendar
function fc_display_week_calendar ($datestring, $querystring = "1 = 1",
    $privacy=false) {
    global $current_imap, $display_buffer, $physician;

    // form the top of the table
    $display_buffer .=  "
      <TABLE WIDTH=100% CELLSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=CENTER
       ALIGN=CENTER BGCOLOR=#000000><TR BGCOLOR=#000000>
       <TD BGCOLOR=#ffffff COLSPAN=2 ALIGN=CENTER VALIGN=CENTER>
       ".__("Week of")." <B>$datestring</B>
       </TD></TR>
    ";

    // loop through the week (+ one for full week)
    for ($day=0; $day<=7; $day++) {

      // if we are past the first day, increment the date
      if ($day>0) $datestring = freemed_get_date_next ($datestring);

      // generate the interference map for the first day
      fc_generate_interference_map ($querystring, $datestring, $privacy);

      // calculate the day of the week
      $day_name_text = day_of_the_week($datestring, true);

      // generate the header for this day...
      $display_buffer .= "
        <TR BGCOLOR=#000000><TD BGCOLOR=#cccccc COLSPAN=1 WIDTH=20%
         ALIGN=RIGHT>
	<A HREF=\"physician_day_view.php?physician=".urlencode($physician)."&".
	"selected_date=".urlencode($datestring)."\"
         ><I>$day_name_text</I><BR>$datestring</A>
        </TD><TD BGCOLOR=#ffffff COLSPAN=1>
       ";

      // loop for hours
      for ($h=fc_starting_hour(); $h<=fc_ending_hour(); $h++) {

        // parse the hour properly
        if      ($h== 0) { $hour = "midnight";    }
         elseif ($h <12) { $hour = "$h am";       }
         elseif ($h==12) { $hour = "noon";        }
         else            { $hour = ($h-12)." pm"; }

        // start with the assumption the there are NO events this hour
        $hourevents = false;
        $hourbody   = "";

        // loop for minutes
        for ($m=0; $m<60; $m+=15) {
          // format minutes properly
          if ($m==0) { $min = "00"; }
           else      { $min = "$m"; }

          // check for events -- if there are, mark 'em and add 'em
          if (strlen($current_imap["$h:$m"])>7) {
            $hourevents  = true;
            $hourbody   .= "(:$min) ".$current_imap["$h:$m"]."<BR>";
          } // end checking for length over 7
        } // end minutes loop

        if ($hourevents) {
         $display_buffer .= "
           <LI><B>$hour</B><BR>$hourbody
          ";
        } else {
         $display_buffer .= " &nbsp; ";
        } // end of checking for events...
      } // end hours loop

      // generate the footer for this day...
      $display_buffer .= "
        </UL></TD></TR>
       ";

    } // end for loop for days

    // generate footer for table
    $display_buffer .= "
      </TABLE>
     ";

} // end function fc_display_week_calendar

function fc_generate_calendar_mini ($given_date, $this_url) {
	// mostly hacked code from TWIG's calendar
	global $cur_date, $lang_months;

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
     <a href=\"$this_url&selected_date=".
       fc_scroll_prev_month(
        fc_scroll_prev_month(
         fc_scroll_prev_month($this_date)
        )
       )."\" class=\"button_text\"
      >3</A>
     <a href=\"$this_url&selected_date=".fc_scroll_prev_month($this_date)."\"
      class=\"button_text\"><small>".__("prev")."</small></a>
     </td>
     <td COLSPAN=\"5\" ALIGN=\"CENTER\">
       <b>".htmlentities(date("M",mktime(0,0,0,($this_month+1),0,0)))." $this_year</b>
     </td>
     <td ALIGN=\"RIGHT\" colspan=\"2\">
     <a href=\"$this_url&selected_date=".fc_scroll_next_month($this_date)."\"
      class=\"button_text\"><small>".__("next")."</small></a>
     <a href=\"$this_url&selected_date=".
       fc_scroll_next_month(
        fc_scroll_next_month(
         fc_scroll_next_month($this_date)
        )
       )."\" class=\"button_text\"
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

        $buffer .= "<a ".
	  "href=\"$this_url&selected_date=".
         date("Y-m-d",mktime(0,0,0,$this_month,$dayp,$this_year) ).
         "\">$dayp</a>\n";
      $buffer .= "
       </td>
      ";
      $day++;
      $day_row++;
    }

    while( $day_row % 7 ) {
   	$buffer .= "
         <td ALIGN=\"RIGHT\" BGCOLOR=\"#dfdfdf\">&nbsp;</td>
        ";
   	$day_row += 1;  
    } // end of day row
    $buffer .= "
      <td colspan=\"1\">&nbsp;</td>
     </tr>
     <tr>
     <td COLSPAN=\"9\" ALIGN=\"RIGHT\" class=\"button_style\">
      <a HREF=\"$this_url&selected_date=".$cur_year."-".$cur_month."-".
       $cur_day."\" class=\"button_text\"
      ><small>".__("go to today")."</small></a>
     </td>
     </tr>
     </table>
     </center>
    ";
	return $buffer;
} // end function fc_generate_calendar_mini

// Function: fc_generate_interference_map
//
//	Create an "interference map" which allows the system to
//	determine which appointments may conflict with others
//	based on several criteria.
//
// Parameters:
//
//	$query_part - SQL qualifiers to narrow the search parameters.
//	Example: "calphysician='2'"
//
//	$this_date - Date that the interference map is being generated
//	for, in SQL date format (YYYY-MM-DD).
//
//	$privacy - (optional) If this is specified, only the initials
//	of the patients in question will be displayed. Defaults to
//	false.
//
// Returns:
//
//	Multidimentional hash/array (interference map).
//
function fc_generate_interference_map ($query_part, $this_date, 
                                         $privacy=false) {
    global $current_imap; // global current interference map
    global $cur_date, $sql;
    global $display_buffer;

    // initialize the new array
    $current_imap          = Array (); 
    $current_imap["count"] = 0;
    
    // perform a query of $this_date for the $query_part qualifier
    $querystring = "SELECT * FROM scheduler WHERE ".
      "(($query_part) AND (caldateof='$this_date')) ".
      "ORDER BY caldateof,calhour,calminute";
    $result = $sql->query ($querystring);

    while ($r = $sql->fetch_array($result)) { // loop for all patients
      // get all common data
      $calhour     = $r["calhour"    ];
      $calminute   = $r["calminute"  ];
      $calduration = $r["calduration"];
      $desc        = substr($r["calprenote"], 0, 50); // clip description
      if (strlen($r["calprenote"])>50) $desc .= " ... "; // if long...

      // since it _is_ a record, increment the counter
      $current_imap["count"]++;

      // now that we have the patient information, check to see if the
      // spot is filled, if so, append a break before it...
      if (strlen($current_imap["$calhour:$calminute"])>0)
        $current_imap["$calhour:$calminute"] .= "<BR>";

      // check for privacy, then add them into the map...
      if ($privacy) 
        $ptname = substr ($ptfname, 0, 1) .
                  substr ($ptmname, 0, 1) .
                  substr ($ptlname, 0, 1);
      else $ptname = $ptlname . ", " . $ptfname . " " . $ptmname;

      // here define the mapping
      switch ($r["caltype"]) {
       case "pat":  // actual patient
        $mapping = "<A HREF=\"manage.php?id=".$r["calpatient"].
                   "\">$ptname</A> [$ptdob] [$ptid] - $desc";
        break;
       case "temp": // call-in patient
        $mapping = "<A HREF=\"call-in.php?action=display&id=".
                   $r["calpatient"]."\">$ptname</A> [$ptdob] - $desc";
        break;
      } // end of switch

	$mapping = freemedCalendar::event_calendar_print($r[id]);

      // map the name
      $current_imap["$calhour:$calminute"] .= "<FONT SIZE=\"-1\">".
      	$mapping."</FONT>\n";

      // now, remap the current mapping for italics or whatever to
      // show a continuing appt
      $mapping = "<I><FONT SIZE=\"-1\">$mapping (con't)</FONT></I>";

      // now the part that no one wants to do -- mapping to all of
      // the times after the starting time...
      if ($calduration>15) { // you don't bother if only 15 minutes
       $cur_hour   = $calhour;
       $cur_minute = $calminute + 15;

       // check for loop overs here, and translate
       if ($cur_minute > 59) {
         $cur_hour   += (int)($cur_minute % 60);
         $cur_minute  = (int)($cur_minute / 60);
       } // end checking for current time spillovers

       $loop_ehour = $calhour   + ((int)($calduration / 60));
       $loop_emin  = $calminute + ((int)($calduration % 60));

       if ($loop_emin > 59) { // if spilling over the hour...
         $loop_ehour += (int)($loop_emin / 60);
         $loop_emin   = (int)($loop_emin % 60);
       } // end checking for spilling over the hour

       // now loop for hours and minutes, and add a modified mapping
       // (for now in italics) that lets the person on the other end
       // know it is continuted
       for ($h=$cur_hour;$h<=$loop_ehour;$h++) {
        if (($h==$cur_hour) AND ($h==$loop_ehour)) { 

         for ($m=$cur_minute;$m<$loop_emin;$m+=15) {
          if (strlen($current_imap["$h:$m"])>0)
           $current_imap["$h:$m"] .= "<BR>";
          $current_imap["$h:$m"] .= $mapping;
         } // end for loop

        } elseif ($h==$cur_hour) {

         for ($m=$cur_minute;$m<60;$m+=15) {
          if (strlen($current_imap["$h:$m"])>0)
           $current_imap["$h:$m"] .= "<BR>";
          $current_imap["$h:$m"] .= $mapping;
         } // end for loop

        } elseif (($h==$loop_ehour) and ($loop_emin > 0)) {

         for ($m=0;$m<$loop_emin;$m+=15) {
          if (strlen($current_imap["$h:$m"])>0)
           $current_imap["$h:$m"] .= "<BR>";
          $current_imap["$h:$m"] .= $mapping;
         } // end for loop

        } elseif (($h==$loop_ehour) and ($loop_emin == 0)) {
         // this is a null instance, since you don't want to display
         // this -- it's just here so that the else won't catch it
        } else {

         for ($m=0; $m<60; $m+=15) {
          if (strlen($current_imap["$h:$m"])>0)
           $current_imap["$h:$m"] .= "<BR>";
          $current_imap["$h:$m"] .= $mapping; 
         } // end for loop

        } // end of checking for special cases in minute loop 
       } // end hours for loop

      } // end checking for >15min length
    } // end while loop

    // now, here's the thing that lets us know that the map has been
    // generated... a "key" if you will, that lets us know for what
    // date is this interference map
    $current_imap["key"] = "$this_date";

} // end function fc_generate_interference_map

// Function: fc_check_interference_map
//
//	Check to see whether an entry exists in a particular interference
//	map.
//
// Parameters:
//
//	$hour -
//
//	$minute -
//
//	$check_date -
//
//	$query_string -
//
// Returns:
//
//	Boolean, true if an entry exists, false if it does not.
//
function fc_check_interference_map ($hour, $minute, $check_date, $querystr) {
    global $current_imap; // the interference map
    global $display_buffer;

    // if the interference map isn't for today, generate a new one
    if ($check_date != $current_imap["key"])
     fc_generate_interference_map ($querystr, $check_date, false);

    // quickly make sure minute isn't 00 ... has to be 0
    if ($minute=="00") $minute="0";

    // return boolean true or false depending on what is there
    // (over 7 because of stupid "&nbsp;")
    return (strlen($current_imap["$hour:$minute"]) > 7);
} // end function fc_check_interference_map

function fc_interference_map_count ($_null_="") {
	global $current_imap;
	return (int)$current_imap["count"];    
} // end function fc_interference_map_count

// Class: freemedCalendar

class freemedCalendar {

	// Method: freemedCalendar::display_hour
	//
	//	Creates AM/PM user-friendly hour display.
	//
	// Parameters:
	//
	//	$hour - Hour in 0..24 military format.
	//
	// Returns:
	//
	//	AM/PM display of hour
	//
  	function display_hour ( $hour ) {
		// time checking/creation if/else clause
		switch (freemed::config_value('hourformat')) {
			case '24':
			return $hour;
			break;

			case '12': default:
			if ($hour<12) {
				return $hour." AM";
			} elseif ($hour == 12) {
				return $hour." PM";
			} else {
				return ($hour-12)." PM";
			}
			break;
		}
  	} // end method freemedCalendar::display_hour

	// Method: freemedCalendar::display_time
	//
	//	Creates AM/PM user-friendly time display.
	//
	// Parameters:
	//
	//	$hour - Hour in 0..24 military format.
	//
	//	$minute - Minute in 0..60 format.
	//
	// Returns:
	//
	//	User-friendly AM/PM display of time.
	//
	function display_time ( $hour, $minute ) {
		$m = ($minute<10 ? '0' : '').($minute+0);
		switch (freemed::config_value('hourformat')) {
			case '24':
			return $hour.':'.$m;
			break;

			case '12': default:
			if ($hour<12) {
				return $hour.":$m AM";
			} elseif ($hour == 12) {
				return $hour.":$m PM";
			} else {
				return ($hour-12).":$m PM";
			}
			break;
		}
	} // end method freemedCalendar::display_time

	// Function: freemedCalendar::event_calendar_print
	//
	//	Display calendar event from scheduler.
	//
	// Parameters:
	//
	//	$event - scheduler table event id number.
	//
	// Returns:
	//
	//	XHTML formatted calendar event.
	//
	function event_calendar_print ( $event ) {
		global $sql;

		// Get event
		$my_event = freemed::get_link_rec($event, "scheduler");

		// Handle travel
		if ($my_event['calpatient'] == 0) {
			return freemedCalendar::event_special($my_event['calmark'])." ".
			"(".$my_event['calduration']."m)\n";
		}

		// Get patient information
		$my_patient = CreateObject('FreeMED.Patient', $my_event['calpatient'],
			($my_event['caltype']=="temp"));

		return "<a HREF=\"".(($my_event['caltype']=="temp") ?
				"call-in.php?action=display&id=" :
				"manage.php?id=" ).
			$my_patient->id."\"".
			">".trim($my_patient->fullName())."</a> ".
			"(".$my_event['calduration']."m)<br/>\n".
			"<a href=\"book_appointment.php?id=".
				urlencode($my_event['id'])."&".
				"type=".$my_event['caltype']."\" ".
			">".__("Move")."</a>".
			//" ( phy = ".$my_event['calphysician']." ) ".
			( !empty($my_event['calprenote']) ?
			"<br/>&nbsp;&nbsp;<i>".
			prepare(stripslashes($my_event[calprenote])).
			"</i>\n" : "" );
	} // end method freemedCalendar::event_calendar_print

	// Method: freemedCalendar::event_special
	//
	//	Return proper names for special event mappings, as per the
	//	group calendar and Travel.
	//
	// Parameters:
	//
	//	$mapping - Special id mapping. This is usually a number from
	//	0 to 8.
	//
	// Returns:
	//
	//	Text name of specified mapping.
	//
	function event_special ( $mapping ) {
		switch ($mapping) {
			case 1: case 2: case 3: case 4:
			case 5: case 6: case 7: case 8:
				return freemed::config_value("cal". $mapping );
				break;

			default: return __("Travel"); break;
		}
	}

	// Method: freemedCalendar::map
	//
	//	Creates a scheduler map. This is the 2nd generation of
	//	the depreciated interference map.
	//
	// Parameters:
	//
	//	$query - SQL query string.
	//
	// Returns:
	//
	//	"map" associative multi-dimentional array containing
	//	scheduling interference data.
	//
	// See Also:
	//	<freemedCalendar::map_fit>
	//	<freemedCalendar::map_init>
	function map ( $query ) {
		global $sql;

		// Initialize the map;
		$idx = "";
		$map = freemedCalendar::map_init();

		// Get the query
		$result = $sql->query($query);

		// If nothing, return empty map
		if (!$sql->results($result)) return $map;

		// Run through query
		while ($r = $sql->fetch_array($result)) {
			// Move to "c" array, which is stripslashes'd
			foreach ($r AS $k => $v) {
				$c[(stripslashes($k))] = stripslashes($v);
			} // end removing slashes

			// Determine index
			$idx = ($c['calhour']+0).":".( $c['calminute']==0 ?
				"00" : ($c['calminute']+0) );
			
			// Insert into current position
			$map[$idx]['link'] = $c['id'];
			$map[$idx]['span'] = ceil($c['calduration'] / 15);
			if ($c['calmark'] > 0) {
				$map[$idx]['mark'] = $c['calmark'];
			}
			$cur_pos = $idx;

			// Clear out remaining portion of slot
			$count = 1;
			while ($count < $map[$idx]['span']) {
				// Move pointer forward
				$cur_pos = freemedCalendar::next_time($cur_pos);
				$count++;

				// Zero those records
				$map[$cur_pos]['link'] = 0;
				$map[$cur_pos]['span'] = 0;
			} // end clear out remaining portion of slot
		} // end running through array

		// Return completed map
		return $map;
	} // end method freemedCalendar::map

	// Method: freemedCalendar::map_fit
	//
	//	Determine whether an appointment of the specified duration
	//	at the specified time will fit in the specified map.
	//
	// Parameters:
	//
	//	$map - Scheduler "map" as generated by
	//	<freemedCalendar::map>.
	//
	//	$time - Time string specifying the time of the appointment
	//	to check. Should be in format HH:MM.
	//
	//	$duration - (optional) Duration of the appointment in
	//	minutes. This is 15 by default.
	//
	//	$id - (optional) If this is specified it shows the
	//	pre-existing scheduler id for an appointment, so that if
	//	it is being moved, it does not conflict with itself.
	//
	// Returns:
	//
	//	Boolean, whether specified appointment fits into the
	//	specified map.
	//
	// See Also:
	//	<freemedCalendar::map>
	//	<freemedCalendar::map_init>
	//
	function map_fit ( $map, $time, $duration=15, $id = -1 ) {
		// If this is already booked, return false
		if ($map[$time]['span'] == 0) { return false; }
		if ($map[$time]['link'] != 0) { return false; }

		// If anything *after* it for its duration is booked...
		if ($duration > 15) {
			// Determine number of blocks to search
			$blocks = ceil(($duration - 1) / 15); $cur_pos = $time;
			for ($check=1; $check<$blocks; $check++) {
				// Increment pointer to time
				$cur_pos = freemedCalendar::next_time($cur_pos);

				// If we're part of this id, return true
				// (so we can slightly move a booking time)
				if ($map[$cur_pos]['link'] == $id) {
					return true;
				}

				// Check for past boundaries
				list ($a, $b) = explode (":", $cur_pos);
				if ($a>=freemed::config_value("calehr")) {
					return false;
				}

				// If there's a link, return false
				if ($map[$cur_pos]['link'] != 0) return false;
			} // end looping through longer duration
		} // end if duration > 15

		// If all else fails, return true
		return true;
	} // end method freemedCalendar::map

	// Method: freemedCalendar::map_init
	//
	//	Creates a blank scheduler map.
	//
	// Returns:
	//
	//	Blank scheduler map (associative array).
	//
	// See Also:
	//	<freemedCalendar::map>
	//	<freemedCalendar::map_fit>
	function map_init () {
		$map = array ( );
		$map['count'] = 0;
		for ($hour=freemed::config_value("calshr");$hour<freemed::config_value("calehr");$hour++) {
			for ($minute=00; $minute<60; $minute+=15) {
				$idx = $hour.":".($minute==0 ? "00" : $minute);
				$map[$idx]['link'] = 0; // no link
				$map[$idx]['span'] = 1; // one slot per
				$map[$idx]['mark'] = 0; // default marking
				$map[$idx]['selected'] = false; // selection
				$map[$idx]['physician'] = 0;
				$map[$idx]['room'] = 0;
			} // end init minute loop
		} // end init hour loop
		return $map;
	} // end method freemedCalendar::map_init

	// Method: freemedCalendar::multimap
	//
	//	Creates 3rd generation multiple scheduling map. This is
	//	used to automatically create additional columns due to
	//	overlapping and overbooking.
	//
	// Parameters:
	//
	//	$query - SQL query string describing options.
	//
	//	$selected - (optional) Scheduler table id of selected
	//	appointment. If this is not specified, no appointment
	//	will be selected by default.
	//
	// Returns:
	//
	//	Multimap (associative array).
	//
	// See Also:
	//	<freemedCalendar::map>
	function multimap ( $query, $selected = -1 ) {
		global $sql;

		// Initialize the first map and current index
		$idx = "";
		$maps[0] = freemedCalendar::map_init();

		// Get the query
		$result = $sql->query($query);

		// If nothing, return empty map
		if (!$sql->results($result)) return $map;

		// Run through query
		while ($r = $sql->fetch_array($result)) {
			// Move to "c" array, which is stripslashes'd
			foreach ($r AS $k => $v) {
				$c[(stripslashes($k))] = stripslashes($v);
			} // end removing slashes

			// Determine index
			$idx = ($c['calhour']+0).":".( $c['calminute']==0 ?
				"00" : ($c['calminute']+0) );

			// Determine which is the first map that this fits into
			$cur_map = 0; $mapped = false;
			while (!$mapped) {
				if (!freemedCalendar::map_fit($maps[$cur_map], $idx, $c['calduration'])) {
					// Move to the next map
					$cur_map++;
					if (!is_array($maps[$cur_map])) {
						$maps[$cur_map] = freemedCalendar::map_init();
					}
				} else {
					// Jump out of the loop
					$mapped = true;
				}
			} // end while not mapped
			
			// Insert into current position
			$maps[$cur_map][$idx]['link'] = $c['id'];
			$maps[$cur_map][$idx]['span'] = ceil($c['calduration'] / 15);
			$maps[$cur_map][$idx]['physician'] = $c['calphysician'];
			$maps[$cur_map][$idx]['room'] = $c['calroom'];

			// Check for selected
			if ($c['id'] == $selected) {
				$maps[$cur_map][$idx]['selected'] = true;
			}
			
			if ($c['calmark'] > 0) {
				$maps[$cur_map][$idx]['mark'] = $c['calmark'];
			}
			$cur_pos = $idx;

			// Clear out remaining portion of slot
			$count = 1;
			while ($count < $maps[$cur_map][$idx]['span']) {
				// Move pointer forward
				$cur_pos = freemedCalendar::next_time($cur_pos);
				$count++;

				// Zero those records
				$maps[$cur_map][$cur_pos]['link'] = 0;
				$maps[$cur_map][$cur_pos]['span'] = 0;
			} // end clear out remaining portion of slot
		} // end running through array

		// Return completed maps
		return $maps;
	} // end method freemedCalendar::multimap

	// Method: freemedCalendar::next_time
	//
	//	Increment time slot by 15 minutes.
	//
	// Parameters:
	//
	//	$time - Time in HH:MM format.
	//
	// Returns:
	//
	//	Next time slot in HH:MM format.
	//
	function next_time ( $time ) {
		// Split into time components
		list ($h, $m) = explode (":", $time);
		
		// Decide what to do based on the minutes
		switch ($m) {
			case "00": $return = $h.":15"; break;
			case "15": $return = $h.":30"; break;
			case "30": $return = $h.":45"; break;
			case "45": $return = ($h+1).":00"; break;
		}
		return $return;
	} // end method freemedCalendar::next_time

	// function refresh_select obsolete.
	// use html_form::select_widget(varname,values,array('refresh'=>true))

	function refresh_text_widget ( $varname, $len, $_max=-1 ) {
		global ${$varname};
		if ($_max != -1) $max = $_max; else $_max = $len;
		return "<INPUT TYPE=\"TEXT\" NAME=\"".prepare($varname)."\" ".
			"SIZE=\"".( $len<50 ? $len+1 : 50 )."\" ".
			"MAXLENGTH=\"".$max."\" ".
			"VALUE=\"".prepare(${$varname})."\" ".
			"onChange=\"this.form.submit(); return true;\" ".
			"onBlur=\"this.form.submit(); return true;\">\n";
	} // end method freemedCalendar::refresh_text_widget
}

} // end checking for __CALENDAR_FUNCTIONS_PHP__

?>
