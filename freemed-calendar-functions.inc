<?php
  # file: freemed-calendar-functions.inc
  # note: calendar functions for the freemed project
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

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

    if (freemed_config_value("calshr")=="")
      return $cal_starting_hour;
    else return freemed_config_value ("calshr");
  } // end function fc_starting_hour

  // function fc_ending_hour
  // -- returns ending hour of booking
  function fc_ending_hour () {
    global $cal_ending_hour;

    if (freemed_config_value("calehr")=="")
      return $cal_ending_hour;
    else return freemed_config_value ("calehr");
  } // end function fc_ending_hour

  // function fc_display_day_calendar
  // -- displays calendar for current day where $querystring
  // -- is the criteria (like calphysician='1') or something...
  function fc_display_day_calendar ($datestring, $querystring = "1 = 1",
    $privacy = false) {
    global $current_imap;  // global interference map
    global $STDFONT_B, $STDFONT_E;

    // first, build the global interference map
    fc_generate_interference_map ($querystring, $datestring, $privacy);

    // construct the top of the calendar
    echo "
     <TABLE WIDTH=100% BGCOLOR=#000000 CELLSPACING=2 CELLPADDING=2
      BORDER=0 VALIGN=CENTER ALIGN=CENTER>
      <TR BGCOLOR=#000000><TD BGCOLOR=#ffffff COLSPAN=2 ALIGN=CENTER
       VALIGN=CENTER>
       <$STDFONT_B><B>$datestring</B> - <I>".$current_imap["count"].
         " appointment(s)</I><$STDFONT_E>
      </TD></TR>
    ";

    // loop through the hours and display them
    for ($h=fc_starting_hour();$h<=fc_ending_hour();$h++) {
      // calculate proper way to display hour
      if      ($h== 0) $hour="midnight";
       elseif ($h< 12) $hour="$h am";
       elseif ($h==12) $hour="noon";
       else            $hour=($h-12)." pm";

      // display heading for hour
      echo "
       <TR BGCOLOR=#000000><TD BGCOLOR=#cccccc COLSPAN=1 WIDTH=20%>
        <$STDFONT_B>$hour<$STDFONT_E>
       </TD><TD BGCOLOR=#ffffff COLSPAN=1>
      "; 

      // display data in fifteen minute increments, by dumping the
      // text of the interference map for the specified time.
      for ($i=0; $i<60; $i+=15) {
        if ($i==0) $itxt="00:"; // format time correctly
         else $itxt="$i:";
        echo "<B>$itxt</B> ".$current_imap["$h:$i"]."<BR>\n";
      } // end of for..(next) minutes loop

      // construct the bottom of the hour
      echo "
       </TD></TR>
      ";

    } // end hours "for" loop

    // construct the bottom of the calendar
    echo "
     </TABLE>
    ";

  } // end function fc_display_day_calendar

  // function fc_display_week_calendar
  function fc_display_week_calendar ($datestring, $querystring = "1 = 1",
    $privacy=false) {
    global $current_imap;
    global $STDFONT_B, $STDFONT_E, $Week_Of;

    // form the top of the table
    echo "
      <TABLE WIDTH=100% CELLSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=CENTER
       ALIGN=CENTER BGCOLOR=#000000><TR BGCOLOR=#000000>
       <TD BGCOLOR=#ffffff COLSPAN=2 ALIGN=CENTER VALIGN=CENTER>
       <$STDFONT_B>$Week_Of <B>$datestring</B><$STDFONT_E>
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
      echo "
        <TR BGCOLOR=#000000><TD BGCOLOR=#cccccc COLSPAN=1 WIDTH=20%
         ALIGN=RIGHT>
         <$STDFONT_B><I>$day_name_text</I><BR>$datestring<$STDFONT_E>
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
         echo "
           <LI><B>$hour</B><BR>$hourbody
          ";
        } else {
         echo " &nbsp; ";
        } // end of checking for events...
      } // end hours loop

      // generate the footer for this day...
      echo "
        </UL></TD></TR>
       ";

    } // end for loop for days

    // generate footer for table
    echo "
      </TABLE>
     ";

  } // end function fc_display_week_calendar

  function fc_generate_calendar_mini ($given_date, $this_url) {
    // mostly hacked code from TWIG's calendar
    global $cur_date, $lang_months, $lang_days, $STDFONT_B, $STDFONT_E;

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
    echo "
     <CENTER>
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>

     </TR>
     <TABLE BORDER=0 CELLSPACING=0>
      <TR BGCOLOR=>
       <TD ALIGN=LEFT>
    ";

    // previous month link
    echo "     
     <A HREF=\"$this_url&selected_date=".
       fc_scroll_prev_month(
        fc_scroll_prev_month(
         fc_scroll_prev_month($this_date)
        )
       )."\"
      <$STDFONT_B>3<$STDFONT_E></A>
     <A HREF=\"$this_url&selected_date=".fc_scroll_prev_month($this_date)."\"
      <$STDFONT_B>prev<$STDFONT_E></A>
     </TD>
     <TD COLSPAN=5 ALIGN=CENTER BGCOLOR=#ffffff>
      <$STDFONT_B>
       <B>".htmlentities($lang_months[($this_month+0)])." $this_year</b>
      <$STDFONT_E>
     </TD>
     <TD ALIGN=RIGHT>
     <A HREF=\"$this_url&selected_date=".fc_scroll_next_month($this_date)."\"
      <$STDFONT_B>next<$STDFONT_E></A>    
     <A HREF=\"$this_url&selected_date=".
       fc_scroll_next_month(
        fc_scroll_next_month(
         fc_scroll_next_month($this_date)
        )
       )."\"
      <$STDFONT_B>3<$STDFONT_E></A>
     </TD>
     </TR>
     <TR>
    ";
    // print days across top
    for( $i = 1; $i <= 7; $i++) {
     echo "
      <TD BGCOLOR=#cccccc>
       <$STDFONT_B>
       <B>".htmlentities($lang_days[$i])."</B>
       <$STDFONT_E>
      </TD>
     ";
    } // end of day display
    echo "
     </TR>
    ";

    // calculate first day
    $first_day = date( 'w', mktime( 0, 0, 0, $this_month, 1, $this_year ) );
    $day_row = 0;

    if( $first_day > 0 ) {
  	while( $day_row < $first_day ) {
   		echo "  <TD ALIGN=RIGHT BGCOLOR=#dfdfdf>&nbsp;</td>\n";
   		$day_row += 1;  
  		}
 	} // end while day row < first day

 	while( $day < $lastday[($this_month + 0)] ) 
		{
  		if( ( $day_row % 7 ) == 0) 
			{
   			echo " </TR>\n<TR BGCOLOR=#bbbbbb>\n";
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

    echo "
     <TD ALIGN=CENTER BGCOLOR=$this_color>
      <$STDFONT_B>
    ";
 
        $hilitecolor = (
	  ( $dayp       == $cur_day AND
            $this_month == $cur_month AND
            $this_year  == $cur_year ) ?
            "#ff0000" : 
            "#0000ff" );
       
        echo "
         <A HREF=\"$this_url&selected_date=".
         date("Y-m-d",mktime(0,0,0,$this_month,$dayp,$this_year) ).
         "\"><$STDFONT_B COLOR=$hilitecolor>$dayp<$STDFONT_E></A>
        ";
   	//if( $dayp       == $cur_day AND
        //    $this_month == $cur_month AND
        //    $this_year  == $cur_year )
        //  { echo "</B></FONT>"; }
      echo "
        <$STDFONT_E>
       </TD>
      ";
      $day++;
      $day_row++;
    }

    while( $day_row % 7 ) {
   	echo "
         <TD ALIGN=RIGHT BGCOLOR=#bbbbbb>&nbsp;</TD>
        ";
   	$day_row += 1;  
    } // end of day row
    echo "
     </TR>
     <TR>
     <TD COLSPAN=7 ALIGN=RIGHT BGCOLOR=#bbbbbb>
     <$STDFONT_B>
      <A HREF=\"$this_url&selected_date=".$cur_year."-".$cur_month."-".
       $cur_day."\"
      ><$STDFONT_B>go to today<$STDFONT_E></A>
     </TD>
     </TR>
     </TABLE>
     </CENTER>
    ";
  } // end function fc_generate_calendar_mini

  function fc_generate_interference_map ($query_part, $this_date, 
                                         $privacy=false) {
    global $current_imap; // global current interference map
    global $database, $cur_date, $_auth;

    // initialize the new array
    $current_imap          = Array (); 
    $current_imap["count"] = 0;
    
    // perform a query of $this_date for the $query_part qualifier
    $querystring = "SELECT * FROM $database.scheduler WHERE ".
      "(($query_part) AND (caldateof='$this_date')) ".
      "ORDER BY caldateof,calhour,calminute";
    $result = fdb_query ($querystring);

    while ($r = fdb_fetch_array($result)) { // loop for all patients
      // get all common data
      $calhour     = $r["calhour"    ];
      $calminute   = $r["calminute"  ];
      $calduration = $r["calduration"];
      $desc        = substr($r["calprenote"], 0, 50); // clip description
      if (strlen($r["calprenote"])>50) $desc .= " ... "; // if long...

      // since it _is_ a record, increment the counter
      $current_imap["count"]++;

      // pull name, date of birth, etc from either actual patient
      // record or temporary patient record, depending on type
      switch ($r["caltype"]) {
       case "pat":
        $calpatient = freemed_get_link_rec ($r["calpatient"], "patient");
        $ptlname    = $calpatient["ptlname"];
        $ptfname    = $calpatient["ptfname"];
        $ptmname    = $calpatient["ptmname"];
        $ptdob      = $calpatient["ptdob"  ];
        $ptid       = $calpatient["ptid"   ];
        break;
       case "temp":
        $calpatient = freemed_get_link_rec ($r["calpatient"], "callin");
        $ptlname    = $calpatient["cilname"];
        $ptfname    = $calpatient["cifname"];
        $ptmname    = $calpatient["cimname"];
        $ptdob      = $calpatient["cidob"  ];
        break;
      } // end of temp/patient switch

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
        $mapping = "<A HREF=\"manage.php3?$_auth&id=".$r["calpatient"].
                   "\">$ptname</A> [$ptdob] [$ptid] - $desc";
        break;
       case "temp": // call-in patient
        $mapping = "<A HREF=\"call-in.php3?$_auth&action=display&id=".
                   $r["calpatient"]."\">$ptname</A> [$ptdob] - $desc";
        break;
      } // end of switch

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

       $loop_ehour = $calhour   + (int)($calduration / 60);
       $loop_emin  = $calminute + (int)($calduration % 60);

       if ($loop_emin > 59) { // if spilling over the hour...
         $loop_ehour += (int)($loop_emin % 60);
         $loop_emin   = (int)($loop_emin / 60);
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

?>
