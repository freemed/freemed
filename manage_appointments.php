<?php
 // $Id$
 // desc: routines for managing appointments... for patients (not call-ins)
 // lic : GPL, v2

 $page_name = "manage_appointments.php";
 include ("global.var.inc");
 include ("freemed-functions.inc");
 include ("lib/calendar-functions.php");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

 if ($patient<1) {
   freemed_display_box_top ("Manage Appointments :: ERROR");
   echo "
     <P>
     "._("You must select a patient.")."
     <P>
     <CENTER>
      <A HREF=\"patient.php3?$_auth\"
       ><$STDFONT_B>"._("Select a Patient")."<$STDFONT_E></A>
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
   echo "\n<$STDFONT_B>"._("Deleting")." ... <$STDFONT_E>\n";
   $query = "DELETE FROM scheduler WHERE id='".addslashes($id)."'";
   $result = fdb_query ($query);
   if ($result) { echo _("done")."."; }
    else        { echo _("ERROR");    }
   echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view&patient=$patient\"
     ><$STDFONT_B>"._("Manage Appointments")."<$STDFONT_E></A> <B>|</B>
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
    </CENTER>
    <P>
   ";
   freemed_display_box_bottom ();
   break; // end delete appointment section
  default: // default action is to view appointments
   // grab patient information
   $this_patient = new Patient ($patient);

   // display top of the box
   freemed_display_box_top (_("Manage Appointments"));
   echo freemed_patient_box($this_patient)."
     <P>
     <CENTER>
      <A HREF=\"book_appointment.php?$_auth&patient=$patient&type=pat\"
       ><$STDFONT_B>"._("Book Appointment")."<$STDFONT_E></A> |
      <A HREF=\"manage.php3?$_auth&id=$patient\"
       ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
     </CENTER>
     <P>
    ";

   // form the query
   $query = "SELECT * FROM scheduler WHERE (
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
        <$STDFONT_B COLOR=#ffffff>
	"._("This patient has no appointments.")."
	<$STDFONT_E>
       </TD></TR></TABLE>
       <P>
       <CENTER>
       <A HREF=\"book_appointment.php?$_auth&patient=$patient&type=pat\"
        ><$STDFONT_B>"._("Book Appointment")."<$STDFONT_E></A> |
       <A HREF=\"manage.php3?$_auth&id=$patient\"
        ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
       </CENTER>
       <P>
      ";
   } else { // if there are results...

     // first display the top of the table
     $_alternate = freemed_bar_alternate_color ($_alternate);
     echo "
       <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 BGCOLOR=#000000
        BORDER=0><TR>
        <TD><$STDFONT_B COLOR=#cccccc>"._("Date")."<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>Time/Duration<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>Location<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>Note<$STDFONT_E></TD>
        <TD><$STDFONT_B COLOR=#cccccc>CPT Code<$STDFONT_E></TD> 
        <TD><$STDFONT_B COLOR=#cccccc>$Action<$STDFONT_E></TD> 
       </TR>
      ";

     // loop for all occurances in calendar db
     while ($r = fdb_fetch_array ($result)) {
       extract ($r);
       $calprenote   = htmlentities (stripslashes (
                        substr ($r["calprenote"],0,50) ) ).
                       ( (strlen($r["calprenote"])>50) ? "... " : "" );

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
        $location = _("NONE SELECTED");
       } // end of location putting together

       if (($minutes+0)==0)    $minutes="00";   // fix for 0 not 00
       if (($calminute+0)==0)  $calminute="00"; // same as above

       if (($calcptcode+0)==0) $calcptcode="<B>$NONE_SELECTED</B>";

       // actual display
       echo "
        <TR BGCOLOR=\"".($_alternate=freemed_bar_alternate_color($_alternate))."\">
         <TD>$caldateof</TD>
         <TD><CENTER>$calhour:$calminute<BR>
             ($hours h $minutes m)<CENTER></TD>
         <TD>".( !empty($location)   ? $location   : "&nbsp;" )."</TD>
         <TD>".( !empty($calprenote) ? $calprenote : "&nbsp;" )."</TD>
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
        <A HREF=\"book_appointment.php?$_auth&patient=$patient&type=pat\"
         ><$STDFONT_B>"._("Book Appointment")."<$STDFONT_E></A> |
        <A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
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
