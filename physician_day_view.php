<?php
 // $Id$
 // desc: physician's daily calendar view
 // lic : GPL, v2

 $page_name="physician_day_view.php";
 include ("lib/freemed.php");
 include ("lib/API.php");
 include ("lib/calendar-functions.php");

 freemed_open_db ($LoginCookie);

 // check if there is a valid date... if not, assign current date
 if (!checkdate(substr($for_date, 5, 2), substr($for_date, 8, 2),
     substr($for_date, 0, 4))) $for_date = $cur_date;

 // calculate previous and next dates for menubar
 $prev_date = freemed_get_date_prev ($for_date);
 $next_date = freemed_get_date_next ($for_date);

 // display the top of the box
 freemed_display_box_top (_("Physician Daily View"));

 // display previous/next bar
 $display_buffer .= "
  <TABLE WIDTH=100% BGCOLOR=#000000 VALIGN=TOP ALIGN=CENTER BORDER=0
   CELLSPACING=0 CELLPADDING=2><TR BGCOLOR=#000000>
   <TD VALIGN=CENTER ALIGN=LEFT>
   <A HREF=\"$page_name?selected_date=$prev_date&physician=$physician\"
    ><$STDFONT_B COLOR=#ffffff>$back_one_day<$STDFONT_E></A>
   </TD><TD VALIGN=CENTER ALIGN=RIGHT>
   <A HREF=\"$page_name?selected_date=$next_date&physician=$physician\"
    ><FONT COLOR=#ffffff>$forward_one_day</FONT></A>
   </TD></TR></TABLE>
   <P>
 ";

 // check if there is a physician specified, and if so, display their
 // name, etc at the top...
 if ($physician<=0) {
   $display_buffer .= "
     <CENTER>
      <B>$No_Physician_Selected</B>
     </CENTER>
     <BR>
   ";
 } else {
   $phyinfo  = freemed_get_link_rec ($physician, "physician");
   $phylname = $phyinfo["phylname"];
   $phyfname = $phyinfo["phyfname"];
   $phymname = $phyinfo["phymname"];
   $display_buffer .= "
     <CENTER><$STDFONT_B>
      <B>$Physician : </B>
       $phylname, $phyfname $phymname
     <$STDFONT_E></CENTER>
     <BR>
   ";
 }

fc_generate_calendar_mini ($selected_date,
  "$page_name?physician=$physician");

 // actually display the calendar
fc_display_day_calendar ($selected_date, "calphysician='$physician'");

 // end everything
freemed_close_db ();
template_display();
?>
