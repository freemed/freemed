<?php
 # file: manage_appointments.php3
 # desc: routines for managing appointments... for patients (not call-ins)
 # code: jeff b (jeff@univrel.pr.uconn.edu)

 $page_name = "manage_appointments.php3";
 include ("global.var.inc");
 include ("freemed-functions.inc");
 include ("freemed-calendar-functions.inc");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

 if ($patient<1) {
   freemed_display_box_top ("Manage Appointments :: ERROR");
   echo "
     <P>
     You must select a patient first.
     <P>
     <CENTER>
      <A HREF=\"patient.php3?$_auth\"
       ><$STDFONT_B>Select a Patient<$STDFONT_E></A>
     </CENTER>
     <P>
    ";
   freemed_display_box_bottom ();
   freemed_display_html_bottom ();
   DIE("");
 } // end checking for patient

 switch ($action) {
  case "del":
   freemed_display_box_top ("$Deleting Appointment");
   echo "\n<$STDFONT_B>$Deleting ... <$STDFONT_E>\n";
   $query = "DELETE FROM $database.scheduler WHERE id='$id'";
   $result = fdb_query ($query);
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view&patient=$patient\"
     ><$STDFONT_B>Manage Appointments<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
    </CENTER>
    <P>
   ";
   freemed_display_box_bottom ();
   break; // end delete appointment section
  default: // default action is to view appointments
   // grab patient information
   $ptname = freemed_get_link_rec ($patient, "patient");
   $ptlname = $ptname ["ptlname"];
   $ptfname = $ptname ["ptfname"];
   $ptmname = $ptname ["ptmname"];
   $ptdob   = fm_date_print($ptname["ptdob"]);

   // display top of the box
   freemed_display_box_top ("Manage Appointments");
   echo "
     <P>
     <CENTER>
      <$STDFONT_B>
      <B>Patient:</B>
       $ptlname, $ptfname $ptmname [ $ptdob ] 
      <$STDFONT_E>
     </CENTER>
     <P>
     <CENTER>
      <A HREF=\"book_appointment.php3?$_auth&patient=$patient&type=pat\"
       ><$STDFONT_B>Book Appointment<$STDFONT_E></A> |
      <A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
     </CENTER>
     <P>
    ";

   // form the query
   $query = "SELECT * FROM $database.scheduler WHERE (
             (caldateof  >= '$cur_date' ) AND
             (calpatient =  '$patient'  ) AND
             (caltype    =  'pat'       ) )
             ORDER BY caldateof, calhour, calminute";

   // submit the query
   $result = fdb_query ($query);

   // check for results
   if (fdb_num_rows($result) < 1) {
     echo "
       <P>
       <TABLE WIDTH=100% BGCOLOR=#000000 CELLSPACING=2 CELLPADDING=2
        BORDER=0 VALIGN=CENTER ALIGN=CENTER><TR><TD BGCOLOR=#000000
        ALIGN=CENTER VALIGN=CENTER>
        <$STDFONT_B COLOR=#ffffff>This patient has no appointments.<$STDFONT_E>
       </TD></TR></TABLE>
       <P>
       <CENTER>
       <A HREF=\"book_appointment.php3?$_auth&patient=$patient&type=pat\"
        ><$STDFONT_B>Book Appointment<$STDFONT_E></A> |
       <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>Manage Patient<$STDFONT_E></A>
       </CENTER>
       <P>
      ";
   } else { // if there are results...

     // first display the top of the table
     $_alternate = freemed_bar_alternate_color ($_alternate);
     echo "
       <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 BGCOLOR=#000000
        BORDER=0><TR>
        <TD><$STDFONT_B COLOR=#cccccc>Date<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>Time/Duration<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>Location<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>Note<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>CPT Code<$STDFONT_E></TD> 
        <TD><$STDFONT_B COLOR=#cccccc>$Action<$STDFONT_E></TD> 
       </TR>
      ";

     // loop for all occurances in calendar db
     while ($r = fdb_fetch_array ($result)) {
       $caldateof    = $r["caldateof"   ];
       $calhour      = $r["calhour"     ];
       $calminute    = $r["calminute"   ];
       $calduration  = $r["calduration" ];
       $calphysician = $r["calphysician"];
       $calcptcode   = $r["calcptcode"  ];
       $calroom      = $r["calroom"     ];
       $calfacility  = $r["calfacility" ];
       $calprenote   = htmlentities (stripslashes (
                        substr ($r["calprenote"],0,50) ) );
       if (strlen($r["calprenote"])>50) $calprenote .= "... ";

       // calculate durational hours & minutes
       $hours        = (int) ($calduration / 60);
       $minutes      = (int) ($calduration % 60);
       if ($calminute==0) $calminute="0";

       // put together location name
       if (($calroom != 0) and ($calfacility != 0)) {
        $location = freemed_get_link_field ($calroom, "room", "roomname")." (".
                    freemed_get_link_field ($calfacility, "facility",
                     "psrname").")";
       } elseif (($calroom != 0) and ($calfacility == 0)) {
        $location = freemed_get_link_field ($calroom, "room", "roomname");
       } else { 
        $location = "NONE SPECIFIED";
       } // end of location putting together

       $_alternate = freemed_bar_alternate_color ($_alternate);

       if (($minutes+0)==0)    $minutes="00";   // fix for 0 not 00
       if (($calminute+0)==0)  $calminute="00"; // same as above

       if (($calcptcode+0)==0) $calcptcode="<B>$NONE_SELECTED</B>";

       // actual display
       echo "
        <TR BGCOLOR=$_alternate>
         <TD>$caldateof</TD>
         <TD><CENTER>$calhour:$calminute<BR>
             ($hours h $minutes m)<CENTER></TD>
         <TD>$location</TD>
         <TD>$calprenote</TD>
         <TD>$calcptcode</TD>
         <TD><A HREF=\"$page_name?$_auth&id=$r[id]&action=del&patient=$patient\"
             >$lang_DEL</A></TD>
        </TR>
        ";
     } // end loop for all occurances (while)

     // bottom of the table
     echo "
       </TABLE>
       <P>
       <CENTER>
        <A HREF=\"book_appointment.php3?$_auth&patient=$patient&type=pat\"
         ><$STDFONT_B>Book Appointment<$STDFONT_E></A> |
        <A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
       </CENTER>
       <P>
      ";

   } // end checking for results (if)

   // display_bottom of the box
   freemed_display_box_bottom ();
   break;
 } // end master switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
