<?php
 // $Id$
 // note: calendar functions for the freemed project
 // lic : GPL, v2

if (!defined ("__CALENDAR_FUNCTIONS_PHP__")) {

define ('__CALENDAR_FUNCTIONS_PHP__', true);

  // freemed_get_date_prev (in freemed-functions.inc)
  // -- returns date before provided date

  // freemed_get_date_next (in freemed-functions.inc)
  // -- returns date after provided date

    // function to see if a date is in a particular range
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

    // function to see if in the past (returns 1)
  function date_in_the_past ($datestamp) {
    global $cur_date;
 
    $y_c = substr ($cur_date, 0, 4);
    $m_c = substr ($cur_date, 5, 2);
    $d_c = substr ($cur_date, 8, 2);
    $y   = substr ($datestamp, 0, 4);
    $m   = substr ($datestamp, 5, 2);
    $d   = substr ($datestamp, 8, 2);
    if ($y<$y_c) return true;
    elseif ($m<$m_c) return true;
    elseif ($d<$d_c) return true;
    else return false;
  }

  // function day_of_the_week
  // -- returns text name of day of the week
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

  function fc_get_time_string($hour,$minute)
  {
	if ($minute==0) $minute="00";

	// time checking/creation if/else clause
	if ($hour<12)
		$_time = $hour.":".$minute." AM";
	elseif ($hour == 12)
		$_time = $hour.":".$minute." PM";
	else
		$_time = ($hour-12).":".$minute." PM";
	return $_time;
  }
  // function fc_scroll_prev_month
  function fc_scroll_prev_month ($given_date="") {
    global $cur_date;
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

  // function fc_scroll_next_month
  function fc_scroll_next_month ($given_date="") {
    global $cur_date;
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

  // function fc_starting_hour
  // -- returns starting hour of booking
  function fc_starting_hour () {
    global $cal_starting_hour;

    if (freemed::config_value("calshr")=="")
      return $cal_starting_hour;
    else return freemed::config_value ("calshr");
  } // end function fc_starting_hour

  // function fc_ending_hour
  // -- returns ending hour of booking
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
       <TR CLASS=\"reverse\"><TD BGCOLOR=#cccccc COLSPAN=1 WIDTH=20%>
        $hour
       </TD><TD CLASS=\"cell".($alt ? "_alt" : "" )."\" COLSPAN=1>
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
    global $cur_date, $lang_months, $lang_days;

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
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=\"MIDDLE\"
      ALIGN=\"CENTER\">
     <TR>

     </TR>
     <TABLE BORDER=0 CELLSPACING=0>
      <TR BGCOLOR=\"#ffffff\">
       <TD ALIGN=LEFT BGCOLOR=\"#ffffff\">
    ";

    // previous month link
    $buffer .= "     
     <A HREF=\"$this_url&selected_date=".
       fc_scroll_prev_month(
        fc_scroll_prev_month(
         fc_scroll_prev_month($this_date)
        )
       )."\"
      >3</A>
     <A HREF=\"$this_url&selected_date=".fc_scroll_prev_month($this_date)."\"
      ><SMALL>".__("prev")."</SMALL></A>
     </TD>
     <TD COLSPAN=5 ALIGN=CENTER BGCOLOR=#ffffff>
       <B>".htmlentities(date("M",mktime(0,0,0,($this_month+1),0,0)))." $this_year</b>
     </TD>
     <TD ALIGN=RIGHT BGCOLOR=\"#ffffff\">
     <A HREF=\"$this_url&selected_date=".fc_scroll_next_month($this_date)."\"
      ><SMALL>".__("next")."</SMALL></A>    
     <A HREF=\"$this_url&selected_date=".
       fc_scroll_next_month(
        fc_scroll_next_month(
         fc_scroll_next_month($this_date)
        )
       )."\"
      >3</A>
     </TD>
     </TR>
     <TR>
    ";
    // print days across top
    for( $i = 1; $i <= 7; $i++) {
     $buffer .= "
      <TD BGCOLOR=#cccccc ALIGN=CENTER>
       <B>".htmlentities($lang_days[$i])."</B>
      </TD>
     ";
    } // end of day display
    $buffer .= "
     </TR>
    ";

    // calculate first day
    $first_day = date( 'w', mktime( 0, 0, 0, $this_month, 1, $this_year ) );
    $day_row = 0;

    if( $first_day > 0 ) {
  	while( $day_row < $first_day ) {
   		$buffer .= "  <TD ALIGN=RIGHT BGCOLOR=\"#dfdfdf\">&nbsp;</td>\n";
   		$day_row += 1;  
  		}
 	} // end while day row < first day

 	while( $day < $lastday[($this_month + 0)] ) 
		{
  		if( ( $day_row % 7 ) == 0) 
			{
   			$buffer .= " </TR>\n<TR BGCOLOR=\"#bbbbbb\">\n";
  			}

  		$dayp = $day + 1;

   		//$datestr = createSqlDate( $thisYear, $thisMonth, $dayp );
   		//$query = "SELECT * FROM " . $dbconfig["schedule_table"] . " WHERE " . sqlDuringDay( $datestr ) . "  AND (" . $groupquery . ")";
   		//$result = dbQuery( $query );

   		//if( $dayp == $thisDay ) 
		//	{ 
		//	$bgcolor = $config["cellheadtext"]; 
		//	$txtcolor = $config["cellheadcolor"]; 
		//	}
   		//elseif( dbNumRows( $result ) >= 1) 
		//	{ 
		//	$bgcolor = $config["cellheadcolor"]; 
		//	$txtcolor = $config["cellheadtext"]; 
		//	}
   		//else 
		//	{ 
		//	$bgcolor = $config["cellcolor"]; 
		//	$txtcolor = $config["celltext"]; 
		//	}
        $this_color = (
	  ( $dayp == $this_day ) ?
           "#ccccff" :
           "#bbbbbb" );

    $buffer .= "
     <TD ALIGN=CENTER BGCOLOR=\"$this_color\">
    ";
 
        $hilitecolor = (
	  ( $dayp       == $cur_day AND
            $this_month == $cur_month AND
            $this_year  == $cur_year ) ?
            "#ff0000" : 
            "#0000ff" );
       
        $buffer .= "&nbsp;&nbsp;<A HREF=\"$this_url&selected_date=".
         date("Y-m-d",mktime(0,0,0,$this_month,$dayp,$this_year) ).
         "\"><FONT COLOR=\"$hilitecolor\">$dayp</FONT></A>&nbsp;&nbsp;
        ";
   	//if( $dayp       == $cur_day AND
        //    $this_month == $cur_month AND
        //    $this_year  == $cur_year )
        //  { $buffer .= "</B></FONT>"; }
      $buffer .= "
       </TD>
      ";
      $day++;
      $day_row++;
    }

    while( $day_row % 7 ) {
   	$buffer .= "
         <TD ALIGN=RIGHT BGCOLOR=\"#bbbbbb\">&nbsp;</TD>
        ";
   	$day_row += 1;  
    } // end of day row
    $buffer .= "
     </TR>
     <TR>
     <TD COLSPAN=7 ALIGN=RIGHT BGCOLOR=\"#bbbbbb\">
      <A HREF=\"$this_url&selected_date=".$cur_year."-".$cur_month."-".
       $cur_day."\"
      >".__("go to today")."</A>
     </TD>
     </TR>
     </TABLE>
     </CENTER>
    ";
	return $buffer;
  } // end function fc_generate_calendar_mini

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
      $current_imap["$calhour:$calminute"] .= $mapping;

      // now, remap the current mapping for italics or whatever to
      // show a continuing appt
      $mapping = "<I><FONT SIZE=-1>$mapping (con't)</FONT></I>";

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

class freemedCalendar {

  	function display_hour ( $hour ) {
		// time checking/creation if/else clause
		if ($hour<12)
			return $hour." AM";
		elseif ($hour == 12)
			return $hour." PM";
		else
			return ($hour-12)." PM";
  	} // end method freemedCalendar::display_hour

	function display_time ( $hour, $minute ) {
		$m = ($minute<10 ? '0' : '').($minute+0);
		if ($hour<12)
			return $hour.":$m AM";
		elseif ($hour == 12)
			return $hour.":$m PM";
		else
			return ($hour-12).":$m PM";
		
	} // end method freemedCalendar::display_time

	function event_calendar_print ( $event ) {
		global $sql;

		// Get event
		$my_event = freemed::get_link_rec($event, "scheduler");

		// Handle travel
		if ($my_event[calpatient] == 0) {
			return freemedCalendar::event_special($my_event[calmark])." ".
			"(".$my_event[calduration]."m)\n";
		}

		// Get patient information
		$my_patient = CreateObject('FreeMED.Patient', $my_event[calpatient],
			($my_event[caltype]=="temp"));

		return "<A HREF=\"".(($my_event[caltype]=="temp") ?
				"call-in.php?action=display&id=" :
				"manage.php?id=" ).
			$my_patient->id."\"".
			">".trim($my_patient->fullName())."</A> ".
			"(".$my_event[calduration]."m)\n".
			( !empty($my_event[calprenote]) ?
			"<br/>&nbsp;&nbsp;<small><i>".
			prepare(stripslashes($my_event[calprenote])).
			"</i></small>\n" : "" );
	} // end method freemedCalendar::event_calendar_print

	function event_special ( $mapping ) {
		switch ($mapping) {
			case 1: case 2: case 3: case 4:
			case 5: case 6: case 7: case 8:
				return freemed::config_value("cal". $mapping );
				break;

			default: return __("Travel"); break;
		}
	}

	// method map: returns a map (associative array)
	function map ( $query ) {
		global $sql;

		// Initialize the map;
		unset ($map);
		$map[count] = 0;
		for ($hour=freemed::config_value("calshr");$hour<freemed::config_value("calehr");$hour++) {
			for ($minute=00; $minute<60; $minute+=15) {
				$idx = $hour.":".($minute==0 ? "00" : $minute);
				$map[$idx][link] = 0; // no link
				$map[$idx][span] = 1; // one slot per
				$map[$idx][mark] = 0; // default marking
			} // end init minute loop
		} // end init hour loop
		$idx = "";

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
			$idx = ($c[calhour]+0).":".( $c[calminute]==0 ?
				"00" : ($c[calminute]+0) );
			
			// Insert into current position
			$map[$idx][link] = $c[id];
			$map[$idx][span] = ceil($c[calduration] / 15);
			if ($c[calmark] > 0) {
				$map[$idx][mark] = $c[calmark];
			}
			$cur_pos = $idx;

			// Clear out remaining portion of slot
			$count = 1;
			while ($count < $map[$idx][span]) {
				// Move pointer forward
				$cur_pos = freemedCalendar::next_time($cur_pos);
				$count++;

				// Zero those records
				$map[$cur_pos][link] = 0;
				$map[$cur_pos][span] = 0;
			} // end clear out remaining portion of slot
		} // end running through array

		// Return completed map
		return $map;
	} // end method freemedCalendar::map

	function map_fit ( $map, $time, $duration=15 ) {
		// If this is already booked, return false
		if ($map[$time][span] == 0) { return false; }
		if ($map[$time][link] != 0) { return false; }

		// If anything *after* it for its duration is booked...
		if ($duration > 15) {
			// Determine number of blocks to search
			$blocks = ceil(($duration - 1) / 15); $cur_pos = $time;
			for ($check=1; $check<$blocks; $check++) {
				// Increment pointer to time
				$cur_pos = freemedCalendar::next_time($cur_pos);

				// Check for past boundaries
				list ($a, $b) = explode (":", $cur_pos);
				if ($a>=freemed::config_value("calehr"))
					return false;

				// If there's a link, return false
				if ($map[$cur_pos][link] != 0) return false;
			} // end looping through longer duration
		} // end if duration > 15

		// If all else fails, return true
		return true;
	} // end method freemedCalendar::map

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
