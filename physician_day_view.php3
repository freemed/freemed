<?php
 # file: physician_day_view.php3
 # desc: physician's daily calendar view
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name="physician_day_view.php3";
 include ("global.var.inc");
 include ("freemed-functions.inc");
 include ("freemed-calendar-functions.inc");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

 // check if there is a valid date... if not, assign current date
 if (!checkdate(substr($for_date, 5, 2), substr($for_date, 8, 2),
     substr($for_date, 0, 4))) $for_date = $cur_date;

 // calculate previous and next dates for menubar
 $prev_date = freemed_get_date_prev ($for_date);
 $next_date = freemed_get_date_next ($for_date);

 // display the top of the box
 freemed_display_box_top ("$Physician_Daily_View");

 // display previous/next bar
 echo "
  <TABLE WIDTH=100% BGCOLOR=#000000 VALIGN=TOP ALIGN=CENTER BORDER=0
   CELLSPACING=0 CELLPADDING=2><TR BGCOLOR=#000000>
   <TD VALIGN=CENTER ALIGN=LEFT>
   <A HREF=\"$page_name?$_auth&selected_date=$prev_date&physician=$physician\"
    ><$STDFONT_B COLOR=#ffffff>$back_one_day<$STDFONT_E></A>
   </TD><TD VALIGN=CENTER ALIGN=RIGHT>
   <A HREF=\"$page_name?$_auth&selected_date=$next_date&physician=$physician\"
    ><$STDFONT_B COLOR=#ffffff>$forward_one_day<$STDFONT_E></A>
   </TD></TR></TABLE>
   <P>
 ";

 // check if there is a physician specified, and if so, display their
 // name, etc at the top...
 if ($physician<=0) {
   echo "
     <CENTER><$STDFONT_B>
      <B>$No_Physician_Selected</B>
     <$STDFONT_E></CENTER>
     <BR>
   ";
 } else {
   $phyinfo  = freemed_get_link_rec ($physician, "physician");
   $phylname = $phyinfo["phylname"];
   $phyfname = $phyinfo["phyfname"];
   $phymname = $phyinfo["phymname"];
   echo "
     <CENTER><$STDFONT_B>
      <B>$Physician : </B>
       $phylname, $phyfname $phymname
     <$STDFONT_E></CENTER>
     <BR>
   ";
 }

 fc_generate_calendar_mini ($selected_date,
  "$page_name?$_auth&physician=$physician");

 // actually display the calendar
 fc_display_day_calendar ($selected_date, "calphysician='$physician'");

 // end everything
 freemed_display_box_bottom ();
 freemed_close_db ();
 freemed_display_html_bottom ();
?>
