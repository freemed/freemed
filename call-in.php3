<?php
 // file: call-in.php3
 // desc: module for call-in patients
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name = "call-in.php3";          // page name
  include ("global.var.inc");           // global variables
  include ("freemed-functions.inc");    // API calls
  $record_name = _("Call In");          // name of record
  $db_name = "callin";                  // database name

freemed_open_db ($LoginCookie);
freemed_display_html_top ();
freemed_display_banner ();

switch ($action) {

 case "addform":
  freemed_display_box_top (_("Add")." "._($record_name));
  if (strlen($citookcall)<1) {
    $f_auth = explode (":", $LoginCookie);
    $citookcall = freemed_get_link_field ($f_auth[0], "user", "userdescrip");
  } // if there wasn't one passed to us...
  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER><TR><TD>

      <!-- form fitting box for both tables -->

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
     VALIGN=TOP ALIGN=CENTER>
    <TR><TD COLSPAN=2 ALIGN=CENTER>
      <B><$STDFONT_B COLOR=#000000>"._("Name")."<$STDFONT_E></B>
    </TD></TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT
     ><$STDFONT_B COLOR=#444444>"._("Last")."<$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cilname\" SIZE=20 MAXLENGTH=50
          VALUE=\"".prepare($cilname)."\"></TD>
    </TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT
     ><$STDFONT_B COLOR=#444444>"._("First")."<$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cifname\" SIZE=20 MAXLENGTH=50
          VALUE=\"".prepare($cifname)."\"></TD>
    </TR>
    <TR>
     <TD WIDTH=30% ALIGN=RIGHT
     ><$STDFONT_B COLOR=#444444>"._("Middle")."<$STDFONT_E></TD>
     <TD><INPUT TYPE=TEXT NAME=\"cimname\" SIZE=20 MAXLENGTH=50
          VALUE=\"$cimname\"></TD>
    </TR>
    </TABLE>

    </TD><TD>

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=TOP
     ALIGN=CENTER BGCOLOR=\"$darker_bgcolor\">
    <TR><TD COLSPAN=2 ALIGN=CENTER>
     <B><$STDFONT_B COLOR=#ffffff>"._("Contact Information")."<$STDFONT_E></B>
    </TD></TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT>
     <$STDFONT_B COLOR=#cccccc>"._("Home Phone")." &nbsp;
      <$STDFONT_E></TD>
     <TD><B>(</B> <INPUT TYPE=TEXT NAME=\"cihphone1\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($cihphone1)."\">
         <B>)</B> <INPUT TYPE=TEXT NAME=\"cihphone2\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($cihphone2)."\">
         <B>-</B> <INPUT TYPE=TEXT NAME=\"cihphone3\" SIZE=5 MAXLENGTH=4
                   VALUE=\"".prepare($cihphone3)."\">
     </TD>
    </TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT>
     <$STDFONT_B COLOR=#cccccc>"._("Work Phone")." &nbsp;
      <$STDFONT_E></TD>
     <TD><B>(</B> <INPUT TYPE=TEXT NAME=\"ciwphone1\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($ciwphone1)."\">
         <B>)</B> <INPUT TYPE=TEXT NAME=\"ciwphone2\" SIZE=4 MAXLENGTH=3
                   VALUE=\"".prepare($ciwphone2)."\">
         <B>-</B> <INPUT TYPE=TEXT NAME=\"ciwphone3\" SIZE=5 MAXLENGTH=4
                   VALUE=\"".prepare($ciwphone3)."\">
     </TD>
    </TR>
    <TR>
     <TD WIDTH=40% ALIGN=RIGHT>
     <$STDFONT_B COLOR=#cccccc>"._("Took Call")." &nbsp;
      <$STDFONT_E></TD>
    <TD>
     <INPUT TYPE=TEXT NAME=\"citookcall\" SIZE=25 MAXLENGTH=50
      VALUE=\"".prepare($citookcall)."\">
    </TD>
    </TR>
    </TABLE>

     <!-- now, end of form fitting table... -->
    </TD></TR></TABLE>

    <P>
    ";
    
    $fac_r = fdb_query("SELECT * FROM facility ORDER BY psrname,psrnote");
    if (!isset($cifacility)) $cifacility=$default_facility; 
      // doesn't seem to hurt, but doesn't seem to do anything...
   
    echo "
    <TABLE WIDTH=100% BORDER=0 ALIGN=CENTER VALIGN=CENTER
     CELLSPACING=0 CELLPADDING=5>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>"._("Date of Birth")."<$STDFONT_E></TD>
      <TD>".fm_date_entry("cidob")."</TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>"._("Complaint")." <$STDFONT_E></TD>
      <TD><TEXTAREA NAME=\"cicomplaint\" ROWS=4 COLS=40
           WRAP=VIRTUAL>".prepare($cicomplaint)."</TEXTAREA>
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>"._("Facility")." <$STDFONT_E></TD>
      <TD>
      ".freemed_display_selectbox ($fac_r, "#psrname# [#psrnote#]", "cifacility")."
      </TD>
     </TR>
     <TR>
      <TD ALIGN=RIGHT><$STDFONT_B>"._("Physician")." <$STDFONT_E></TD>
      <TD>
    ";

    if ($ciphysician < 1) {
      $ciphysician = freemed_get_link_field ($default_facility, "facility",
        "psrdefphy");
    }
    $phys_r = fdb_query("SELECT * FROM physician ORDER BY phylname, phyfname");

    echo "
    ".freemed_display_selectbox($phys_r, "#phylname#, #phyfname#", "ciphysician")."
      </TD>
    </TR>
    </TABLE>
    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" "._("Add")." \"  >
     <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
    </CENTER>
    </FORM>
    <P>
  ";
  freemed_display_box_bottom ();
  break;

 case "add":
  freemed_display_box_top (_("Adding")." "._("$record_name"));
  $cicomplaint = addslashes ($cicomplaint);
  $cicomment   = addslashes ($citookcall);
  $cihphone    = $cihphone1 . $cihphone2 . $cihphone3;
  $ciwphone    = $ciwphone1 . $ciwphone2 . $ciwphone3;
  echo "\n<$STDFONT_B>"._("Adding")." "._("$record_name")." ... \n";
  $query = "INSERT INTO $db_name VALUES (
    '$cilname',
    '$cifname',
    '$cimname',
    '$cihphone',
    '$ciwphone',
    '".fm_date_assemble("cidob")."',
    '$cicomplaint',
    '$cur_date',
    '$default_facility',
    '$ciphysician',
    '$citookcall',
    '0',
    NULL )";
  $result = fdb_query ($query);

  if ($result) echo _("done");
   else echo _("ERROR");
  echo " <$STDFONT_E>
    <P>
    <CENTER>
     <A HREF=\"patient.php3?$_auth\"
      ><$STDFONT_B>$Patient_Menu<$STDFONT_E> |
     <A HREF=\"call-in.php3?$_auth\"
      ><$STDFONT_B>$Call_In_Menu<$STDFONT_E> |
     <A HREF=\"main.php3?$_auth\"
      ><$STDFONT_B>"._("Return to the Main Menu")."<$STDFONT_E>
    </CENTER>
    <P>
  ";
  freemed_display_box_bottom ();
  break;

 case "display":
  freemed_display_box_top (_("$record_name")." "._("View/Manage"));
  $query   = "SELECT * FROM scheduler WHERE
              ((calpatient='$id') AND (caltype='temp'))
              ORDER BY caldateof, calhour, calminute";
  $result  = fdb_query ($query);
  $rows    = fdb_num_rows ($result);
  $ciname  = freemed_get_link_rec ($id, "callin");
  $cilname = $ciname ["cilname"];
  $cifname = $ciname ["cifname"];
  $cimname = $ciname ["cimname"];
  echo "
    <TABLE WIDTH=100% BGCOLOR=#000000 CELLSPACING=0 CELLPADDING=2
     VALIGN=CENTER ALIGN=CENTER>
    <TR><TD ALIGN=CENTER BGCOLOR=#000000>
     <$STDFONT_B COLOR=#ffffff>
      <B>$cilname, $cifname $cimname</B> : $rows "._("Appointments")."
     <$STDFONT_E>
    </TD></TR>
    </TABLE>
    <P>
    <A HREF=\"show_appointments.php3?$_auth&patient=$id&type=temp\"
     ><$STDFONT_B>"._("Show Today's Appointments")."<$STDFONT_E></A>
    <P>
    <A HREF=\"show_appointments.php3?$_auth&patient=$id&type=temp&show=all\"
     ><$STDFONT_B>"._("Show All Appointments")."<$STDFONT_E></A>
    <P>
    <A HREF=\"main.php3?$_auth\"
     ><$STDFONT_B>"._("Return to the Main Menu")."<$STDFONT_E></A>
    </A>
    <P>
  ";
  freemed_display_box_bottom ();
  break;

 default:
  freemed_display_box_top (_("$record_name"));

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&type=old\"
      ><$STDFONT_B>"._("Old")."<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=all\"
      ><$STDFONT_B>"._("All")."<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=cur\"
      ><$STDFONT_B>"._("Current")."<$STDFONT_E></A>
    </CENTER>
    <BR>
  ";

  echo freemed_display_actionbar ($page_name);

  echo "
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 VALIGN=CENTER
     ALIGN=CENTER BGCOLOR=\"".($_alternate=freemed_bar_alternate_color())."\">
    <TR>
     <TD><B>"._("Name")."</B></TD>
     <TD><B>"._("Date of Call")."</B></TD>
     <TD><B>"._("Home/Work Phone")."</B></TD>
     <TD><B>"._("Action")."</B></TD>
    </TR> 
  ";

    // checks to make sure this hasn't been entered yet...
  switch ($type) {
    case "old":          $__type_call_in__ = "cipatient > 0";  break;
    case "all":          $__type_call_in__ = "0 = 0"; break;
    case "cur": default: $__type_call_in__ = "cipatient = 0";  break;
  } // end checking for type...

  $result = fdb_query ("SELECT * FROM $db_name
             WHERE ($__type_call_in__)
             ORDER BY cidatestamp, cilname, cifname, cimname");

  while ($r = fdb_fetch_array ($result)) {
    extract ($r);

    if (freemed_check_access_for_facility ($LoginCookie, $cifacility)) {

    if (strlen($cimname)>0) $ci_comma = ", ";
     else $ci_comma = " ";
    $cihphone_raw = $r["cihphone"];
    if (strlen($cihphone_raw)>6)
      $cihphone = "H: " .
                  substr ($cihphone_raw, 0, 3) . "-" .
                  substr ($cihphone_raw, 3, 3) . "-" .
                  substr ($cihphone_raw, 6, 4);
      else $cihphone = "";
    $ciwphone_raw = $r["ciwphone"]; 
    if (strlen($ciwphone_raw)>6)
      $ciwphone = "W: " .
                  substr ($ciwphone_raw, 0, 3) . "-" .
                  substr ($ciwphone_raw, 3, 3) . "-" .
                  substr ($ciwphone_raw, 6, 4);
      else $ciwphone = "";
    if ((strlen($ciwphone)>0) and (strlen($cihphone)>0))
      $ciphonesep = "<BR>";
    else $ciphonesep = " ";

    echo "
      <TR BGCOLOR=\"".($_alternate=freemed_bar_alternate_color($alternate))."\">
       <TD><$STDFONT_B>$cilname, $cifname$ci_comma $cimname<$STDFONT_E></TD>
       <TD><$STDFONT_B>$cidatestamp<$STDFONT_E></TD>
       <TD><$STDFONT_B>$ciwphone $ciphonesep $cihphone&nbsp;<$STDFONT_E></TD>
       <TD><$STDFONT_B>
    ";

     // display the convert link
    echo "
     <A HREF=\"patient.php3?$_auth&action=addform".
        "&ptfname=".rawurlencode ($cifname).
        "&ptlname=".rawurlencode ($cilname).
        "&ptmname=".rawurlencode ($cimname).
        "&pthphone1=".rawurlencode (substr($cihphone_raw, 0, 3)).
        "&pthphone2=".rawurlencode (substr($cihphone_raw, 3, 3)).
        "&pthphone3=".rawurlencode (substr($cihphone_raw, 6, 4)).
        "&ptwphone1=".rawurlencode (substr($ciwphone_raw, 0, 3)).
        "&ptwphone2=".rawurlencode (substr($ciwphone_raw, 3, 3)).
        "&ptwphone3=".rawurlencode (substr($ciwphone_raw, 6, 4)).
        "&ptdob1=".rawurlencode (substr($cidob, 0, 4)).
        "&ptdob2=".rawurlencode (substr($cidob, 5, 2)).
        "&ptdob3=".rawurlencode (substr($cidob, 8, 2)).
        "&ci="     . $id.
        "\"><FONT SIZE=-1>ENTER</FONT></A> &nbsp;
    ";

      // view link
    echo "
     <A HREF=\"$page_name?$_auth&action=display&id=$id\"
      ><FONT SIZE=-1>"._("VIEW")."</FONT></A> &nbsp;
    ";

     // book link
    echo "
     <A HREF=\"book_appointment.php3?$_auth&action=&".
      "patient=$id&type=temp\"
      ><FONT SIZE=-1>"._("BOOK")."</FONT></A> &nbsp;
    ";

    echo "
        <$STDFONT_E></TD>
      </TR>
    ";

    } // if there was no access for the facility

    $cihphone = "";
    $ciwphone = "";
  } // end while

  echo "
    </TABLE>
  "; // end of the table

  echo freemed_display_actionbar ($page_name);

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&type=old\"
      ><$STDFONT_B>"._("Old")."<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=all\"
      ><$STDFONT_B>"._("All")."<$STDFONT_E></A> |
     <A HREF=\"$page_name?$_auth&type=cur\"
      ><$STDFONT_B>"._("Current")."<$STDFONT_E></A>
    </CENTER>
    <BR>
  ";

  freemed_display_box_bottom ();
  break;

} // end master switch

freemed_display_html_bottom ();
freemed_close_db ();

?>
